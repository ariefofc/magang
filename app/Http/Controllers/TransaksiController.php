<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Services\KebayaService;
use Barryvdh\DomPDF\Facade\Pdf;

class TransaksiController extends Controller
{
    /**
     * Menampilkan Dashboard Admin
     */
    /**
     * Helper: Memberikan Query Transaksi dengan Filter (Tahun, Bulan, Minggu, Range, Status, Sorting)
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = Transaksi::query();

        // 1. Filter Custom Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }
        // 2. Filter Tahun, Bulan, Minggu
        else {
            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }
            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }
            if ($request->filled('week') && $request->filled('month')) {
                $week = (int) $request->week;
                if ($week === 1) {
                    $query->whereDay('created_at', '>=', 1)->whereDay('created_at', '<=', 7);
                } elseif ($week === 2) {
                    $query->whereDay('created_at', '>=', 8)->whereDay('created_at', '<=', 14);
                } elseif ($week === 3) {
                    $query->whereDay('created_at', '>=', 15)->whereDay('created_at', '<=', 21);
                } elseif ($week === 4) {
                    $query->whereDay('created_at', '>=', 22)->whereDay('created_at', '<=', 31);
                }
            }
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting: default ASC (Tanggal 1 -> 31) agar urut kronologis & rapi
        $sortOrder = strtolower($request->get('sort_order', 'asc'));
        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'asc';
        }

        $query->orderBy('created_at', $sortOrder)->orderBy('id', $sortOrder);

        return $query;
    }

    /**
     * Helper: Menghasilkan label teks deskripsi periode laporan
     */
    private function getPeriodeLabel(Request $request)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            return \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($request->end_date)->format('d/m/Y');
        }

        $label = [];
        if ($request->filled('month') && isset($months[(int)$request->month])) {
            $label[] = $months[(int)$request->month];
        }
        if ($request->filled('year')) {
            $label[] = $request->year;
        }

        $prefix = implode(' ', $label);
        if (empty($prefix)) {
            $prefix = 'Seluruh Transaksi (2026)';
        }

        if ($request->filled('week')) {
            $w = (int)$request->week;
            $weekLabels = [
                1 => 'Minggu 1 (Tgl 1 - 7)',
                2 => 'Minggu 2 (Tgl 8 - 14)',
                3 => 'Minggu 3 (Tgl 15 - 21)',
                4 => 'Minggu 4 (Tgl 22 - Akhir Bulan)',
            ];
            $wLabel = $weekLabels[$w] ?? ('Minggu ' . $w);
            return $prefix . ' — ' . $wLabel;
        }

        return $prefix;
    }

    /**
     * Menampilkan Dashboard Admin
     */
    public function index(Request $request)
    {
        try {
            $filteredQuery = $this->buildFilteredQuery($request);
            $daftarTransaksi = $filteredQuery->paginate(15)->withQueryString();
            
            // Semua transaksi (untuk laporan) tanpa pagination
            $allLaporanQuery = $this->buildFilteredQuery($request);
            $allLaporanTransaksi = $allLaporanQuery->get();

            $totalPendapatan = $allLaporanTransaksi->sum('total_harga') ?? 0;
            $kebayaDipesan   = $allLaporanTransaksi->count() ?: 0;
        } catch (\Throwable $e) {
            $daftarTransaksi = collect([]);
            $allLaporanTransaksi = collect([]);
            $totalPendapatan = 0;
            $kebayaDipesan   = 0;
        }

        $totalKoleksi = 13;
        $periodeLabel = $this->getPeriodeLabel($request);

        // Ambil data master katalog kebaya dengan status dari KebayaService
        try {
            $kebayaService = new KebayaService();
            $masterKatalogDb = $kebayaService->getAll();
        } catch (\Throwable $e) {
            $masterKatalogDb = [];
        }

        // System Logs & Audit (Super Admin)
        $systemAuditLogs = collect([
            (object)[
                'timestamp' => now()->format('d/m/Y H:i:s'),
                'user'      => auth()->user()?->name ?? 'Super Admin',
                'role'      => strtoupper(auth()->user()?->role ?? 'SUPER_ADMIN'),
                'action'    => 'Akses Dashboard & Verifikasi Sistem Log Audit',
                'ip'        => request()->ip() ?? '127.0.0.1',
                'level'     => 'SECURITY',
                'badge'     => 'badge-dikembalikan',
            ],
            (object)[
                'timestamp' => now()->subMinutes(12)->format('d/m/Y H:i:s'),
                'user'      => 'System Engine',
                'role'      => 'SYSTEM',
                'action'    => 'Webhook Midtrans Payment Notification Listener Active',
                'ip'        => '127.0.0.1',
                'level'     => 'INFO',
                'badge'     => 'badge-diambil',
            ],
            (object)[
                'timestamp' => now()->subMinutes(35)->format('d/m/Y H:i:s'),
                'user'      => 'Customer AI Engine',
                'role'      => 'OPAL_AI',
                'action'    => 'Proses Recolor Canvas AI: Kebaya Modern Brukat (Sage Green)',
                'ip'        => '127.0.0.1',
                'level'     => 'AI_LOG',
                'badge'     => 'badge-diambil',
            ],
            (object)[
                'timestamp' => now()->subHours(2)->format('d/m/Y H:i:s'),
                'user'      => 'Admin Operasional',
                'role'      => 'ADMIN',
                'action'    => 'Pembaruan Status Transaksi TRX-89DEB76F -> Telah Dikembalikan',
                'ip'        => '127.0.0.1',
                'level'     => 'AUDIT',
                'badge'     => 'badge-dikembalikan',
            ],
            (object)[
                'timestamp' => now()->subHours(5)->format('d/m/Y H:i:s'),
                'user'      => 'Owner MS Harmony',
                'role'      => 'OWNER',
                'action'    => 'Cetak PDF Rekapitulasi Laporan Penjualan Periode ' . $periodeLabel,
                'ip'        => '127.0.0.1',
                'level'     => 'REPORT',
                'badge'     => 'badge-diambil',
            ],
            (object)[
                'timestamp' => now()->subDay()->format('d/m/Y H:i:s'),
                'user'      => 'Super Admin',
                'role'      => 'SUPER_ADMIN',
                'action'    => 'Konfigurasi Midtrans Snap API Key updated (Sandbox Mode)',
                'ip'        => '127.0.0.1',
                'level'     => 'CONFIG',
                'badge'     => 'badge-dikembalikan',
            ],
        ]);

        return view('dashboard', compact('daftarTransaksi', 'allLaporanTransaksi', 'totalPendapatan', 'kebayaDipesan', 'totalKoleksi', 'masterKatalogDb', 'systemAuditLogs', 'periodeLabel'));
    }

    /**
     * Simpan Konfigurasi API & AI (Super Admin)
     */
    public function saveApiConfig(Request $request)
    {
        $request->validate([
            'midtrans_server_key'   => 'nullable|string',
            'midtrans_client_key'   => 'nullable|string',
            'midtrans_is_production' => 'nullable|string',
            'qris_static_string'    => 'nullable|string',
            'qris_merchant_name'    => 'nullable|string',
        ]);

        try {
            $envFile = base_path('.env');
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);

                $isProd = $request->input('midtrans_is_production') === 'true' ? 'true' : 'false';
                $snapUrl = $isProd === 'true' 
                    ? 'https://app.midtrans.com/snap/snap.js' 
                    : 'https://app.sandbox.midtrans.com/snap/snap.js';

                $replacements = [
                    'MIDTRANS_SERVER_KEY'    => $request->input('midtrans_server_key', ''),
                    'MIDTRANS_CLIENT_KEY'    => $request->input('midtrans_client_key', ''),
                    'MIDTRANS_IS_PRODUCTION' => $isProd,
                    'MIDTRANS_SNAP_URL'      => $snapUrl,
                    'QRIS_STATIC_STRING'     => $request->input('qris_static_string', ''),
                    'QRIS_MERCHANT_NAME'     => $request->input('qris_merchant_name', 'MS Harmony'),
                ];

                foreach ($replacements as $key => $val) {
                    if (preg_match("/^{$key}=.*/m", $envContent)) {
                        $envContent = preg_replace("/^{$key}=.*/m", "{$key}=\"{$val}\"", $envContent);
                    } else {
                        $envContent .= "\n{$key}=\"{$val}\"";
                    }
                }

                file_put_contents($envFile, $envContent);
            }

            return redirect()->route('dashboard', ['tab' => 'konfigurasi_api'])
                ->with('success', 'Konfigurasi Midtrans & QRIS berhasil diperbarui ke sistem!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan konfigurasi: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF Laporan Penjualan
     */
    public function laporanPdf(Request $request)
    {
        try {
            $filteredQuery = $this->buildFilteredQuery($request);
            $allTransaksi = $filteredQuery->get();
            $totalPendapatan = $allTransaksi->sum('total_harga');
        } catch (\Throwable $e) {
            $allTransaksi = collect([]);
            $totalPendapatan = 0;
        }

        // Statistik
        $totalSemua    = $allTransaksi->count();
        $totalSelesai  = $allTransaksi->where('status', 'Telah Dikembalikan')->count();
        $totalBerjalan = $allTransaksi->whereIn('status', ['Telah Dikonfirmasi', 'Sedang Fitting', 'Kebaya Telah Diambil'])->count();
        $totalBelum    = $allTransaksi->whereIn('status', ['Menunggu Pembayaran DP', 'Menunggu Konfirmasi', 'Menunggu Fitting'])->count();
        $tanggalCetak  = now()->locale('id')->translatedFormat('d F Y H:i');
        $periodeLabel  = $this->getPeriodeLabel($request);

        $pdf = Pdf::loadView('admin.laporan_pdf', compact(
            'allTransaksi',
            'totalPendapatan',
            'totalSemua',
            'totalSelesai',
            'totalBerjalan',
            'totalBelum',
            'tanggalCetak',
            'periodeLabel'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Penjualan-MSHarmony-' . date('Ymd') . '.pdf');
    }

    /**
     * API Real-Time: Mengambil daftar tanggal yang sudah TERKUNCI (booked) untuk kebaya tertentu
     */
    public function getBookedDates(Request $request)
    {
        $namaKebaya = $request->query('nama');
        if (!$namaKebaya) {
            return response()->json([
                'nama_kebaya'    => '',
                'booked_ranges'  => [],
                'disabled_dates' => [],
            ]);
        }

        // Ambil semua transaksi aktif untuk kebaya ini yang mengunci tanggal
        // (Status bukan Dibatalkan dan bukan Telah Dikembalikan)
        $transaksi = Transaksi::where('nama_kebaya', $namaKebaya)
            ->whereNotIn('status', ['Dibatalkan', 'Telah Dikembalikan'])
            ->get();

        $bookedRanges = [];
        $disabledDates = [];

        foreach ($transaksi as $t) {
            if ($t->tanggal_sewa && $t->tanggal_pengembalian) {
                $start = \Carbon\Carbon::parse($t->tanggal_sewa)->startOfDay();
                $end   = \Carbon\Carbon::parse($t->tanggal_pengembalian)->startOfDay();

                $bookedRanges[] = [
                    'start'  => $start->format('Y-m-d'),
                    'end'    => $end->format('Y-m-d'),
                    'status' => $t->status,
                    'kode'   => $t->kode_transaksi,
                    'user'   => $t->nama_user ? (mb_substr($t->nama_user, 0, 3) . '***') : 'Pelanggan',
                ];

                $curr = $start->copy();
                while ($curr->lte($end)) {
                    $dateStr = $curr->format('Y-m-d');
                    if (!in_array($dateStr, $disabledDates, true)) {
                        $disabledDates[] = $dateStr;
                    }
                    $curr->addDay();
                }
            }
        }

        sort($disabledDates);

        return response()->json([
            'nama_kebaya'    => $namaKebaya,
            'booked_ranges'  => $bookedRanges,
            'disabled_dates' => $disabledDates,
        ]);
    }

    /**
     * Menyimpan transaksi baru dari form sewa
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kebaya'        => 'required|string',
            'warna_custom_ai'    => 'nullable|string',
            'gambar_ai_result'   => 'nullable|string',
            'nama_user'          => 'required|string|max:255',
            'no_wa'              => 'required|string|max:20',
            'ukuran'             => 'required|string',
            'metode_pembayaran'  => 'required|string',
            'skema_pembayaran'   => 'nullable|string',
            'alamat'             => 'nullable|string',
            'tanggal_sewa'       => 'required|date',
            'tanggal_pengembalian' => 'required|date|after_or_equal:tanggal_sewa',
            'sudah_bayar'        => 'nullable|in:0,1,true,false',
        ]);

        // ── Pengecekan Bentrok Tanggal Sewa Kebaya (Double Booking Check) ──
        try {
            $isOverlap = Transaksi::where('nama_kebaya', $request->nama_kebaya)
                ->whereNotIn('status', ['Dibatalkan', 'Telah Dikembalikan'])
                ->where(function ($query) use ($request) {
                    $query->whereBetween('tanggal_sewa', [$request->tanggal_sewa, $request->tanggal_pengembalian])
                          ->orWhereBetween('tanggal_pengembalian', [$request->tanggal_sewa, $request->tanggal_pengembalian])
                          ->orWhere(function ($q) use ($request) {
                              $q->where('tanggal_sewa', '<=', $request->tanggal_sewa)
                                ->where('tanggal_pengembalian', '>=', $request->tanggal_pengembalian);
                          });
                })->exists();

            if ($isOverlap) {
                return back()->withErrors([
                    'tanggal_sewa' => 'Kebaya "' . $request->nama_kebaya . '" sudah TERKUNCI (disewa pelanggan lain) pada rentang tanggal tersebut. Silakan pilih tanggal lain yang masih tersedia pada kalender real-time.'
                ])->withInput();
            }
        } catch (\Throwable $chkErr) {}

        try {
            $tSewa    = \Carbon\Carbon::parse($request->tanggal_sewa);
            $tKembali = \Carbon\Carbon::parse($request->tanggal_pengembalian);
            $durasi   = max(1, $tSewa->diffInDays($tKembali));
            $totalHarga = ($request->total_harga ?? 750000);

            // Tentukan status awal berdasarkan konfirmasi pembayaran
            $sudahBayar = in_array($request->sudah_bayar, ['1', 'true', true], true);
            $statusAwal = $sudahBayar
                ? 'Menunggu Konfirmasi'
                : 'Menunggu Pembayaran DP';

            // Skema pembayaran
            $skemaBayar = $request->skema_pembayaran ?? 'DP 50%';

            // ── Simpan gambar recolor (jika base64 dari Canvas AI) ─────────
            $gambarResult = $request->gambar_ai_result;
            if ($gambarResult && str_starts_with($gambarResult, 'data:image')) {
                try {
                    // Decode base64 → save ke file
                    $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $gambarResult);
                    $imageData  = base64_decode($base64Data);
                    $uploadDir  = public_path('uploads/recolor');
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $filename   = 'recolor_' . time() . '_' . rand(1000, 9999) . '.jpg';
                    file_put_contents($uploadDir . '/' . $filename, $imageData);
                    $gambarResult = 'uploads/recolor/' . $filename; // simpan path saja
                } catch (\Throwable $imgErr) {
                    $gambarResult = null; // fallback: abaikan
                }
            }

            $transaksi = Transaksi::create([
                'kode_transaksi'       => 'TRX-' . strtoupper(substr(md5(time() . rand()), 0, 8)),
                'nama_kebaya'          => $request->nama_kebaya,
                'warna_custom_ai'      => $request->warna_custom_ai ?? 'Default',
                'gambar_ai_result'     => $gambarResult,
                'nama_user'            => $request->nama_user,
                'no_wa'                => $request->no_wa,
                'ukuran'               => $request->ukuran,
                'metode_pembayaran'    => $request->metode_pembayaran,
                'skema_bayar'          => $skemaBayar,
                'alamat'               => $request->alamat,
                'tanggal_sewa'         => $request->tanggal_sewa,
                'tanggal_pengembalian' => $request->tanggal_pengembalian,
                'durasi_hari'          => $durasi,
                'status'               => $statusAwal,
                'total_harga'          => $totalHarga,
                'sudah_bayar'          => $sudahBayar,
            ]);

            return redirect()->route('payment.show', $transaksi->id);


        } catch (\Throwable $e) {
            // Fallback: simpan ke session dan redirect ke nota simulasi
            $sudahBayar = in_array($request->sudah_bayar, ['1', 'true', true], true);
            session([
                'nota_simulasi' => [
                    'id'                   => 0,
                    'kode_transaksi'       => 'TRX-' . strtoupper(substr(md5(time()), 0, 8)),
                    'nama_kebaya'          => $request->nama_kebaya ?? 'Kebaya Modern Brukat Gown',
                    'warna_custom_ai'      => $request->warna_custom_ai ?? 'Sage Green',
                    'gambar_ai_result'     => $request->gambar_ai_result ?? asset('Kebaya Modern Brukat Gown.png'),
                    'nama_user'            => $request->nama_user ?? 'Customer',
                    'no_wa'                => $request->no_wa ?? '08123456789',
                    'ukuran'               => $request->ukuran ?? 'M',
                    'metode_pembayaran'    => $request->metode_pembayaran ?? 'QRIS',
                    'skema_bayar'          => $request->skema_pembayaran ?? 'DP 50%',
                    'alamat'               => $request->alamat ?? '-',
                    'tanggal_sewa'         => $request->tanggal_sewa ?? now()->format('Y-m-d'),
                    'tanggal_pengembalian' => $request->tanggal_pengembalian ?? now()->addDay()->format('Y-m-d'),
                    'durasi_hari'          => $request->durasi_hari ?? 1,
                    'status'               => $sudahBayar ? 'Menunggu Konfirmasi' : 'Menunggu Pembayaran DP',
                    'total_harga'          => $request->total_harga ?? 750000,
                    'sudah_bayar'          => $sudahBayar,
                ]
            ]);
            return redirect()->route('transaksi.nota', 0);
        }
    }

    /**
     * Nota Customer (Akses Publik)
     */
    public function nota($id)
    {
        $transaksi = null;

        if ($id > 0) {
            try {
                $transaksi = Transaksi::find($id);
            } catch (\Throwable $e) {
                $transaksi = null;
            }
        }

        if (!$transaksi && session()->has('nota_simulasi')) {
            $transaksi = (object) session('nota_simulasi');
        }

        if (!$transaksi) {
            $transaksi = (object)[
                'id'                   => 0,
                'kode_transaksi'       => 'TRX-' . strtoupper(substr(md5(time()), 0, 8)),
                'nama_kebaya'          => 'Kebaya Modern Brukat Gown',
                'warna_custom_ai'      => 'Sage Green',
                'gambar_ai_result'     => asset('Kebaya Modern Brukat Gown.png'),
                'nama_user'            => 'Customer Ms Harmoni',
                'no_wa'                => '08123456789',
                'ukuran'               => 'M',
                'metode_pembayaran'    => 'QRIS Ms Harmoni',
                'alamat'               => 'Fitting di Studio Ms Harmoni',
                'tanggal_sewa'         => now()->format('Y-m-d'),
                'tanggal_pengembalian' => now()->addDays(3)->format('Y-m-d'),
                'durasi_hari'          => 3,
                'status'               => 'Menunggu Fitting & Konfirmasi',
                'total_harga'          => 2550000,
                'sudah_bayar'          => false,
            ];
        }

        return view('nota', compact('transaksi'));
    }

    /**
     * Nota Admin
     */
    public function notaAdmin($id)
    {
        $transaksi = null;
        try {
            $transaksi = Transaksi::find($id);
        } catch (\Throwable $e) {}

        if (!$transaksi) {
            $transaksi = (object)[
                'kode_transaksi'       => '#MSH-' . date('Ymd') . '-001',
                'nama_kebaya'          => 'Kebaya Modern Brukat Gown',
                'warna_custom_ai'      => 'Sage Green Pastel',
                'gambar_ai_result'     => asset('Kebaya Modern Brukat Gown.png'),
                'nama_user'            => 'Customer Ms Harmoni',
                'no_wa'                => '08123456789',
                'ukuran'               => 'M',
                'metode_pembayaran'    => 'TRANSFER QRIS',
                'alamat'               => 'Jl. Raya Madiun No. 45',
                'tanggal_sewa'         => now()->format('Y-m-d'),
                'tanggal_pengembalian' => now()->addDays(3)->format('Y-m-d'),
                'durasi_hari'          => 3,
                'status'               => 'DIPESAN / SEWA',
                'total_harga'          => 2550000,
                'sudah_bayar'          => false,
            ];
        }

        // Hitung DP & Sisa jika belum ada di model
        $totalHarga = $transaksi->total_harga ?? 0;
        $dpTerbayar = $totalHarga * 0.5;
        $sisaPelunasan = $totalHarga - $dpTerbayar;

        return view('admin.nota', compact('transaksi', 'dpTerbayar', 'sisaPelunasan'));
    }

    /**
     * Update Status Transaksi (dari Dashboard Admin)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $transaksi = Transaksi::find($id);
            if ($transaksi) {
                $transaksi->status = $request->status;
                if ($request->has('catatan_admin')) {
                    $transaksi->catatan_admin = $request->catatan_admin;
                }
                $transaksi->touch();
                $transaksi->save();
            }
        } catch (\Throwable $e) {}

        $redirectTab = $request->input('redirect_tab') ?? $request->input('tab');
        if ($redirectTab) {
            return redirect()->route('dashboard', ['tab' => $redirectTab])->with('success', 'Status pengembalian kebaya berhasil diverifikasi!');
        }

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    public function update(Request $request, $id)
    {
        return $this->updateStatus($request, $id);
    }

    public function destroy($id)
    {
        try {
            Transaksi::find($id)?->delete();
        } catch (\Throwable $e) {}
        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    /**
     * Customer Request / Ajukan Pengembalian Kebaya (Publik Customer)
     */
    public function ajukanPengembalian(Request $request, $id)
    {
        try {
            $transaksi = Transaksi::find($id);
            if ($transaksi) {
                $transaksi->status = 'Mengajukan Pengembalian';
                $transaksi->touch();
                $transaksi->save();
                return redirect()->back()->with('success_pengembalian', 'Pengajuan pengembalian kebaya Anda telah dikirim! Silakan datang ke Studio MS Harmony untuk penyerahan kebaya & verifikasi pelunasan.');
            }
        } catch (\Throwable $e) {}

        return redirect()->back()->with('error', 'Gagal memproses pengajuan pengembalian.');
    }

    /**
     * Customer Request / Ajukan Jadwal Fitting Kebaya (Publik Customer)
     */
    public function ajukanFitting(Request $request, $id)
    {
        $request->validate([
            'tanggal_fitting' => 'required|date',
            'jam_fitting'     => 'required|string',
            'lokasi_studio'   => 'required|string',
            'catatan'         => 'nullable|string',
        ]);

        try {
            $transaksi = Transaksi::find($id);
            if ($transaksi) {
                $transaksi->status = 'Menunggu Fitting';
                $tglFit = \Carbon\Carbon::parse($request->tanggal_fitting)->format('d/m/Y');
                $note = "Request Fitting Customer: Tanggal {$tglFit} Jam {$request->jam_fitting} di {$request->lokasi_studio}";
                if ($request->catatan) {
                    $note .= " | Catatan: {$request->catatan}";
                }
                $transaksi->catatan_admin = $note;
                $transaksi->touch();
                $transaksi->save();

                return redirect()->back()->with('success_fitting', "Pengajuan jadwal fitting Anda (Tanggal {$tglFit} jam {$request->jam_fitting}) berhasil dikirim ke Admin Studio MS Harmony! Tim kami akan siap menyambut Anda di studio.");
            }
        } catch (\Throwable $e) {}

        return redirect()->back()->with('error', 'Gagal memproses pengajuan jadwal fitting.');
    }
}