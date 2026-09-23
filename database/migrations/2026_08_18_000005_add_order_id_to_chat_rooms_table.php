<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            // Tambah kolom order_id jika belum ada
            if (! Schema::hasColumn('chat_rooms', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('session_id');
            }

            // Unique constraint: 1 room per (session + order)
            // Pakai unique partial: jika order_id null, room general; jika ada, unik per order
            try {
                $table->unique(['session_id', 'order_id'], 'chat_rooms_session_order_unique');
            } catch (\Throwable $e) {
                // Constraint sudah ada, skip
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            try {
                $table->dropUnique('chat_rooms_session_order_unique');
            } catch (\Throwable $e) {}

            if (Schema::hasColumn('chat_rooms', 'order_id')) {
                $table->dropColumn('order_id');
            }
        });
    }
};
