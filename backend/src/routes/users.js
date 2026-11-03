const express = require('express');
const router = express.Router();
const prisma = require('../prisma');
const bcrypt = require('bcryptjs');
const { auth, checkRole } = require('../middleware/auth');

// All routes require super_admin
router.use(auth, checkRole('super_admin'));

// GET /api/admin/users
router.get('/', async (req, res) => {
  try {
    const users = await prisma.user.findMany({
      select: { id: true, name: true, email: true, role: true, createdAt: true },
      orderBy: { createdAt: 'desc' },
    });
    res.json({ users });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/admin/users
router.post('/', async (req, res) => {
  try {
    const { name, email, role, password } = req.body;
    if (!name || !email || !password) {
      return res.status(422).json({ message: 'Semua field wajib diisi.' });
    }

    const existing = await prisma.user.findUnique({ where: { email } });
    if (existing) return res.status(422).json({ message: 'Email sudah terdaftar.' });

    const user = await prisma.user.create({
      data: {
        name,
        email,
        role: role || 'admin',
        password: await bcrypt.hash(password, 10),
      },
    });

    res.status(201).json({
      user: { id: user.id, name: user.name, email: user.email, role: user.role },
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// PUT /api/admin/users/:id
router.put('/:id', async (req, res) => {
  try {
    const { name, email, role, password } = req.body;
    const data = {};
    if (name) data.name = name;
    if (email) data.email = email;
    if (role) data.role = role;
    if (password) data.password = await bcrypt.hash(password, 10);

    const user = await prisma.user.update({
      where: { id: parseInt(req.params.id) },
      data,
    });

    res.json({ user: { id: user.id, name: user.name, email: user.email, role: user.role } });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// DELETE /api/admin/users/:id
router.delete('/:id', async (req, res) => {
  try {
    await prisma.user.delete({ where: { id: parseInt(req.params.id) } });
    res.json({ message: 'User berhasil dihapus.' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;
