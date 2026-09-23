<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi_sewa';

    protected $fillable = [
        'kode_transaksi',
        'nama_kebaya',
        'warna_custom_ai',
        'gambar_ai_result',
        'nama_user',
        'no_wa',
        'ukuran',
        'metode_pembayaran',
        'skema_bayar',
        'alamat',
        'tanggal_sewa',
        'tanggal_pengembalian',
        'durasi_hari',
        'status',
        'total_harga',
        'sudah_bayar',
        'catatan_admin',
        // Kolom Pembayaran QRIS/Midtrans
        'payment_status',
        'transaction_id',
        'snap_token',
        'qris_url',
        'paid_at',
        'jumlah_dibayar',
    ];

    protected $casts = [
        'tanggal_sewa'         => 'date',
        'tanggal_pengembalian' => 'date',
        'sudah_bayar'          => 'boolean',
        'paid_at'              => 'datetime',
        'jumlah_dibayar'       => 'decimal:2',
        'total_harga'          => 'decimal:2',
    ];

    // ─── Payment Helper Methods ──────────────────────────────────────

    /**
     * Cek apakah transaksi sudah lunas via payment gateway.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Cek apakah snap token masih ada (bisa tampilkan widget Midtrans).
     */
    public function hasSnapToken(): bool
    {
        return !empty($this->snap_token);
    }

    /**
     * Hitung jumlah DP berdasarkan skema bayar.
     */
    public function nominalDP(): float
    {
        $skema = strtolower($this->skema_bayar ?? 'dp 50%');
        $pct   = str_contains($skema, '70') ? 0.7
               : (str_contains($skema, '100') || str_contains($skema, 'lunas') ? 1.0 : 0.5);
        return ceil($this->total_harga * $pct);
    }

    /**
     * Hitung sisa pelunasan.
     */
    public function sisaPelunasan(): float
    {
        return max(0, $this->total_harga - $this->nominalDP());
    }

    /**
     * Label skema bayar yang tampil.
     */
    public function labelSkema(): string
    {
        $skema = strtolower($this->skema_bayar ?? '');
        if (str_contains($skema, '70'))   return 'DP 70%';
        if (str_contains($skema, '100') || str_contains($skema, 'lunas')) return 'Lunas 100%';
        return 'DP 50%';
    }

    // ─── Status Helper Methods ───────────────────────────────────────

    /**
     * Warna badge berdasarkan status (normalized names)
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            'Telah Dikonfirmasi'   => 'bg-blue-100 text-blue-900 border-blue-200',
            'Sedang Fitting'       => 'bg-purple-100 text-purple-900 border-purple-200',
            'Kebaya Telah Diambil' => 'bg-orange-100 text-orange-900 border-orange-200',
            'Telah Dikembalikan'   => 'bg-emerald-100 text-emerald-900 border-emerald-200',
            'Dibatalkan'           => 'bg-red-100 text-red-900 border-red-200',
            default                => 'bg-amber-100 text-amber-900 border-amber-200',
        };
    }

    /**
     * Warna badge payment_status.
     */
    public function paymentStatusColor(): string
    {
        return match ($this->payment_status) {
            'paid'      => '#059669',
            'failed'    => '#dc2626',
            'expired'   => '#6b7280',
            'challenge' => '#d97706',
            default     => '#d97706', // pending
        };
    }

    /**
     * Label payment_status dalam Bahasa Indonesia.
     */
    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'paid'      => 'Lunas',
            'failed'    => 'Gagal',
            'expired'   => 'Kedaluwarsa',
            'challenge' => 'Perlu Review',
            default     => 'Menunggu Bayar',
        };
    }
}
