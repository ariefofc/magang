<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nota Pemesanan — {{ $transaksi->kode_transaksi ?? 'TRX-000' }} | MS Harmony</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Modular CSS --}}
    <link rel="stylesheet" href="{{ asset('css/layouts/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/buttons.css') }}">
</head>
<body class="nota-page">

    <div class="nota-container">
        
        <!-- Header Nota -->
        <div class="nota-header">
            <div class="header-brand">
                <img src="{{ asset('logo-mayangsari.jpg') }}" 
                     onerror="this.onerror=null; this.src='{{ asset('logo-mayangsari.jpeg') }}';"
                     alt="Logo Ms Harmoni" 
                     class="brand-logo">
                <div>
                    <span class="brand-subtitle">Ms Harmoni Kebaya Boutique</span>
                    <h1 class="header-title font-serif">Nota Bukti Pemesanan</h1>
                </div>
            </div>
            <div style="text-align:right;">
                <span style="font-size:12px;color:#d6d3d1;display:block;">Kode Transaksi:</span>
                <span class="trx-code">{{ $transaksi->kode_transaksi ?? 'TRX-DEFAULT' }}</span>
            </div>
        </div>

        @php
            $sudahBayar  = $transaksi->sudah_bayar ?? false;
            $metode      = strtolower($transaksi->metode_pembayaran ?? '');
            $isQris      = str_contains($metode, 'qris');
            $isBank      = str_contains($metode, 'seabank') || str_contains($metode, 'bank') || str_contains($metode, 'transfer');
            $totalHrg    = $transaksi->total_harga ?? 0;

            // Skema bayar dinamis
            $skemaBayar  = strtolower($transaksi->skema_bayar ?? 'dp50');
            $pctDP       = str_contains($skemaBayar, '70') ? 0.7 : (str_contains($skemaBayar, 'lunas') || str_contains($skemaBayar, '100') ? 1.0 : 0.5);
            $labelSkema  = $pctDP >= 1.0 ? 'Lunas 100%' : ($pctDP >= 0.7 ? 'DP 70%' : 'DP 50%');
            $dp          = ceil($totalHrg * $pctDP);
            $sisa        = $totalHrg - $dp;

            $tglSewa     = $transaksi->tanggal_sewa    ? \Carbon\Carbon::parse($transaksi->tanggal_sewa)->format('d M Y')    : '-';
            $tglKembali  = $transaksi->tanggal_pengembalian ? \Carbon\Carbon::parse($transaksi->tanggal_pengembalian)->format('d M Y') : '-';
        @endphp

        <!-- Status Bar Pembayaran -->
        <div class="status-bar {{ $sudahBayar ? 'sudah' : 'belum' }}">
            <div>
                <span style="color:#44403c;font-size:12px;">Status Pesanan: 
                    <strong style="color:#78350f;">{{ $transaksi->status ?? 'Menunggu Konfirmasi' }}</strong>
                </span>
            </div>
            <span class="status-badge {{ $sudahBayar ? 'badge-sudah' : 'badge-belum' }}">
                {{ $sudahBayar ? '✅ DP Sudah Dibayar' : '⏳ Belum Bayar DP' }}
            </span>
        </div>

        <div class="nota-body">

            @php
                $gambarSrc = $transaksi->gambar_ai_result ?? null;
                if ($gambarSrc && str_starts_with($gambarSrc, 'uploads/')) {
                    $gambarSrc = asset($gambarSrc);
                } elseif (!$gambarSrc || str_starts_with($gambarSrc, 'http') === false) {
                    $gambarSrc = asset('Kebaya Modern Brukat Gown.png');
                }
            @endphp
            <div class="card-box">
                <img src="{{ $gambarSrc }}"
                     alt="Visual Kebaya" class="kebaya-img">

                <div class="kebaya-info">
                    <span class="card-label">Model Kebaya</span>
                    <h2 class="kebaya-title font-serif">{{ $transaksi->nama_kebaya ?? 'Kebaya Modern Brukat Gown' }}</h2>
                    <div style="border-top:1px solid #e7e5e4;padding-top:8px;font-size:12px;">
                        <p style="margin-bottom:4px;"><strong>Ukuran:</strong> <span style="background:#e7e5e4;padding:2px 6px;border-radius:4px;font-weight:bold;">Size {{ $transaksi->ukuran ?? 'M' }}</span></p>
                        <p><strong>Warna AI:</strong> <span style="color:#92400e;font-style:italic;">"{{ $transaksi->warna_custom_ai ?? 'Default' }}"</span></p>
                        <p style="margin-top:6px;"><strong>Sewa:</strong> {{ $tglSewa }} → {{ $tglKembali }}
                            @if($transaksi->durasi_hari) <span style="color:#78350f;font-weight:600;">({{ $transaksi->durasi_hari }} hari)</span> @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detail Penyewa -->
            <div class="detail-grid">
                <div class="info-card">
                    <span class="card-label">Informasi Penyewa</span>
                    <p style="font-weight:700;font-size:14px;color:#1c1917;margin-bottom:4px;">{{ $transaksi->nama_user ?? 'Customer' }}</p>
                    <p style="color:#57534e;margin-bottom:2px;">📱 No. WA: {{ $transaksi->no_wa ?? '-' }}</p>
                    <p style="color:#57534e;">📍 {{ $transaksi->alamat ? $transaksi->alamat : '— Fitting di Studio Ms Harmoni —' }}</p>
                </div>
                <div class="info-card">
                    <span class="card-label">Rincian Pembayaran</span>
                    <p style="font-weight:700;color:#1c1917;margin-bottom:4px;">{{ $transaksi->metode_pembayaran ?? 'QRIS' }}</p>
                    <p style="font-size:11px;color:#57534e;margin-bottom:2px;">Total Sewa: <strong>Rp {{ number_format($totalHrg, 0, ',', '.') }}</strong></p>
                    <p style="font-size:11px;color:#78350f;font-weight:700;margin-bottom:2px;">Skema: <strong>{{ $labelSkema }}</strong></p>
                    <p style="font-size:11px;color:#059669;font-weight:600;">Dibayar ({{ $labelSkema }}): Rp {{ number_format($dp, 0, ',', '.') }}</p>
                    @if($pctDP < 1.0)
                    <p style="font-size:11px;color:#b45309;font-weight:600;">Sisa Pelunasan: Rp {{ number_format($sisa, 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>

            @if(!$sudahBayar)
            <!-- ===== INSTRUKSI PEMBAYARAN DP ===== -->
            @if($isQris)
            <!-- QRIS / Midtrans Payment -->
            <div class="payment-box qris">
                <p class="payment-title" style="color:#991b1b;">💳 Instruksi Pembayaran</p>
                <div style="display:flex;flex-direction:column;gap:12px;">

                    {{-- Badge metode --}}
                    <div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;">
                        @foreach([
                            ['🔳','QRIS','#6B0012'],
                            ['💚','GoPay','#00880a'],
                            ['🟠','ShopeePay','#ee4d2d'],
                            ['🔵','DANA','#118eca'],
                            ['🟣','OVO','#4c3494'],
                            ['🏦','Transfer Bank','#1e3a5f'],
                        ] as [$icon, $lbl, $clr])
                        <span style="display:inline-flex;align-items:center;gap:3px;padding:3px 9px;background:{{ $clr }}12;border:1px solid {{ $clr }}44;border-radius:999px;font-size:9px;font-weight:700;color:{{ $clr }};">{{ $icon }} {{ $lbl }}</span>
                        @endforeach
                    </div>

                    {{-- Nominal --}}
                    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:12px;text-align:center;">
                        <p style="font-size:10px;font-weight:700;color:#b45309;text-transform:uppercase;margin-bottom:4px;">Jumlah Dibayar ({{ $labelSkema }})</p>
                        <p style="font-size:20px;font-family:'Cormorant Garamond',serif;font-weight:700;color:#78350f;">Rp {{ number_format($dp, 0, ',', '.') }}</p>
                    </div>

                    {{-- Tombol Bayar --}}
                    <a href="{{ route('payment.show', $transaksi->id) }}"
                       style="display:block;text-align:center;padding:11px 16px;background:linear-gradient(135deg,#6B0012,#9B1A2A);color:#fff;font-weight:700;font-size:12px;border-radius:8px;text-decoration:none;letter-spacing:0.03em;">
                        💳 Bayar Sekarang via Midtrans
                    </a>

                    {{-- Langkah --}}
                    <ol style="font-size:11px;color:#57534e;padding-left:14px;line-height:1.7;margin:0;">
                        <li>Klik tombol <strong>Bayar Sekarang</strong> di atas</li>
                        <li>Pilih metode: QRIS, GoPay, Transfer Bank, dll</li>
                        <li>Selesaikan pembayaran — sistem otomatis update status</li>
                        <li>Screenshot bukti bayar & simpan sebagai arsip</li>
                    </ol>
                </div>
            </div>
            @elseif($isBank)
            <!-- Transfer Bank SeaBank -->
            <div class="payment-box bank">
                <p class="payment-title" style="color:#0c4a6e;">🏦 Instruksi Transfer SeaBank</p>
                <div class="rekening-box">
                    <p style="font-size:10px;font-weight:700;color:#0284c7;text-transform:uppercase;margin-bottom:4px;">SeaBank Indonesia</p>
                    <p class="rekening-nomor">9015 8274 1928</p>
                    <p style="font-size:12px;font-weight:600;color:#1c1917;margin-top:6px;">a.n. <strong>Novan Helmi</strong></p>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;font-size:11px;">
                    <div style="background:#fff;border:1px solid #bae6fd;border-radius:8px;padding:10px;">
                        <p style="color:#0284c7;font-weight:700;font-size:10px;text-transform:uppercase;margin-bottom:2px;">Jumlah Transfer (DP 50%)</p>
                        <p style="font-weight:800;color:#0c4a6e;font-size:16px;">Rp {{ number_format($dp, 0, ',', '.') }}</p>
                    </div>
                    <div style="background:#fff;border:1px solid #bae6fd;border-radius:8px;padding:10px;">
                        <p style="color:#0284c7;font-weight:700;font-size:10px;text-transform:uppercase;margin-bottom:2px;">Berita Transfer (wajib)</p>
                        <p style="font-weight:700;color:#1c1917;font-size:12px;">SEWA-{{ strtoupper(substr($transaksi->nama_kebaya ?? 'KEBAYA', 0, 8)) }}</p>
                    </div>
                </div>
                <div style="background:#e0f2fe;border-radius:8px;padding:10px;font-size:11px;color:#0c4a6e;margin-bottom:10px;">
                    📸 <strong>Wajib:</strong> Screenshot bukti transfer dan kirim ke WhatsApp admin <strong>untuk konfirmasi pembayaran</strong>.
                </div>
                <a href="{{ route('payment.show', $transaksi->id) }}"
                   style="display:block;text-align:center;padding:10px 16px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;font-weight:700;font-size:12px;border-radius:8px;text-decoration:none;">
                    💳 Atau Bayar Online via Midtrans (QRIS, GoPay, DLL)
                </a>
            </div>
            @else
            <!-- Cash -->
            <div class="payment-box cash">
                <p class="payment-title" style="color:#334155;">💵 Pembayaran Cash di Studio</p>
                <div style="font-size:12px;color:#57534e;">
                    <p style="margin-bottom:6px;">📍 <strong>Jogja:</strong> Perumahan Griyo Ageng Jl Buwosox No.A2, Ngentak, Bangunjiwo, Kab. Bantul, DIY 55184</p>
                    <p style="margin-bottom:6px;">📍 <strong>Purworejo:</strong> Mayangsari Telogo Sink, Sangubanyu, Kec. Grabag, Kab. Purworejo, Jawa Tengah 54265</p>
                    <a href="{{ route('payment.show', $transaksi->id) }}"
                       style="display:block;text-align:center;padding:10px 16px;margin-top:10px;background:linear-gradient(135deg,#6B0012,#9B1A2A);color:#fff;font-weight:700;font-size:12px;border-radius:8px;text-decoration:none;">
                        💳 Atau Bayar Online via Midtrans (QRIS / GoPay / Bank Transfer)
                    </a>
                </div>
            </div>
                    <p style="margin-bottom:6px;">🕐 Jam Operasional: Senin–Minggu, 08.00–20.00 WIB</p>
                    <p>💰 Skema: <strong>{{ $labelSkema }}</strong> — Dibayar: <strong style="color:#78350f;">Rp {{ number_format($dp, 0, ',', '.') }}</strong></p>
                    @if($pctDP < 1.0)
                    <p style="color:#b45309;">Sisa pelunasan saat pengambilan: <strong>Rp {{ number_format($sisa, 0, ',', '.') }}</strong></p>
                    @endif
                </div>
            </div>
            @endif
            @else
            <!-- Sudah Bayar DP -->
            <div class="payment-box done">
                <p class="payment-title" style="color:#15803d;">✅ Pembayaran DP Dikonfirmasi</p>
                <div style="font-size:12px;color:#166534;">
                    <p style="margin-bottom:4px;">Metode: <strong>{{ $transaksi->metode_pembayaran ?? '-' }}</strong></p>
                    <p style="margin-bottom:4px;">DP Terbayar: <strong>Rp {{ number_format($dp, 0, ',', '.') }}</strong></p>
                    <p>Sisa Pelunasan saat pengambilan: <strong>Rp {{ number_format($sisa, 0, ',', '.') }}</strong></p>
                </div>
            </div>
            @endif

            <!-- ===== SEKSI PENJADWALAN FITTING KEBAYA (Customer Request) ===== -->
            <div class="fitting-box" style="margin-top:1.25rem;background:#fffdfa;border:1.5px solid #d4af37;border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px dashed #e7e5e4;padding-bottom:0.75rem;margin-bottom:0.85rem;flex-wrap:wrap;gap:0.5rem;">
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <span style="font-size:1.25rem;">✂️</span>
                        <div>
                            <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:700;color:#1c1917;margin:0;">Jadwal Fitting &amp; Pengukuran Kebaya</h3>
                            <p style="font-size:11px;color:#78716c;margin:0;">Pilih &amp; ajukan tanggal kedatangan Anda ke Studio MS Harmony</p>
                        </div>
                    </div>

                    @if(str_contains($transaksi->catatan_admin ?? '', 'Request Fitting Customer'))
                        <span style="background:#e0f2fe;color:#0284c7;border:1px solid #7dd3fc;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">
                            🔔 Menunggu Konfirmasi Admin
                        </span>
                    @elseif(($transaksi->status ?? '') === 'Sedang Fitting')
                        <span style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">
                            ✂️ Sesi Fitting Dikonfirmasi / Berlangsung
                        </span>
                    @else
                        <span style="background:#f5f5f4;color:#57534e;border:1px solid #e7e5e4;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;">
                            📅 Jadwalkan Kedatangan Fitting
                        </span>
                    @endif
                </div>

                @if(session('success_fitting'))
                    <div style="width:100%;background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:0.75rem 1rem;border-radius:8px;font-size:11px;font-weight:700;margin-bottom:0.85rem;">
                        ✅ {{ session('success_fitting') }}
                    </div>
                @endif

                @if(str_contains($transaksi->catatan_admin ?? '', 'Request Fitting Customer'))
                    <div style="background:#f0f9ff;border:1px solid #7dd3fc;border-radius:8px;padding:0.85rem 1rem;font-size:11px;color:#0369a1;">
                        <p style="font-weight:700;margin:0 0 0.25rem 0;font-size:12px;">📩 Permintaan Jadwal Fitting Telah Dikirim ke Admin:</p>
                        <p style="margin:0;font-weight:600;">{{ $transaksi->catatan_admin }}</p>
                        <p style="margin:0.35rem 0 0 0;font-size:10px;color:#0284c7;">Tim Admin MS Harmony akan bersiap menyambut Anda pada jam &amp; studio yang dipilih. Silakan datang sesuai jadwal.</p>
                    </div>
                @else
                    @if(isset($transaksi->id) && $transaksi->id > 0)
                    <form action="{{ route('transaksi.ajukanFitting', $transaksi->id) }}" method="POST" style="background:#fff;border:1px solid #e7e5e4;border-radius:10px;padding:1rem;">
                        @csrf
                        <p style="font-size:11px;font-weight:700;color:#1c1917;margin-bottom:0.75rem;">📅 Form Pengajuan Jadwal Fitting (Customer):</p>
                        
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:0.75rem;margin-bottom:0.75rem;">
                            <div>
                                <label style="display:block;font-size:10px;font-weight:700;color:#57534e;margin-bottom:4px;">Rencana Tanggal Fitting *</label>
                                <input type="date" name="tanggal_fitting" required min="{{ date('Y-m-d') }}" value="{{ $transaksi->tanggal_sewa ? \Carbon\Carbon::parse($transaksi->tanggal_sewa)->format('Y-m-d') : date('Y-m-d') }}" style="width:100%;font-size:11px;padding:6px 10px;border-radius:6px;border:1px solid #ccc;">
                            </div>
                            <div>
                                <label style="display:block;font-size:10px;font-weight:700;color:#57534e;margin-bottom:4px;">Jam Kedatangan *</label>
                                <select name="jam_fitting" required style="width:100%;font-size:11px;padding:6px 10px;border-radius:6px;border:1px solid #ccc;">
                                    <option value="09:00 WIB">Pagi — 09:00 WIB</option>
                                    <option value="11:00 WIB" selected>Siang — 11:00 WIB</option>
                                    <option value="14:00 WIB">Siang — 14:00 WIB</option>
                                    <option value="16:00 WIB">Sore — 16:00 WIB</option>
                                    <option value="18:30 WIB">Malam — 18:30 WIB</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:10px;font-weight:700;color:#57534e;margin-bottom:4px;">Pilihan Studio MS Harmony *</label>
                                <select name="lokasi_studio" required style="width:100%;font-size:11px;padding:6px 10px;border-radius:6px;border:1px solid #ccc;">
                                    <option value="Studio Jogja (Bantul)">Studio Jogja (Bangunjiwo, Bantul)</option>
                                    <option value="Studio Purworejo (Grabag)">Studio Purworejo (Grabag)</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-bottom:0.85rem;">
                            <label style="display:block;font-size:10px;font-weight:700;color:#57534e;margin-bottom:4px;">Catatan Tambahan (Opsional)</label>
                            <input type="text" name="catatan" placeholder="Contoh: Datang bersama 1 pendamping / butuh manset tambahan..." style="width:100%;font-size:11px;padding:6px 10px;border-radius:6px;border:1px solid #ccc;">
                        </div>

                        <button type="submit" style="background:linear-gradient(135deg,#8B5A2B,#6B0012);color:#fff;border:none;padding:0.65rem 1.25rem;border-radius:8px;font-weight:700;font-size:11px;cursor:pointer;display:inline-flex;align-items:center;gap:0.4rem;box-shadow:0 3px 10px rgba(139,90,43,0.25);">
                            <span>✂️ Kirim Pengajuan Jadwal Fitting ke Admin</span>
                        </button>
                    </form>
                    @endif
                @endif
            </div>

            <!-- ===== SEKSI PENGEMBALIAN KEBAYA (Customer Perspective) ===== -->
            <div class="return-box" style="margin-top:1.25rem;background:#fafaf9;border:1.5px solid #e7e5e4;border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px dashed #d6d3d1;padding-bottom:0.75rem;margin-bottom:0.85rem;flex-wrap:wrap;gap:0.5rem;">
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <span style="font-size:1.25rem;">🔁</span>
                        <div>
                            <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:700;color:#1c1917;margin:0;">Informasi &amp; Status Pengembalian Kebaya</h3>
                            <p style="font-size:11px;color:#78716c;margin:0;">Panduan &amp; jadwal pengembalian gaun kebaya Anda</p>
                        </div>
                    </div>
                    
                    @if(($transaksi->status ?? '') === 'Telah Dikembalikan')
                        <span style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">
                            ✅ Telah Dikembalikan (Selesai)
                        </span>
                    @elseif(($transaksi->status ?? '') === 'Kebaya Telah Diambil')
                        <span style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">
                            📦 Sedang Disewa — Harap Dikembalikan
                        </span>
                    @else
                        <span style="background:#f5f5f4;color:#57534e;border:1px solid #e7e5e4;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;">
                            📅 Belum Diambil (Jadwal Sewa)
                        </span>
                    @endif
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:0.85rem;margin-bottom:1rem;">
                    <div style="background:#fff;border:1px solid #e7e5e4;border-radius:8px;padding:0.75rem;">
                        <p style="font-size:10px;font-weight:700;color:#78716c;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.25rem;">Mulai Sewa</p>
                        <p style="font-size:13px;font-weight:700;color:#1c1917;margin:0;">{{ $tglSewa }}</p>
                    </div>
                    <div style="background:#fff;border:1.5px solid rgba(107,0,18,0.25);border-radius:8px;padding:0.75rem;">
                        <p style="font-size:10px;font-weight:700;color:#6B0012;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.25rem;">Batas Pengembalian</p>
                        <p style="font-size:13px;font-weight:800;color:#6B0012;margin:0;">{{ $tglKembali }}</p>
                    </div>
                    <div style="background:#fff;border:1px solid #e7e5e4;border-radius:8px;padding:0.75rem;">
                        <p style="font-size:10px;font-weight:700;color:#78716c;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.25rem;">Sisa Pelunasan Saat Kembali</p>
                        <p style="font-size:13px;font-weight:700;color:{{ $pctDP >= 1.0 ? '#15803d' : '#b45309' }};margin:0;">
                            {{ $pctDP >= 1.0 ? 'Rp 0 (Sudah Lunas)' : 'Rp ' . number_format($sisa, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div style="background:#fff8f6;border-left:3.5px solid #6B0012;padding:0.85rem 1rem;border-radius:0 8px 8px 0;font-size:11px;color:#44403c;line-height:1.65;">
                    <p style="font-weight:700;color:#6B0012;margin-bottom:0.35rem;">📌 Catatan Penting Pengembalian Kebaya:</p>
                    <ul style="margin:0;padding-left:1.1rem;">
                        <li>Pengembalian dilakukan langsung di <strong>Studio MS Harmony Kebaya Boutique</strong> (sesuai alamat fitting Anda).</li>
                        <li>Mohon kembalikan kebaya beserta seluruh kelengkapan aksesoris (rok/kain, manset, bros) dalam kondisi baik.</li>
                        @if($pctDP < 1.0)
                        <li>Sisa pelunasan sebesar <strong style="color:#b45309;">Rp {{ number_format($sisa, 0, ',', '.') }}</strong> dilunasi saat penyerahan kembali kebaya di studio.</li>
                        @endif
                        <li>Jika melebihi tanggal batas pengembalian (<strong>{{ $tglKembali }}</strong>), akan dikenakan biaya keterlambatan sesuai kebijakan studio.</li>
                    </ul>
                </div>

                {{-- Aksi Customer Request Pengembalian --}}
                <div style="margin-top:1rem;padding-top:0.85rem;border-top:1px dashed #d6d3d1;">
                    @if(session('success_pengembalian'))
                        <div style="width:100%;background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:0.75rem 1rem;border-radius:8px;font-size:11px;font-weight:700;margin-bottom:0.75rem;">
                            ✅ {{ session('success_pengembalian') }}
                        </div>
                    @endif

                    @if(($transaksi->status ?? '') === 'Mengajukan Pengembalian')
                        <div style="width:100%;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:0.75rem 1rem;border-radius:8px;font-size:11px;font-weight:700;display:flex;align-items:center;gap:0.5rem;">
                            <span>📩</span> <div><strong>Pengajuan Dikirim!</strong> Anda telah mengirimkan permintaan pengembalian kebaya. Silakan bawa kebaya ke Studio MS Harmony untuk penyerahan fisik &amp; verifikasi pelunasan oleh Admin.</div>
                        </div>
                    @elseif(($transaksi->status ?? '') === 'Telah Dikembalikan')
                        <div style="width:100%;background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:0.75rem 1rem;border-radius:8px;font-size:11px;font-weight:700;display:flex;align-items:center;gap:0.5rem;">
                            <span>🎉</span> <div><strong>Selesai &amp; Diverifikasi:</strong> Kebaya telah dikembalikan dan diverifikasi lengkap oleh Admin Studio MS Harmony.</div>
                        </div>
                    @else
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
                            <div style="font-size:11px;color:#78716c;">
                                💬 <strong>Sudah siap mengembalikan kebaya?</strong> Klik tombol di samping untuk mengirim pemberitahuan pengembalian ke Admin.
                            </div>
                            @if(isset($transaksi->id) && $transaksi->id > 0)
                            <form action="{{ route('transaksi.ajukanPengembalian', $transaksi->id) }}" method="POST" onsubmit="return confirm('Ajukan pengembalian kebaya ke Studio MS Harmony?')">
                                @csrf
                                <button type="submit" style="background:linear-gradient(135deg,#6B0012,#8B1522);color:#fff;border:none;padding:0.65rem 1.25rem;border-radius:8px;font-weight:700;font-size:11px;cursor:pointer;display:inline-flex;align-items:center;gap:0.4rem;box-shadow:0 3px 10px rgba(107,0,18,0.25);">
                                    📩 Ajukan Pengembalian Kebaya (Request Return)
                                </button>
                            </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Langkah Selanjutnya -->
            <div class="info-step">
                <p style="font-weight:700;color:#292524;">📌 Langkah Selanjutnya:</p>
                @if(!$sudahBayar && !str_contains($metode, 'cash'))
                <p>1. Lakukan pembayaran DP 50% sesuai instruksi di atas.</p>
                <p>2. Kirim bukti pembayaran ke WhatsApp admin Ms Harmoni.</p>
                <p>3. Tunjukkan nota/kode transaksi ini saat sesi fitting di studio.</p>
                <p>4. Admin akan menghubungi WhatsApp Anda untuk konfirmasi jadwal fitting.</p>
                @else
                <p>1. Tunjukkan nota/kode transaksi ini saat berkunjung ke Studio Ms Harmoni untuk sesi fitting &amp; pengambilan.</p>
                <p>2. Tim admin kami juga akan menghubungi WhatsApp Anda untuk mengonfirmasi jam fitting.</p>
                @endif
                <p style="margin-top:8px;padding-top:8px;border-top:1px solid #e7e5e4;color:#78350f;font-weight:600;">
                    ✅ Pesanan Anda telah masuk dan tercatat di sistem dashboard admin Ms Harmoni.
                </p>
            </div>

            <!-- Footer Note -->
            <div class="footer-note">
                <p>Terima kasih telah mempercayakan busana Anda pada <strong>Ms Harmoni</strong>.</p>
                <p><em>*Harap simpan nota ini sebagai bukti sah saat pengambilan/fitting kebaya.</em></p>
            </div>

            <!-- Action Buttons (no-print) -->
            <div class="action-buttons no-print" style="margin-top:1.5rem;display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">

                {{-- Tombol Bayar QRIS Midtrans (hanya jika belum paid & memilih QRIS) --}}
                @if(isset($transaksi->id) && $transaksi->id > 0 && !($transaksi->payment_status ?? false === 'paid'))
                @php $isQrisMethod = str_contains(strtolower($transaksi->metode_pembayaran ?? ''), 'qris'); @endphp
                @if($isQrisMethod)
                <a href="{{ route('payment.show', $transaksi->id) }}"
                   style="background:linear-gradient(135deg,#059669,#047857);color:white;border:none;border-radius:8px;padding:0.85rem 2rem;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.85rem;font-weight:700;letter-spacing:0.05em;cursor:pointer;display:flex;align-items:center;gap:0.5rem;box-shadow:0 4px 16px rgba(5,150,105,0.3);text-decoration:none;transition:all 0.2s;">
                    💳 Bayar via QRIS / Midtrans
                </a>
                @endif
                @endif

                <button onclick="window.print()"
                        style="background:linear-gradient(135deg,#6B0012,#4A0B10);color:#E8C98A;border:1px solid rgba(200,169,110,0.3);border-radius:8px;padding:0.85rem 2rem;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.85rem;font-weight:700;letter-spacing:0.05em;cursor:pointer;display:flex;align-items:center;gap:0.5rem;box-shadow:0 4px 16px rgba(107,0,18,0.2);transition:all 0.2s;">
                    🖨️ Cetak / Print Bukti Pemesanan
                </button>
                <a href="https://wa.me/6281949862260?text={{ rawurlencode('Halo Admin MS Harmony! Saya ingin konfirmasi pesanan saya. Kode Transaksi: ' . ($transaksi->kode_transaksi ?? 'TRX-XXX') . ' a.n. ' . ($transaksi->nama_user ?? 'Customer') . '. Mohon diproses. Terima kasih 🙏') }}"
                   target="_blank"
                   style="background:#15803d;color:white;border:none;border-radius:8px;padding:0.85rem 1.5rem;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.85rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:0.5rem;text-decoration:none;box-shadow:0 4px 12px rgba(21,128,61,0.2);">
                    💬 Konfirmasi ke Admin WA
                </a>
                <a href="{{ url('/') }}"
                   style="background:#F4EDE0;color:#4A3728;border:1.5px solid rgba(200,169,110,0.35);border-radius:8px;padding:0.85rem 1.5rem;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.85rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:0.5rem;text-decoration:none;">
                    🏠 Kembali ke Beranda
                </a>
            </div>


        </div>
    </div>

<script>
(function() {
    try {
        const trxCode = '{{ $transaksi->kode_transaksi ?? "" }}';
        if (trxCode && trxCode !== 'TRX-DEFAULT') {
            let trxs = JSON.parse(localStorage.getItem('msh_customer_trxs') || '[]');
            if (!trxs.includes(trxCode)) {
                trxs.unshift(trxCode);
                localStorage.setItem('msh_customer_trxs', JSON.stringify(trxs.slice(0, 10)));
            }
        }
    } catch(e) {}
})();
</script>

{{-- ═══ FLOATING CHAT WIDGET (Customer Support) ═══ --}}
@include('chat.widget', ['transaksi' => $transaksi])

</body>
</html>