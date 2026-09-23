<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin SIM - Ms Harmoni</title>
    
    <!-- Google Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-stone-100 text-stone-800 antialiased">

    <div class="flex min-h-screen">
        
        <!-- SIDEBAR NAVIGASI SIM (INPUT - PROSES - OUTPUT) -->
        <aside class="w-64 bg-stone-900 text-stone-300 flex flex-col justify-between p-5 border-r border-stone-800">
            <div>
                <!-- Brand Header -->
                <div class="flex items-center gap-3 pb-5 mb-5 border-b border-stone-800">
                    <img src="{{ asset('logo-mayangsari.jpeg') }}" alt="Logo" class="h-9 w-auto rounded">
                    <div>
                        <h1 class="font-bold text-white text-base leading-tight">Ms Harmoni</h1>
                        <span class="text-[10px] text-amber-500 font-semibold uppercase tracking-wider block">Admin Panel SIM</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-6">
                    
                    <!-- 1. INPUT / MASTER -->
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-stone-500 block mb-2 px-2">
                            1. Input & Master Data
                        </span>
                        <div class="space-y-1">
                            <a href="{{ route('admin.kebaya.index') }}" 
                               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-stone-300 hover:bg-stone-800 hover:text-white transition">
                                📦 Master Katalog Kebaya
                            </a>
                        </div>
                    </div>

                    <!-- 2. PROSES -->
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-stone-500 block mb-2 px-2">
                            2. Proses & Transaksi
                        </span>
                        <div class="space-y-1">
                            <a href="{{ route('admin.transaksi.index') }}" 
                               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-stone-300 hover:bg-stone-800 hover:text-white transition">
                                📑 Kelola Pemesanan
                            </a>
                            <a href="{{ route('admin.fitting.index') }}" 
                               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-stone-300 hover:bg-stone-800 hover:text-white transition">
                                ✂️ Penjadwalan Fitting
                            </a>
                        </div>
                    </div>

                    <!-- 3. OUTPUT / LAPORAN -->
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-stone-500 block mb-2 px-2">
                            3. Output & Laporan
                        </span>
                        <div class="space-y-1">
                            <a href="{{ route('admin.laporan.index') }}" 
                               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-stone-300 hover:bg-stone-800 hover:text-white transition">
                                📊 Laporan Transaksi
                            </a>
                        </div>
                    </div>

                </nav>
            </div>

            <!-- Footer Sidebar Admin -->
            <div class="pt-4 border-t border-stone-800 flex items-center justify-between">
                <a href="{{ url('/') }}" class="text-xs text-stone-400 hover:text-white font-medium flex items-center gap-1">
                    ⬅ Katalog Web
                </a>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-red-400 hover:text-red-300 font-semibold">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col">
            <!-- Top Bar -->
            <header class="bg-white border-b border-stone-200 px-8 py-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-stone-800">Sistem Informasi Persewaan Kebaya</h2>
                <span class="text-xs text-stone-500">
                    Administrator: <strong class="text-stone-800">{{ Auth::user()->name ?? 'Admin' }}</strong>
                </span>
            </header>

            <!-- Page Content Injection -->
            <main class="p-8 flex-1">
                @yield('content')
            </main>
        </div>

    </div>

</body>
</html>