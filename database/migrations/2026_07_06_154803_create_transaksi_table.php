<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kebaya_id')->constrained('kebaya')->onDelete('cascade');

            // Informasi Pemesan & AI
            $table->string('no_wa');
            $table->text('alamat');
            $table->string('ukuran', 10)->default('M');
            $table->string('warna_custom_ai')->default('Original');

            // Data Tanggal & Pembayaran
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('total_hari');
            $table->decimal('total_harga', 10, 2);
            $table->enum('status_transaksi', ['pending', 'dipesan', 'diambil', 'kembali', 'dibatalkan'])->default('pending');
            $table->string('bukti_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};