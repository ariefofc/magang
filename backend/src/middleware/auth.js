const jwt = require('jsonwebtoken');
const prisma = require('../prisma');

// Auth middleware - verify JWT token
const auth = async (req, res, next) => {
  try {
    const token = req.cookies.token || req.headers.authorization?.replace('Bearer ', '');
    if (!token) {
      return res.status(401).json({ message: 'Silakan login terlebih dahulu.' });
    }

    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    const user = await prisma.user.findUnique({ where: { id: decoded.id } });
    if (!user) {
      return res.status(401).json({ message: 'User tidak ditemukan.' });
    }

    req.user = user;
    next();
  } catch (err) {
    return res.status(401).json({ message: 'Token tidak valid.' });
  }
};

// Role check middleware
const checkRole = (...roles) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ message: 'Silakan login terlebih dahulu.' });
    }
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({
        message: `Anda tidak memiliki akses ke halaman ini. (Role: ${req.user.role})`,
      });
    }
    next();
  };
};

module.exports = { auth, checkRole };
