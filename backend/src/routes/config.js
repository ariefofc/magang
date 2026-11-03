const express = require('express');
const router = express.Router();
const { auth, checkRole } = require('../middleware/auth');
const fs = require('fs');
const path = require('path');

// POST /api/admin/config/api-ai (Super Admin only)
router.post('/api-ai', auth, checkRole('super_admin'), (req, res) => {
  try {
    const {
      midtrans_server_key, midtrans_client_key, midtrans_is_production,
      qris_static_string, qris_merchant_name,
    } = req.body;

    const envPath = path.join(__dirname, '..', '..', '.env');
    if (!fs.existsSync(envPath)) {
      return res.status(500).json({ message: '.env file not found.' });
    }

    let envContent = fs.readFileSync(envPath, 'utf8');

    const isProd = midtrans_is_production === 'true' ? 'true' : 'false';
    const snapUrl = isProd === 'true'
      ? 'https://app.midtrans.com/snap/snap.js'
      : 'https://app.sandbox.midtrans.com/snap/snap.js';

    const replacements = {
      MIDTRANS_SERVER_KEY: midtrans_server_key || '',
      MIDTRANS_CLIENT_KEY: midtrans_client_key || '',
      MIDTRANS_IS_PRODUCTION: isProd,
      MIDTRANS_SNAP_URL: snapUrl,
      QRIS_STATIC_STRING: qris_static_string || '',
      QRIS_MERCHANT_NAME: qris_merchant_name || 'MS Harmony',
    };

    for (const [key, val] of Object.entries(replacements)) {
      const regex = new RegExp(`^${key}=.*`, 'm');
      if (regex.test(envContent)) {
        envContent = envContent.replace(regex, `${key}="${val}"`);
      } else {
        envContent += `\n${key}="${val}"`;
      }
    }

    fs.writeFileSync(envPath, envContent);

    // Update process.env
    Object.assign(process.env, replacements);

    res.json({ message: 'Konfigurasi Midtrans & QRIS berhasil diperbarui!' });
  } catch (err) {
    res.status(500).json({ message: 'Gagal menyimpan konfigurasi: ' + err.message });
  }
});

module.exports = router;
