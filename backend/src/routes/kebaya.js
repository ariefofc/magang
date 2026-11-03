const express = require('express');
const router = express.Router();
const { auth, checkRole } = require('../middleware/auth');
const KebayaService = require('../services/KebayaService');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

const imgDir = path.join(__dirname, '..', '..', 'public', 'images');
if (!fs.existsSync(imgDir)) fs.mkdirSync(imgDir, { recursive: true });

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, imgDir),
  filename: (req, file, cb) => cb(null, Date.now() + '-' + file.originalname),
});
const upload = multer({ storage });

const svc = new KebayaService();

// Helper: format kebaya for public API
const toPublic = (k) => ({
  slug: k.slug,
  nama_kebaya: k.nama,
  deskripsi: k.deskripsi,
  harga_sewa_per_hari: k.harga,
  foto_url: k.foto ? `/images/${k.foto}` : null,
  foto_belakang_url: k.fotoBelakang ? `/images/${k.fotoBelakang}` : null,
  kain_utama: k.kainUtama,
  furing: k.furing,
  karakteristik_kain: k.karakteristikKain,
  detail_payet: k.detailPayet,
  rekomendasi_acara: k.rekomendasiAcara,
  tersedia: k.status === 'aktif',
  status: k.status,
});

// Helper: format for admin API
const toAdmin = (k) => ({
  id: k.id,
  slug: k.slug,
  nama: k.nama,
  deskripsi: k.deskripsi,
  harga: k.harga,
  foto: k.foto,
  foto_belakang: k.fotoBelakang,
  kain_utama: k.kainUtama,
  furing: k.furing,
  karakteristik_kain: k.karakteristikKain,
  detail_payet: k.detailPayet,
  rekomendasi_acara: k.rekomendasiAcara,
  status: k.status,
  created_at: k.createdAt,
});

// GET /api/kebaya — Public catalog
router.get('/', async (req, res) => {
  try {
    const list = await svc.getPublic();
    res.json({ kebaya: list.map(toPublic) });
  } catch (err) {
    res.json({ kebaya: [] });
  }
});

// GET /api/kebaya/all — Admin: all kebaya
router.get('/all', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const list = await svc.getAll();
    res.json({ kebaya: list.map(toAdmin) });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/kebaya/:slug — Public detail
router.get('/:slug', async (req, res) => {
  try {
    const k = await svc.findBySlug(req.params.slug);
    if (!k) return res.status(404).json({ message: 'Kebaya tidak ditemukan.' });
    res.json(toPublic(k));
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/kebaya — Create kebaya
router.post('/', auth, checkRole('admin', 'super_admin'), upload.fields([
  { name: 'foto', maxCount: 1 },
  { name: 'foto_belakang', maxCount: 1 },
]), async (req, res) => {
  try {
    const data = {
      nama: req.body.nama,
      deskripsi: req.body.deskripsi || null,
      harga: parseInt(req.body.harga) || 0,
      foto: req.files?.foto?.[0]?.filename || null,
      fotoBelakang: req.files?.foto_belakang?.[0]?.filename || null,
      kainUtama: req.body.kain_utama || null,
      furing: req.body.furing || null,
      karakteristikKain: req.body.karakteristik_kain || null,
      detailPayet: req.body.detail_payet || null,
      rekomendasiAcara: req.body.rekomendasi_acara || null,
      status: req.body.status || 'aktif',
    };
    if (!data.nama) return res.status(422).json({ message: 'Nama kebaya wajib diisi.' });
    const k = await svc.create(data);
    res.status(201).json({ message: 'Kebaya berhasil ditambahkan.', kebaya: toAdmin(k) });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/kebaya/:slug — Update kebaya
router.put('/:slug', auth, checkRole('admin', 'super_admin'), upload.fields([
  { name: 'foto', maxCount: 1 },
  { name: 'foto_belakang', maxCount: 1 },
]), async (req, res) => {
  try {
    const existing = await svc.findBySlug(req.params.slug);
    if (!existing) return res.status(404).json({ message: 'Kebaya tidak ditemukan.' });

    const data = {};
    if (req.body.nama) data.nama = req.body.nama;
    if (req.body.deskripsi !== undefined) data.deskripsi = req.body.deskripsi;
    if (req.body.harga) data.harga = parseInt(req.body.harga);
    if (req.body.kain_utama !== undefined) data.kainUtama = req.body.kain_utama;
    if (req.body.furing !== undefined) data.furing = req.body.furing;
    if (req.body.karakteristik_kain !== undefined) data.karakteristikKain = req.body.karakteristik_kain;
    if (req.body.detail_payet !== undefined) data.detailPayet = req.body.detail_payet;
    if (req.body.rekomendasi_acara !== undefined) data.rekomendasiAcara = req.body.rekomendasi_acara;
    if (req.body.status) data.status = req.body.status;

    if (req.files?.foto?.[0]) {
      data.foto = req.files.foto[0].filename;
      // Delete old image
      if (existing.foto) {
        const old = path.join(imgDir, existing.foto);
        if (fs.existsSync(old)) fs.unlinkSync(old);
      }
    }
    if (req.files?.foto_belakang?.[0]) {
      data.fotoBelakang = req.files.foto_belakang[0].filename;
      if (existing.fotoBelakang) {
        const old = path.join(imgDir, existing.fotoBelakang);
        if (fs.existsSync(old)) fs.unlinkSync(old);
      }
    }

    const k = await svc.update(req.params.slug, data);
    res.json({ message: 'Kebaya berhasil diperbarui.', kebaya: toAdmin(k) });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// DELETE /api/kebaya/:slug
router.delete('/:slug', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const existing = await svc.findBySlug(req.params.slug);
    if (!existing) return res.status(404).json({ message: 'Kebaya tidak ditemukan.' });

    // Delete images
    if (existing.foto) { const p = path.join(imgDir, existing.foto); if (fs.existsSync(p)) fs.unlinkSync(p); }
    if (existing.fotoBelakang) { const p = path.join(imgDir, existing.fotoBelakang); if (fs.existsSync(p)) fs.unlinkSync(p); }

    await svc.delete(req.params.slug);
    res.json({ message: 'Kebaya berhasil dihapus.' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/kebaya/:slug/toggle
router.post('/:slug/toggle', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const newStatus = await svc.cycleStatus(req.params.slug);
    if (!newStatus) return res.status(404).json({ message: 'Kebaya tidak ditemukan.' });
    res.json({ slug: req.params.slug, status: newStatus });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/kebaya/:slug/status
router.post('/:slug/status', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const k = await svc.setStatus(req.params.slug, req.body.status);
    if (!k) return res.status(400).json({ message: 'Status tidak valid.' });
    res.json({ slug: req.params.slug, status: req.body.status });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;
