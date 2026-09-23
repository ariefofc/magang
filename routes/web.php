<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\KebayaController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserManagementController;

// ═══════════════════════════════════════════════════════════════════
//  1. RUTE PUBLIK (Tanpa Login)
// ═══════════════════════════════════════════════════════════════════

// Halaman Utama
Route::get('/', function () {
    try {
        $kebayaService = new \App\Services\KebayaService();
        $kebaya = collect($kebayaService->getPublic())->map(function ($k) {
            return (object)[
                'slug'                => $k['slug'] ?? '',
                'nama_kebaya'         => $k['nama'],
                'deskripsi'           => $k['deskripsi'],
                'harga_sewa_per_hari' => $k['harga'],
                'foto_url'            => !empty($k['foto']) ? asset($k['foto']) : null,
                'foto_belakang_url'   => !empty($k['foto_belakang']) ? asset($k['foto_belakang']) : null,
                'kain_utama'          => $k['kain_utama'] ?? 'Brokat / Katun Premium',
                'furing'              => $k['furing'] ?? 'Furing Katun Hero Adem',
                'karakteristik_kain'  => $k['karakteristik_kain'] ?? 'Soft, halus, dan nyaman dipakai',
                'detail_payet'        => $k['detail_payet'] ?? 'Payet & Bordir Eksklusif',
                'rekomendasi_acara'   => $k['rekomendasi_acara'] ?? 'Wisuda & Resepsi',
                'tersedia'            => ($k['status'] === 'aktif'),
            ];
        })->values()->all();
    } catch (\Throwable $e) {
        $kebaya = collect([]);
    }
    return view('welcome', compact('kebaya'));
})->name('home');

// Simpan Transaksi Baru
Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');

// Customer Request Pengembalian Kebaya
Route::post('/transaksi/{id}/ajukan-pengembalian', [TransaksiController::class, 'ajukanPengembalian'])->name('transaksi.ajukanPengembalian');

// Customer Request Jadwal Fitting Kebaya
Route::post('/transaksi/{id}/ajukan-fitting', [TransaksiController::class, 'ajukanFitting'])->name('transaksi.ajukanFitting');

// API Real-Time Tanggal Terkunci (Booked Dates) Kebaya
Route::get('/api/kebaya/booked-dates', [TransaksiController::class, 'getBookedDates'])->name('kebaya.booked_dates');

// AI Recolor (publik)
Route::post('/api/ai-recolor', [App\Http\Controllers\AIColorController::class, 'recolor'])->name('ai.recolor');

// Nota Customer (Publik)
Route::get('/transaksi/nota/{id}', [TransaksiController::class, 'nota'])->name('transaksi.nota');

// ─── Pembayaran QRIS + Midtrans Snap (Publik) ────────────────────
Route::get('/payment/{id}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{id}/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::get('/payment/{id}/status', [PaymentController::class, 'checkStatus'])->name('payment.status');
Route::get('/payment/{id}/refresh-qris', [PaymentController::class, 'refreshQris'])->name('payment.refresh_qris');
Route::post('/payment/{id}/simulate-sandbox', [PaymentController::class, 'simulateSandboxPayment'])->name('payment.simulate_sandbox');

// ─── Midtrans Webhook / Notification Handler (CSRF Exempt) ──────
//     Midtrans POST dari server mereka → harus bebas CSRF middleware
Route::post('/webhook/midtrans', [PaymentController::class, 'webhook'])
    ->name('webhook.midtrans')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// ─── Live Chat: Sisi Customer (Publik, via Session) ──────────────
Route::post('/chat/start-room',      [ChatController::class, 'startRoom'])->name('chat.start_room'); // verifikasi kode transaksi
Route::get('/chat/my-rooms',         [ChatController::class, 'getMyRooms'])->name('chat.my_rooms');    // ambil riwayat room customer
Route::get('/chat/my-transactions',  [ChatController::class, 'getMyTransactions'])->name('chat.my_transactions'); // ambil riwayat transaksi customer
Route::post('/chat/init',            [ChatController::class, 'initRoom'])->name('chat.init');        // legacy
Route::get('/chat/{roomId}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
Route::post('/chat/{roomId}/send',    [ChatController::class, 'sendMessage'])->name('chat.send');


// ═══════════════════════════════════════════════════════════════════
//  2. RUTE ADMIN — Authenticated (semua role)
// ═══════════════════════════════════════════════════════════════════

Route::middleware(['auth'])->group(function () {

    // Dashboard Utama (Admin & Super Admin: full; Owner: read-only)
    Route::get('/dashboard', [TransaksiController::class, 'index'])->name('dashboard');

    // Nota Admin (semua role)
    Route::get('/admin/nota/{id}', [TransaksiController::class, 'notaAdmin'])->name('admin.nota');

    // ─── Konfirmasi Bayar QRIS Manual (Admin) ─────────────────
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::post('/payment/{id}/confirm-qris', [PaymentController::class, 'confirmQris'])->name('payment.confirm_qris');
    });

    // ─── Laporan PDF & Statistik (semua role bisa lihat) ─────────
    Route::get('/admin/laporan/pdf', [TransaksiController::class, 'laporanPdf'])->name('admin.laporan.pdf');

    // ─── Live Chat: Sisi Admin ────────────────────────────────────
    Route::middleware('role:admin,super_admin')->prefix('admin')->group(function () {
        Route::get('/chat', [ChatController::class, 'inbox'])->name('admin.chat.inbox');
        Route::get('/chat/rooms', [ChatController::class, 'getRooms'])->name('admin.chat.rooms');
        Route::get('/chat/{roomId}/messages', [ChatController::class, 'getMessages'])->name('admin.chat.messages');
        Route::post('/chat/{roomId}/send', [ChatController::class, 'sendMessage'])->name('admin.chat.send');
        Route::post('/chat/{roomId}/close', [ChatController::class, 'closeRoom'])->name('admin.chat.close');
        Route::post('/chat/{roomId}/reopen', [ChatController::class, 'reopenRoom'])->name('admin.chat.reopen');
    });

    // ─── Master & Custom Kebaya (Admin & Super Admin) ─────────────
    Route::middleware('role:admin,super_admin')->prefix('admin')->group(function () {
        // Status management (existing)
        Route::post('/kebaya/{slug}/toggle', [KebayaController::class, 'toggleStatus'])->name('admin.kebaya.toggle');
        Route::post('/kebaya/{slug}/status', [KebayaController::class, 'setStatus'])->name('admin.kebaya.status');
        Route::post('/kebaya/{slug}/hide',   [KebayaController::class, 'hide'])->name('admin.kebaya.hide');
        Route::post('/kebaya/reset-all',     [KebayaController::class, 'resetAll'])->name('admin.kebaya.reset');

        // CRUD kebaya baru
        Route::post('/kebaya',                [KebayaController::class, 'store'])->name('admin.kebaya.store');
        Route::match(['PUT','POST'], '/kebaya/{slug}/update', [KebayaController::class, 'update'])->name('admin.kebaya.update');
        Route::delete('/kebaya/{slug}',       [KebayaController::class, 'destroy'])->name('admin.kebaya.destroy');
    });

    // ─── Kelola Transaksi/Pesanan (Admin & Super Admin) ──────────
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::post('/transaksi/{id}/status', [TransaksiController::class, 'updateStatus'])->name('transaksi.updateStatus');
        Route::put('/transaksi/{id}',         [TransaksiController::class, 'update'])->name('transaksi.update');
        Route::delete('/transaksi/{id}',      [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
    });

    // ─── Manajemen User & Config (Super Admin Only) ───────────────────────
    Route::middleware('role:super_admin')->prefix('admin')->group(function () {
        Route::get('/users',                        [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::post('/users',                       [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{user}',                 [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}',              [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/config/api-ai',               [TransaksiController::class, 'saveApiConfig'])->name('admin.config.save');
    });
});


// ═══════════════════════════════════════════════════════════════════
//  3. AUTH ROUTES (Laravel Breeze)
// ═══════════════════════════════════════════════════════════════════

if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}