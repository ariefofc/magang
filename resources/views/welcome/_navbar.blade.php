{{-- SECTION: Navbar --}}
    <!-- ===================== NAVBAR ===================== -->
    <header class="lp-navbar">
        <div class="lp-navbar-inner">
            <a href="{{ url('/') }}" class="lp-navbar-brand">
                <img src="{{ asset('logo-mayangsari.jpg') }}"
                     onerror="this.onerror=null;this.src='{{ asset('logo-mayangsari.jpeg') }}';"
                     alt="MS Harmony Logo"
                     class="lp-navbar-logo">
                <div class="lp-navbar-brand-text">
                    <span class="lp-navbar-name">MS HARMONY</span>
                    <div class="lp-navbar-tagline-bar">
                        <span class="lp-navbar-subtitle">— Penyewaan Kebaya —</span>
                    </div>
                </div>
            </a>

            <nav class="lp-navbar-nav">
                <a href="#layanan"   class="lp-navbar-link">Layanan</a>
                <a href="#cara-sewa" class="lp-navbar-link">Cara Sewa</a>
                <a href="#katalog"   class="lp-navbar-link">Katalog</a>
                <a href="#tentang"   class="lp-navbar-link">Tentang</a>
            </nav>

            <div>
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-maroon btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-maroon btn-sm">Masuk Admin</a>
                @endauth
            </div>
        </div>
    </header>
