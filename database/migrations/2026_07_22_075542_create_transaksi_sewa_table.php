<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel ini digunakan oleh TransaksiController untuk menyimpan data pesanan sewa kebaya.
     */
    public function up(): void
    {
        Schema::create('transaksi_sewa', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->string('nama_kebaya');
            $table->string('warna_custom_ai')->nullable()->default('Default');
            $table->text('gambar_ai_result')->nullable();
            $table->string('nama_user');
            $table->string('no_wa', 20);
            $table->string('ukuran', 10)->default('M');
            $table->string('metode_pembayaran');
            $table->text('alamat')->nullable();
            $table->string('status')->default('Menunggu Fitting & Konfirmasi');
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_sewa');
    }
};
