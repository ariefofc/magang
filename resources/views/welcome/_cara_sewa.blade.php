{{-- SECTION: Cara Sewa --}}
    <!-- ===================== CARA SEWA ===================== -->
    <section id="cara-sewa" class="lp-cara-sewa">
        <div class="lp-cara-sewa-inner">
            <div class="lp-cara-sewa-header">
                <span class="section-label">Mudah &amp; Cepat</span>
                <h2 class="lp-cara-sewa-title heading-serif">Cara Sewa Kebaya</h2>
                <p class="lp-cara-sewa-sub">Proses penyewaan yang simpel, transparan, dan aman &mdash; selesai dalam hitungan menit.</p>
            </div>

            <div class="lp-steps-grid" style="position:relative;">
                <div class="lp-step-connector"></div>

                @php
                    $langkah = [
                        ['no'=>'01', 'icon'=>'🔍', 'judul'=>'Pilih Kebaya',       'isi'=>'Jelajahi katalog 13+ koleksi kebaya premium. Gunakan navigasi halaman untuk melihat semua koleksi.'],
                        ['no'=>'02', 'icon'=>'🤖', 'judul'=>'Custom Warna AI',    'isi'=>'Klik tombol "Sewa & Custom AI", lalu masukkan perintah warna ke sistem AI untuk visualisasi.'],
                        ['no'=>'03', 'icon'=>'📝', 'judul'=>'Isi Data & Tanggal', 'isi'=>'Lengkapi nama, nomor WA, ukuran, serta tanggal sewa dan pengembalian kebaya Anda.'],
                        ['no'=>'04', 'icon'=>'💳', 'judul'=>'Bayar DP',           'isi'=>'Pilih metode pembayaran: QRIS, Transfer SeaBank, atau Cash di studio. DP minimal 50%.'],
                        ['no'=>'05', 'icon'=>'📄', 'judul'=>'Terima Nota',        'isi'=>'Nota digital berisi kode transaksi unik dikirim setelah konfirmasi. Simpan sebagai bukti sah.'],
                        ['no'=>'06', 'icon'=>'✨', 'judul'=>'Fitting & Pakai',    'isi'=>'Kunjungi studio untuk sesi fitting. Admin akan konfirmasi jadwal via WhatsApp sebelumnya.'],
                    ];
                @endphp

                @foreach($langkah as $step)
                    <div class="lp-step">
                        <div class="lp-step-circle">
                            {{ $step['icon'] }}
                            <div class="lp-step-num">{{ $step['no'] }}</div>
                        </div>
                        <h4 class="lp-step-title">{{ $step['judul'] }}</h4>
                        <p class="lp-step-desc">{{ $step['isi'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Syarat & Ketentuan -->
            <div class="lp-syarat-box">
                <span class="lp-syarat-icon">📌</span>
                <div>
                    <p class="lp-syarat-title">Syarat &amp; Ketentuan Penyewaan</p>
                    <ul class="lp-syarat-list">
                        <li>Pelunasan wajib dibayarkan saat pengambilan kebaya di studio.</li>
                        <li>Keterlambatan pengembalian dikenakan denda Rp 50.000 per hari.</li>
                        <li>Dilarang mencuci kebaya sendiri — perawatan khusus dry-clean oleh studio.</li>
                        <li>Kerusakan akibat kelalaian penyewa menjadi tanggung jawab penyewa.</li>
                        <li>Pembatalan setelah DP lunas tidak dapat dikembalikan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
