<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom skema_bayar (DP 50%, DP 70%, Lunas 100%)
     */
    public function up(): void
    {
        Schema::table('transaksi_sewa', function (Blueprint $table) {
            $table->string('skema_bayar')->nullable()->default('DP 50%')->after('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_sewa', function (Blueprint $table) {
            $table->dropColumn('skema_bayar');
        });
    }
};
