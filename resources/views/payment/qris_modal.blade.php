<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pembayaran — {{ $transaksi->kode_transaksi }} | MS Harmony</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/payment-qris.css') }}">

    {{-- Midtrans Snap JS — SELALU dimuat agar popup siap --}}
    <script src="{{ config('midtrans.snap_url') }}"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>
<body>
<div class="pay-card">

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <div class="pay-header">
        <div class="pay-header-logo">
            <img src="{{ asset('logo-mayangsari.jpg') }}"
                 onerror="this.onerror=null;this.parentElement.textContent='💎';"
                 alt="MS Harmony">
        </div>
        <div class="pay-header-info">
            <div class="pay-brand">MS HARMONY</div>
            <div class="pay-brand-sub">Pembayaran Pesanan Sewa Kebaya</div>
        </div>
        <a href="{{ route('transaksi.nota', $transaksi->id) }}" class="pay-back">← Nota</a>
    </div>

    <div class="pay-body">

        @php
            $nominal = $transaksi->nominalDP();
            $skema   = $transaksi->labelSkema();
            $sisa    = $transaksi->sisaPelunasan();
        @endphp

        {{-- ── Info Pesanan ──────────────────────────────────────── --}}
        <div class="order-info">
            <div class="order-label">Detail Pesanan</div>
            <div class="order-title">{{ $transaksi->nama_kebaya }}</div>
            <div class="order-row"><span>Kode</span><strong>{{ $transaksi->kode_transaksi }}</strong></div>
            <div class="order-row"><span>Penyewa</span><strong>{{ $transaksi->nama_user }}</strong></div>
            <div class="order-row"><span>Total Sewa</span><strong>Rp {{ number_format($transaksi->total_harga,0,',','.') }}</strong></div>
            <div class="order-row">
                <span>Skema Bayar</span>
                <strong style="color:#6B0012;">{{ $skema }}</strong>
            </div>
            @if($sisa > 0)
            <div class="order-row">
                <span>Sisa Pelunasan</span>
                <strong style="color:#78350f;">Rp {{ number_format($sisa,0,',','.') }}</strong>
            </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SUDAH PAID
             ════════════════════════════════════════════════════════ --}}
        @if($transaksi->isPaid())
            <div class="pay-success-icon" style="display:block;">✅</div>
            <div class="pay-status paid">Pembayaran sudah lunas. Terima kasih!</div>
            <a href="{{ route('transaksi.nota', $transaksi->id) }}" class="btn-secondary">
                Lihat Nota Bukti Pemesanan
            </a>

        @else

        {{-- ══════════════════════════════════════════════════════════
             PRIMARY: MIDTRANS SNAP (QRIS Dinamis Resmi + e-Wallet)
             ════════════════════════════════════════════════════════ --}}
        @if($midtransReady)

            {{-- Nominal yang akan dibayar --}}
            <div class="qr-nominal-badge" style="margin-bottom:1.25rem;">
                <span class="qr-lock-icon">🔒</span>
                <span>Nominal Terkunci: <strong>Rp {{ number_format($nominal,0,',','.') }}</strong></span>
                <span style="font-size:.6rem;color:#d97706;">{{ $skema }}</span>
            </div>

            {{-- Status feedback --}}
            <div class="pay-status" id="pay-status-box"></div>
            <div class="pay-success-icon" id="pay-success-icon">✅</div>

            {{-- TOMBOL BAYAR UTAMA --}}
            <button class="btn-primary" id="pay-btn" onclick="startMidtrans()">
                <span id="pay-spinner"
                      style="width:18px;height:18px;border:2.5px solid rgba(230,198,135,.3);
                             border-top-color:#DFC28A;border-radius:50%;
                             animation:spin .8s linear infinite;display:none;">
                </span>
                <span id="pay-btn-text">💳 Bayar Sekarang (Rp {{ number_format($nominal,0,',','.') }})</span>
            </button>
            <div class="polling-bar" id="polling-bar"></div>

            {{-- Info payment methods --}}
            <div class="pay-note" style="margin-top:10px;">
                Didukung: <strong>QRIS</strong>, GoPay, ShopeePay, DANA, OVO,
                Transfer Bank BCA/BNI/BRI/Mandiri dan lainnya.<br>
                Pop-up Midtrans Snap akan tampil, pilih metode favoritmu.
            </div>

            @if(!config('midtrans.is_production'))
            {{-- Banner Bantuan Mode Sandbox Testing --}}
            <div style="margin-top:12px;background:#f0f9ff;border:1.5px dashed #0284c7;border-radius:10px;padding:12px;text-align:center;font-size:11px;color:#0369a1;line-height:1.5;">
                <div style="font-weight:800;font-size:12px;margin-bottom:4px;color:#0369a1;">🧪 Mode Sandbox (Pengujian)</div>
                <span>Ingin mensimulasikan pembayaran lunas secara instan tanpa perlu scan?</span>
                <div style="margin-top:8px;display:flex;gap:8px;justify-content:center;align-items:center;flex-wrap:wrap;">
                    <form action="{{ route('payment.simulate_sandbox', $transaksi->id) }}" method="POST" style="margin:0;padding:0;display:inline-block;">
                        @csrf
                        <button type="submit"
                                style="padding:8px 16px;background:#15803d;color:#fff;font-weight:700;border:none;border-radius:6px;cursor:pointer;font-size:11px;box-shadow:0 2px 5px rgba(0,0,0,0.15);">
                            ⚡ 1-Click Test: Simulasi Bayar Lunas Now
                        </button>
                    </form>
                    <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" 
                       style="padding:7px 12px;background:#0284c7;color:#fff;font-weight:700;border-radius:6px;text-decoration:none;font-size:10px;display:inline-flex;align-items:center;">
                        🔗 Simulator Midtrans ↗
                    </a>
                </div>
            </div>
            @endif

            @if($snapError)
            <div class="pay-status failed" style="display:block;margin-top:8px;">
                Gagal menyiapkan gateway: {{ $snapError }}. Coba refresh halaman.
            </div>
            @endif

            {{-- Divider --}}
            @if($qrisMode && $qrisImageData)
            <div class="btn-divider" style="margin:1.5rem 0;">atau scan QRIS DANA di bawah</div>
            @endif

        @endif

        {{-- ══════════════════════════════════════════════════════════
             DYNAMIC QRIS CODE (Midtrans Core API / Statis Fallback)
             ════════════════════════════════════════════════════════ --}}
        @if($qrisMode && $qrisImageData)

            <div class="qris-section" id="qris-section">
                <div class="qris-header">
                    <div class="qris-logo-badge">Midtrans<br>Gateway</div>
                    <div style="font-size:1.1rem;">×</div>
                    <div class="qris-nasional-badge">QRIS<br>Nasional</div>
                </div>
                <div class="qris-title">Scan QRIS Dinamis untuk Membayar</div>

                <div class="qr-box">
                    <img id="qr-img"
                         src="{{ $qrisImageData }}"
                         alt="QRIS Midtrans — Rp {{ number_format($qrisNominal,0,',','.') }}"
                         width="{{ config('qris.qr_size', 280) }}"
                         height="{{ config('qris.qr_size', 280) }}">
                </div>

                <div class="qr-nominal-badge">
                    <span class="qr-lock-icon">🔒</span>
                    <span>Nominal terkunci: <strong>Rp {{ number_format($qrisNominal,0,',','.') }}</strong></span>
                    <span style="font-size:.6rem;color:#d97706;">{{ $qrisSkema }}</span>
                </div>

                <div class="pay-timer">
                    <span>QR berlaku selama</span>
                    <span id="countdown">15:00</span>
                </div>

                <div class="qr-instruction">
                    <ol>
                        <li>Buka aplikasi m-Banking atau E-Wallet apa pun (GoPay, DANA, OVO, ShopeePay, BCA, Mandiri, dll)</li>
                        <li>Pilih menu <strong>Scan QR / QRIS</strong></li>
                        <li>Arahkan kamera ke kode QR di atas</li>
                        <li>Nominal <strong>Rp {{ number_format($qrisNominal,0,',','.') }}</strong> akan otomatis terisi</li>
                        <li>Konfirmasi pembayaran — status akan otomatis lunas tanpa perlu konfirmasi manual</li>
                    </ol>
                </div>
            </div>

            <div class="btn-divider">atau</div>

            <button class="btn-secondary" onclick="refreshQr()" id="btn-refresh-qr">
                Refresh QR Code (jika kedaluwarsa)
            </button>

        @endif

        {{-- ══════════════════════════════════════════════════════════
             FALLBACK: Keduanya tidak dikonfigurasi
             ════════════════════════════════════════════════════════ --}}
        @if(!$midtransReady && !$qrisMode)
            <div class="setup-box">
                <h3>Konfigurasi Pembayaran Belum Lengkap</h3>
                <p>Hubungi admin untuk melanjutkan pembayaran.</p>
                <ol style="text-align:left;font-size:.72rem;color:#78350f;padding-left:20px;line-height:1.8;margin-top:8px;">
                    <li>Set <code>MIDTRANS_SERVER_KEY</code> & <code>MIDTRANS_CLIENT_KEY</code> di <code>.env</code></li>
                    <li>Atau set <code>QRIS_STATIC_STRING</code> untuk QRIS DANA Bisnis</li>
                    <li>Jalankan: <code>php artisan config:cache</code></li>
                </ol>
            </div>
            <a href="{{ route('transaksi.nota', $transaksi->id) }}" class="btn-secondary" style="margin-top:12px;">
                Kembali ke Nota
            </a>
        @endif

        @endif {{-- end !isPaid() --}}

    </div>
</div>

<script>
(function () {
    'use strict';

    const CSRF        = document.querySelector('meta[name="csrf-token"]').content;
    const STATUS_URL  = '{{ route("payment.status", $transaksi->id) }}';
    const REFRESH_URL = '{{ route("payment.refresh_qris", $transaksi->id) }}';
    const INITIATE_URL= '{{ route("payment.initiate", $transaksi->id) }}';
    const NOTA_URL    = '{{ route("transaksi.nota", $transaksi->id) }}';
    const SIMULATE_URL = '{{ route("payment.simulate_sandbox", $transaksi->id) }}';
    const QRIS_MODE   = {{ $qrisMode ? 'true' : 'false' }};
    const MIDTRANS_READY = {{ $midtransReady ? 'true' : 'false' }};

    // Snap token yang sudah di-generate saat halaman di-load (bisa null)
    let snapToken = {{ $snapToken ? json_encode($snapToken) : 'null' }};

    let pollInt       = null;
    let countdownInt  = null;
    let totalSeconds  = 0;

    // ── 1-CLICK SIMULASI SANDBOX TEST ─────────────────────────────
    window.simulasiBayarSandbox = function () {
        setStatus('info', 'Memproses simulasi pembayaran lunas...');
        fetch(SIMULATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                handlePaid();
            } else {
                setStatus('failed', data.message || 'Gagal simulasi.');
            }
        })
        .catch(() => setStatus('failed', 'Koneksi error.'));
    };

    // ── TOMBOL BAYAR MIDTRANS ─────────────────────────────────────
    window.startMidtrans = function () {
        const btn     = document.getElementById('pay-btn');
        const spinner = document.getElementById('pay-spinner');
        const txt     = document.getElementById('pay-btn-text');
        if (!btn) return;

        // Jika snap_token sudah ada (di-generate saat page load), langsung buka
        if (snapToken) {
            openSnap(snapToken, btn, spinner, txt);
            return;
        }

        // Belum ada token → fetch dulu
        btn.disabled = true;
        if (spinner) spinner.style.display = '';
        if (txt) txt.textContent = 'Menyiapkan pembayaran...';

        fetch(INITIATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
        })
        .then(r => r.json())
        .then(data => {
            if (spinner) spinner.style.display = 'none';
            if (data.paid) { handlePaid(); return; }
            if (!data.success) {
                btn.disabled = false;
                if (txt) txt.textContent = '💳 Bayar Sekarang';
                setStatus('failed', data.message || 'Gagal memuat gateway.');
                return;
            }
            snapToken = data.snap_token;
            openSnap(snapToken, btn, spinner, txt);
        })
        .catch(err => {
            btn.disabled = false;
            if (spinner) spinner.style.display = 'none';
            if (txt) txt.textContent = '💳 Bayar Sekarang';
            setStatus('failed', 'Koneksi error. Coba lagi.');
        });
    };

    function openSnap(token, btn, spinner, txt) {
        if (typeof window.snap === 'undefined') {
            setStatus('failed', 'Snap.js belum termuat. Refresh halaman.');
            if (btn) btn.disabled = false;
            return;
        }
        if (txt) txt.textContent = 'Gateway Siap ✓';
        window.snap.pay(token, {
            onSuccess: function (result) {
                console.log('[Midtrans] onSuccess', result);
                handlePaid();
            },
            onPending: function (result) {
                console.log('[Midtrans] onPending', result);
                setStatus('pending', 'Pembayaran sedang diproses...');
                startPolling();
            },
            onError: function (result) {
                console.error('[Midtrans] onError', result);
                if (btn) btn.disabled = false;
                if (txt) txt.textContent = '💳 Coba Bayar Lagi';
                setStatus('failed', 'Pembayaran gagal. Silakan coba lagi.');
            },
            onClose: function () {
                if (btn) btn.disabled = false;
                if (txt) txt.textContent = '💳 Bayar Sekarang';
                startPolling(); // tetap polling jika user tutup popup
            },
        });
    }

    // ── QRIS REFRESH ──────────────────────────────────────────────
    window.refreshQr = function () {
        const img = document.getElementById('qr-img');
        if (img) img.style.opacity = '0.4';

        fetch(REFRESH_URL, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.image_data) {
                if (img) { img.src = data.image_data; img.style.opacity = '1'; }
                setStatus('info', 'QR Code diperbarui. Silakan scan ulang.');
                setTimeout(() => clearStatus(), 3000);
                startCountdown(15 * 60);
                const el = document.getElementById('countdown');
                if (el) el.classList.remove('timer-expired');
            } else {
                if (img) img.style.opacity = '1';
                setStatus('failed', 'Gagal refresh: ' + (data.message || 'Error'));
            }
        })
        .catch(() => {
            if (img) img.style.opacity = '1';
            setStatus('failed', 'Koneksi error.');
        });
    };

    // ── COUNTDOWN ──────────────────────────────────────────────────
    function startCountdown(sec) {
        totalSeconds = sec;
        clearInterval(countdownInt);
        countdownInt = setInterval(() => {
            totalSeconds--;
            const m  = Math.floor(totalSeconds / 60);
            const s  = totalSeconds % 60;
            const el = document.getElementById('countdown');
            if (el) {
                el.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
                if (totalSeconds <= 0) {
                    el.classList.add('timer-expired');
                    clearInterval(countdownInt);
                    if (QRIS_MODE) setStatus('expired', 'QR Code kedaluwarsa. Klik Refresh untuk QR baru.');
                }
            }
        }, 1000);
    }

    // ── POLLING STATUS ─────────────────────────────────────────────
    function startPolling() {
        stopPolling();
        const bar = document.getElementById('polling-bar');
        if (bar) bar.style.display = '';
        pollInt = setInterval(() => {
            fetch(STATUS_URL, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            })
            .then(r => r.json())
            .then(data => {
                if (data.is_paid) handlePaid();
                else if (data.payment_status === 'failed' || data.payment_status === 'expired') {
                    stopPolling();
                    setStatus('failed', 'Pembayaran ' + data.status_label + '. Silakan coba lagi.');
                }
            })
            .catch(() => {});
        }, 5000);
    }
    function stopPolling() {
        if (pollInt) { clearInterval(pollInt); pollInt = null; }
        const bar = document.getElementById('polling-bar');
        if (bar) bar.style.display = 'none';
    }

    function handlePaid() {
        stopPolling();
        clearInterval(countdownInt);
        ['qris-section','pay-timer','btn-konfirmasi-wa','btn-refresh-qr','pay-btn','polling-bar']
            .forEach(id => { const el = document.getElementById(id); if (el) el.style.display = 'none'; });
        const icon = document.getElementById('pay-success-icon');
        if (icon) icon.style.display = 'block';
        setStatus('paid', '✅ Pembayaran berhasil dikonfirmasi! Pesanan Anda sedang diproses.');
        setTimeout(() => { window.location.href = NOTA_URL; }, 3000);
    }

    // ── HELPERS ────────────────────────────────────────────────────
    function setStatus(type, msg) {
        const box = document.getElementById('pay-status-box');
        if (!box) return;
        box.className = 'pay-status ' + type;
        box.textContent = msg;
        box.style.display = '';
    }
    function clearStatus() {
        const box = document.getElementById('pay-status-box');
        if (box) { box.className = 'pay-status'; box.style.display = 'none'; }
    }

    // ── INIT ───────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        if (QRIS_MODE) startCountdown(15 * 60);
        if (MIDTRANS_READY) startPolling();
    });

})();
</script>
</body>
</html>
