{{-- ===================================================================
     DASHBOARD PARTIAL: Top Bar + Flash Alerts
     =================================================================== --}}
<div class="db-main">

    <!-- Top Bar -->
    <header class="db-topbar">
        <button class="db-hamburger" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle Sidebar">
            <span x-show="!sidebarOpen">☰</span>
            <span x-show="sidebarOpen" x-cloak>✕</span>
        </button>
        <div>
            <h2 class="db-topbar-title">{{ Auth::user()?->isOwner() ? 'Dashboard Bisnis' : 'Dashboard Sistem Informasi Management' }}</h2>
            <p class="db-topbar-sub">Sistem Pengelolaan Persewaan Boutique MS Harmony</p>
        </div>
        <div class="db-topbar-actions">
            <div class="topbar-user-info">
                @php
                    $topbarRole = match(true) {
                        Auth::user()?->isSuperAdmin() => 'SUPER ADMIN',
                        Auth::user()?->isAdmin()      => 'ADMINISTRATOR',
                        Auth::user()?->isOwner()      => 'OWNER',
                        default                       => 'PENGGUNA',
                    };
                @endphp
                <span class="topbar-user-role">{{ $topbarRole }}</span>
                <span class="topbar-user-name">{{ Auth::user()?->name ?? 'Admin' }}</span>
            </div>
        </div>
    </header>

    <!-- Body Content -->
    <main class="db-content">

        {{-- Flash Success --}}
        @if(session('success'))
        <div class="db-alert-success">
            <span style="font-size:1.25rem;">✅</span>
            <p>{{ session('success') }}</p>
        </div>
        @endif

        {{-- Notifikasi Pesanan Baru --}}
        @php
            $pesananBaru = collect($daftarTransaksi ?? [])->filter(function($trx) {
                return isset($trx->created_at) && $trx->created_at >= now()->subMinutes(10);
            })->first();
        @endphp
        @if($pesananBaru)
        <div class="db-alert-new-order pulse-once">
            <div class="db-alert-new-order-info">
                <span style="font-size:1.5rem;">🔔</span>
                <div>
                    <p class="db-alert-new-order-title">Pesanan Baru Masuk!</p>
                    <p class="db-alert-new-order-text">
                        <strong>{{ $pesananBaru->nama_user }}</strong> baru saja memesan
                        <strong>{{ $pesananBaru->nama_kebaya }}</strong> —
                        Kode: <span style="font-family:monospace;">{{ $pesananBaru->kode_transaksi }}</span>
                    </p>
                </div>
            </div>
            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $pesananBaru->no_wa ?? '08123456789') }}"
               target="_blank"
               class="btn btn-sm" style="background:#15803d;color:white;border:none;flex-shrink:0;">
                💬 Hubungi Sekarang
            </a>
        </div>
        @endif
