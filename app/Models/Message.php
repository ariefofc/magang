<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'room_id',
        'sender_type',
        'sender_name',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeFromCustomer($query)
    {
        return $query->where('sender_type', 'customer');
    }

    public function scopeFromAdmin($query)
    {
        return $query->where('sender_type', 'admin');
    }

    // ─── Helper Methods ──────────────────────────────────────────────

    public function isFromCustomer(): bool
    {
        return $this->sender_type === 'customer';
    }

    public function isFromAdmin(): bool
    {
        return $this->sender_type === 'admin';
    }

    /**
     * Format waktu pesan untuk tampil di chat bubble.
     */
    public function timeLabel(): string
    {
        return $this->created_at?->format('H:i') ?? '';
    }
}
