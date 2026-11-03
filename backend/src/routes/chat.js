const express = require('express');
const router = express.Router();
const prisma = require('../prisma');
const { auth, checkRole } = require('../middleware/auth');

// ═══════════════════════════════════════════════════════
//  CUSTOMER SIDE (Public, via session)
// ═══════════════════════════════════════════════════════

// POST /api/chat/start-room
router.post('/start-room', async (req, res) => {
  try {
    const { kode_transaksi, nama, kontak } = req.body;
    const sessionId = req.sessionID;

    let transaksi = null;
    if (kode_transaksi) {
      transaksi = await prisma.transaksiSewa.findFirst({
        where: { kodeTransaksi: kode_transaksi },
      });
      if (!transaksi) return res.status(404).json({ message: 'Kode transaksi tidak ditemukan.' });
    }

    const existing = await prisma.chatRoom.findFirst({
      where: {
        sessionId,
        orderId: transaksi?.id || null,
        status: 'open',
      },
    });

    if (existing) {
      return res.json({ room: existing });
    }

    const room = await prisma.chatRoom.create({
      data: {
        sessionId,
        orderId: transaksi?.id || null,
        transactionCode: transaksi?.kodeTransaksi || null,
        customerName: nama || 'Pelanggan',
        customerContact: kontak || null,
        status: 'open',
        lastMessageAt: new Date(),
      },
    });

    res.status(201).json({ room });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/chat/my-rooms
router.get('/my-rooms', async (req, res) => {
  try {
    const rooms = await prisma.chatRoom.findMany({
      where: { sessionId: req.sessionID },
      include: { messages: { orderBy: { createdAt: 'desc' }, take: 1 } },
      orderBy: { lastMessageAt: 'desc' },
    });
    res.json({ rooms });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/chat/my-transactions
router.get('/my-transactions', async (req, res) => {
  try {
    const rooms = await prisma.chatRoom.findMany({
      where: { sessionId: req.sessionID },
      select: { transactionCode: true },
    });
    const codes = rooms.map(r => r.transactionCode).filter(Boolean);
    const transaksi = codes.length ? await prisma.transaksiSewa.findMany({
      where: { kodeTransaksi: { in: codes } },
    }) : [];
    res.json({ transaksi });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/chat/:roomId/messages
router.get('/:roomId/messages', async (req, res) => {
  try {
    const roomId = parseInt(req.params.roomId);
    const messages = await prisma.message.findMany({
      where: { roomId },
      orderBy: { createdAt: 'asc' },
    });

    // Mark admin messages as read for customer
    await prisma.message.updateMany({
      where: { roomId, senderType: 'admin', isRead: false },
      data: { isRead: true },
    });

    res.json({ messages });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/chat/:roomId/send
router.post('/:roomId/send', async (req, res) => {
  try {
    const roomId = parseInt(req.params.roomId);
    const { message: msg, sender_name, sender_type } = req.body;

    const isAdmin = sender_type === 'admin';

    const message = await prisma.message.create({
      data: {
        roomId,
        senderType: isAdmin ? 'admin' : 'customer',
        senderName: sender_name || (isAdmin ? 'Admin' : 'Pelanggan'),
        message: msg,
        isRead: false,
      },
    });

    await prisma.chatRoom.update({
      where: { id: roomId },
      data: { lastMessageAt: new Date() },
    });

    res.status(201).json({ message });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// ═══════════════════════════════════════════════════════
//  ADMIN SIDE (Authenticated)
// ═══════════════════════════════════════════════════════

// GET /api/chat/admin/inbox
router.get('/admin/inbox', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const rooms = await prisma.chatRoom.findMany({
      include: {
        messages: { orderBy: { createdAt: 'desc' }, take: 1 },
        transaksi: { select: { kodeTransaksi: true, namaKebaya: true, status: true } },
      },
      orderBy: { lastMessageAt: 'desc' },
    });

    // Count unread per room
    const roomsWithUnread = await Promise.all(rooms.map(async (room) => {
      const unreadCount = await prisma.message.count({
        where: { roomId: room.id, senderType: 'customer', isRead: false },
      });
      return { ...room, unreadCount };
    }));

    res.json({ rooms: roomsWithUnread });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// GET /api/chat/admin/rooms
router.get('/admin/rooms', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const rooms = await prisma.chatRoom.findMany({
      include: { messages: { orderBy: { createdAt: 'desc' }, take: 1 } },
      orderBy: { lastMessageAt: 'desc' },
    });
    res.json({ rooms });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/chat/admin/:roomId/close
router.post('/admin/:roomId/close', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const room = await prisma.chatRoom.update({
      where: { id: parseInt(req.params.roomId) },
      data: { status: 'closed' },
    });
    res.json({ room });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// POST /api/chat/admin/:roomId/reopen
router.post('/admin/:roomId/reopen', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    const room = await prisma.chatRoom.update({
      where: { id: parseInt(req.params.roomId) },
      data: { status: 'open' },
    });
    res.json({ room });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;
