const express = require('express');
const router = express.Router();
const prisma = require('../prisma');
const { auth, checkRole } = require('../middleware/auth');

// ═══════════════════════════════════════════════════════
//  ADMIN: AVAILABILITY HISTORY
// ═══════════════════════════════════════════════════════

// GET /api/availability-history
router.get('/', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const histories = await prisma.kebayaAvailabilityHistory.findMany({
      include: {
        kebaya: { select: { id: true, slug: true, nama: true, foto: true } },
        createdBy: { select: { id: true, name: true, email: true, role: true } },
      },
      orderBy: { createdAt: 'desc' },
    });

    const formatted = histories.map((h) => ({
      id: h.id,
      kebaya_id: h.kebayaId,
      kebaya_slug: h.kebaya.slug,
      kebaya_nama: h.kebaya.nama,
      kebaya_foto: h.kebaya.foto,
      status_lama: h.statusLama,
      status_baru: h.statusBaru,
      catatan: h.catatan,
      created_by: h.createdBy
        ? { id: h.createdBy.id, name: h.createdBy.name, email: h.createdBy.email, role: h.createdBy.role }
        : null,
      created_at: h.createdAt,
    }));

    res.json({ histories: formatted });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/availability-history/:kebayaId
router.get('/:kebayaId', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const kebayaId = parseInt(req.params.kebayaId);
    const histories = await prisma.kebayaAvailabilityHistory.findMany({
      where: { kebayaId },
      include: {
        kebaya: { select: { id: true, slug: true, nama: true, foto: true } },
        createdBy: { select: { id: true, name: true, email: true, role: true } },
      },
      orderBy: { createdAt: 'desc' },
    });

    const formatted = histories.map((h) => ({
      id: h.id,
      kebaya_id: h.kebayaId,
      kebaya_slug: h.kebaya.slug,
      kebaya_nama: h.kebaya.nama,
      kebaya_foto: h.kebaya.foto,
      status_lama: h.statusLama,
      status_baru: h.statusBaru,
      catatan: h.catatan,
      created_by: h.createdBy
        ? { id: h.createdBy.id, name: h.createdBy.name, email: h.createdBy.email, role: h.createdBy.role }
        : null,
      created_at: h.createdAt,
    }));

    res.json({ histories: formatted });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;
