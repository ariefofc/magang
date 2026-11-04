const express = require('express');
const router = express.Router();
const prisma = require('../prisma');
const { auth, checkRole } = require('../middleware/auth');
const crypto = require('crypto');
const path = require('path');
const fs = require('fs');
const PDFDocument = require('pdfkit');

// ═══════════════════════════════════════════════════════
//  HELPER: Build filtered query
// ═══════════════════════════════════════════════════════
function buildWhereClause(query) {
  const where = {};
  const { start_date, end_date, year, month, week, status } = query;

  if (start_date && end_date) {
    where.createdAt = {
      gte: new Date(start_date + 'T00:00:00'),
      lte: new Date(end_date + 'T23:59:59'),
    };
  } else {
    if (year) {
      where.createdAt = {
        ...where.createdAt,
        gte: new Date(`${year}-01-01T00:00:00`),
        lte: new Date(`${year}-12-31T23:59:59`),
      };
    }
    if (month && year) {
      const m = parseInt(month);
      const startDate = new Date(year, m - 1, 1);
      const endDate = new Date(year, m, 0, 23, 59, 59);
      where.createdAt = { gte: startDate, lte: endDate };

      if (week) {
        const w = parseInt(week);
        const dayRanges = { 1: [1, 7], 2: [8, 14], 3: [15, 21], 4: [22, 31] };
        const [dayStart, dayEnd] = dayRanges[w] || [1, 31];
        where.createdAt = {
          gte: new Date(year, m - 1, dayStart),
          lte: new Date(year, m - 1, Math.min(dayEnd, endDate.getDate()), 23, 59, 59),
        };
      }
    }
  }

  if (status) {
    where.status = status;
  }

  return where;
}

function getSortOrder(query) {
  const order = (query.sort_order || 'asc').toLowerCase();
  return ['asc', 'desc'].includes(order) ? order : 'asc';
}

// ═══════════════════════════════════════════════════════
//  HELPER: Periode Label
// ═══════════════════════════════════════════════════════
function getPeriodeLabel(query) {
  const months = {
    1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
    5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
    9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember',
  };

  if (query.start_date && query.end_date) {
    return `${query.start_date} s/d ${query.end_date}`;
  }

  const parts = [];
  if (query.month && months[parseInt(query.month)]) parts.push(months[parseInt(query.month)]);
  if (query.year) parts.push(query.year);

  let prefix = parts.join(' ') || 'Seluruh Transaksi (2026)';

  if (query.week) {
    const weekLabels = {
      1: 'Minggu 1 (Tgl 1 - 7)', 2: 'Minggu 2 (Tgl 8 - 14)',
      3: 'Minggu 3 (Tgl 15 - 21)', 4: 'Minggu 4 (Tgl 22 - Akhir Bulan)',
    };
    prefix += ' — ' + (weekLabels[parseInt(query.week)] || `Minggu ${query.week}`);
  }

  return prefix;
}

// ═══════════════════════════════════════════════════════
//  HELPER: Nominal DP
// ═══════════════════════════════════════════════════════
function nominalDP(transaksi) {
  const skema = (transaksi.skemaBayar || 'dp 50%').toLowerCase();
  let pct = 0.5;
  if (skema.includes('70')) pct = 0.7;
  else if (skema.includes('100') || skema.includes('lunas')) pct = 1.0;
  return Math.ceil(Number(transaksi.totalHarga) * pct);
}

function labelSkema(transaksi) {
  const skema = (transaksi.skemaBayar || '').toLowerCase();
  if (skema.includes('70')) return 'DP 70%';
  if (skema.includes('100') || skema.includes('lunas')) return 'Lunas 100%';
  return 'DP 50%';
}

// ═══════════════════════════════════════════════════════
//  ROUTES
// ═══════════════════════════════════════════════════════

// GET /api/transaksi/dashboard - Admin Dashboard Data
router.get('/dashboard', auth, async (req, res) => {
  try {
    const where = buildWhereClause(req.query);
    const sortOrder = getSortOrder(req.query);
    const page = parseInt(req.query.page) || 1;
    const perPage = 15;

    const [transaksi, total] = await Promise.all([
      prisma.transaksiSewa.findMany({
        where,
        orderBy: [{ createdAt: sortOrder }, { id: sortOrder }],
        skip: (page - 1) * perPage,
        take: perPage,
      }),
      prisma.transaksiSewa.count({ where }),
    ]);

    const allTransaksi = await prisma.transaksiSewa.findMany({
      where,
      orderBy: [{ createdAt: sortOrder }, { id: sortOrder }],
    });

    const totalPendapatan = allTransaksi.reduce((sum, t) => sum + Number(t.totalHarga), 0);
    const periodeLabel = getPeriodeLabel(req.query);

    res.json({
      daftarTransaksi: transaksi,
      pagination: { page, perPage, total, totalPages: Math.ceil(total / perPage) },
      allLaporanTransaksi: allTransaksi,
      totalPendapatan,
      kebayaDipesan: allTransaksi.length,
      totalKoleksi: 13,
      periodeLabel,
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/transaksi - Store new transaction
router.post('/', async (req, res) => {
  try {
    const {
      nama_kebaya, warna_custom_ai, gambar_ai_result, nama_user, no_wa,
      ukuran, metode_pembayaran, skema_pembayaran, alamat,
      tanggal_sewa, tanggal_pengembalian, total_harga, sudah_bayar,
    } = req.body;

    const namaCust = (nama_user || req.body.nama || '').trim();
    let kebayaName = (nama_kebaya || req.body.nama_kebaya || '').trim();
    if (!kebayaName && req.body.kebaya_id) {
      const k = await prisma.kebaya.findFirst({
        where: {
          OR: [
            { slug: String(req.body.kebaya_id) },
            ...(Number.isInteger(Number(req.body.kebaya_id)) ? [{ id: Number(req.body.kebaya_id) }] : []),
          ],
        },
      });
      if (k) kebayaName = k.nama;
    }

    // Validate
    if (!kebayaName || !namaCust || !no_wa || !ukuran || !metode_pembayaran || !tanggal_sewa || !tanggal_pengembalian) {
      return res.status(422).json({ message: 'Field wajib belum lengkap.' });
    }

    // Double booking check
    const overlap = await prisma.transaksiSewa.findFirst({
      where: {
        namaKebaya: kebayaName,
        status: { notIn: ['Dibatalkan', 'Telah Dikembalikan'] },
        OR: [
          { tanggalSewa: { gte: new Date(tanggal_sewa), lte: new Date(tanggal_pengembalian) } },
          { tanggalPengembalian: { gte: new Date(tanggal_sewa), lte: new Date(tanggal_pengembalian) } },
          {
            AND: [
              { tanggalSewa: { lte: new Date(tanggal_sewa) } },
              { tanggalPengembalian: { gte: new Date(tanggal_pengembalian) } },
            ],
          },
        ],
      },
    });

    if (overlap) {
      return res.status(422).json({
        message: `Kebaya "${kebayaName}" sudah TERKUNCI pada rentang tanggal tersebut.`,
      });
    }

    const tSewa = new Date(tanggal_sewa);
    const tKembali = new Date(tanggal_pengembalian);
    const durasi = Math.max(1, Math.ceil((tKembali - tSewa) / (1000 * 60 * 60 * 24)));
    const isBayar = ['1', 'true', true].includes(sudah_bayar);

    // Calculate proper total price if not provided
    let calculatedTotal = total_harga ? Number(total_harga) : null;
    if (!calculatedTotal) {
      const k = await prisma.kebaya.findFirst({ where: { nama: kebayaName } });
      calculatedTotal = k ? k.harga * durasi : 750000;
    }

    // Handle base64 image
    let savedImage = gambar_ai_result;
    if (gambar_ai_result && gambar_ai_result.startsWith('data:image')) {
      try {
        const base64Data = gambar_ai_result.replace(/^data:image\/\w+;base64,/, '');
        const uploadDir = path.join(__dirname, '..', '..', 'public', 'uploads', 'recolor');
        if (!fs.existsSync(uploadDir)) fs.mkdirSync(uploadDir, { recursive: true });
        const filename = `recolor_${Date.now()}_${Math.floor(Math.random() * 9000) + 1000}.jpg`;
        fs.writeFileSync(path.join(uploadDir, filename), Buffer.from(base64Data, 'base64'));
        savedImage = `uploads/recolor/${filename}`;
      } catch { savedImage = null; }
    }

    const kode = 'TRX-' + crypto.createHash('md5').update(String(Date.now() + Math.random())).digest('hex').substring(0, 8).toUpperCase();

    const transaksi = await prisma.transaksiSewa.create({
      data: {
        kodeTransaksi: kode,
        namaKebaya: kebayaName,
        warnaCustomAi: warna_custom_ai || 'Default',
        gambarAiResult: savedImage,
        namaUser: namaCust,
        noWa: no_wa,
        ukuran,
        metodePembayaran: metode_pembayaran,
        skemaBayar: skema_pembayaran || 'DP 50%',
        alamat,
        tanggalSewa: tSewa,
        tanggalPengembalian: tKembali,
        durasiHari: durasi,
        status: isBayar ? 'Menunggu Konfirmasi' : 'Menunggu Pembayaran DP',
        totalHarga: calculatedTotal,
        sudahBayar: isBayar,
      },
    });

    res.status(201).json({ id: transaksi.id, transaksi, redirect: `/payment/${transaksi.id}` });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/transaksi/booked-dates
router.get('/booked-dates', async (req, res) => {
  const namaKebaya = req.query.nama;
  if (!namaKebaya) {
    return res.json({ nama_kebaya: '', booked_ranges: [], disabled_dates: [] });
  }

  const transaksi = await prisma.transaksiSewa.findMany({
    where: {
      namaKebaya,
      status: { notIn: ['Dibatalkan', 'Telah Dikembalikan'] },
    },
  });

  const bookedRanges = [];
  const disabledDates = new Set();

  for (const t of transaksi) {
    if (t.tanggalSewa && t.tanggalPengembalian) {
      const start = new Date(t.tanggalSewa);
      const end = new Date(t.tanggalPengembalian);

      bookedRanges.push({
        start: start.toISOString().split('T')[0],
        end: end.toISOString().split('T')[0],
        status: t.status,
        kode: t.kodeTransaksi,
        user: t.namaUser ? t.namaUser.substring(0, 3) + '***' : 'Pelanggan',
      });

      const curr = new Date(start);
      while (curr <= end) {
        disabledDates.add(curr.toISOString().split('T')[0]);
        curr.setDate(curr.getDate() + 1);
      }
    }
  }

  res.json({
    nama_kebaya: namaKebaya,
    booked_ranges: bookedRanges,
    disabled_dates: [...disabledDates].sort(),
  });
});

// GET /api/transaksi/nota/:id
router.get('/nota/:id', async (req, res) => {
  try {
    const transaksi = await prisma.transaksiSewa.findUnique({
      where: { id: parseInt(req.params.id) },
    });
    if (!transaksi) return res.status(404).json({ message: 'Transaksi tidak ditemukan.' });

    const skema = (transaksi.skemaBayar || 'dp 50%').toLowerCase();
    let pct = 0.5;
    if (skema.includes('70')) pct = 0.7;
    else if (skema.includes('100') || skema.includes('lunas')) pct = 1.0;
    const nominal_dp = Math.ceil(Number(transaksi.totalHarga) * pct);
    const sisa = Math.max(0, Number(transaksi.totalHarga) - (transaksi.paymentStatus === 'paid' ? Number(transaksi.jumlahDibayar || nominal_dp) : 0));

    res.json({
      ...transaksi,
      transaksi,
      kode_transaksi: transaksi.kodeTransaksi,
      kebaya_name: transaksi.namaKebaya,
      nama_pelanggan: transaksi.namaUser,
      warna_custom: transaksi.warnaCustomAi !== 'Default' ? transaksi.warnaCustomAi : null,
      gambar_ai: transaksi.gambarAiResult ? (transaksi.gambarAiResult.startsWith('data:') ? transaksi.gambarAiResult : `/${transaksi.gambarAiResult}`) : null,
      ukuran: transaksi.ukuran,
      no_wa: transaksi.noWa,
      alamat: transaksi.alamat,
      tanggal_sewa: transaksi.tanggalSewa ? new Date(transaksi.tanggalSewa).toISOString().slice(0, 10) : '',
      tanggal_pengembalian: transaksi.tanggalPengembalian ? new Date(transaksi.tanggalPengembalian).toISOString().slice(0, 10) : '',
      durasi: transaksi.durasiHari,
      total_harga: Number(transaksi.totalHarga),
      skema: transaksi.skemaBayar,
      nominal_dp,
      sisa_pelunasan: sisa,
      status: transaksi.status,
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/transaksi/:id/ajukan-pengembalian
router.post('/:id/ajukan-pengembalian', async (req, res) => {
  try {
    const transaksi = await prisma.transaksiSewa.update({
      where: { id: parseInt(req.params.id) },
      data: { status: 'Mengajukan Pengembalian' },
    });
    res.json({ message: 'Pengajuan pengembalian berhasil dikirim!', transaksi });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/transaksi/:id/ajukan-fitting
router.post('/:id/ajukan-fitting', async (req, res) => {
  try {
    const { tanggal_fitting, jam_fitting, lokasi_studio, catatan } = req.body;
    const note = `Request Fitting Customer: Tanggal ${tanggal_fitting} Jam ${jam_fitting} di ${lokasi_studio}` +
      (catatan ? ` | Catatan: ${catatan}` : '');

    const transaksi = await prisma.transaksiSewa.update({
      where: { id: parseInt(req.params.id) },
      data: { status: 'Menunggu Fitting', catatanAdmin: note },
    });
    res.json({ message: 'Pengajuan fitting berhasil dikirim!', transaksi });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/transaksi/:id/status (Admin)
router.post('/:id/status', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const data = { status: req.body.status };
    if (req.body.catatan_admin) data.catatanAdmin = req.body.catatan_admin;

    const transaksi = await prisma.transaksiSewa.update({
      where: { id: parseInt(req.params.id) },
      data,
    });
    res.json({ message: 'Status berhasil diperbarui!', transaksi });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/transaksi/:id (Admin)
router.put('/:id', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const data = { status: req.body.status };
    if (req.body.catatan_admin) data.catatanAdmin = req.body.catatan_admin;

    const transaksi = await prisma.transaksiSewa.update({
      where: { id: parseInt(req.params.id) },
      data,
    });
    res.json({ message: 'Transaksi berhasil diperbarui!', transaksi });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// DELETE /api/transaksi/:id (Admin)
router.delete('/:id', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    await prisma.transaksiSewa.delete({ where: { id: parseInt(req.params.id) } });
    res.json({ message: 'Data berhasil dihapus.' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/transaksi/laporan/pdf
router.get('/laporan/pdf', auth, async (req, res) => {
  try {
    const where = buildWhereClause(req.query);
    const sortOrder = getSortOrder(req.query);
    const allTransaksi = await prisma.transaksiSewa.findMany({
      where,
      orderBy: [{ createdAt: sortOrder }],
    });

    const totalPendapatan = allTransaksi.reduce((s, t) => s + Number(t.totalHarga), 0);
    const periodeLabel = getPeriodeLabel(req.query);

    const doc = new PDFDocument({ layout: 'landscape', size: 'A4', margin: 40 });
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader('Content-Disposition', `inline; filename=Laporan-MSHarmony-${Date.now()}.pdf`);
    doc.pipe(res);

    // Header
    doc.fontSize(18).text('LAPORAN PENJUALAN - MS HARMONY', { align: 'center' });
    doc.fontSize(10).text(`Periode: ${periodeLabel}`, { align: 'center' });
    doc.moveDown();

    // Summary
    doc.fontSize(10)
      .text(`Total Transaksi: ${allTransaksi.length}`)
      .text(`Total Pendapatan: Rp ${totalPendapatan.toLocaleString('id-ID')}`)
      .moveDown();

    // Table header
    const tableTop = doc.y;
    const colWidths = [30, 80, 120, 100, 70, 80, 100, 100];
    const headers = ['No', 'Kode', 'Kebaya', 'Customer', 'Ukuran', 'Harga', 'Status', 'Tanggal'];

    doc.fontSize(8).font('Helvetica-Bold');
    let x = 40;
    headers.forEach((h, i) => {
      doc.text(h, x, tableTop, { width: colWidths[i] });
      x += colWidths[i];
    });

    doc.font('Helvetica').fontSize(7);
    let y = tableTop + 15;

    allTransaksi.forEach((t, idx) => {
      if (y > 540) { doc.addPage(); y = 40; }
      x = 40;
      const row = [
        idx + 1,
        t.kodeTransaksi,
        t.namaKebaya,
        t.namaUser,
        t.ukuran,
        `Rp ${Number(t.totalHarga).toLocaleString('id-ID')}`,
        t.status,
        t.createdAt ? new Date(t.createdAt).toLocaleDateString('id-ID') : '-',
      ];
      row.forEach((val, i) => {
        doc.text(String(val), x, y, { width: colWidths[i] });
        x += colWidths[i];
      });
      y += 12;
    });

    doc.end();
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;
