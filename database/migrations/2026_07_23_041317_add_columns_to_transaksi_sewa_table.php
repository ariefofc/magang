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
        Schema::table('transaksi_sewa', function (Blueprint $table) {
            $table->boolean('sudah_bayar')->default(false);
            $table->string('konfirmasi_pembayaran')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_sewa', function (Blueprint $table) {
            $table->dropColumn(['sudah_bayar', 'konfirmasi_pembayaran']);
        });
    }
};
