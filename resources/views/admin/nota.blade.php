<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Admin — {{ $transaksi->kode_transaksi ?? 'MSH-001' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Modular CSS Admin --}}
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/buttons.css') }}">
</head>
<body class="nota-page">

    <div class="nota-container nota-container--admin">
        
        <!-- Header -->
        <div class="nota-header">
            <div class="header-brand">
                <img src="{{ asset('logo-mayangsari.jpg') }}" alt="Logo Ms Harmoni" class="brand-logo brand-logo--admin">
                <div>
                    <span class="brand-subtitle">SIM Ms Harmoni — Dashboard Admin</span>
                    <h1 class="header-title font-serif">Nota Transaksi Resmi</h1>
                    <span class="admin-badge">🔐 Salinan Admin</span>
                </div>
            </div>
            <div style="text-align:right;">
                <span style="font-size:11px;color:#d6d3d1;display:block;">No. Nota / Transaksi:</span>
                <span class="trx-code">{{ $transaksi->kode_transaksi ?? '#MSH-' . date('Ymd') . '-001' }}</span>
            </div>
        </div>

        @php
            $sudahBayar = $transaksi->sudah_bayar ?? false;
            $totalHrg   = $transaksi->total_harga ?? 0;
            $dp         = $dpTerbayar ?? ($totalHrg * 0.5);
            $sisa       = $sisaPelunasan ?? ($totalHrg - $dp);
            $tglCetak   = now()->format('d M Y, H:i') . ' WIB';
            $tglSewa    = ($transaksi->tanggal_sewa instanceof \Carbon\Carbon)
                ? $transaksi->tanggal_sewa->format('d M Y')
                : (\Carbon\Carbon::parse($transaksi->tanggal_sewa ?? now())->format('d M Y'));
            $tglKembali = ($transaksi->tanggal_pengembalian instanceof \Carbon\Carbon)
                ? $transaksi->tanggal_pengembalian->format('d M Y')
                : (\Carbon\Carbon::parse($transaksi->tanggal_pengembalian ?? now()->addDays(3))->format('d M Y'));
            $durasi     = $transaksi->durasi_hari ?? 1;
        @endphp

        <!-- Status Bar -->
        <div class="status-bar status-bar--admin">
            <span>Status: <strong style="color:#78350f;">{{ $transaksi->status ?? 'DIPESAN / SEWA' }}</strong></span>
            <span class="status-badge status-badge--admin">{{ $transaksi->metode_pembayaran ?? 'TRANSFER / QRIS' }}</span>
        </div>

        <div class="nota-body">

            <!-- Info Cetak & Periode -->
            <div class="info-grid-2" style="margin-bottom:16px;">
                <div class="info-card">
                    <span class="card-label">Tanggal Cetak</span>
                    <p style="color:#1c1917;font-weight:600;">{{ $tglCetak }}</p>
                </div>
                <div class="info-card">
                    <span class="card-label">Periode Sewa</span>
                    <p style="color:#92400e;font-weight:700;">{{ $tglSewa }} – {{ $tglKembali }}</p>
                    <p style="font-size:11px;color:#78716c;">({{ $durasi }} hari)</p>
                </div>
            </div>

            <!-- Data Penyewa Lengkap -->
            <div class="penyewa-card">
                <span class="card-label">Data Penyewa Lengkap</span>
                <div class="penyewa-grid">
                    <div>
                        <p style="font-size:10px;color:#a8a29e;font-weight:700;text-transform:uppercase;margin-bottom:3px;">Nama</p>
                        <p style="font-weight:700;font-size:14px;color:#1c1917;">{{ $transaksi->nama_user ?? '-' }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#a8a29e;font-weight:700;text-transform:uppercase;margin-bottom:3px;">No. WhatsApp</p>
                        <p style="font-weight:700;color:#047857;font-size:13px;">📱 {{ $transaksi->no_wa ?? '-' }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#a8a29e;font-weight:700;text-transform:uppercase;margin-bottom:3px;">Alamat / Catatan Fitting</p>
                        <p style="color:#57534e;font-size:12px;">📍 {{ $transaksi->alamat ? $transaksi->alamat : '— Fitting di Studio —' }}</p>
                    </div>
                </div>
            </div>

            <!-- Status Pembayaran DP -->
            <div class="bayar-status {{ $sudahBayar ? 'bayar-ok' : 'bayar-belum' }}">
                <span style="font-size:20px;">{{ $sudahBayar ? '✅' : '⏳' }}</span>
                <div>
                    <p style="font-weight:700;font-size:13px;">
                        {{ $sudahBayar ? 'DP Sudah Dibayar Customer' : 'DP Belum Dibayar — Menunggu Konfirmasi' }}
                    </p>
                    <p style="font-size:11px;margin-top:2px;">
                        {{ $sudahBayar
                            ? 'Customer mengkonfirmasi sudah melakukan pembayaran DP. Verifikasi bukti transfer/QRIS jika diperlukan.'
                            : 'Customer memilih bayar nanti. Konfirmasi pembayaran sebelum menjadwalkan fitting.' }}
                    </p>
                </div>
            </div>

            <!-- Detail Kebaya -->
            <div class="card-box">
                <img src="{{ $transaksi->gambar_ai_result ?? asset('Kebaya Modern Brukat Gown.png') }}" alt="Kebaya" class="kebaya-img kebaya-img--admin">
                <div class="kebaya-info">
                    <span class="card-label">Kebaya & Detail</span>
                    <h2 class="kebaya-title kebaya-title--admin font-serif">{{ $transaksi->nama_kebaya ?? 'Kebaya Modern Brukat Gown' }}</h2>
                    <p style="margin-bottom:4px;"><strong>Ukuran:</strong> <span style="background:#e7e5e4;padding:2px 6px;border-radius:4px;font-weight:bold;">Size {{ $transaksi->ukuran ?? 'M' }}</span></p>
                    <p><strong>AI Color:</strong> <span style="color:#92400e;font-style:italic;">"{{ $transaksi->warna_custom_ai ?? 'Default' }}"</span></p>
                </div>
            </div>

            <!-- Rincian Keuangan -->
            <div class="finance-card">
                <div class="finance-row">
                    <span>Total Harga Sewa ({{ $durasi }} hari):</span>
                    <span>Rp {{ number_format($totalHrg, 0, ',', '.') }}</span>
                </div>
                <div class="finance-row dp">
                    <span>Uang Muka / DP 50% {{ $sudahBayar ? '(✅ Terbayar)' : '(⏳ Belum)' }}:</span>
                    <span>- Rp {{ number_format($dp, 0, ',', '.') }}</span>
                </div>
                <div class="finance-row total">
                    <span>SISA PELUNASAN (saat pengambilan):</span>
                    <span>Rp {{ number_format($sisa, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Syarat & Ketentuan -->
            <div class="info-step">
                <p style="font-weight:700;color:#292524;">📋 Syarat & Ketentuan Persewaan:</p>
                <p>1. Pelunasan wajib dibayarkan saat pengambilan kebaya.</p>
                <p>2. Keterlambatan pengembalian dikenakan denda Rp 50.000 / hari.</p>
                <p>3. Dilarang mencuci kebaya sendiri (perawatan khusus dry-clean oleh studio).</p>
                <p>4. Kerusakan akibat kelalaian menjadi tanggung jawab penyewa.</p>
            </div>

            <!-- Footer -->
            <div class="footer-note">
                <p>Terima kasih telah mempercayakan busana Anda pada <strong>Ms Harmoni</strong>.</p>
                <p><em>*Dokumen ini adalah salinan admin. Harap simpan sebagai arsip transaksi.</em></p>
            </div>

            <!-- Buttons -->
            <div class="action-buttons no-print">
                <button onclick="window.print()" class="btn btn-print">
                    🖨️ Cetak / Simpan PDF
                </button>
                <a href="{{ url('/dashboard') }}" class="btn btn-back">
                    🏠 Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>

</body>
</html>