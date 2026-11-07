/**
 * KebayaService - Prisma-based (migrated from hardcoded + JSON)
 */
const prisma = require('../prisma');

const ACTIVE_RENTAL_STATUSES = [
  'Menunggu Pembayaran DP',
  'Menunggu Konfirmasi',
  'Telah Dikonfirmasi',
  'Menunggu Fitting',
  'Sedang Fitting',
  'Kebaya Telah Diambil',
  'Mengajukan Pengembalian',
];

class KebayaService {
  async enrichWithStock(kebayaList) {
    if (!kebayaList || kebayaList.length === 0) return [];

    const now = new Date();
    const todayStr = now.toISOString().slice(0, 10);
    const tomorrow = new Date(now.getTime() + 86400000);
    const tomorrowStr = tomorrow.toISOString().slice(0, 10);

    const todayStart = new Date(todayStr + 'T00:00:00.000Z');
    const tomorrowEnd = new Date(tomorrowStr + 'T23:59:59.999Z');

    // Rentals active on today or tomorrow
    const activeRentals = await prisma.transaksiSewa.findMany({
      where: {
        status: { in: ACTIVE_RENTAL_STATUSES },
        tanggalSewa: { lte: tomorrowEnd },
        tanggalPengembalian: { gte: todayStart },
      },
      select: {
        namaKebaya: true,
        tanggalSewa: true,
        tanggalPengembalian: true,
      },
    });

    const bookedTodayMap = {};
    const bookedTomorrowMap = {};

    activeRentals.forEach((r) => {
      const sewaStr = r.tanggalSewa ? r.tanggalSewa.toISOString().slice(0, 10) : '';
      const kembaliStr = r.tanggalPengembalian ? r.tanggalPengembalian.toISOString().slice(0, 10) : '';

      if (sewaStr <= todayStr && kembaliStr >= todayStr) {
        bookedTodayMap[r.namaKebaya] = (bookedTodayMap[r.namaKebaya] || 0) + 1;
      }
      if (sewaStr <= tomorrowStr && kembaliStr >= tomorrowStr) {
        bookedTomorrowMap[r.namaKebaya] = (bookedTomorrowMap[r.namaKebaya] || 0) + 1;
      }
    });

    return kebayaList.map((k) => {
      const totalStok = k.stok ?? 1;
      const bookedToday = bookedTodayMap[k.nama] || 0;
      const bookedTomorrow = bookedTomorrowMap[k.nama] || 0;

      const stokHariIni = Math.max(0, totalStok - bookedToday);
      const stokBesok = Math.max(0, totalStok - bookedTomorrow);
      const isFullHariIni = bookedToday >= totalStok;
      const isFullBesok = bookedTomorrow >= totalStok;

      const stokTersedia = stokHariIni;
      const stokDisewa = bookedToday;

      let displayStatus = k.status;
      let tersedia = k.status === 'aktif';
      if (k.status === 'aktif' && isFullHariIni) {
        displayStatus = 'disewa';
        tersedia = false;
      }

      return {
        ...k,
        stok: totalStok,
        stokDisewa,
        stokTersedia,
        stokHariIni,
        stokBesok,
        isFullHariIni,
        isFullBesok,
        tersediaHariIni: k.status === 'aktif' && !isFullHariIni,
        tersediaBesok: k.status === 'aktif' && !isFullBesok,
        tersedia,
        displayStatus,
      };
    });
  }

  async getAll() {
    const list = await prisma.kebaya.findMany({ orderBy: { id: 'asc' } });
    return this.enrichWithStock(list);
  }

  async getPublic() {
    const list = await prisma.kebaya.findMany({
      where: { status: { in: ['aktif', 'segera'] } },
      orderBy: { id: 'asc' },
    });
    return this.enrichWithStock(list);
  }

  async findBySlug(slug) {
    const k = await prisma.kebaya.findUnique({ where: { slug } });
    if (!k) return null;
    const [enriched] = await this.enrichWithStock([k]);
    return enriched;
  }

  async create(data) {
    const slug = (data.nama || 'kebaya-' + Date.now())
      .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    const created = await prisma.kebaya.create({ data: { ...data, slug } });
    const [enriched] = await this.enrichWithStock([created]);
    return enriched;
  }

  async update(slug, data) {
    // If nama changed, regenerate slug
    if (data.nama) {
      data.slug = data.nama.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
    const updated = await prisma.kebaya.update({ where: { slug }, data });
    const [enriched] = await this.enrichWithStock([updated]);
    return enriched;
  }

  async delete(slug) {
    return prisma.kebaya.delete({ where: { slug } });
  }

  async setStatus(slug, status, { createdById, catatan } = {}) {
    const allowed = ['aktif', 'nonaktif', 'perbaikan', 'segera'];
    if (!allowed.includes(status)) return null;

    const existing = await prisma.kebaya.findUnique({ where: { slug } });
    if (!existing) return null;

    const updated = await prisma.kebaya.update({ where: { slug }, data: { status } });

    if (existing.status !== status) {
      await prisma.kebayaAvailabilityHistory.create({
        data: {
          kebayaId: existing.id,
          statusLama: existing.status,
          statusBaru: status,
          catatan: catatan || null,
          createdById: createdById || null,
        },
      });
    }

    return updated;
  }

  async cycleStatus(slug, { createdById, catatan } = {}) {
    const k = await prisma.kebaya.findUnique({ where: { slug } });
    if (!k) return null;
    const next = { aktif: 'nonaktif', nonaktif: 'perbaikan', perbaikan: 'aktif', segera: 'aktif' }[k.status] || 'aktif';

    await prisma.kebaya.update({ where: { slug }, data: { status: next } });

    await prisma.kebayaAvailabilityHistory.create({
      data: {
        kebayaId: k.id,
        statusLama: k.status,
        statusBaru: next,
        catatan: catatan || null,
        createdById: createdById || null,
      },
    });

    return next;
  }

  async getAvailabilityCalendar(slug, targetYear, targetMonth) {
    const kebaya = await prisma.kebaya.findUnique({ where: { slug } });
    if (!kebaya) return null;

    const now = new Date();
    const y = parseInt(targetYear) || now.getFullYear();
    const m = parseInt(targetMonth) || (now.getMonth() + 1);

    const startDate = new Date(Date.UTC(y, m - 1, 1));
    const endDate = new Date(Date.UTC(y, m, 0, 23, 59, 59));

    const activeRentals = await prisma.transaksiSewa.findMany({
      where: {
        namaKebaya: kebaya.nama,
        status: { notIn: ['Dibatalkan', 'Telah Dikembalikan'] },
        tanggalSewa: { lte: endDate },
        tanggalPengembalian: { gte: startDate },
      },
      select: {
        kodeTransaksi: true,
        tanggalSewa: true,
        tanggalPengembalian: true,
        status: true,
        namaUser: true,
        noWa: true,
        ukuran: true,
      },
    });

    const totalStock = kebaya.stok ?? 1;
    const isKebayaActive = kebaya.status === 'aktif';
    const daysInMonth = new Date(y, m, 0).getDate();
    const calendarDays = {};

    for (let day = 1; day <= daysInMonth; day++) {
      const dayStr = `${y}-${String(m).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      let bookedOnDay = 0;

      activeRentals.forEach((r) => {
        const sewaStr = r.tanggalSewa ? r.tanggalSewa.toISOString().slice(0, 10) : '';
        const kembaliStr = r.tanggalPengembalian ? r.tanggalPengembalian.toISOString().slice(0, 10) : '';
        if (sewaStr <= dayStr && kembaliStr >= dayStr) {
          bookedOnDay++;
        }
      });

      const availableSlots = Math.max(0, totalStock - bookedOnDay);
      const isFull = !isKebayaActive || bookedOnDay >= totalStock;

      let statusLabel = 'available';
      if (!isKebayaActive) {
        statusLabel = 'disabled';
      } else if (isFull) {
        statusLabel = 'full';
      } else if (availableSlots === 1 && totalStock > 1) {
        statusLabel = 'limited';
      }

      calendarDays[dayStr] = {
        date: dayStr,
        day,
        totalStock,
        bookedCount: bookedOnDay,
        availableSlots,
        isFull,
        status: statusLabel,
      };
    }

    return {
      slug: kebaya.slug,
      nama: kebaya.nama,
      totalStock,
      kebayaStatus: kebaya.status,
      year: y,
      month: m,
      days: calendarDays,
      bookedRanges: activeRentals.map((r) => ({
        kode: r.kodeTransaksi,
        start: r.tanggalSewa ? r.tanggalSewa.toISOString().slice(0, 10) : null,
        end: r.tanggalPengembalian ? r.tanggalPengembalian.toISOString().slice(0, 10) : null,
        status: r.status,
        user: r.namaUser || 'Pelanggan',
        noWa: r.noWa || '',
        ukuran: r.ukuran || 'M',
      })),
    };
  }

  async checkRangeAvailability(slug, startStr, endStr) {
    const kebaya = await prisma.kebaya.findUnique({ where: { slug } });
    if (!kebaya) return null;

    const totalStock = kebaya.stok ?? 1;
    const isKebayaActive = kebaya.status === 'aktif';

    const now = new Date();
    const todayStr = now.toISOString().slice(0, 10);
    const s = startStr || todayStr;
    const e = endStr || s;
    const validStart = s <= e ? s : e;
    const validEnd = s <= e ? e : s;

    const startDate = new Date(validStart + 'T00:00:00.000Z');
    const endDate = new Date(validEnd + 'T23:59:59.999Z');

    const activeRentals = await prisma.transaksiSewa.findMany({
      where: {
        namaKebaya: kebaya.nama,
        status: { notIn: ['Dibatalkan', 'Telah Dikembalikan'] },
        tanggalSewa: { lte: endDate },
        tanggalPengembalian: { gte: startDate },
      },
      select: {
        kodeTransaksi: true,
        tanggalSewa: true,
        tanggalPengembalian: true,
        status: true,
      },
    });

    const fullDates = [];
    const dailyDetails = {};
    let minAvailable = totalStock;
    let maxBooked = 0;

    let curr = new Date(validStart + 'T00:00:00.000Z');
    const endBound = new Date(validEnd + 'T00:00:00.000Z');

    while (curr <= endBound) {
      const dStr = curr.toISOString().slice(0, 10);
      let bookedOnDay = 0;

      activeRentals.forEach((r) => {
        const sewa = r.tanggalSewa ? r.tanggalSewa.toISOString().slice(0, 10) : '';
        const kembali = r.tanggalPengembalian ? r.tanggalPengembalian.toISOString().slice(0, 10) : '';
        if (sewa <= dStr && kembali >= dStr) {
          bookedOnDay++;
        }
      });

      const availableSlots = Math.max(0, totalStock - bookedOnDay);
      const isFull = !isKebayaActive || bookedOnDay >= totalStock;

      if (isFull) fullDates.push(dStr);
      if (availableSlots < minAvailable) minAvailable = availableSlots;
      if (bookedOnDay > maxBooked) maxBooked = bookedOnDay;

      dailyDetails[dStr] = {
        date: dStr,
        totalStock,
        bookedCount: bookedOnDay,
        availableSlots,
        isFull,
      };

      curr.setUTCDate(curr.getUTCDate() + 1);
    }

    const isFull = !isKebayaActive || minAvailable <= 0;
    const isAvailable = isKebayaActive && minAvailable > 0;

    return {
      slug: kebaya.slug,
      nama: kebaya.nama,
      totalStock,
      kebayaStatus: kebaya.status,
      startDate: validStart,
      endDate: validEnd,
      minAvailableSlots: minAvailable,
      maxBooked,
      isFull,
      isAvailable,
      fullDates,
      status: !isKebayaActive ? 'disabled' : isFull ? 'full_booked' : (minAvailable === 1 && totalStock > 1 ? 'limited' : 'available'),
      dailyDetails,
    };
  }
}

module.exports = KebayaService;
