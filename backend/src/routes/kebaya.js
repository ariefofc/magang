const express = require('express');
const router = express.Router();
const { auth, checkRole } = require('../middleware/auth');
const KebayaService = require('../services/KebayaService');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

// File upload config
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dir = path.join(__dirname, '..', '..', 'public', 'images');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + '-' + file.originalname);
  },
});
const upload = multer({ storage });

// GET /api/kebaya - Public catalog
router.get('/', (req, res) => {
  try {
    const service = new KebayaService();
    const kebaya = service.getPublic().map(k => ({
      slug: k.slug || '',
      nama_kebaya: k.nama,
      deskripsi: k.deskripsi,
      harga_sewa_per_hari: k.harga,
      foto_url: k.foto ? `/images/${k.foto}` : null,
      foto_belakang_url: k.foto_belakang ? `/images/${k.foto_belakang}` : null,
      kain_utama: k.kain_utama || 'Brokat / Katun Premium',
      furing: k.furing || 'Furing Katun Hero Adem',
      karakteristik_kain: k.karakteristik_kain || 'Soft, halus, dan nyaman dipakai',
      detail_payet: k.detail_payet || 'Payet & Bordir Eksklusif',
      rekomendasi_acara: k.rekomendasi_acara || 'Wisuda & Resepsi',
      tersedia: k.status === 'aktif',
      status: k.status,
    }));
    res.json({ kebaya });
  } catch (err) {
    res.json({ kebaya: [] });
  }
});

// GET /api/kebaya/all - Admin: all kebaya with status
router.get('/all', auth, checkRole('admin', 'super_admin'), (req, res) => {
  try {
    const service = new KebayaService();
    res.json({ kebaya: service.getAll() });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/kebaya/:slug - Public detail
router.get('/:slug', (req, res) => {
  try {
    const service = new KebayaService();
    const all = service.getPublic();
    const k = all.find(x => x.slug === req.params.slug);
    if (!k) return res.status(404).json({ message: 'Kebaya tidak ditemukan.' });
    res.json({
      slug: k.slug,
      nama_kebaya: k.nama,
      deskripsi: k.deskripsi,
      harga_sewa_per_hari: k.harga,
      foto_url: k.foto ? `/images/${k.foto}` : null,
      foto_belakang_url: k.foto_belakang ? `/images/${k.foto_belakang}` : null,
      kain_utama: k.kain_utama || null,
      furing: k.furing || null,
      karakteristik_kain: k.karakteristik_kain || null,
      detail_payet: k.detail_payet || null,
      rekomendasi_acara: k.rekomendasi_acara || null,
      tersedia: k.status === 'aktif',
      status: k.status,
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/kebaya/:slug/toggle
router.post('/:slug/toggle', auth, checkRole('admin', 'super_admin'), (req, res) => {
  try {
    const service = new KebayaService();
    const newStatus = service.cycleStatus(req.params.slug);
    res.json({ slug: req.params.slug, status: newStatus });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/kebaya/:slug/status
router.post('/:slug/status', auth, checkRole('admin', 'super_admin'), (req, res) => {
  try {
    const service = new KebayaService();
    const ok = service.setStatus(req.params.slug, req.body.status);
    if (!ok) return res.status(400).json({ message: 'Status tidak valid.' });
    res.json({ slug: req.params.slug, status: req.body.status });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/kebaya/:slug/hide
router.post('/:slug/hide', auth, checkRole('admin', 'super_admin'), (req, res) => {
  try {
    const service = new KebayaService();
    service.hide(req.params.slug);
    res.json({ slug: req.params.slug, status: 'nonaktif' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/kebaya/reset-all
router.post('/reset-all', auth, checkRole('admin', 'super_admin'), (req, res) => {
  try {
    const service = new KebayaService();
    service.resetAll();
    res.json({ message: 'Semua status kebaya direset.' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/kebaya - Create new custom kebaya
router.post('/', auth, checkRole('admin', 'super_admin'), upload.fields([
  { name: 'foto', maxCount: 1 },
  { name: 'foto_belakang', maxCount: 1 },
]), (req, res) => {
  try {
    const service = new KebayaService();
    const data = {
      ...req.body,
      harga: parseInt(req.body.harga) || 0,
      foto: req.files?.foto?.[0]?.filename || null,
      foto_belakang: req.files?.foto_belakang?.[0]?.filename || null,
    };
    service.addCustom(data);
    res.status(201).json({ message: 'Kebaya berhasil ditambahkan.', data });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/kebaya/:slug/update
router.put('/:slug/update', auth, checkRole('admin', 'super_admin'), upload.fields([
  { name: 'foto', maxCount: 1 },
  { name: 'foto_belakang', maxCount: 1 },
]), (req, res) => {
  try {
    const service = new KebayaService();
    const data = {
      ...req.body,
      harga: req.body.harga ? parseInt(req.body.harga) : undefined,
      foto: req.files?.foto?.[0]?.filename || undefined,
      foto_belakang: req.files?.foto_belakang?.[0]?.filename || undefined,
    };
    service.updateCustom(req.params.slug, data);
    res.json({ message: 'Kebaya berhasil diperbarui.', slug: req.params.slug });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// DELETE /api/kebaya/:slug
router.delete('/:slug', auth, checkRole('admin', 'super_admin'), (req, res) => {
  try {
    const service = new KebayaService();
    service.deleteCustom(req.params.slug);
    res.json({ message: 'Kebaya berhasil dihapus.' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;
