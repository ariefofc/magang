<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel chat_rooms: setiap percakapan customer dengan admin.
     * session_id digunakan untuk identifikasi customer publik (tanpa login).
     */
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->unique()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('customer_name', 100);
            $table->string('customer_contact', 50)->nullable()->comment('No WA atau email customer');
            $table->enum('status', ['open', 'closed'])->default('open')->index();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            // FK ke transaksi_sewa jika ada pesanan terkait
            $table->foreign('order_id')
                  ->references('id')
                  ->on('transaksi_sewa')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};
