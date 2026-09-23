<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM MS Harmony — Dashboard Manajemen Royal</title>
    <meta name="description" content="Dashboard Admin MS Harmony — Sistem Informasi Manajemen Persewaan Kebaya Premium.">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-custom.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Helper: buka modal detail pesanan --}}
    <script>
        function openDetailModal(btn) {
            try {
                var data = JSON.parse(atob(btn.getAttribute('data-trx')));
                window.dispatchEvent(new CustomEvent('open-detail', {
                    bubbles: true,
                    detail: data
                }));
            } catch(e) { console.error('[Detail Modal]', e); }
        }
    </script>

</head>
<body class="db-body"
      x-data="{ tab: '{{ request('tab', Auth::user()?->canManageOrders() ? 'pemesanan' : 'laporan') }}', sidebarOpen: false, detailModal: false, detailTrx: {} }"
      @keydown.escape.window="detailModal = false"
      @open-detail.window="detailTrx = $event.detail; detailModal = true">

    {{-- Sidebar (overlay + aside) --}}
    @include('dashboard._sidebar')

    {{-- Top Bar + Flash Alerts + buka tag <div class="db-main"> dan <main> --}}
    @include('dashboard._topbar')

        {{-- TAB 1: Master Katalog --}}
        @include('dashboard._tab_katalog')

        {{-- TAB 2: Kelola Pemesanan --}}
        @include('dashboard._tab_pemesanan')

        {{-- TAB 3: Penjadwalan Fitting --}}
        @include('dashboard._tab_fitting')

        {{-- TAB 4: Verifikasi Pengembalian --}}
        @include('dashboard._tab_pengembalian')

        {{-- TAB 5: Laporan Penjualan --}}
        @include('dashboard._tab_laporan')

        {{-- TAB 6: Sistem Log & Audit (Super Admin) --}}
        @include('dashboard._tab_log_audit')

        {{-- TAB 7: Konfigurasi API & AI (Super Admin) --}}
        @include('dashboard._tab_konfigurasi')

    </main>
</div>{{-- end .db-main --}}

    {{-- Modals: Detail Transaksi + CRUD Kebaya --}}
    @include('dashboard._modals')

</body>
</html>