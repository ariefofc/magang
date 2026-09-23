<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom-kolom pembayaran QRIS/Midtrans ke tabel transaksi_sewa.
     * Kompatibel MySQL.
     */
    public function up(): void
    {
        Schema::table('transaksi_sewa', function (Blueprint $table) {
            // Status pembayaran Midtrans
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired', 'challenge'])
                  ->default('pending')
                  ->after('sudah_bayar')
                  ->comment('Status dari Midtrans/payment gateway');

            // Order ID unik untuk Midtrans (bisa sama dengan kode_transaksi)
            $table->string('transaction_id', 100)
                  ->nullable()
                  ->after('payment_status')
                  ->comment('Order ID yang dikirim ke Midtrans');

            // Snap token dari Midtrans untuk embed widget
            $table->string('snap_token', 255)
                  ->nullable()
                  ->after('transaction_id')
                  ->comment('Token untuk Midtrans Snap.js widget');

            // URL QR code (jika generate sendiri / dari Midtrans)
            $table->string('qris_url', 500)
                  ->nullable()
                  ->after('snap_token')
                  ->comment('URL gambar QR code QRIS');

            // Waktu pembayaran berhasil
            $table->timestamp('paid_at')
                  ->nullable()
                  ->after('qris_url')
                  ->comment('Timestamp ketika pembayaran dikonfirmasi paid');

            // Jumlah yang dibayarkan (untuk DP / partial)
            $table->decimal('jumlah_dibayar', 12, 2)
                  ->default(0)
                  ->after('paid_at')
                  ->comment('Nominal yang sudah dibayarkan via gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_sewa', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'transaction_id',
                'snap_token',
                'qris_url',
                'paid_at',
                'jumlah_dibayar',
            ]);
        });
    }
};
