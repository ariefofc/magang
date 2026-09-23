<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog & Sewa Kebaya - Ms Harmoni</title>
    <!-- Fonts Editorial High-End -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS & AlpineJS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/pages/katalog.css') }}">
</head>
<body class="bg-[#fcfbfa] text-stone-800 font-sans antialiased min-h-screen">

    <!-- Header Navbar -->
    <header class="bg-white/80 backdrop-blur-md border-b border-stone-200/80 sticky top-0 z-30 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo-mayangsari.jpeg') }}" alt="Ms Harmoni" class="h-10 w-10 object-cover rounded-full border border-amber-600/30">
                <div>
                    <h1 class="font-serif font-bold text-stone-900 text-lg leading-tight">MS HARMONI</h1>
                    <span class="text-[10px] text-amber-800 font-semibold tracking-widest uppercase block">Boutique & Kebaya Rental</span>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="bg-stone-900 hover:bg-black text-white px-4 py-2 rounded-xl text-xs font-semibold transition">
                <span>🔒</span> Area Admin / SIM
            </a>
        </div>
    </header>

    <!-- Main Content Form Sewa & AI Color Simulator -->
    <main class="max-w-6xl mx-auto px-6 py-10" 
          x-data="{
              isGenerating: false,
              selectedModel: 'Kebaya Kutu Baru Modern',
              hargaBase: 750000,
              selectedColor: 'Sage Green Pastel',
              selectedImage: '{{ asset('images/kebaya-sage.jpg') }}',
              
              // Database Color Preset Mapping (Pengganti API Berbayar)
              presetMap: {
                  'Sage Green Pastel': '{{ asset('images/kebaya-sage.jpg') }}',
                  'Rose Gold Luxury': '{{ asset('images/kebaya-rosegold.jpg') }}',
                  'Terracotta Earthy': '{{ asset('images/kebaya-terracotta.jpg') }}',
                  'Royal Navy Blue': '{{ asset('images/kebaya-navy.jpg') }}'
              },

              // Fungsi Simulasi Generate AI
              generateAIColor(warna) {
                  this.isGenerating = true;
                  this.selectedColor = warna;
                  setTimeout(() => {
                      this.selectedImage = this.presetMap[warna] || '{{ asset('images/kebaya-sage.jpg') }}';
                      this.isGenerating = false;
                  }, 1200); // Effect Delay Loading AI 1.2 detik
              }
          }">

        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-[11px] font-bold text-amber-800 uppercase tracking-widest bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
                AI Fabric Color Customizer
            </span>
            <h2 class="font-serif font-bold text-3xl md:text-4xl text-stone-900 mt-3">Pesan & Sesuaikan Warna Kebaya Eleganku</h2>
            <p class="text-xs md:text-sm text-stone-500 mt-2">Pilih model favoritmu dan gunakan fitur AI untuk melihat simulasi paduan warna eksklusif sebelum disewa.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- KOLOM KIRI: VISUAL PREVIEW & SIMULASI AI GENERATOR -->
            <div class="lg:col-span-5 space-y-5 sticky top-24">
                <div class="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-sm relative overflow-hidden">
                    
                    <!-- Badge Info Kebaya -->
                    <div class="absolute top-6 left-6 z-10 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-lg border border-stone-200 shadow-sm">
                        <span class="text-[10px] text-stone-500 uppercase tracking-wider block">Model Dipilih:</span>
                        <strong class="text-xs font-serif text-stone-900" x-text="selectedModel"></strong>
                    </div>

                    <!-- Visual Container Preview Foto -->
                    <div class="relative w-full h-[450px] bg-stone-100 rounded-xl overflow-hidden flex items-center justify-center">
                        
                        <!-- Image Output Kebaya -->
                        <img :src="selectedImage" 
                             :alt="selectedModel" 
                             class="w-full h-full object-cover transition-all duration-700"
                             :class="isGenerating ? 'scale-105 blur-sm opacity-60' : 'scale-100 blur-0 opacity-100'">

                        <!-- Overlay Loading Animation 'Generating AI' -->
                        <div x-show="isGenerating" x-cloak class="absolute inset-0 bg-black/40 backdrop-blur-md flex flex-col items-center justify-center text-white space-y-3">
                            <div class="w-10 h-10 border-4 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
                            <div class="text-center">
                                <span class="text-xs font-bold tracking-widest uppercase block text-amber-300">AI Color Processing...</span>
                                <span class="text-[10px] text-stone-200">Applying fabric palette: <strong x-text="selectedColor"></strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Note Result AI Output -->
                    <div class="mt-4 p-3 bg-amber-50/70 border border-amber-200/60 rounded-xl flex items-center gap-3">
                        <span class="text-xl">✨</span>
                        <div class="text-xs">
                            <span class="font-bold text-amber-900 block">AI Custom Color Active:</span>
                            <span class="text-stone-600 font-medium" x-text="selectedColor"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: FORMULIR PEMESANAN & PILIHAN WARNA AI -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-stone-200/80 p-6 md:p-8 shadow-sm">
                
                <form action="{{ route('transaksi.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- Hidden Input untuk dikirim ke Backend/DB -->
                    <input type="hidden" name="nama_kebaya" :value="selectedModel">
                    <input type="hidden" name="warna_custom_ai" :value="selectedColor">
                    <input type="hidden" name="gambar_ai_result" :value="selectedImage">

                    <!-- STEP 1: PILIH MODEL KEBAYA DASAR -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">
                            1. Pilih Model Kebaya
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" 
                                    @click="selectedModel = 'Kebaya Kutu Baru Modern'; hargaBase = 750000"
                                    :class="selectedModel === 'Kebaya Kutu Baru Modern' ? 'border-amber-800 bg-amber-50/50 ring-1 ring-amber-800 font-bold' : 'border-stone-200 hover:border-stone-300'"
                                    class="p-3.5 border rounded-xl text-left transition text-xs space-y-1">
                                <span class="block text-stone-900">Kebaya Kutu Baru Modern</span>
                                <span class="block text-amber-900 font-semibold">Rp 750.000</span>
                            </button>

                            <button type="button" 
                                    @click="selectedModel = 'Kebaya Simpel Elegant'; hargaBase = 650000"
                                    :class="selectedModel === 'Kebaya Simpel Elegant' ? 'border-amber-800 bg-amber-50/50 ring-1 ring-amber-800 font-bold' : 'border-stone-200 hover:border-stone-300'"
                                    class="p-3.5 border rounded-xl text-left transition text-xs space-y-1">
                                <span class="block text-stone-900">Kebaya Simpel Elegant</span>
                                <span class="block text-amber-900 font-semibold">Rp 650.000</span>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: SIMULATED AI COLOR GENERATOR -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">
                                2. AI Color Suggestion (Ubah Warna Kebaya)
                            </label>
                            <span class="text-[10px] text-amber-800 font-semibold bg-amber-100 px-2 py-0.5 rounded">Preset AI Active</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="(imgPath, colorName) in presetMap" :key="colorName">
                                <button type="button" 
                                        @click="generateAIColor(colorName)"
                                        :disabled="isGenerating"
                                        :class="selectedColor === colorName ? 'bg-stone-900 text-white font-semibold' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'"
                                        class="py-2.5 px-3 rounded-xl text-[11px] border border-stone-200/80 transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <span>🎨</span>
                                    <span x-text="colorName.split(' ')[0]"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- STEP 3: DATA ISI FORMULIR PENYEWAAN -->
                    <div class="pt-4 border-t border-stone-200 space-y-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">
                            3. Detail Penyewa & Pengukuran
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-medium text-stone-600 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama_user" required placeholder="Contoh: Nabila Putri" 
                                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-amber-800">
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-stone-600 mb-1">Nomor WhatsApp</label>
                                <input type="text" name="no_wa" required placeholder="08123456789" 
                                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-amber-800">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-medium text-stone-600 mb-1">Pilih Ukuran Busana</label>
                                <select name="ukuran" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-amber-800">
                                    <option value="S">Size S (Lingkar Dada 88 cm)</option>
                                    <option value="M" selected>Size M (Lingkar Dada 92 cm)</option>
                                    <option value="L">Size L (Lingkar Dada 98 cm)</option>
                                    <option value="XL">Size XL (Lingkar Dada 104 cm)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-stone-600 mb-1">Metode Pembayaran</label>
                                <select name="metode_pembayaran" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-amber-800">
                                    <option value="Transfer QRIS / DP 50%">Transfer QRIS / DP 50%</option>
                                    <option value="Transfer Bank Mandiri">Transfer Bank Mandiri</option>
                                    <option value="Cash / Bayar di Studio">Cash / Bayar di Studio</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-stone-600 mb-1">Alamat Lengkap / Catatan Fitting</label>
                            <textarea name="alamat" rows="2" placeholder="Tuliskan alamat pengiriman / catatan tanggal acara..." 
                                      class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-amber-800"></textarea>
                        </div>
                    </div>

                    <!-- STEP 4: RINGKASAN TOTAL & TOMBOL SUBMIT -->
                    <div class="pt-4 border-t border-stone-200 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-stone-400 uppercase tracking-widest block">Total Biaya Sewa</span>
                            <span class="font-serif font-bold text-2xl text-amber-900" x-text="'Rp ' + hargaBase.toLocaleString('id-ID')"></span>
                        </div>

                        <button type="submit" 
                                class="bg-stone-900 hover:bg-black text-white px-6 py-3 rounded-xl text-xs font-bold transition shadow-lg flex items-center gap-2">
                            <span>🛍️</span>
                            <span>Konfirmasi & Sewa Sekarang</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </main>

</body>
</html>