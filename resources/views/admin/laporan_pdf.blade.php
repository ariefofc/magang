<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penjualan — MS Harmony Penyewaan Kebaya</title>
    <style>
        /* ── Base ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #2C1810;
            background: #FFFDF9;
        }

        /* ── Header Kop Surat ── */
        .kop {
            border-bottom: 3px solid #6B0012;
            padding-bottom: 10px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .kop-logo {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 2px solid #C8A96E;
            object-fit: cover;
        }
        .kop-title {
            font-size: 18pt;
            font-weight: bold;
            color: #6B0012;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .kop-sub {
            font-size: 8pt;
            color: #8A7060;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .kop-address {
            font-size: 7pt;
            color: #B0998A;
            margin-top: 3px;
        }
        .kop-right {
            margin-left: auto;
            text-align: right;
        }
        .kop-right .report-title {
            font-size: 13pt;
            font-weight: bold;
            color: #4A0B10;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-right .report-date {
            font-size: 7.5pt;
            color: #8A7060;
            margin-top: 3px;
        }

        /* ── Divider Gold ── */
        .gold-line {
            height: 2px;
            background: linear-gradient(to right, #C8A96E, #F5EDD8, #C8A96E);
            margin: 10px 0;
        }

        /* ── Statistik Ringkasan ── */
        .stats-row {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
        }
        .stat-box {
            flex: 1;
            border: 1px solid #E8D9C0;
            border-radius: 6px;
            padding: 8px 10px;
            background: #FDF8F0;
            text-align: center;
        }
        .stat-box.omzet {
            background: #FFF8F0;
            border-color: #C8A96E;
            border-width: 1.5px;
        }
        .stat-label {
            font-size: 6.5pt;
            color: #8A7060;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .stat-value {
            font-size: 11pt;
            font-weight: bold;
            color: #6B0012;
        }
        .stat-value.green { color: #15803d; }
        .stat-value.blue  { color: #1d4ed8; }
        .stat-value.amber { color: #92400e; }
        .stat-sub {
            font-size: 6pt;
            color: #B0998A;
            margin-top: 2px;
        }

        /* ── Section Title ── */
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            color: #4A0B10;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-left: 3px solid #C8A96E;
            padding-left: 7px;
            margin-bottom: 8px;
        }

        /* ── Tabel Transaksi ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        thead tr {
            background: #6B0012;
            color: #F5EDD8;
        }
        thead th {
            padding: 6px 7px;
            text-align: left;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }

        tbody tr:nth-child(even) { background: #FDF8F0; }
        tbody tr:nth-child(odd)  { background: #FFFDF9; }
        tbody tr:hover { background: #F5EDD8; }

        tbody td {
            padding: 5px 7px;
            border-bottom: 1px solid #E8D9C0;
            color: #3D342F;
            vertical-align: middle;
        }
        tbody td.right { text-align: right; }
        tbody td.center { text-align: center; }
        tbody td.mono { font-family: 'Courier New', monospace; font-size: 7pt; }
        tbody td.price { color: #6B0012; font-weight: bold; text-align: right; }
        tbody td.muted { color: #8A7060; }

        /* ── Status Badge ── */
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-selesai    { background: #dcfce7; color: #14532d; }
        .badge-berjalan   { background: #dbeafe; color: #1e3a8a; }
        .badge-menunggu   { background: #fef9c3; color: #713f12; }
        .badge-batal      { background: #fee2e2; color: #7f1d1d; }

        /* ── Footer Tabel ── */
        tfoot td {
            padding: 6px 7px;
            font-weight: bold;
            background: #F5EDD8;
            border-top: 2px solid #C8A96E;
            font-size: 8pt;
        }
        tfoot td.total-omzet { color: #6B0012; font-size: 9pt; text-align: right; }
        tfoot td.total-label { text-align: right; color: #4A0B10; }

        /* ── Footer Halaman ── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0; right: 0;
            border-top: 1px solid #E8D9C0;
            padding: 6px 14px;
            display: flex;
            justify-content: space-between;
            font-size: 6.5pt;
            color: #B0998A;
            background: #FFFDF9;
        }
        .tanda-tangan {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            text-align: center;
            min-width: 140px;
        }
        .ttd-label {
            font-size: 7.5pt;
            color: #4A0B10;
            margin-bottom: 4px;
        }
        .ttd-line {
            border-top: 1px solid #4A0B10;
            width: 120px;
            margin: 30px auto 4px;
        }
        .ttd-name {
            font-size: 7pt;
            color: #6B0012;
            font-weight: bold;
        }

        /* Nomor urut kolom */
        .no-col { width: 22px; text-align: center; color: #B0998A; }
    </style>
</head>
<body>

    {{-- ══ HEADER KOP SURAT ══ --}}
    <table style="width:100%;margin-bottom:10px;">
        <tr>
            <td style="width:55px;vertical-align:middle;padding-right:10px;">
                <img src="{{ public_path('logo-mayangsari.jpeg') }}"
                     alt="MS Harmony"
                     style="width:50px;height:50px;border-radius:50%;border:2px solid #C8A96E;object-fit:cover;">
            </td>
            <td style="vertical-align:middle;">
                <div style="font-size:16pt;font-weight:bold;color:#6B0012;letter-spacing:2px;text-transform:uppercase;">MS HARMONY</div>
                <div style="font-size:8pt;color:#8A7060;margin-top:1px;letter-spacing:1px;">BOUTIQUE PENYEWAAN KEBAYA PREMIUM</div>
                <div style="font-size:7pt;color:#B0998A;margin-top:2px;">📍 Jogja: Jl Buwosox No.A2, Bangunjiwo, Bantul &nbsp;|&nbsp; Purworejo: Sangubanyu, Grabag</div>
            </td>
            <td style="vertical-align:middle;text-align:right;">
                <div style="font-size:13pt;font-weight:bold;color:#4A0B10;text-transform:uppercase;letter-spacing:1px;">Laporan Penjualan</div>
                <div style="font-size:7.5pt;color:#8A7060;margin-top:3px;">Dicetak: {{ $tanggalCetak }} WIB</div>
                <div style="font-size:7pt;color:#B0998A;">Periode: {{ $periodeLabel ?? 'Seluruh Transaksi' }}</div>
            </td>
        </tr>
    </table>

    <div style="height:2px;background:#C8A96E;margin-bottom:12px;"></div>

    {{-- ══ STATISTIK RINGKASAN ══ --}}
    <table style="width:100%;margin-bottom:14px;border-collapse:separate;border-spacing:8px 0;">
        <tr>
            {{-- Total Omzet --}}
            <td style="width:28%;background:#FFF8F0;border:1.5px solid #C8A96E;border-radius:6px;padding:8px 10px;text-align:center;vertical-align:middle;">
                <div style="font-size:6.5pt;color:#8A7060;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">💰 Total Omzet Terekap</div>
                <div style="font-size:12pt;font-weight:bold;color:#6B0012;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                <div style="font-size:6pt;color:#B0998A;margin-top:2px;">Dari {{ $totalSemua }} transaksi</div>
            </td>
            {{-- Selesai --}}
            <td style="width:18%;background:#F0FDF4;border:1px solid #bbf7d0;border-radius:6px;padding:8px 10px;text-align:center;vertical-align:middle;">
                <div style="font-size:6.5pt;color:#166534;text-transform:uppercase;margin-bottom:4px;">✅ Selesai</div>
                <div style="font-size:16pt;font-weight:bold;color:#15803d;">{{ $totalSelesai }}</div>
                <div style="font-size:6pt;color:#B0998A;margin-top:2px;">Telah Dikembalikan</div>
            </td>
            {{-- Berjalan --}}
            <td style="width:18%;background:#EFF6FF;border:1px solid #bfdbfe;border-radius:6px;padding:8px 10px;text-align:center;vertical-align:middle;">
                <div style="font-size:6.5pt;color:#1e40af;text-transform:uppercase;margin-bottom:4px;">🔵 Berjalan</div>
                <div style="font-size:16pt;font-weight:bold;color:#1d4ed8;">{{ $totalBerjalan }}</div>
                <div style="font-size:6pt;color:#B0998A;margin-top:2px;">Fitting / Diambil</div>
            </td>
            {{-- Pending --}}
            <td style="width:18%;background:#FFFBEB;border:1px solid #fde68a;border-radius:6px;padding:8px 10px;text-align:center;vertical-align:middle;">
                <div style="font-size:6.5pt;color:#92400e;text-transform:uppercase;margin-bottom:4px;">⏳ Pending</div>
                <div style="font-size:16pt;font-weight:bold;color:#b45309;">{{ $totalBelum }}</div>
                <div style="font-size:6pt;color:#B0998A;margin-top:2px;">Menunggu Konfirmasi</div>
            </td>
            {{-- Total Semua --}}
            <td style="width:18%;background:#FDF4FF;border:1px solid #e9d5ff;border-radius:6px;padding:8px 10px;text-align:center;vertical-align:middle;">
                <div style="font-size:6.5pt;color:#6b21a8;text-transform:uppercase;margin-bottom:4px;">📊 Total Pesanan</div>
                <div style="font-size:16pt;font-weight:bold;color:#7c3aed;">{{ $totalSemua }}</div>
                <div style="font-size:6pt;color:#B0998A;margin-top:2px;">Seluruh Transaksi</div>
            </td>
        </tr>
    </table>

    {{-- ══ TABEL REKAPITULASI ══ --}}
    <div style="font-size:9pt;font-weight:bold;color:#4A0B10;text-transform:uppercase;letter-spacing:0.8px;border-left:3px solid #C8A96E;padding-left:7px;margin-bottom:8px;">
        Rekapitulasi Transaksi
    </div>

    @php
        $lapTotal = 0;
        $lapDP    = 0;
        $nomorUrut = 1;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width:20px;text-align:center;">#</th>
                <th style="width:50px;">Tanggal</th>
                <th style="width:75px;">Kode Transaksi</th>
                <th style="width:90px;">Customer</th>
                <th style="width:120px;">Kebaya</th>
                <th style="width:65px;">Metode Bayar</th>
                <th style="width:40px;text-align:center;">Durasi</th>
                <th style="width:80px;text-align:right;">Total Biaya</th>
                <th style="width:70px;text-align:right;">DP (50%)</th>
                <th style="width:65px;text-align:center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allTransaksi as $trx)
                @php
                    $lapTotal   += $trx->total_harga;
                    $lapDP      += $trx->total_harga * 0.5;
                    $tglTrx      = isset($trx->created_at) ? \Carbon\Carbon::parse($trx->created_at)->format('d/m/Y') : '-';
                    $tglSewa     = $trx->tanggal_sewa ? \Carbon\Carbon::parse($trx->tanggal_sewa)->format('d/m/Y') : '-';
                    $statusLabel = match($trx->status ?? '') {
                        'Telah Dikembalikan'     => 'SELESAI',
                        'Telah Dikonfirmasi'     => 'BERJALAN',
                        'Sedang Fitting'         => 'FITTING',
                        'Kebaya Telah Diambil'   => 'DIAMBIL',
                        'Menunggu Pembayaran DP' => 'BELUM DP',
                        'Menunggu Konfirmasi'    => 'PENDING',
                        'Menunggu Fitting'       => 'BELUM FIT',
                        'Dibatalkan'             => 'BATAL',
                        default                  => $trx->status ?? '-',
                    };
                    $badgeClass = match($trx->status ?? '') {
                        'Telah Dikembalikan'                            => 'badge-selesai',
                        'Telah Dikonfirmasi','Sedang Fitting',
                        'Kebaya Telah Diambil'                         => 'badge-berjalan',
                        'Menunggu Pembayaran DP','Menunggu Konfirmasi',
                        'Menunggu Fitting'                             => 'badge-menunggu',
                        'Dibatalkan'                                   => 'badge-batal',
                        default                                        => 'badge-menunggu',
                    };
                @endphp
                <tr>
                    <td class="no-col">{{ $nomorUrut++ }}</td>
                    <td class="muted" style="white-space:nowrap;">{{ $tglTrx }}</td>
                    <td class="mono">{{ $trx->kode_transaksi }}</td>
                    <td style="font-weight:600;color:#3D342F;">{{ $trx->nama_user }}</td>
                    <td style="color:#5A4A40;">{{ \Illuminate\Support\Str::limit($trx->nama_kebaya, 28) }}</td>
                    <td class="muted">{{ $trx->metode_pembayaran ?? '-' }}</td>
                    <td class="center muted">{{ $trx->durasi_hari ?? '-' }} hr</td>
                    <td class="price">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                    <td style="text-align:right;color:#15803d;">Rp {{ number_format($trx->total_harga * 0.5, 0, ',', '.') }}</td>
                    <td class="center"><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:20px;color:#8A7060;">
                        Belum ada data transaksi yang tercatat.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($allTransaksi->count() > 0)
        <tfoot>
            <tr>
                <td colspan="7" class="total-label">TOTAL REKAPITULASI ({{ $allTransaksi->count() }} Transaksi):</td>
                <td class="total-omzet">Rp {{ number_format($lapTotal, 0, ',', '.') }}</td>
                <td style="text-align:right;color:#166534;font-weight:bold;">Rp {{ number_format($lapDP, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- ══ TANDA TANGAN ══ --}}
    <table style="width:100%;margin-top:24px;">
        <tr>
            <td style="vertical-align:bottom;">
                <div style="font-size:7pt;color:#8A7060;font-style:italic;">
                    * Laporan ini dibuat secara otomatis oleh Sistem Informasi MS Harmony.<br>
                    * Harap konfirmasi data dengan admin sebelum digunakan sebagai laporan resmi.
                </div>
            </td>
            <td style="width:150px;text-align:center;vertical-align:bottom;">
                <div style="font-size:7.5pt;color:#4A0B10;margin-bottom:2px;">Disetujui oleh,</div>
                <div style="border-top:1px solid #4A0B10;width:120px;margin:32px auto 4px;"></div>
                <div style="font-size:7pt;color:#6B0012;font-weight:bold;">Admin MS Harmony</div>
                <div style="font-size:6pt;color:#8A7060;">Boutique Penyewaan Kebaya</div>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div style="position:fixed;bottom:0;left:0;right:0;border-top:1px solid #E8D9C0;padding:5px 14px;display:flex;justify-content:space-between;font-size:6pt;color:#B0998A;background:#FFFDF9;">
        <span>MS Harmony — Boutique Penyewaan Kebaya Premium | Sistem Informasi Manajemen</span>
        <span>Laporan dicetak: {{ $tanggalCetak }} WIB</span>
    </div>

</body>
</html>
