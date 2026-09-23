<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatRoom extends Model
{
    protected $table = 'chat_rooms';

    protected $fillable = [
        'session_id',
        'order_id',
        'transaction_code',   // kode_transaksi dari tabel transaksi_sewa
        'customer_name',
        'customer_contact',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'room_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'room_id')->latestOfMany();
    }

    /** Relasi ke transaksi (untuk ambil detail pesanan jika perlu) */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'order_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    // ─── Helper Methods ──────────────────────────────────────────────

    /**
     * Jumlah pesan belum dibaca oleh admin (dari customer).
     */
    public function unreadCount(): int
    {
        return $this->messages()
                    ->where('sender_type', 'customer')
                    ->where('is_read', false)
                    ->count();
    }

    /**
     * Total unread dari semua room aktif (static, untuk badge sidebar).
     */
    public static function totalUnread(): int
    {
        return Message::where('sender_type', 'customer')
                      ->where('is_read', false)
                      ->whereHas('room', fn ($q) => $q->where('status', 'open'))
                      ->count();
    }
}
