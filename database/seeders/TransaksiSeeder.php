<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiSeeder extends Seeder
{
    /**
     * Seeder Data Dummy Transaksi Penyewaan Kebaya MS Harmony
     * ─────────────────────────────────────────────────────────
     * Mencakup:
     *  - Januari s/d Agustus 2026 (multi-bulan)
     *  - Variasi status: Selesai, Berjalan, Fitting, Belum Bayar, Dibatalkan
     *  - Variasi skema: DP 50%, DP 70%, Lunas 100%
     *  - Variasi metode bayar: QRIS, Transfer Bank, COD
     *  - Variasi kebaya, ukuran, dan durasi sewa
     */
    public function run(): void
    {
        // ── Data Master ─────────────────────────────────────
        $kebayaList = [
            ['nama' => 'Kebaya Modern Brukat Gown',        'harga_dasar' => 850000],
            ['nama' => 'Kebaya Encim Peach Blossom',       'harga_dasar' => 750000],
            ['nama' => 'Kebaya Kutubaru Hijau Tosca',      'harga_dasar' => 650000],
            ['nama' => 'Kebaya Pengantin Ivory Gold',      'harga_dasar' => 1200000],
            ['nama' => 'Kebaya Kartini Biru Dongker',      'harga_dasar' => 700000],
            ['nama' => 'Kebaya Wisuda Marun Beludru',      'harga_dasar' => 800000],
            ['nama' => 'Kebaya Sundanese Pink Dusty',      'harga_dasar' => 600000],
            ['nama' => 'Kebaya Javanese Sidomukti',        'harga_dasar' => 950000],
            ['nama' => 'Kebaya Pesta Silver Glitter',      'harga_dasar' => 1100000],
            ['nama' => 'Kebaya Batik Kawung Coklat',       'harga_dasar' => 550000],
        ];

        $namaCustomer = [
            'Anisa Rahmawati', 'Berliana Putri', 'Cindy Maharani', 'Desy Kartika',
            'Elisa Wulandari', 'Fina Kusuma', 'Gilang Permata', 'Hana Safitri',
            'Indah Lestari', 'Julia Maulida', 'Kirana Salsabila', 'Layla Nur Azizah',
            'Maya Sari', 'Nadia Fitriani', 'Olivia Santoso', 'Putri Cahayani',
            'Qory Andriani', 'Rara Kencana', 'Sinta Dewi', 'Tiara Agustin',
            'Ulfa Ramadhani', 'Vira Oktaviani', 'Wulan Septiana', 'Xena Anggraini',
            'Yuli Prasetya', 'Zara Maullana', 'Aulia Hamidah', 'Bella Indriani',
            'Cahya Melati', 'Dwi Lestariningsih',
        ];

        $noWaList = [
            '081234567890', '082345678901', '083456789012', '084567890123',
            '085678901234', '086789012345', '087890123456', '088901234567',
            '089012345678', '081123456789', '082234567890', '083345678901',
            '084456789012', '085567890123', '086678901234', '087789012345',
        ];

        $alamatList = [
            'Jl. Magelang No. 12, Sleman, DIY',
            'Jl. Parangtritis Km 5, Bantul, DIY',
            'Jl. Godean Raya No. 34, Sleman, DIY',
            'Perumahan Griyo Ageng Blok B5, Bangunjiwo, Bantul',
            'Jl. Veteran No. 8, Kota Yogyakarta',
            'Jl. Affandi No. 20, Sleman, DIY',
            'Kledokan RT 03, Depok, Sleman, DIY',
            'Jl. Wates Km 7, Gamping, Sleman',
            'Jl. Solo Km 10, Kalasan, Sleman',
            'Jl. Kaliurang Km 12, Ngaglik, Sleman',
            'Maguwoharjo, Depok, Sleman',
            'Jl. Ring Road Utara No. 5, Sleman',
        ];

        $ukuranList  = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $skemaList   = ['DP 50%', 'DP 70%', 'Lunas 100%'];
        $metodeList  = ['QRIS', 'Transfer Bank BCA', 'Transfer Bank BRI', 'Transfer Bank Mandiri', 'COD'];

        // ── Definisi Batch Transaksi per Bulan ──────────────
        // Format: [created_at, tanggal_sewa, tanggal_pengembalian, status, sudah_bayar]
        $batches = [

            // ══════════ JANUARI 2026 ══════════
            ['2026-01-03', '2026-01-05', '2026-01-07', 'Telah Dikembalikan', true,  'DP 50%',    0],
            ['2026-01-08', '2026-01-10', '2026-01-11', 'Telah Dikembalikan', true,  'Lunas 100%',1],
            ['2026-01-12', '2026-01-14', '2026-01-16', 'Telah Dikembalikan', true,  'DP 70%',    2],
            ['2026-01-15', '2026-01-17', '2026-01-18', 'Telah Dikembalikan', true,  'DP 50%',    3],
            ['2026-01-20', '2026-01-22', '2026-01-24', 'Telah Dikembalikan', true,  'Lunas 100%',4],
            ['2026-01-25', '2026-01-27', '2026-01-29', 'Dibatalkan',         false, 'DP 50%',    5],

            // ══════════ FEBRUARI 2026 ══════════
            ['2026-02-02', '2026-02-04', '2026-02-05', 'Telah Dikembalikan', true,  'DP 50%',    6],
            ['2026-02-07', '2026-02-09', '2026-02-11', 'Telah Dikembalikan', true,  'DP 70%',    7],
            ['2026-02-10', '2026-02-12', '2026-02-14', 'Telah Dikembalikan', true,  'Lunas 100%',8],
            ['2026-02-14', '2026-02-16', '2026-02-18', 'Telah Dikembalikan', true,  'DP 50%',    9],
            ['2026-02-18', '2026-02-20', '2026-02-22', 'Telah Dikembalikan', true,  'DP 70%',    0],
            ['2026-02-22', '2026-02-24', '2026-02-26', 'Dibatalkan',         false, 'DP 50%',    1],
            ['2026-02-25', '2026-02-27', '2026-02-28', 'Telah Dikembalikan', true,  'Lunas 100%',2],

            // ══════════ MARET 2026 ══════════
            ['2026-03-01', '2026-03-03', '2026-03-04', 'Telah Dikembalikan', true,  'DP 50%',    3],
            ['2026-03-05', '2026-03-07', '2026-03-09', 'Telah Dikembalikan', true,  'Lunas 100%',4],
            ['2026-03-08', '2026-03-10', '2026-03-12', 'Telah Dikembalikan', true,  'DP 70%',    5],
            ['2026-03-12', '2026-03-14', '2026-03-16', 'Telah Dikembalikan', true,  'DP 50%',    6],
            ['2026-03-15', '2026-03-17', '2026-03-19', 'Telah Dikembalikan', true,  'Lunas 100%',7],
            ['2026-03-19', '2026-03-21', '2026-03-23', 'Telah Dikembalikan', true,  'DP 70%',    8],
            ['2026-03-22', '2026-03-24', '2026-03-26', 'Dibatalkan',         false, 'DP 50%',    9],
            ['2026-03-26', '2026-03-28', '2026-03-30', 'Telah Dikembalikan', true,  'DP 50%',    0],

            // ══════════ APRIL 2026 ══════════
            ['2026-04-01', '2026-04-03', '2026-04-05', 'Telah Dikembalikan', true,  'DP 70%',    1],
            ['2026-04-06', '2026-04-08', '2026-04-10', 'Telah Dikembalikan', true,  'Lunas 100%',2],
            ['2026-04-10', '2026-04-12', '2026-04-13', 'Telah Dikembalikan', true,  'DP 50%',    3],
            ['2026-04-14', '2026-04-16', '2026-04-18', 'Telah Dikembalikan', true,  'DP 70%',    4],
            ['2026-04-18', '2026-04-20', '2026-04-22', 'Telah Dikembalikan', true,  'Lunas 100%',5],
            ['2026-04-22', '2026-04-24', '2026-04-26', 'Dibatalkan',         false, 'DP 50%',    6],
            ['2026-04-26', '2026-04-28', '2026-04-30', 'Telah Dikembalikan', true,  'DP 50%',    7],

            // ══════════ MEI 2026 ══════════
            ['2026-05-02', '2026-05-04', '2026-05-06', 'Telah Dikembalikan', true,  'Lunas 100%',8],
            ['2026-05-06', '2026-05-08', '2026-05-10', 'Telah Dikembalikan', true,  'DP 50%',    9],
            ['2026-05-10', '2026-05-12', '2026-05-14', 'Telah Dikembalikan', true,  'DP 70%',    0],
            ['2026-05-14', '2026-05-16', '2026-05-18', 'Telah Dikembalikan', true,  'Lunas 100%',1],
            ['2026-05-18', '2026-05-20', '2026-05-22', 'Telah Dikembalikan', true,  'DP 50%',    2],
            ['2026-05-22', '2026-05-24', '2026-05-26', 'Dibatalkan',         false, 'DP 50%',    3],
            ['2026-05-26', '2026-05-28', '2026-05-30', 'Telah Dikembalikan', true,  'DP 70%',    4],

            // ══════════ JUNI 2026 ══════════
            ['2026-06-01', '2026-06-03', '2026-06-05', 'Telah Dikembalikan', true,  'DP 50%',    5],
            ['2026-06-05', '2026-06-07', '2026-06-09', 'Telah Dikembalikan', true,  'Lunas 100%',6],
            ['2026-06-10', '2026-06-12', '2026-06-14', 'Telah Dikembalikan', true,  'DP 70%',    7],
            ['2026-06-14', '2026-06-16', '2026-06-18', 'Telah Dikembalikan', true,  'DP 50%',    8],
            ['2026-06-18', '2026-06-20', '2026-06-22', 'Telah Dikembalikan', true,  'Lunas 100%',9],
            ['2026-06-22', '2026-06-24', '2026-06-26', 'Dibatalkan',         false, 'DP 50%',    0],
            ['2026-06-27', '2026-06-29', '2026-07-01', 'Telah Dikembalikan', true,  'DP 70%',    1],

            // ══════════ JULI 2026 ══════════
            ['2026-07-01', '2026-07-03', '2026-07-05', 'Telah Dikembalikan', true,  'Lunas 100%',2],
            ['2026-07-05', '2026-07-07', '2026-07-09', 'Telah Dikembalikan', true,  'DP 50%',    3],
            ['2026-07-09', '2026-07-11', '2026-07-13', 'Telah Dikembalikan', true,  'DP 70%',    4],
            ['2026-07-13', '2026-07-15', '2026-07-17', 'Telah Dikembalikan', true,  'DP 50%',    5],
            ['2026-07-17', '2026-07-19', '2026-07-21', 'Kebaya Telah Diambil', true, 'DP 70%',   6],
            ['2026-07-20', '2026-07-22', '2026-07-24', 'Telah Dikonfirmasi', true,  'Lunas 100%',7],
            ['2026-07-24', '2026-07-26', '2026-07-28', 'Sedang Fitting',     true,  'DP 50%',    8],
            ['2026-07-27', '2026-07-29', '2026-07-31', 'Telah Dikonfirmasi', true,  'DP 70%',    9],

            // ══════════ AGUSTUS 2026 (bulan berjalan) ══════════
            ['2026-08-01', '2026-08-03', '2026-08-05', 'Telah Dikembalikan',   true,  'DP 50%',    0],
            ['2026-08-04', '2026-08-06', '2026-08-08', 'Telah Dikonfirmasi',   true,  'Lunas 100%',1],
            ['2026-08-06', '2026-08-08', '2026-08-10', 'Menunggu Fitting',     true,  'DP 70%',    2],
            ['2026-08-08', '2026-08-10', '2026-08-12', 'Sedang Fitting',       true,  'DP 50%',    3],
            ['2026-08-10', '2026-08-12', '2026-08-14', 'Kebaya Telah Diambil', true,  'DP 70%',    4],
            ['2026-08-12', '2026-08-15', '2026-08-17', 'Menunggu Konfirmasi',  true,  'Lunas 100%',5],
            ['2026-08-14', '2026-08-16', '2026-08-18', 'Menunggu Fitting',     true,  'DP 50%',    6],
            ['2026-08-16', '2026-08-18', '2026-08-20', 'Menunggu Pembayaran DP', false,'DP 50%',   7],
            ['2026-08-18', '2026-08-20', '2026-08-22', 'Menunggu Konfirmasi',  true,  'DP 70%',    8],
            ['2026-08-19', '2026-08-21', '2026-08-23', 'Menunggu Pembayaran DP', false,'DP 50%',   9],
            ['2026-08-20', '2026-08-22', '2026-08-24', 'Menunggu Konfirmasi',  true,  'Lunas 100%',0],
        ];

        // ── Generate & Insert ────────────────────────────────
        $rows = [];
        foreach ($batches as $i => $b) {
            [$created, $tglSewa, $tglKembali, $status, $sudahBayar, $skema, $kdxIdx] = $b;

            $kebaya    = $kebayaList[$i % count($kebayaList)];
            $durasi    = Carbon::parse($tglSewa)->diffInDays(Carbon::parse($tglKembali));
            $durasi    = max(1, $durasi);
            $total     = $kebaya['harga_dasar'] * $durasi;

            // Variasi harga: sesekali ada diskon atau paket weekend
            if ($i % 7 === 0) $total = intval($total * 0.9); // diskon 10%
            if ($i % 5 === 0) $total = intval($total * 1.1); // markup weekend

            $namaIdx  = $i % count($namaCustomer);
            $noWaIdx  = $i % count($noWaList);
            $alamatIdx = $i % count($alamatList);
            $metodeIdx = $i % count($metodeList);

            $rows[] = [
                'kode_transaksi'       => 'TRX-' . strtoupper(substr(md5($created . $i . $kebaya['nama']), 0, 8)),
                'nama_kebaya'          => $kebaya['nama'],
                'warna_custom_ai'      => ['Default', 'Sage Green', 'Dusty Rose', 'Ivory', 'Navy Blue', 'Maroon', 'Tosca'][$i % 7],
                'gambar_ai_result'     => null,
                'nama_user'            => $namaCustomer[$namaIdx],
                'no_wa'                => $noWaList[$noWaIdx],
                'ukuran'               => $ukuranList[$i % count($ukuranList)],
                'metode_pembayaran'    => $metodeList[$metodeIdx],
                'skema_bayar'          => $skema,
                'alamat'               => $alamatList[$alamatIdx],
                'tanggal_sewa'         => $tglSewa,
                'tanggal_pengembalian' => $tglKembali,
                'durasi_hari'          => $durasi,
                'status'               => $status,
                'total_harga'          => $total,
                'sudah_bayar'          => $sudahBayar,
                'payment_status'       => $sudahBayar ? 'paid' : 'pending',
                'transaction_id'       => null,
                'snap_token'           => null,
                'qris_url'             => null,
                'konfirmasi_pembayaran'=> null,
                'paid_at'              => $sudahBayar ? Carbon::parse($created)->addHours(rand(1, 6)) : null,
                'jumlah_dibayar'       => $sudahBayar ? $total : 0,
                'created_at'           => Carbon::parse($created)->setTime(rand(8, 20), rand(0, 59), 0),
                'updated_at'           => Carbon::parse($created)->addHours(rand(1, 48)),
            ];
        }

        // Insert ke DB (chunked agar aman)
        foreach (array_chunk($rows, 20) as $chunk) {
            DB::table('transaksi_sewa')->insert($chunk);
        }

        $this->command->info('✅ TransaksiSeeder selesai: ' . count($rows) . ' data dummy berhasil dimasukkan.');
    }
}
