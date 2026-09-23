<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            // Simpan kode_transaksi langsung di room agar mudah ditampilkan di inbox admin
            $table->string('transaction_code', 50)
                  ->nullable()
                  ->after('order_id')
                  ->comment('kode_transaksi dari tabel transaksi_sewa, untuk display di inbox admin');
        });
    }

    public function down(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->dropColumn('transaction_code');
        });
    }
};
