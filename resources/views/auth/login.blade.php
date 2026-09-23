<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — MS Harmony Penyewaan Kebaya</title>
    <meta name="description" content="Panel masuk admin MS Harmony — Sistem Informasi Manajemen Persewaan Kebaya Premium.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&family=Cinzel:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Accent — hanya untuk tombol & garis aksen kecil */
            --maroon:       #6B0012;
            --maroon-dark:  #4A0B10;
            --maroon-deeper:#3A0809;
            /* Gold — soft & muted */
            --gold:         #C8A96E;
            --gold-light:   #DFC28A;
            --gold-muted:   #B8965A;
            --gold-pale:    #F5EDD8;
            --gold-dark:    #9A7A38;
            /* Backgrounds — Warm Ivory (bukan putih polos) */
            --bg-page:      #FDFBF7;
            --bg-cream:     #F9F6F0;
            --bg-ivory:     #F4EDE0;
            --bg-panel-dark:#2C1F15;  /* Warm dark brown — bukan maroon */
            /* Typography — Soft Warm Taupe */
            --text-dark:    #3D342F;
            --text-medium:  #5A4A40;
            --text-muted:   #8A7060;
            /* Borders */
            --border-gold:  rgba(200, 169, 110, 0.35);
            /* Fonts */
            --font-heading: 'Playfair Display', Georgia, serif;
            --font-display: 'Cinzel', serif;
            --font-body:    'Plus Jakarta Sans', system-ui, sans-serif;
            --radius-sm:    6px;
            --radius-md:    12px;
            --transition:   all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 16px; height: 100%; }
        body {
            font-family: var(--font-body);
            background: var(--bg-page);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
        }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }
        ::selection { background: var(--gold-pale); color: var(--maroon-dark); }

        /* ── LAYOUT ─────────────────────────────────── */
        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ── LEFT PANEL — Dekorasi Visual (Warm Dark Earth-Tone) ─── */
        .login-left {
            flex: 1;
            background: var(--bg-panel-dark);  /* #3D342F Warm Dark Brown */
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            overflow: hidden;
        }

        /* Batik pattern overlay */
        .login-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='none' stroke='%23C8A96E' stroke-width='0.6' opacity='0.12'%3E%3Ccircle cx='40' cy='40' r='30'/%3E%3Ccircle cx='40' cy='40' r='20'/%3E%3Ccircle cx='40' cy='40' r='10'/%3E%3Ccircle cx='40' cy='40' r='3'/%3E%3Cpath d='M10 40 L70 40 M40 10 L40 70'/%3E%3Cpath d='M18 18 L62 62 M62 18 L18 62'/%3E%3C/g%3E%3C/svg%3E");
            background-size: 80px 80px;
            pointer-events: none;
        }

        /* Gold gradient border right */
        .login-left::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 2px; height: 100%;
            background: linear-gradient(180deg, transparent 0%, var(--gold) 30%, var(--gold-light) 50%, var(--gold) 70%, transparent 100%);
            opacity: 0.6;
        }

        /* Ukiran corner top-left */
        .login-left-corner-tl {
            position: absolute;
            top: 20px; left: 20px;
            width: 80px; height: 80px;
            border-top: 2px solid var(--gold-muted);
            border-left: 2px solid var(--gold-muted);
            opacity: 0.5;
            border-radius: 4px 0 0 0;
        }
        .login-left-corner-br {
            position: absolute;
            bottom: 20px; right: 20px;
            width: 80px; height: 80px;
            border-bottom: 2px solid var(--gold-muted);
            border-right: 2px solid var(--gold-muted);
            opacity: 0.5;
            border-radius: 0 0 4px 0;
        }

        /* Top gold bar */
        .login-left-topbar {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), var(--gold-light), var(--gold), transparent);
        }

        .login-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 380px;
        }

        .login-left-logo-wrap {
            width: 88px; height: 88px;
            margin: 0 auto 1.75rem;
            border-radius: 50%;
            border: 2px solid rgba(212,175,55,0.5);
            box-shadow: 0 0 0 6px rgba(212,175,55,0.1), 0 8px 32px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-left-logo-wrap img {
            width: 100%; height: 100%;
            object-fit: cover;
        }

        .login-left-ornament {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .login-left-ornament-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212,175,55,0.5));
        }
        .login-left-ornament-line:last-child {
            background: linear-gradient(90deg, rgba(212,175,55,0.5), transparent);
        }
        .login-left-ornament-text {
            font-family: var(--font-display);
            font-size: 0.5rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .login-left-brand {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            color: var(--gold-light);
            letter-spacing: 0.12em;
            line-height: 1.1;
            margin-bottom: 0.35rem;
            text-shadow: 0 2px 12px rgba(0,0,0,0.3);
        }
        .login-left-tagline {
            font-family: var(--font-heading);
            font-size: 0.85rem;
            font-style: italic;
            color: rgba(230,198,135,0.65);
            margin-bottom: 2rem;
        }

        /* Kebaya image showcase */
        .login-left-showcase {
            position: relative;
            width: 220px;
            margin: 0 auto 2rem;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: 0 16px 48px rgba(0,0,0,0.5), 0 0 0 1px rgba(212,175,55,0.25);
            aspect-ratio: 3/4;
        }
        .login-left-showcase img {
            width: 100%; height: 100%;
            object-fit: cover;
            object-position: top;
        }
        .login-left-showcase::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 60%, rgba(74,11,16,0.7) 100%);
            z-index: 1;
        }
        .login-left-showcase-badge {
            position: absolute;
            bottom: 0.75rem;
            left: 0.75rem;
            right: 0.75rem;
            z-index: 2;
            text-align: center;
        }
        .login-left-showcase-badge p:first-child {
            font-family: var(--font-display);
            font-size: 0.45rem;
            letter-spacing: 0.25em;
            color: var(--gold);
            text-transform: uppercase;
        }
        .login-left-showcase-badge p:last-child {
            font-family: var(--font-heading);
            font-size: 0.85rem;
            font-weight: 700;
            color: white;
        }

        /* Corner accents on showcase */
        .showcase-corner {
            position: absolute;
            width: 18px; height: 18px;
            border-color: var(--gold);
            border-style: solid;
            z-index: 3;
        }
        .showcase-corner-tl { top: 8px; left: 8px; border-width: 1.5px 0 0 1.5px; }
        .showcase-corner-tr { top: 8px; right: 8px; border-width: 1.5px 1.5px 0 0; }
        .showcase-corner-bl { bottom: 8px; left: 8px; border-width: 0 0 1.5px 1.5px; }
        .showcase-corner-br { bottom: 8px; right: 8px; border-width: 0 1.5px 1.5px 0; }

        .login-left-features {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }
        .login-left-feature {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.72rem;
            color: rgba(230,198,135,0.55);
        }
        .login-left-feature-dot {
            width: 4px; height: 4px;
            border-radius: 50%;
            background: var(--gold);
            flex-shrink: 0;
        }

        /* ── RIGHT PANEL — Form Login ─────────── */
        .login-right {
            width: 420px;
            flex-shrink: 0;
            background: var(--bg-page);  /* #FDFBF7 Warm Ivory */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
        }

        /* Subtle batik pattern on right */
        .login-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cg fill='none' stroke='%23D4AF37' stroke-width='0.3' opacity='0.08'%3E%3Ccircle cx='20' cy='20' r='12'/%3E%3Ccircle cx='20' cy='20' r='6'/%3E%3Ccircle cx='20' cy='20' r='2'/%3E%3Cpath d='M8 20 L32 20 M20 8 L20 32'/%3E%3C/g%3E%3C/svg%3E");
            background-size: 40px 40px;
            pointer-events: none;
        }

        .login-form-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 340px;
        }

        /* Form header */
        .login-form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-form-ornament {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .login-form-ornament-line {
            flex: 1;
            height: 1px;
            background: var(--border-gold);
        }
        .login-form-ornament span {
            font-size: 1.1rem;
            color: var(--gold-muted);
        }

        .login-form-title {
            font-family: var(--font-heading);
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }
        .login-form-subtitle {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Form card */
        .login-card {
            background: var(--bg-ivory);   /* Warm off-white */
            border: 1px solid var(--border-gold);
            border-radius: var(--radius-md);
            padding: 1.75rem;
            box-shadow: 0 4px 24px rgba(74,55,40,0.08), 0 1px 4px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        /* Gold line top */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold-muted), var(--gold), var(--gold-muted), transparent);
        }

        /* Ukiran corner inside card */
        .login-card-corner-tl {
            position: absolute;
            top: 8px; left: 8px;
            width: 20px; height: 20px;
            border-top: 1px solid rgba(212,175,55,0.4);
            border-left: 1px solid rgba(212,175,55,0.4);
        }
        .login-card-corner-br {
            position: absolute;
            bottom: 8px; right: 8px;
            width: 20px; height: 20px;
            border-bottom: 1px solid rgba(212,175,55,0.4);
            border-right: 1px solid rgba(212,175,55,0.4);
        }

        /* Form elements */
        .login-field {
            margin-bottom: 1rem;
        }
        .login-label {
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--text-medium);
            margin-bottom: 0.4rem;
        }
        .login-input-wrap {
            position: relative;
        }
        .login-input-icon {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold-muted);
            font-size: 0.85rem;
            pointer-events: none;
        }
        .login-input {
            width: 100%;
            font-family: var(--font-body);
            font-size: 0.82rem;
            padding: 0.7rem 0.85rem 0.7rem 2.4rem;
            background: rgba(255,253,249,0.9);
            border: 1.5px solid rgba(212,175,55,0.3);
            border-radius: var(--radius-sm);
            color: var(--text-dark);
            transition: var(--transition);
            outline: none;
        }
        .login-input:focus {
            border-color: var(--gold-muted);
            box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
            background: white;
        }
        .login-input::placeholder {
            color: rgba(122,101,88,0.4);
        }

        /* Error messages */
        .login-error {
            font-size: 0.68rem;
            color: #b91c1c;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Session status */
        .login-status {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: var(--radius-sm);
            padding: 0.6rem 0.85rem;
            font-size: 0.72rem;
            color: #166534;
            margin-bottom: 1rem;
        }

        /* Submit button — Tetap maroon untuk aksen premium */
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: #E8C98A;
            border: 1px solid rgba(200,169,110,0.3);
            border-radius: var(--radius-sm);
            padding: 0.85rem;
            font-family: var(--font-display);
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(200,169,110,0.12), transparent);
            transition: left 0.5s ease;
        }
        .login-btn:hover::before { left: 100%; }
        .login-btn:hover {
            background: linear-gradient(135deg, var(--maroon-dark), #280507);
            box-shadow: 0 4px 20px rgba(107,0,18,0.3);
            transform: translateY(-1px);
        }
        .login-btn:active { transform: translateY(0); }

        /* Back to web link */
        .login-back-link {
            text-align: center;
            font-size: 0.7rem;
            color: var(--text-muted);
        }
        .login-back-link a {
            color: var(--gold-dark);
            font-weight: 600;
            transition: var(--transition);
        }
        .login-back-link a:hover { color: var(--maroon); }

        /* Copyright */
        .login-copyright {
            position: absolute;
            bottom: 1.25rem;
            left: 0; right: 0;
            text-align: center;
            font-size: 0.6rem;
            color: var(--text-muted);
            letter-spacing: 0.05em;
            z-index: 1;
        }

        /* ── RESPONSIVE ─────────────────────────────── */
        @media (max-width: 820px) {
            .login-left { display: none; }
            .login-right { width: 100%; padding: 2rem 1.5rem; }
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .login-form-wrap { animation: fadeInUp 0.5s ease both; }

        /* Batik divider */
        .login-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1rem 0 0.75rem;
        }
        .login-divider::before, .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-gold);
        }
        .login-divider span {
            font-size: 0.55rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="login-wrapper">

        <!-- ═══════════════════════════════════════
             LEFT PANEL — Dekorasi Visual Keraton
             ═══════════════════════════════════════ -->
        <div class="login-left">
            <div class="login-left-topbar"></div>
            <div class="login-left-corner-tl"></div>
            <div class="login-left-corner-br"></div>

            <div class="login-left-content">

                <!-- Logo -->
                <div class="login-left-logo-wrap">
                    <img src="{{ asset('logo-mayangsari.jpg') }}"
                         onerror="this.onerror=null;this.src='{{ asset('logo-mayangsari.jpeg') }}';"
                         alt="MS Harmony Logo">
                </div>

                <!-- Ornament + Brand -->
                <div class="login-left-ornament">
                    <div class="login-left-ornament-line"></div>
                    <span class="login-left-ornament-text">Boutique Kebaya</span>
                    <div class="login-left-ornament-line"></div>
                </div>
                <div class="login-left-brand">MS HARMONY</div>
                <div class="login-left-tagline">Penyewaan Kebaya Eksklusif</div>

                <!-- Showcase image -->
                <div class="login-left-showcase">
                    <div class="showcase-corner showcase-corner-tl"></div>
                    <div class="showcase-corner showcase-corner-tr"></div>
                    <div class="showcase-corner showcase-corner-bl"></div>
                    <div class="showcase-corner showcase-corner-br"></div>
                    <img src="{{ asset('Kebaya Kartini Beludru.png') }}"
                         onerror="this.onerror=null;this.style.display='none';"
                         alt="Koleksi Kebaya MS Harmony">
                    <div class="login-left-showcase-badge">
                        <p>Koleksi Unggulan</p>
                        <p>Kartini Beludru</p>
                    </div>
                </div>

                <!-- Features list -->
                <div class="login-left-features">
                    <div class="login-left-feature">
                        <div class="login-left-feature-dot"></div>
                        <span>13+ Koleksi Kebaya Premium</span>
                    </div>
                    <div class="login-left-feature">
                        <div class="login-left-feature-dot"></div>
                        <span>Fitting Langsung di Studio</span>
                    </div>
                    <div class="login-left-feature">
                        <div class="login-left-feature-dot"></div>
                        <span>Sistem Informasi Manajemen Modern</span>
                    </div>
                </div>

            </div>
        </div>


        <!-- ═══════════════════════════════════════
             RIGHT PANEL — Form Login Admin
             ═══════════════════════════════════════ -->
        <div class="login-right">

            <div class="login-form-wrap">

                <!-- Header -->
                <div class="login-form-header">
                    <div class="login-form-ornament">
                        <div class="login-form-ornament-line"></div>
                        <span>✦</span>
                        <div class="login-form-ornament-line"></div>
                    </div>
                    <h1 class="login-form-title">Masuk Admin</h1>
                    <p class="login-form-subtitle">Panel Manajemen MS Harmony Penyewaan Kebaya</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="login-status">{{ session('status') }}</div>
                @endif

                <!-- Login Form Card -->
                <div class="login-card">
                    <div class="login-card-corner-tl"></div>
                    <div class="login-card-corner-br"></div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="login-field">
                            <label class="login-label" for="email">📧 Alamat Email</label>
                            <div class="login-input-wrap">
                                <span class="login-input-icon">@</span>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       class="login-input"
                                       value="{{ old('email') }}"
                                       required
                                       autofocus
                                       autocomplete="username"
                                       placeholder="admin@msharmony.com">
                            </div>
                            @error('email')
                                <div class="login-error">⚠ {{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="login-field">
                            <label class="login-label" for="password">🔒 Kata Sandi</label>
                            <div class="login-input-wrap">
                                <span class="login-input-icon">●</span>
                                <input id="password"
                                       type="password"
                                       name="password"
                                       class="login-input"
                                       required
                                       autocomplete="current-password"
                                       placeholder="••••••••">
                            </div>
                            @error('password')
                                <div class="login-error">⚠ {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="login-divider">
                            <span>Login Admin MS Harmony</span>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="login-btn" id="btn-login">
                            <span>✦</span>
                            <span>Masuk ke Dashboard</span>
                            <span>✦</span>
                        </button>
                    </form>
                </div>

                <!-- Back to web -->
                <div class="login-back-link">
                    <a href="{{ url('/') }}">⬅ Kembali ke Halaman Utama Web</a>
                </div>

            </div>

            <!-- Copyright -->
            <div class="login-copyright">
                © 2026 MS Harmony Penyewaan Kebaya · All rights reserved
            </div>

        </div>

    </div>
</body>
</html>
