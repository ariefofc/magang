const express = require('express');
const router = express.Router();
const prisma = require('../prisma');
const { auth, checkRole } = require('../middleware/auth');

// ═══════════════════════════════════════════════════════
//  FAQ / QUICK REPLIES (10 most-asked questions)
// ═══════════════════════════════════════════════════════
const FAQS = [
  {
    id: 'q1',
    question: 'Bagaimana cara menyewa kebaya?',
    answer: 'Pilih kebaya → klik "Sewa Sekarang" → isi data penyewaan → bayar DP/lunas → kebaya siap diambil sesuai tanggal.',
  },
  {
    id: 'q2',
    question: 'Berapa lama durasi penyewaan?',
    answer: 'Durasi sewa fleksibel mulai dari 1 hari. Anda bisa menentukan tanggal pengembalian saat checkout.',
  },
  {
    id: 'q3',
    question: 'Apakah bisa bayar DP terlebih dahulu?',
    answer: 'Bisa. Kami menyediakan skema DP 50%, DP 70%, maupun Lunas 100% sesuai kenyamanan Anda.',
  },
  {
    id: 'q4',
    question: 'Metode pembayaran apa saja yang tersedia?',
    answer: 'Kami menerima QRIS, transfer bank, dan bayar di tempat (COD) saat pengambilan kebaya.',
  },
  {
    id: 'q5',
    question: 'Bagaimana jika kebaya tidak sesuai ukuran?',
    answer: 'Kami menyediakan fitting sebelum pengambilan. Pastikan ukuran sudah sesuai saat checkout.',
  },
  {
    id: 'q6',
    question: 'Apakah ada layanan pengantaran kebaya?',
    answer: 'Saat ini kebaya diambil langsung di studio. Silakan cek alamat kami pada halaman kontak.',
  },
  {
    id: 'q7',
    question: 'Bagaimana cara melihat status pesanan saya?',
    answer: 'Status pesanan dapat dilihat melalui halaman nota transaksi atau dengan menghubungi admin via chat ini.',
  },
  {
    id: 'q8',
    question: 'Apakah kebaya sudah termasuk aksesoris?',
    answer: 'Kebaya yang disewakan berupa gaun/atasan. Aksesoris tambahan dapat ditanyakan langsung ke admin.',
  },
  {
    id: 'q9',
    question: 'Bagaimana kebijakan pembatalan sewa?',
    answer: 'Pembatalan dapat dilakukan sebelum kebaya diambil. DP yang sudah masuk tidak dapat dikembalikan.',
  },
  {
    id: 'q10',
    question: 'Apakah bisa mengubah warna kebaya dengan AI?',
    answer: 'Bisa. Gunakan fitur AI Recolor di halaman detail kebaya untuk melihat visualisasi warna lain.',
  },
];

// ═══════════════════════════════════════════════════════
//  CUSTOMER SIDE (Public, via session)
// ═══════════════════════════════════════════════════════

// GET /api/chat/faq
router.get('/faq', async (req, res) => {
  try {
    res.json({ faqs: FAQS });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

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
      include: {
        messages: { orderBy: { createdAt: 'desc' }, take: 1 },
        transaksi: { select: { kodeTransaksi: true, namaKebaya: true, status: true } },
      },
      orderBy: { lastMessageAt: 'desc' },
    });

    const roomsWithLast = rooms.map((room) => {
      const last = room.messages?.[0];
      return {
        ...room,
        last_message: last?.message || null,
        last_message_at: last?.createdAt || room.createdAt,
      };
    });

    res.json({ rooms: roomsWithLast });
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

// POST /api/chat/:roomId/quick-reply
// Used by customer when clicking a FAQ/quick-reply chip.
router.post('/:roomId/quick-reply', async (req, res) => {
  try {
    const roomId = parseInt(req.params.roomId);
    const { faq_id } = req.body;

    const faq = FAQS.find((f) => f.id === faq_id);
    if (!faq) return res.status(404).json({ message: 'Pertanyaan tidak ditemukan.' });

    const room = await prisma.chatRoom.findUnique({ where: { id: roomId } });
    if (!room) return res.status(404).json({ message: 'Room tidak ditemukan.' });

    // Save customer question
    await prisma.message.create({
      data: {
        roomId,
        senderType: 'customer',
        senderName: room.customerName || 'Pelanggan',
        message: faq.question,
        isRead: false,
      },
    });

    // Save automated admin answer
    const answer = await prisma.message.create({
      data: {
        roomId,
        senderType: 'admin',
        senderName: 'Admin (Auto)',
        message: faq.answer,
        isRead: true,
      },
    });

    await prisma.chatRoom.update({
      where: { id: roomId },
      data: { lastMessageAt: new Date() },
    });

    res.status(201).json({ message: answer });
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

// GET /api/chat/admin/faq
router.get('/admin/faq', auth, checkRole('admin', 'super_admin'), async (req, res) => {
  try {
    res.json({ faqs: FAQS });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;
