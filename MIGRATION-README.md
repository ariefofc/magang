# MS Harmony - Kebaya Rental System

## Stack Baru (Migrasi dari Laravel)

| Layer | Teknologi |
|-------|-----------|
| Frontend | React.js + Tailwind CSS |
| Backend | Express.js (Node.js) |
| ORM | Prisma |
| Database | MySQL (phpMyAdmin) |
| Payment | Midtrans Snap + QRIS DANA |
| AI Service | Python Flask (dipertahankan) |

## Struktur Project

```
magang/
├── backend/          # Express.js API Server
│   ├── prisma/       # Prisma schema, migrations, seeders
│   ├── src/          # Source code
│   │   ├── routes/   # API routes
│   │   ├── middleware/# Auth & role middleware
│   │   ├── helpers/  # QRIS helper
│   │   └── services/ # KebayaService
│   └── .env          # Backend config
├── frontend/         # React.js SPA
│   ├── src/
│   │   ├── pages/    # React pages
│   │   ├── components/
│   │   ├── services/ # API client & Auth context
│   │   └── styles/   # Tailwind CSS
│   └── index.html
├── python/           # AI Recolor Service (Flask)
├── setup.bat         # Initial setup
└── start-all.bat     # Start all services
```

## Quick Start

### 1. Setup
```bash
# Jalankan setup.bat atau manual:
cd backend && npm install
cd frontend && npm install
```

### 2. Database (MySQL via phpMyAdmin)
1. Buka phpMyAdmin (biasanya http://localhost/phpmyadmin)
2. Buat database baru: `msharmony_kebaya`
3. Edit `backend/.env`:
   ```
   DATABASE_URL="mysql://root:@localhost:3306/msharmony_kebaya"
   ```
4. Jalankan migrasi & seeder:
   ```bash
   cd backend
   npx prisma migrate dev --name init
   npm run prisma:seed
   ```

### 3. Jalankan
```bash
# Semua sekaligus:
start-all.bat

# Atau manual:
cd backend && npm run dev      # Port 5000
cd frontend && npm run dev     # Port 5173
cd python && python recolor_service.py  # Port 5001
```

### 4. Akses
- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:5000/api
- **phpMyAdmin**: http://localhost/phpmyadmin

### Akun Default
| Email | Password | Role |
|-------|----------|------|
| superadmin@msharmony.id | SuperAdmin123! | Super Admin |
| admin@msharmony.id | Admin123! | Admin |
| owner@msharmony.id | Owner123! | Owner |
| ariefharmoni@gmail.com | Harmoni123 | Admin |

## API Endpoints

### Public
- `GET /api/kebaya` - Katalog kebaya
- `POST /api/transaksi` - Buat transaksi baru
- `GET /api/transaksi/booked-dates?nama=...` - Tanggal terkunci
- `GET /api/payment/:id` - Halaman pembayaran
- `POST /api/payment/webhook/midtrans` - Webhook Midtrans
- `POST /api/ai/recolor` - AI Recolor

### Auth Required
- `GET /api/transaksi/dashboard` - Dashboard data
- `POST /api/transaksi/:id/status` - Update status
- `GET /api/transaksi/laporan/pdf` - Download PDF

### Super Admin
- `GET /api/admin/users` - User management
- `POST /api/admin/config/api-ai` - Update config
