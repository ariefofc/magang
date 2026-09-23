{{-- ===================================================================
     DASHBOARD PARTIAL: Sidebar
     Dipanggil dari: dashboard.blade.php via @include
     =================================================================== --}}
<div class="db-sidebar-overlay" x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak></div>

<aside class="db-sidebar" :class="sidebarOpen ? 'open' : ''">
    <div>
        <!-- Brand -->
        <div class="db-sidebar-brand">
            <img src="{{ asset('logo-mayangsari.jpg') }}"
                 onerror="this.onerror=null; this.src='{{ asset('logo-mayangsari.jpeg') }}';"
                 alt="MS Harmony"
                 class="db-sidebar-logo">
            <div>
                <div class="db-sidebar-brand-name">MS HARMONY</div>
                <span class="db-sidebar-brand-sub">Penyewaan Kebaya</span>
            </div>
        </div>

        <!-- Navigation: Role-Aware Menu -->
        <nav class="db-sidebar-nav">
            @php
                $jmlMenunggu     = collect($daftarTransaksi ?? [])->whereIn('status', ['Menunggu Konfirmasi', 'Menunggu Pembayaran DP', 'Menunggu Fitting'])->count();
                $jmlFitting      = \App\Models\Transaksi::whereIn('status', ['Telah Dikonfirmasi', 'Menunggu Fitting', 'Sedang Fitting'])->count();
                $jmlPengembalian = \App\Models\Transaksi::where('status', 'Mengajukan Pengembalian')->count();
                $authUser        = Auth::user();
                $chatUnread      = 0;
                if ($authUser && $authUser->canManageOrders()) {
                    try { $chatUnread = \App\Models\ChatRoom::totalUnread(); } catch (\Throwable $e) { $chatUnread = 0; }
                }
            @endphp

            {{-- Role Badge --}}
            <div class="role-section">
                <span class="role-section-label">Akses Role</span>
                @if($authUser?->isSuperAdmin())
                    <span class="role-badge role-badge-super-admin">SUPER ADMIN</span>
                @elseif($authUser?->isAdmin())
                    <span class="role-badge role-badge-admin">ADMINISTRATOR</span>
                @elseif($authUser?->isOwner())
                    <span class="role-badge role-badge-owner">OWNER</span>
                @else
                    <span class="role-badge role-badge-owner">PENGGUNA</span>
                @endif
            </div>
            <div class="role-divider"></div>

            {{-- Master Katalog: Admin & Super Admin --}}
            @if($authUser?->canManageOrders())
            <button @click="tab = 'katalog'"
                    :class="tab === 'katalog' ? 'active' : ''"
                    class="db-nav-btn">
                <span class="db-nav-btn-icon">📦</span>
                <span>Kelola Data Kebaya Master</span>
            </button>
            @endif

            {{-- Kelola Pemesanan: Admin & Super Admin --}}
            @if($authUser?->canManageOrders())
            <button @click="tab = 'pemesanan'"
                    :class="tab === 'pemesanan' ? 'active' : ''"
                    class="db-nav-btn">
                <span class="db-nav-btn-icon">📑</span>
                <span>Kelola Pemesanan</span>
                @if($jmlMenunggu > 0)
                    <span class="db-nav-badge">{{ $jmlMenunggu }}</span>
                @endif
            </button>

            <button @click="tab = 'fitting'"
                    :class="tab === 'fitting' ? 'active' : ''"
                    class="db-nav-btn">
                <span class="db-nav-btn-icon">✂️</span>
                <span>Penjadwalan Fitting</span>
                @if($jmlFitting > 0)
                    <span class="db-nav-badge" style="background:#8B5A2B;">{{ $jmlFitting }}</span>
                @endif
            </button>

            <button @click="tab = 'pengembalian'"
                    :class="tab === 'pengembalian' ? 'active' : ''"
                    class="db-nav-btn">
                <span class="db-nav-btn-icon">🔁</span>
                <span>Verifikasi Pengembalian</span>
                @if($jmlPengembalian > 0)
                    <span class="db-nav-badge" style="background:#0284c7;">{{ $jmlPengembalian }}</span>
                @endif
            </button>
            @endif

            {{-- Laporan: semua role --}}
            <button @click="tab = 'laporan'"
                    :class="tab === 'laporan' ? 'active' : ''"
                    class="db-nav-btn">
                <span class="db-nav-btn-icon">📊</span>
                <span>Laporan Transaksi / Penjualan</span>
            </button>

            {{-- Live Chat: Admin & Super Admin --}}
            @if($authUser?->canManageOrders())
            <a href="{{ route('admin.chat.inbox') }}"
               class="db-nav-btn" style="text-decoration:none;">
                <span class="db-nav-btn-icon">💬</span>
                <span>Live Chat</span>
                @if($chatUnread > 0)
                    <span class="db-nav-badge" style="background:#ef4444;">{{ $chatUnread }}</span>
                @endif
            </a>
            @endif

            {{-- Fitur Super Admin --}}
            @if($authUser?->isSuperAdmin())
            <div style="height:1px;background:rgba(200,169,110,0.12);margin:6px 14px;"></div>
            <div style="padding: 4px 14px; font-size: 0.6rem; color: var(--text-muted); font-weight: bold; text-transform: uppercase;">Super Admin Menu</div>

            <a href="{{ route('admin.users.index') }}"
               class="db-nav-btn" style="text-decoration:none;">
                <span class="db-nav-btn-icon">👥</span>
                <span>Kelola Pengguna &amp; Hak Akses</span>
            </a>

            <button @click="tab = 'log_audit'"
                    :class="tab === 'log_audit' ? 'active' : ''"
                    class="db-nav-btn">
                <span class="db-nav-btn-icon">🛡️</span>
                <span>Lihat Sistem Log &amp; Audit</span>
            </button>

            <button @click="tab = 'konfigurasi_api'"
                    :class="tab === 'konfigurasi_api' ? 'active' : ''"
                    class="db-nav-btn">
                <span class="db-nav-btn-icon">⚙️</span>
                <span>Konfigurasi API &amp; AI</span>
            </button>
            @endif
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="db-sidebar-footer-full">
        <a href="{{ url('/') }}" class="db-nav-btn db-nav-btn-ghost" style="display:flex;text-decoration:none;">
            <span class="db-nav-btn-icon">🌐</span>
            <span>Kembali ke Web Utama</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="width:100%;">
            @csrf
            <button type="submit" class="db-nav-btn db-nav-btn-logout" style="width:100%;">
                <span class="db-nav-btn-icon">🚪</span>
                <span>Log Out</span>
            </button>
        </form>
    </div>
</aside>
