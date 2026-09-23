<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel messages: setiap pesan dalam chat_rooms.
     * sender_type: 'customer' = dari customer publik, 'admin' = dari staff admin.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->index();
            $table->enum('sender_type', ['customer', 'admin'])->index();
            $table->string('sender_name', 100)->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false)->index();
            $table->timestamps();

            $table->foreign('room_id')
                  ->references('id')
                  ->on('chat_rooms')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
