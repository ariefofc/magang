require('dotenv').config();
const express = require('express');
const cors = require('cors');
const cookieParser = require('cookie-parser');
const session = require('express-session');
const path = require('path');

const app = express();

// ═══════════════════════════════════════════════════════
//  MIDDLEWARE
// ═══════════════════════════════════════════════════════
app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:5173',
  credentials: true,
}));
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));
app.use(cookieParser());
app.use(session({
  secret: process.env.SESSION_SECRET || 'msharmony-secret',
  resave: false,
  saveUninitialized: true,
  cookie: { maxAge: 24 * 60 * 60 * 1000 }, // 24 hours
}));

// Static files
app.use('/uploads', express.static(path.join(__dirname, '..', 'public', 'uploads')));
app.use('/images', express.static(path.join(__dirname, '..', 'public', 'images')));

// ═══════════════════════════════════════════════════════
//  ROUTES
// ═══════════════════════════════════════════════════════
const authRoutes = require('./routes/auth');
const transaksiRoutes = require('./routes/transaksi');
const paymentRoutes = require('./routes/payment');
const chatRoutes = require('./routes/chat');
const kebayaRoutes = require('./routes/kebaya');
const availabilityHistoryRoutes = require('./routes/availabilityHistory');
const userRoutes = require('./routes/users');
const aiRoutes = require('./routes/ai');
const configRoutes = require('./routes/config');

app.use('/api/auth', authRoutes);
app.use('/api/transaksi', transaksiRoutes);
app.use('/api/payment', paymentRoutes);
app.use('/api/chat', chatRoutes);
app.use('/api/kebaya', kebayaRoutes);
app.use('/api/admin/availability-history', availabilityHistoryRoutes);
app.use('/api/admin/users', userRoutes);
app.use('/api/ai', aiRoutes);
app.use('/api/admin/config', configRoutes);

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// ═══════════════════════════════════════════════════════
//  START SERVER
// ═══════════════════════════════════════════════════════
const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`🚀 MS Harmony Backend running on port ${PORT}`);
  console.log(`📦 Environment: ${process.env.NODE_ENV || 'development'}`);
});

module.exports = app;
