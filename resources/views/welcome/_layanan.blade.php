{{-- SECTION: Layanan --}}
    <!-- ===================== LAYANAN / FEATURES ===================== -->
    <section id="layanan" class="lp-features">
        <div class="lp-features-inner">
            <div class="lp-features-header">
                <span class="section-label">Yang Kami Tawarkan</span>
                <h2 class="lp-features-title">Layanan MS Harmony</h2>
                <p class="lp-features-subtitle">Studio persewaan kebaya terpercaya dengan standar pelayanan premium dan pengalaman sewa yang menyenangkan.</p>
            </div>

            @php
                $layanan = [
                    ['icon'=>'👗', 'judul'=>'Sewa Kebaya Premium', 'isi'=>'Lebih dari 13 koleksi kebaya eksklusif — modern, tradisional, brokat, beludru, hingga batik tulis kualitas terbaik siap dipakai.'],
                    ['icon'=>'✂️', 'judul'=>'Fitting & Penyesuaian', 'isi'=>'Sesi fitting langsung di studio dengan tim profesional untuk memastikan kebaya pas sempurna di tubuh Anda sebelum acara.'],
                    ['icon'=>'🤖', 'judul'=>'AI Color Customization', 'isi'=>'Visualisasikan warna kebaya impian Anda menggunakan teknologi AI. Lihat tampilan sebelum memutuskan — langsung dari layar Anda.'],
                    ['icon'=>'🧺', 'judul'=>'Dry-Clean Sterilisasi', 'isi'=>'Setiap kebaya dibersihkan menggunakan metode dry-clean profesional setelah penyewaan. Kebaya selalu steril, wangi, dan siap pakai.'],
                    ['icon'=>'📋', 'judul'=>'Nota Digital Resmi', 'isi'=>'Dapatkan nota pemesanan digital lengkap dengan kode transaksi unik. Bisa dicetak sebagai bukti sah saat pengambilan kebaya.'],
                    ['icon'=>'💬', 'judul'=>'Konsultasi via WhatsApp', 'isi'=>'Tim admin MS Harmony siap membantu konsultasi pilihan kebaya, ukuran, jadwal fitting, dan konfirmasi pesanan via WhatsApp.'],
                ];
            @endphp

            <div class="lp-features-grid">
                @foreach($layanan as $layan)
                    <div class="lp-feature-card">
                        <div class="lp-feature-icon">{{ $layan['icon'] }}</div>
                        <h3 class="lp-feature-title">{{ $layan['judul'] }}</h3>
                        <p class="lp-feature-desc">{{ $layan['isi'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- CTA Bar -->
            <div class="lp-features-cta-bar">
                <div>
                    <h3 class="lp-features-cta-title">Siap Tampil Memukau?</h3>
                    <p class="lp-features-cta-sub">Mulai pilih kebaya impian Anda dari katalog kami sekarang.</p>
                </div>
                <a href="#katalog" class="btn btn-gold">Pilih Kebaya Sekarang →</a>
            </div>
        </div>
    </section>
