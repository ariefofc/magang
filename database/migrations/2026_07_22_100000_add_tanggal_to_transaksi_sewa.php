<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_sewa', function (Blueprint $table) {
            $table->date('tanggal_sewa')->nullable()->after('alamat');
            $table->date('tanggal_pengembalian')->nullable()->after('tanggal_sewa');
            $table->integer('durasi_hari')->default(1)->after('tanggal_pengembalian');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_sewa', function (Blueprint $table) {
            $table->dropColumn(['tanggal_sewa', 'tanggal_pengembalian', 'durasi_hari']);
        });
    }
};
