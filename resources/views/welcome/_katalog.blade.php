{{-- SECTION: Katalog + Modal Sewa Multi-Step --}}
    <!-- ===================== KATALOG ===================== -->
    <section id="katalog" class="lp-katalog">
        <div class="lp-katalog-inner">
            <div class="lp-katalog-header">
                <div>
                    <span class="section-label">Koleksi Pilihan</span>
                    <h2 class="lp-katalog-title heading-serif">Katalog Kebaya</h2>
                </div>
                <p class="lp-katalog-note">Pilih koleksi favorit Anda, perintahkan AI untuk kustom warna, lalu tentukan jadwal fitting.</p>
            </div>

            @php
                // $kebaya diisi dari route via KebayaService::getPublic()
                // aktif = tersedia true, segera = tersedia false, nonaktif/perbaikan = tidak tampil
                // Fallback jika service gagal (13 item: 8 aktif + 5 segera)
                $katalogDefault = [
                    (object)[
                        'nama_kebaya'=>'Kebaya Modern Brukat Gown',
                        'deskripsi'=>'Gaun modern brokat premium dengan siluet elegan dan aksen mewah bergaya tinggi.',
                        'harga_sewa_per_hari'=>850000,
                        'foto_url'=>asset('Kebaya Modern Brukat Gown.png'),
                        'foto_belakang_url'=>asset('Kebaya Modern Brukat Gown-belakang.jpg'),
                        'kain_utama'=>'Brokat Chantilly Perancis & Soft Tulle',
                        'furing'=>'Katun Hero Super (Adem & Menyerap Keringat)',
                        'karakteristik_kain'=>'Tekstur sangat lembut, drape jatuh anggun, bertekstur empuk, tidak gatal & tidak panas.',
                        'detail_payet'=>'Payet Kristal Swarovski sintetik & Mutiara Jepang di leher & lingkar dada.',
                        'rekomendasi_acara'=>'Resepsi Pernikahan, Wisuda, Gala Dinner, Evening Event',
                        'tersedia'=>true
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Aurora Slit Gown',
                        'deskripsi'=>'Gaun aurora eksklusif belahan modern yang anggun untuk setiap momen spesial Anda.',
                        'harga_sewa_per_hari'=>900000,
                        'foto_url'=>asset('Kebaya Aurora Slit Gown.png'),
                        'foto_belakang_url'=>asset('Kebaya Aurora Slit Gown-belakang.jpg'),
                        'kain_utama'=>'Brokat Cornely High-Grade & Silk Satin Velvet',
                        'furing'=>'Full Furing Silky Cotton',
                        'karakteristik_kain'=>'Tampilan mewah mengkilap elegan, elastisitas pas, belahan slit modern yang tetap sopan.',
                        'detail_payet'=>'Aplikasi renda bordir 3D dengan payet tabur berkilau di pinggang & punggung.',
                        'rekomendasi_acara'=>'Engagement / Lamaran, Event Red Carpet, Prewedding, Party',
                        'tersedia'=>true
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Kartini Beludru',
                        'deskripsi'=>'Kebaya Kartini klasik berbahan beludru premium bernuansa royal penuh wibawa.',
                        'harga_sewa_per_hari'=>750000,
                        'foto_url'=>asset('Kebaya Kartini Beludru.png'),
                        'foto_belakang_url'=>asset('Kebaya Kartini Beludru-belakang.jpg'),
                        'kain_utama'=>'Beludru (Velvet) Silk Stretch Royal',
                        'furing'=>'Lapisan Tricot Halus (Menjaga bentuk presisi)',
                        'karakteristik_kain'=>'Permukaan beludru lembut berkilau mewah, cukup fleksibel di badan, hangat & nyaman.',
                        'detail_payet'=>'Bordir benang emas keraton di sepanjang kelim depan, lengan, dan kerah Kartini.',
                        'rekomendasi_acara'=>'Acara Adat Jawa, Midodareni, Wisuda, Resepsi Keraton',
                        'tersedia'=>true
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Kutubaru Klasik Batik',
                        'deskripsi'=>'Kebaya kutubaru tradisional dipadu bawahan kain batik otentik khas Jawa Tengah.',
                        'harga_sewa_per_hari'=>650000,
                        'foto_url'=>asset('Kebaya Kutubaru Klasik Batik.png'),
                        'foto_belakang_url'=>asset('Kebaya Kutubaru Klasik Batik-belakang.jpg'),
                        'kain_utama'=>'Katun Jacquard Emboss (Atasan) & Batik Tulis Solo (Bawahan)',
                        'furing'=>'Katun Prima Adem 100% Cotton',
                        'karakteristik_kain'=>'Sangat sejuk, ringan, mudah bergerak, serat kain katun bernafas baik.',
                        'detail_payet'=>'Kutubaru klasik dengan kancing bungkus rapi & bros etnik benang emas.',
                        'rekomendasi_acara'=>'Hari Kartini, Wisuda, Upacara Adat, Acara Formal Kebudayaan',
                        'tersedia'=>true
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Encim Peranakan',
                        'deskripsi'=>'Kebaya encim klasik berhias bordir halus nan cantik khas budaya Peranakan.',
                        'harga_sewa_per_hari'=>600000,
                        'foto_url'=>asset('Kebaya Encim Peranakan.png'),
                        'foto_belakang_url'=>asset('Kebaya Encim Peranakan-belakang.jpg'),
                        'kain_utama'=>'Katun Voile Silky Premium',
                        'furing'=>'Unlined / Tanpa Furing Tebal (Sejuk & Ringan)',
                        'karakteristik_kain'=>'Kain katun halus, serat rapat, sejuk dipakai di cuaca hangat & iklim tropis.',
                        'detail_payet'=>'Bordir keranjang & flora buatan tangan (hand-made embroidery) di sepanjang tepi bawah.',
                        'rekomendasi_acara'=>'Kebaya Encim Day, Sangjit, Festival Budaya, Acara Semi-Formal',
                        'tersedia'=>true
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Peplum Brokat',
                        'deskripsi'=>'Kebaya peplum feminin berhias renda brokat pilihan cocok untuk momen wisuda.',
                        'harga_sewa_per_hari'=>700000,
                        'foto_url'=>asset('Kebaya Peplum Brokat.png'),
                        'foto_belakang_url'=>asset('Kebaya Peplum Brokat-belakang.jpg'),
                        'kain_utama'=>'Brokat Tile Semi-Perancis & Organza Premium',
                        'furing'=>'Furing Satin Maxmara Silk',
                        'karakteristik_kain'=>'Struktur peplum mengembang anggun di pinggang, memberi siluet tubuh ramping & tinggi.',
                        'detail_payet'=>'Renda Guipure melingkar di pinggang & payet mutiara sintetis di pergelangan lengan.',
                        'rekomendasi_acara'=>'Wisuda Universitas, Pengajian Formal, Kondangan, Syukuran',
                        'tersedia'=>true
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Songket Palembang',
                        'deskripsi'=>'Kebaya mewah dengan kain songket Palembang asli bermotif emas yang indah memukau.',
                        'harga_sewa_per_hari'=>800000,
                        'foto_url'=>asset('Kebaya Songket Palembang.jpg'),
                        'foto_belakang_url'=>asset('Kebaya Songket Palembang-belakang.jpg'),
                        'kain_utama'=>'Tenun Songket Palembang Benang Emas (Bawahan) & Taffeta Silk (Atasan)',
                        'furing'=>'Tricot & Full Furing Asahi Silky',
                        'karakteristik_kain'=>'Tekstur kain tegas & bermotif timbul emas megah, sangat mewah dan regal.',
                        'detail_payet'=>'Motif Songket Lepus benang emas otentik dengan hiasan bros dada khas Sumatera.',
                        'rekomendasi_acara'=>'Resepsi Pernikahan Adat Sumatera, Akad Nikah, Gala Dinner VIP',
                        'tersedia'=>true
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Sutra Solo',
                        'deskripsi'=>'Kebaya sutra pilihan khas Solo dengan tekstur lembut dan tampilan sangat elegan.',
                        'harga_sewa_per_hari'=>950000,
                        'foto_url'=>asset('Kebaya Sutra Solo.jpg'),
                        'foto_belakang_url'=>asset('Kebaya Sutra Solo-belakang.jpg'),
                        'kain_utama'=>'100% Sutra Alam Solo (Natural Silk Premium)',
                        'furing'=>'Sutra Habutai Furing',
                        'karakteristik_kain'=>'Super halus, kilau sutra alami memukau, jatuh sangat mengalir (flowy), sangat sejuk.',
                        'detail_payet'=>'Hand-painted motif batik sutra halus dengan aksen sentuhan prada emas tipis.',
                        'rekomendasi_acara'=>'Royal Wedding Guest, VIP Official Reception, Gala Night',
                        'tersedia'=>true
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Batik Tulis Jawa',
                        'deskripsi'=>'Kebaya berbahan dasar batik tulis tangan bermotif Jawa klasik yang otentik.',
                        'harga_sewa_per_hari'=>720000,
                        'foto_url'=>null,
                        'foto_belakang_url'=>null,
                        'kain_utama'=>'Katun Primissima Batik Tulis Hand-crafted',
                        'furing'=>'Katun Hero Super',
                        'karakteristik_kain'=>'Halus, berbobot mantap, pewarnaan malam alami khas batik klasik Jawa.',
                        'detail_payet'=>'Motif batik tulis Parang Gurdo tradisional dengan sentuhan kerah modern.',
                        'rekomendasi_acara'=>'Acara Resmi Kebudayaan, Seminar Nasional, Event Diplomatik',
                        'tersedia'=>false
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Brokat Evening',
                        'deskripsi'=>'Kebaya eksklusif untuk gala malam formal dengan material brokat premium pilihan.',
                        'harga_sewa_per_hari'=>1000000,
                        'foto_url'=>null,
                        'foto_belakang_url'=>null,
                        'kain_utama'=>'Heavy French Corded Lace & Crepe Silk',
                        'furing'=>'Full Satin Silk',
                        'karakteristik_kain'=>'Kain bertekstur tegas, pembentukan pola presisi, berkilau di bawah lampu malam.',
                        'detail_payet'=>'Full payet jepang di sekeliling bodice dan lengan.',
                        'rekomendasi_acara'=>'Gala Night Dinner, Awarding Night, Formal Party',
                        'tersedia'=>false
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Tenun NTT',
                        'deskripsi'=>'Kebaya tenun ikat asli dari Nusa Tenggara Timur penuh warna dan kekayaan budaya.',
                        'harga_sewa_per_hari'=>680000,
                        'foto_url'=>null,
                        'foto_belakang_url'=>null,
                        'kain_utama'=>'Tenun Ikat Handwoven NTT (Nusa Tenggara Timur)',
                        'furing'=>'Katun Prima',
                        'karakteristik_kain'=>'Tenunan benang katun tebal bermotif etnik khas pulau NTT.',
                        'detail_payet'=>'Pewarnaan benang alami dengan garis tenun etnik kontras.',
                        'rekomendasi_acara'=>'Festival Etnik, Pameran Seni, Acara Kebudayaan',
                        'tersedia'=>false
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Batik Madura',
                        'deskripsi'=>'Kebaya khas Madura motif batik cerah penuh semangat dengan warna berani dan cerah.',
                        'harga_sewa_per_hari'=>600000,
                        'foto_url'=>null,
                        'foto_belakang_url'=>null,
                        'kain_utama'=>'Katun Bergas Batik Madura Pesisiran',
                        'furing'=>'Katun Adem',
                        'karakteristik_kain'=>'Katun tebal sejuk dengan perpaduan warna berani khas pesisir Madura.',
                        'detail_payet'=>'Motif flora fauna Madura bergaris tegas dan ekspresif.',
                        'rekomendasi_acara'=>'Acara Santai Formal, Festival Seni, Parade Busana Etnik',
                        'tersedia'=>false
                    ],
                    (object)[
                        'nama_kebaya'=>'Kebaya Bordiran Sumatera',
                        'deskripsi'=>'Kebaya Sumatera berhias bordiran tangan benang emas dengan detail mewah menawan.',
                        'harga_sewa_per_hari'=>750000,
                        'foto_url'=>null,
                        'foto_belakang_url'=>null,
                        'kain_utama'=>'Katun Balotelli Premium & Benang Emas Metallic',
                        'furing'=>'Furing Katun',
                        'karakteristik_kain'=>'Bordir tebal benang emas di atas kain bertekstur struktural yang anggun.',
                        'detail_payet'=>'Bordiran tangan motif suji caia khas Sumatera Barat/Utara.',
                        'rekomendasi_acara'=>'Resepsi Pernikahan, Acara Adat Sumatera, Perayaan Resmi',
                        'tersedia'=>false
                    ],
                ];
                $daftarKatalog = (!empty($kebaya) && count($kebaya) > 0) ? $kebaya : $katalogDefault;
                $totalKatalog  = count($daftarKatalog);
            @endphp

            {{-- PAGINATION Alpine JS --}}
            <div x-data="{ page: halamanKatalog, perPage: itemPerHalaman, total: {{ $totalKatalog }} }"
                 x-init="$watch('halamanKatalog', v => page = v)">

                <div class="lp-kebaya-grid">
                    @foreach($daftarKatalog as $idx => $item)
                        @php
                            $idxNum           = $idx;
                            $fotoKebaya       = $item->foto_url ? $item->foto_url : null;
                            $fotoBelakang     = !empty($item->foto_belakang_url) ? $item->foto_belakang_url : $fotoKebaya;
                            $kainUtama        = $item->kain_utama ?? 'Brokat Premium';
                            $furing           = $item->furing ?? 'Katun Hero Adem';
                            $karakteristikKain= $item->karakteristik_kain ?? 'Halus & Sejuk';
                            $detailPayet      = $item->detail_payet ?? 'Payet & Renda';
                            $rekomendasiAcara = $item->rekomendasi_acara ?? 'Wisuda & Resepsi';
                        @endphp
                        <div x-show="Math.floor({{ $idxNum }} / perPage) + 1 === page"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-data="{ cardView: 'depan' }"
                             class="kebaya-card">

                            @if(!$item->tersedia)
                                <span class="kebaya-card-badge-coming">Segera Hadir</span>
                            @endif

                            <div class="kebaya-card-img-wrap">
                                @if($fotoKebaya)
                                    <img :src="cardView === 'depan' ? '{{ $fotoKebaya }}' : '{{ $fotoBelakang }}'"
                                         alt="{{ $item->nama_kebaya }}"
                                         class="kebaya-card-img">

                                    @if(!empty($item->foto_belakang_url) && $item->foto_belakang_url !== $fotoKebaya)
                                        <div class="kebaya-view-switcher">
                                            <button type="button"
                                                    @click.stop="cardView = 'depan'"
                                                    :class="cardView === 'depan' ? 'active' : ''"
                                                    class="kebaya-view-pill">
                                                Tampak Depan
                                            </button>
                                            <button type="button"
                                                    @click.stop="cardView = 'belakang'"
                                                    :class="cardView === 'belakang' ? 'active' : ''"
                                                    class="kebaya-view-pill">
                                                Tampak Belakang
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <div class="kebaya-placeholder">
                                        <div class="kebaya-placeholder-icon">👗</div>
                                        <span class="kebaya-placeholder-text">Foto Segera Tersedia</span>
                                    </div>
                                @endif
                            </div>

                            <div class="kebaya-card-body">
                                <h3 class="kebaya-card-name">{{ $item->nama_kebaya }}</h3>
                                <p class="kebaya-card-desc">{{ $item->deskripsi }}</p>

                                {{-- Spesifikasi Kain --}}
                                <div class="kebaya-card-fabric-box">
                                    <div class="fabric-box-title">🧵 Detail &amp; Spesifikasi Kain</div>
                                    <div class="fabric-grid">
                                        <div class="fabric-item" title="Bahan Kain Utama">
                                            <span class="fabric-icon">✨</span>
                                            <span><strong>Kain:</strong> {{ Str::limit($kainUtama, 22) }}</span>
                                        </div>
                                        <div class="fabric-item" title="Lapisan Furing">
                                            <span class="fabric-icon">👕</span>
                                            <span><strong>Furing:</strong> {{ Str::limit($furing, 20) }}</span>
                                        </div>
                                        <div class="fabric-item" title="Karakter & Tekstur">
                                            <span class="fabric-icon">👌</span>
                                            <span><strong>Tekstur:</strong> {{ Str::limit($karakteristikKain, 22) }}</span>
                                        </div>
                                        <div class="fabric-item" title="Rekomendasi Acara">
                                            <span class="fabric-icon">💃</span>
                                            <span><strong>Acara:</strong> {{ Str::limit($rekomendasiAcara, 20) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="kebaya-card-footer">
                                <div>
                                    <span class="kebaya-card-price-label">Sewa Per Hari</span>
                                    <span class="kebaya-card-price">Rp {{ number_format($item->harga_sewa_per_hari, 0, ',', '.') }}</span>
                                </div>

                                @if($item->tersedia)
                                    <button type="button"
                                            @click="openSewaModal(
                                                '{{ addslashes($item->nama_kebaya) }}',
                                                {{ $item->harga_sewa_per_hari }},
                                                '{{ $fotoKebaya }}',
                                                '{{ $fotoBelakang }}',
                                                '{{ addslashes($kainUtama) }}',
                                                '{{ addslashes($furing) }}',
                                                '{{ addslashes($karakteristikKain) }}',
                                                '{{ addslashes($detailPayet) }}',
                                                '{{ addslashes($rekomendasiAcara) }}'
                                            )"
                                            class="btn btn-maroon btn-sm">
                                        Sewa &amp; Custom AI
                                    </button>
                                @else
                                    <button type="button"
                                            onclick="window.open('https://wa.me/6281949862260?text=Saya+tertarik+menyewa+{{ urlencode($item->nama_kebaya) }}', '_blank')"
                                            class="btn btn-sm" style="background:#15803d;color:white;border:none;">
                                        Tanya Admin
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Controls —— auto-scroll ke atas katalog saat ganti halaman -->
                <div class="lp-pagination-wrap">

                    {{-- Info counter di atas --}}
                    <p class="lp-pag-counter">
                        Menampilkan koleksi
                        <strong x-text="(page-1)*perPage + 1"></strong> –
                        <strong x-text="Math.min(page*perPage, total)"></strong>
                        dari <strong x-text="total"></strong>
                    </p>

                    {{-- Dots indicator halaman --}}
                    <div class="lp-pag-dots">
                        <template x-for="p in Math.ceil(total / perPage)" :key="p">
                            <button
                                @click="
                                    page = p;
                                    halamanKatalog = p;
                                    document.getElementById('katalog').scrollIntoView({ behavior: 'smooth', block: 'start' });
                                "
                                :class="page === p ? 'lp-pag-dot active' : 'lp-pag-dot'"
                                :title="'Halaman ' + p"
                                x-text="p">
                            </button>
                        </template>
                    </div>

                    {{-- Tombol Prev / Next --}}
                    <div class="lp-pag-navrow">
                        <button
                            @click="
                                if(page > 1) {
                                    page--;
                                    halamanKatalog = page;
                                    document.getElementById('katalog').scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            "
                            :disabled="page === 1"
                            :class="page === 1 ? 'lp-pag-nav disabled' : 'lp-pag-nav'"
                            class="lp-pag-nav">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Sebelumnya
                        </button>

                        <div class="lp-pag-current">
                            <span x-text="page"></span>
                            <span style="opacity:0.4;margin:0 0.25rem;">/</span>
                            <span x-text="Math.ceil(total/perPage)"></span>
                        </div>

                        <button
                            @click="
                                if(page < Math.ceil(total/perPage)) {
                                    page++;
                                    halamanKatalog = page;
                                    document.getElementById('katalog').scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            "
                            :disabled="page === Math.ceil(total/perPage)"
                            :class="page === Math.ceil(total/perPage) ? 'lp-pag-nav disabled' : 'lp-pag-nav'"
                            class="lp-pag-nav">
                            Selanjutnya
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </section>


    <!-- ===================== MODAL SEWA MULTI-STEP ===================== -->
    <div x-show="modalSewa" x-cloak class="modal-overlay">
        <div @click.away="modalSewa = false" class="modal-box">

            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-header-left">
                    <h3>Formulir Pemesanan Sewa</h3>
                    <p x-text="modalStep === 1 ? 'Step 1: Isi data & kustomisasi kebaya' : 'Step 2: Konfirmasi & pembayaran'"></p>
                </div>
                <div class="modal-header-right">
                    <div class="modal-steps">
                        <div :class="modalStep >= 1 ? 'active' : 'inactive'" class="modal-step-dot">1</div>
                        <div :class="modalStep >= 2 ? 'active' : 'inactive'" class="modal-step-line"></div>
                        <div :class="modalStep >= 2 ? 'active' : 'inactive'" class="modal-step-dot">2</div>
                    </div>
                    <button @click="modalSewa = false" class="modal-close-btn">✕</button>
                </div>
            </div>

            <!-- HIDDEN FORM -->
            <form id="form-sewa" action="{{ route('transaksi.store') }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="nama_kebaya"          x-bind:value="selectedModel">
                <input type="hidden" name="warna_custom_ai"      x-bind:value="hasRecolored ? selectedColor : 'Original'">
                <input type="hidden" name="gambar_ai_result"     x-bind:value="selectedImage">
                <input type="hidden" name="nama_user"            x-bind:value="namaUser">
                <input type="hidden" name="no_wa"                x-bind:value="noWa">
                <input type="hidden" name="ukuran"               x-bind:value="ukuran">
                <input type="hidden" name="alamat"               x-bind:value="alamat">
                <input type="hidden" name="tanggal_sewa"         x-bind:value="tanggalSewa">
                <input type="hidden" name="tanggal_pengembalian" x-bind:value="tanggalKembali">
                <input type="hidden" name="durasi_hari"          x-bind:value="durasiHari">
                <input type="hidden" name="metode_pembayaran"    x-bind:value="metodeBayar === 'qris' ? 'QRIS Ms Harmoni' : (metodeBayar === 'bank' ? 'Transfer SeaBank' : 'Cash / Bayar di Studio')">
                <input type="hidden" name="skema_pembayaran"     x-bind:value="labelSkema">
                <input type="hidden" name="total_harga"          x-bind:value="totalHarga">
                <input type="hidden" name="sudah_bayar" id="input-sudah-bayar" value="0">

            </form>

            <!-- STEP 1: FORM DATA -->
            <div x-show="modalStep === 1" class="modal-body">
                <div class="modal-grid">

                    <!-- Kiri: Preview -->
                    <div>
                        <div class="modal-preview-box">
                            <div class="modal-preview-info">
                                <span class="modal-preview-label">Model Dipilih</span>
                                <div class="modal-preview-name" x-text="selectedModel"></div>
                                <div class="modal-preview-price">Rp <span x-text="hargaPerHari.toLocaleString('id-ID')"></span> / hari</div>
                            </div>

                            <!-- Toggle Tampak Depan / Belakang di Modal -->
                            <div x-show="fotoBelakangSrc && fotoBelakangSrc !== fotoDepanSrc" class="modal-view-switcher">
                                <button type="button"
                                        @click="switchModalView('depan')"
                                        :class="activeView === 'depan' ? 'active' : ''"
                                        class="modal-view-tab">
                                    Tampak Depan
                                </button>
                                <button type="button"
                                        @click="switchModalView('belakang')"
                                        :class="activeView === 'belakang' ? 'active' : ''"
                                        class="modal-view-tab">
                                    Tampak Belakang
                                </button>
                            </div>

                            <div class="modal-preview-img-wrap">
                                <img :src="selectedImage" :alt="selectedModel"
                                     class="modal-preview-img"
                                     :style="isGenerating ? 'transform:scale(1.05);filter:blur(4px);opacity:0.5' : ''">
                                <div x-show="isGenerating" x-cloak
                                     style="position:absolute;inset:0;background:rgba(26,10,12,0.65);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.75rem;">
                                    <div class="spin" style="width:28px;height:28px;border:3px solid var(--gold);border-top-color:transparent;border-radius:50%;"></div>
                                    <span style="font-size:0.6rem;font-weight:700;letter-spacing:0.12em;color:var(--gold-light);text-transform:uppercase;" x-text="recolorProgress < 100 ? 'Rendering '+recolorProgress+'%' : 'Selesai!'"></span>
                                </div>
                            </div>

                            <!-- Color Badge -->
                            <div class="modal-ai-badge">
                                <span>🤖</span>
                                <span x-text="isGenerating ? 'Menerapkan warna...' : (hasRecolored ? '✨ ' + selectedColor : 'AI siap dikustomisasi')"></span>
                            </div>

                            <!-- Detail Spesifikasi Kain di Modal -->
                            <div class="modal-fabric-card">
                                <div class="modal-fabric-card-title">🧵 Detail &amp; Spesifikasi Busana</div>
                                <div class="modal-fabric-list">
                                    <div class="m-fab-row">
                                        <span class="m-fab-label">Bahan Utama:</span>
                                        <span class="m-fab-value" x-text="kainUtama"></span>
                                    </div>
                                    <div class="m-fab-row">
                                        <span class="m-fab-label">Furing / Lapisan:</span>
                                        <span class="m-fab-value" x-text="furing"></span>
                                    </div>
                                    <div class="m-fab-row">
                                        <span class="m-fab-label">Tekstur &amp; Karakter:</span>
                                        <span class="m-fab-value" x-text="karakteristikKain"></span>
                                    </div>
                                    <div class="m-fab-row">
                                        <span class="m-fab-label">Payet &amp; Hiasan:</span>
                                        <span class="m-fab-value" x-text="detailPayet"></span>
                                    </div>
                                    <div class="m-fab-row">
                                        <span class="m-fab-label">Rekomendasi Acara:</span>
                                        <span class="m-fab-value" x-text="rekomendasiAcara"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Form -->
                    <div>
                        <!-- AI Custom Warna -->
                        <div class="ai-prompt-box">
                            <p class="form-section-title">🎨 Custom Warna Kebaya via AI</p>

                            <!-- Input Prompt -->
                            <div class="ai-prompt-wrap">
                                <input type="text" x-model="aiPrompt"
                                       @keyup.enter="processAIGenerate()"
                                       placeholder="Ketik warna bebas: Sage Green pastel, Emerald tua, Navy Blue, Terracotta..."
                                       class="ai-prompt-input"
                                       :disabled="isGenerating">
                                <button type="button" @click="processAIGenerate()"
                                        :disabled="isGenerating || !aiPrompt.trim()"
                                        class="ai-generate-btn">
                                    <span x-show="!isGenerating">✨ Terapkan Warna</span>
                                    <span x-show="isGenerating" style="display:flex;align-items:center;gap:0.4rem;">
                                        <span class="spin" style="width:12px;height:12px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;display:inline-block;"></span>
                                        Rendering...
                                    </span>
                                </button>
                            </div>

                            <!-- Progress Bar -->
                            <div x-show="isGenerating || recolorProgress > 0" x-cloak
                                 style="margin-top:0.5rem;">
                                <div style="background:var(--bg-ivory);border-radius:99px;height:5px;overflow:hidden;">
                                    <div :style="'width:'+recolorProgress+'%;background:linear-gradient(90deg,var(--gold-muted),var(--gold-light));height:100%;border-radius:99px;transition:width 0.25s ease;'"></div>
                                </div>
                                <p style="font-size:0.58rem;color:var(--text-muted);margin-top:0.3rem;" x-text="recolorProgress < 100 ? 'Memproses... '+recolorProgress+'%' : 'Selesai! Warna berhasil diterapkan ✨'"></p>
                            </div>

                            <!-- Error Message -->
                            <div x-show="recolorError" x-cloak
                                 style="background:rgba(220,53,69,0.08);border:1px solid rgba(220,53,69,0.2);border-radius:var(--radius-sm);padding:0.5rem 0.75rem;margin-top:0.5rem;font-size:0.65rem;color:#c0392b;">
                                ⚠️ <span x-text="recolorError"></span>
                            </div>

                            <!-- Reset to Original -->
                            <div x-show="hasRecolored" x-cloak style="margin-top:0.5rem;">
                                <button type="button" @click="resetToOriginal()"
                                        style="font-size:0.62rem;color:var(--text-muted);text-decoration:underline;background:none;border:none;cursor:pointer;padding:0;">
                                    ↺ Reset ke foto asli
                                </button>
                                <span style="font-size:0.62rem;color:var(--text-muted);margin-left:0.5rem;">|
                                    <span style="color:var(--gold-dark);font-weight:600;" x-text="selectedColor"></span>
                                </span>
                            </div>

                            <!-- Info Text -->
                            <p style="font-size:0.58rem;color:var(--text-light);margin-top:0.4rem;line-height:1.5;">
                                Ketik warna apa saja: <em>"Sage Green pastel"</em>, <em>"Emerald tua"</em>, <em>"Navy Blue"</em>, <em>"Dusty Rose"</em>, <em>"Terracotta"</em>, dsb.
                                Warna diterapkan secara instan tanpa mengganggu foto asli.
                            </p>
                        </div>

                        <!-- Data Penyewa -->
                        <p class="form-section-title">👤 Data Penyewa</p>
                        <div class="form-2-col">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap Penyewa *</label>
                                <input type="text" x-model="namaUser" required placeholder="Siti Rahma" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">No. WhatsApp Aktif *</label>
                                <input type="text" x-model="noWa" required placeholder="08123456789" class="form-input">
                            </div>
                        </div>

                        <!-- Ukuran Baju — Size Chart dengan LD -->
                        <div class="form-group" style="margin-top:0.25rem;">
                            <p class="form-section-title" style="margin-bottom:0.5rem;">📐 Ukuran Baju (Panduan LD)</p>
                            <div class="size-chart-grid">
                                <label class="size-chip" :class="ukuran === 'XS' ? 'selected' : ''">
                                    <input type="radio" x-model="ukuran" value="XS" style="display:none;">
                                    <span class="size-chip-code">XS</span>
                                    <span class="size-chip-ld">LD: 82 cm</span>
                                </label>
                                <label class="size-chip" :class="ukuran === 'S' ? 'selected' : ''">
                                    <input type="radio" x-model="ukuran" value="S" style="display:none;">
                                    <span class="size-chip-code">S</span>
                                    <span class="size-chip-ld">LD: 88 cm</span>
                                </label>
                                <label class="size-chip" :class="ukuran === 'M' ? 'selected' : ''">
                                    <input type="radio" x-model="ukuran" value="M" style="display:none;">
                                    <span class="size-chip-code">M</span>
                                    <span class="size-chip-ld">LD: 94 cm</span>
                                </label>
                                <label class="size-chip" :class="ukuran === 'L' ? 'selected' : ''">
                                    <input type="radio" x-model="ukuran" value="L" style="display:none;">
                                    <span class="size-chip-code">L</span>
                                    <span class="size-chip-ld">LD: 100 cm</span>
                                </label>
                                <label class="size-chip" :class="ukuran === 'XL' ? 'selected' : ''">
                                    <input type="radio" x-model="ukuran" value="XL" style="display:none;">
                                    <span class="size-chip-code">XL</span>
                                    <span class="size-chip-ld">LD: 106 cm</span>
                                </label>
                                <label class="size-chip" :class="ukuran === 'XXL' ? 'selected' : ''">
                                    <input type="radio" x-model="ukuran" value="XXL" style="display:none;">
                                    <span class="size-chip-code">XXL</span>
                                    <span class="size-chip-ld">LD: 112 cm</span>
                                </label>
                            </div>
                            <p style="font-size:0.6rem;color:var(--text-muted);margin-top:0.4rem;">* LD = Lingkar Dada. Ukuran dipilih: <strong x-text="ukuran"></strong></p>
                        </div>

                        <!-- Alamat -->
                        <div class="form-group">
                            <label class="form-label">Alamat Lengkap (opsional)</label>
                            <input type="text" x-model="alamat" placeholder="Alamat pengiriman / pengambilan..." class="form-input">
                        </div>

                        <!-- Tanggal Sewa & Kalender Real-Time Interaktif -->
                        <div style="margin-top:1rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
                                <p class="form-section-title" style="margin:0;">📅 Kalender Real-Time Jadwal Sewa</p>
                                <span style="font-size:0.6rem;background:rgba(220,53,69,0.1);color:#c0392b;padding:0.2rem 0.5rem;border-radius:20px;font-weight:700;display:inline-flex;align-items:center;gap:0.25rem;">
                                    <span style="width:6px;height:6px;background:#e74c3c;border-radius:50%;display:inline-block;animation:pulse 1.5s infinite;"></span>
                                    Auto-Sync Real-Time
                                </span>
                            </div>

                            <p style="font-size:0.62rem;color:var(--text-muted);margin-bottom:0.65rem;">
                                Klik tanggal pada kalender di bawah untuk memilih jadwal sewa. Tanggal bertanda <strong style="color:#c0392b;">🔒 Terkunci</strong> telah disewa pelanggan lain dan otomatis tidak dapat dipilih.
                            </p>

                            <!-- Visual Calendar Box -->
                            <div style="background:#ffffff;border:1px solid var(--border-gold);border-radius:var(--radius-md);padding:0.85rem;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                                
                                <!-- Header Month Controls -->
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.65rem;padding-bottom:0.5rem;border-bottom:1px solid #f0f0f0;">
                                    <button type="button" @click="prevMonth()" style="background:var(--bg-ivory);border:1px solid var(--border-gold);border-radius:6px;padding:0.25rem 0.6rem;font-size:0.75rem;cursor:pointer;font-weight:700;color:var(--maroon);">
                                        ◄ Prev
                                    </button>
                                    <div style="text-align:center;">
                                        <span style="font-family:var(--font-heading);font-weight:700;font-size:0.95rem;color:var(--maroon);" x-text="calMonthName + ' ' + calYear"></span>
                                        <div x-show="isLoadingBookedDates" style="font-size:0.55rem;color:var(--text-muted);display:flex;align-items:center;gap:0.25rem;justify-content:center;margin-top:2px;">
                                            <span class="spin" style="width:10px;height:10px;border:2px solid var(--gold);border-top-color:transparent;border-radius:50%;display:inline-block;"></span>
                                            Memuat status real-time...
                                        </div>
                                    </div>
                                    <button type="button" @click="nextMonth()" style="background:var(--bg-ivory);border:1px solid var(--border-gold);border-radius:6px;padding:0.25rem 0.6rem;font-size:0.75rem;cursor:pointer;font-weight:700;color:var(--maroon);">
                                        Next ►
                                    </button>
                                </div>

                                <!-- Legend Bar -->
                                <div style="display:flex;gap:0.75rem;justify-content:center;align-items:center;margin-bottom:0.65rem;font-size:0.6rem;color:var(--text-dark);flex-wrap:wrap;background:var(--bg-ivory);padding:0.35rem;border-radius:6px;">
                                    <div style="display:flex;align-items:center;gap:0.25rem;">
                                        <span style="width:12px;height:12px;background:#e8f5e9;border:1px solid #81c784;border-radius:3px;"></span>
                                        <span>🟢 Tersedia</span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:0.25rem;">
                                        <span style="width:12px;height:12px;background:#ffebee;border:1px solid #ef5350;border-radius:3px;display:flex;align-items:center;justify-content:center;font-size:8px;color:#c0392b;">🔒</span>
                                        <span>🔒 Terkunci (Tersewa)</span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:0.25rem;">
                                        <span style="width:12px;height:12px;background:var(--maroon);border-radius:3px;"></span>
                                        <span>✨ Pilihan Anda</span>
                                    </div>
                                </div>

                                <!-- Calendar Days Header -->
                                <div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:2px;text-align:center;font-size:0.6rem;font-weight:700;color:var(--text-muted);margin-bottom:4px;">
                                    <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                                </div>

                                <!-- Calendar Grid -->
                                <div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:3px;">
                                    <template x-for="(day, index) in calendarDays" :key="index">
                                        <div>
                                            <!-- Blank padding cell -->
                                            <template x-if="day.isPadding">
                                                <div style="height:34px;background:transparent;"></div>
                                            </template>

                                            <!-- Real Day Cell -->
                                            <template x-if="!day.isPadding">
                                                <button type="button"
                                                        @click="selectCalendarDate(day.dateStr)"
                                                        :disabled="day.isBooked || day.isPast"
                                                        :title="day.isBooked ? 'TERKUNCI: Kebaya sudah disewa pelanggan lain' : (day.isPast ? 'Tanggal sudah lewat' : 'Pilih ' + day.dateStr)"
                                                        :style="
                                                            day.isBooked
                                                                ? 'background:#ffebee;border:1px solid #ef5350;color:#c0392b;cursor:not-allowed;opacity:0.85;text-decoration:line-through;'
                                                                : (day.isStart || day.isEnd
                                                                    ? 'background:var(--maroon);color:var(--gold-light);font-weight:800;border:1px solid var(--gold);box-shadow:0 2px 4px rgba(0,0,0,0.2);'
                                                                    : (day.isInRange
                                                                        ? 'background:var(--gold-pale);color:var(--maroon);font-weight:700;border:1px solid var(--border-gold);'
                                                                        : (day.isPast
                                                                            ? 'background:#f5f5f5;color:#bbb;cursor:not-allowed;border:1px solid #eee;'
                                                                            : 'background:#f1f8e9;border:1px solid #c8e6c9;color:#2e7d32;cursor:pointer;'
                                                                          )
                                                                      )
                                                                  )
                                                        "
                                                        style="width:100%;height:34px;border-radius:6px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:0.7rem;font-weight:600;transition:all 0.15s ease;position:relative;padding:0;">
                                                    <span x-text="day.dayNum"></span>
                                                    <template x-if="day.isBooked">
                                                        <span style="font-size:7px;line-height:1;margin-top:-2px;">🔒</span>
                                                    </template>
                                                    <template x-if="day.isStart">
                                                        <span style="font-size:6px;line-height:1;margin-top:-2px;color:var(--gold-light);">START</span>
                                                    </template>
                                                    <template x-if="day.isEnd">
                                                        <span style="font-size:6px;line-height:1;margin-top:-2px;color:var(--gold-light);">END</span>
                                                    </template>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Form Input Manual Sync -->
                            <div class="form-2-col" style="margin-top:0.75rem;">
                                <div class="form-group">
                                    <label class="form-label">Tanggal Sewa (Pengambilan) *</label>
                                    <input type="date" x-model="tanggalSewa" @change="hitungDurasi(); checkDateOverlap();"
                                           :min="minDate()" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Tanggal Pengembalian *</label>
                                    <input type="date" x-model="tanggalKembali" @change="hitungDurasi(); checkDateOverlap();"
                                           :min="tanggalSewa || minDate()" class="form-input">
                                </div>
                            </div>

                            <!-- Error Box Peringatan Tanggal Bentrok Real-Time -->
                            <div x-show="dateOverlapError" x-cloak
                                 style="background:#fff0f0;border:1.5px solid #e74c3c;border-radius:var(--radius-sm);padding:0.75rem 1rem;margin-top:0.65rem;font-size:0.68rem;color:#c0392b;line-height:1.5;box-shadow:0 2px 6px rgba(231,76,60,0.15);"
                                 class="slide-up">
                                <div style="font-weight:800;font-size:0.75rem;margin-bottom:0.2rem;display:flex;align-items:center;gap:0.35rem;">
                                    <span>🚫 JADWAL SEWA TERKUNCI</span>
                                </div>
                                <span x-text="dateOverlapError"></span>
                            </div>
                        </div>

                        <!-- Durasi & Total -->
                        <div style="background:var(--gold-pale);border:1px solid var(--border-gold);border-radius:var(--radius-sm);padding:0.85rem 1rem;display:flex;align-items:center;justify-content:space-between;margin-top:0.5rem;flex-wrap:wrap;gap:0.5rem;">
                            <div>
                                <span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--text-muted);">Durasi Sewa</span>
                                <p style="font-family:var(--font-heading);font-size:1.1rem;font-weight:700;color:var(--maroon);">
                                    <span x-text="durasiHari"></span> Hari
                                </p>
                            </div>
                            <div style="text-align:right;">
                                <span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--text-muted);">Total Harga</span>
                                <p style="font-family:var(--font-heading);font-size:1.35rem;font-weight:800;color:var(--maroon);">
                                    Rp <span x-text="totalHarga.toLocaleString('id-ID')"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Skema Pembayaran — Step 1 -->
                        <div style="margin-top:1rem;">
                            <p class="form-section-title">💰 Skema Pembayaran</p>
                            <div class="skema-grid">
                                <button type="button" @click="skemaBayar='dp50'" :class="skemaBayar==='dp50'?'active':''" class="skema-btn">
                                    <div style="font-weight:700;">DP 50%</div>
                                    <div style="font-size:0.6rem;opacity:0.75;" x-text="'Rp ' + Math.ceil(totalHarga*0.5).toLocaleString('id-ID')"></div>
                                </button>
                                <button type="button" @click="skemaBayar='dp70'" :class="skemaBayar==='dp70'?'active':''" class="skema-btn">
                                    <div style="font-weight:700;">DP 70%</div>
                                    <div style="font-size:0.6rem;opacity:0.75;" x-text="'Rp ' + Math.ceil(totalHarga*0.7).toLocaleString('id-ID')"></div>
                                </button>
                                <button type="button" @click="skemaBayar='lunas'" :class="skemaBayar==='lunas'?'active':''" class="skema-btn">
                                    <div style="font-weight:700;">Lunas 100%</div>
                                    <div style="font-size:0.6rem;opacity:0.75;" x-text="'Rp ' + totalHarga.toLocaleString('id-ID')"></div>
                                </button>
                            </div>
                        </div>

                        <!-- Metode Pembayaran — Step 1 -->
                        <div style="margin-top:0.75rem;">
                            <p class="form-section-title">🏦 Metode Pembayaran</p>
                            <div class="payment-cards-grid">
                                <button type="button" @click="metodeBayar = 'qris'"
                                        :class="metodeBayar === 'qris' ? 'selected' : ''"
                                        class="payment-card">
                                    <div class="payment-card-icon">📱</div>
                                    <div class="payment-card-name">Scan QRIS</div>
                                    <div class="payment-card-sub">Bayar via QR Code</div>
                                </button>
                                <button type="button" @click="metodeBayar = 'bank'"
                                        :class="metodeBayar === 'bank' ? 'selected' : ''"
                                        class="payment-card">
                                    <div class="payment-card-icon">🏦</div>
                                    <div class="payment-card-name">Transfer Bank</div>
                                    <div class="payment-card-sub">SeaBank Indonesia</div>
                                </button>
                                <button type="button" @click="metodeBayar = 'cash'"
                                        :class="metodeBayar === 'cash' ? 'selected' : ''"
                                        class="payment-card">
                                    <div class="payment-card-icon">💵</div>
                                    <div class="payment-card-name">Cash / Tunai</div>
                                    <div class="payment-card-sub">Bayar di Studio</div>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Lanjut -->
                        <div style="margin-top:1.25rem;">
                            <button type="button" @click="lanjutBayar()"
                                    :disabled="isLoading || !!dateOverlapError || !tanggalSewa || !tanggalKembali"
                                    :style="dateOverlapError ? 'opacity:0.6;cursor:not-allowed;background:#888;' : ''"
                                    class="btn btn-maroon" style="width:100%;justify-content:center;padding:0.9rem;">
                                <span x-show="!isLoading && !dateOverlapError">Lanjut ke Konfirmasi Pembayaran →</span>
                                <span x-show="!isLoading && !!dateOverlapError">🚫 Tanggal Terkunci (Pilih Tanggal Lain)</span>
                                <span x-show="isLoading" class="spin" style="width:16px;height:16px;border:2px solid var(--gold-light);border-top-color:transparent;border-radius:50%;display:inline-block;"></span>
                                <span x-show="isLoading">Memproses...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: KONFIRMASI & INSTRUKSI PEMBAYARAN DINAMIS -->
            <div x-show="modalStep === 2" class="modal-body slide-up">
                <!-- Summary Order -->
                <div style="background:var(--bg-ivory);border:1px solid var(--border-gold);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.25rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;">
                        <div>
                            <span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--text-muted);">Pesanan</span>
                            <p style="font-family:var(--font-heading);font-weight:700;font-size:1rem;color:var(--text-dark);" x-text="selectedModel"></p>
                            <p style="font-size:0.72rem;color:var(--text-muted);" x-text="namaUser + ' • ' + durasiHari + ' Hari • Ukuran ' + ukuran"></p>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--text-muted);">Dibayar (<span x-text="labelSkema"></span>)</span>
                            <p style="font-family:var(--font-heading);font-size:1.4rem;font-weight:800;color:var(--maroon);">
                                Rp <span x-text="nominalBayar.toLocaleString('id-ID')"></span>
                            </p>
                        </div>
                    </div>
                    <!-- Summary skema & metode yang dipilih -->
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <span style="font-size:0.6rem;background:var(--maroon);color:var(--gold-light);padding:0.2rem 0.65rem;border-radius:20px;font-weight:700;" x-text="labelSkema"></span>
                        <span style="font-size:0.6rem;background:var(--gold-pale);color:var(--maroon);border:1px solid var(--border-gold);padding:0.2rem 0.65rem;border-radius:20px;font-weight:700;"
                              x-text="metodeBayar==='qris' ? '📱 Scan QRIS' : (metodeBayar==='bank' ? '🏦 Transfer Bank' : '💵 Cash di Studio')"></span>
                    </div>
                </div>

                <!-- ═══ QRIS DINAMIS MIDTRANS — NOMINAL TERKUNCI OTOMATIS ═══ -->
                <div x-show="metodeBayar === 'qris'" class="slide-up">
                    <div style="background:var(--bg-warm-white);border:1px solid var(--border-gold);border-radius:var(--radius-md);overflow:hidden;margin-bottom:0.75rem;">
                        <div class="qris-header">
                            <div class="qris-header-text">
                                <p>🔒 QRIS Dinamis Midtrans (Nominal Terkunci Otomatis)</p>
                                <p>Tanpa ketik angka manual — Nominal terisi otomatis saat di-scan</p>
                            </div>
                            <span class="qris-label">OTOMATIS</span>
                        </div>

                        <div style="padding:1.5rem;display:flex;flex-direction:column;align-items:center;gap:1.25rem;">

                            {{-- Badge Fitur Otomatis --}}
                            <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:var(--radius-md);padding:1rem;text-align:center;width:100%;">
                                <span style="font-size:1.5rem;display:block;margin-bottom:0.3rem;">✨ 🔒</span>
                                <p style="font-size:0.85rem;font-weight:700;color:#92400e;">QRIS Dinamis Resmi Midtrans</p>
                                <p style="font-size:0.72rem;color:#78350f;margin-top:0.25rem;">
                                    Nominal <strong>Rp <span x-text="nominalBayar.toLocaleString('id-ID')"></span></strong> akan <strong>otomatis terkunci & terisi sendiri</strong> di aplikasi Anda. Anda <u>tidak perlu mengetik nominal manual</u>.
                                </p>
                            </div>

                            {{-- Metode yang didukung --}}
                            <div style="text-align:center;">
                                <p style="font-size:0.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;">Bisa Di-scan Menggunakan:</p>
                                <div style="display:flex;flex-wrap:wrap;gap:0.4rem;justify-content:center;">
                                    @foreach(['GoPay','DANA','OVO','ShopeePay','BCA Mobile','Livin Mandiri','BRImo','LinkAja'] as $app)
                                    <span style="font-size:0.6rem;background:white;border:1px solid var(--border-gold);padding:0.25rem 0.6rem;border-radius:12px;font-weight:600;color:var(--text-dark);">
                                        ✓ {{ $app }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Box Nominal --}}
                            <div style="background:var(--gold-pale);border:1px solid var(--border-gold);border-radius:var(--radius-sm);padding:0.85rem 1.5rem;text-align:center;width:100%;">
                                <div style="font-size:0.6rem;font-weight:700;text-transform:uppercase;color:var(--gold-dark);margin-bottom:0.25rem;">Total Nominal Pembayaran (<span x-text="labelSkema"></span>)</div>
                                <div style="font-family:var(--font-heading);font-size:1.6rem;font-weight:800;color:var(--maroon);"
                                     x-text="'Rp ' + nominalBayar.toLocaleString('id-ID')"></div>
                                <div style="font-size:0.62rem;color:var(--gold-dark);margin-top:0.2rem;">🔒 Terkunci otomatis oleh Midtrans API</div>
                            </div>

                        </div>

                        <div style="padding:0 1.25rem 1.25rem;">
                            <div class="confirm-grid">
                                <button type="button" @click="konfirmasiDanSubmit(true)" class="confirm-yes" style="font-size:0.85rem;padding:1rem;">
                                    🚀 Lanjut ke QRIS Dinamis & Bayar (Rp <span x-text="nominalBayar.toLocaleString('id-ID')"></span>)
                                </button>
                            </div>
                            <p style="font-size:0.6rem;color:var(--text-light);text-align:center;margin-top:0.4rem;">*Klik tombol di atas untuk membuka Kode QRIS Dinamis & Gateway Midtrans.</p>
                            <div style="margin-top:0.75rem;display:flex;gap:0.5rem;">
                                <a href="https://wa.me/6281949862260" target="_blank"
                                   style="flex:1;text-align:center;font-size:0.65rem;padding:0.5rem;background:rgba(21,128,61,0.08);border:1px solid rgba(21,128,61,0.25);border-radius:var(--radius-sm);color:#15803d;font-weight:600;text-decoration:none;display:block;">
                                    💬 Hubungi Admin WA — Bantuan Pembayaran
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ TRANSFER BANK — Info Rekening Saja ═══ -->
                <div x-show="metodeBayar === 'bank'" class="slide-up">
                    <div style="background:var(--bg-warm-white);border:1px solid var(--border-gold);border-radius:var(--radius-md);overflow:hidden;margin-bottom:0.75rem;">
                        <div style="background:#2C1F15;padding:0.85rem 1.25rem;">
                            <p style="font-weight:700;font-size:0.85rem;color:#E8C98A;">Detail Rekening Transfer</p>
                            <p style="font-size:0.65rem;color:rgba(250,246,240,0.65);">Transfer <span x-text="labelSkema"></span> ke rekening berikut</p>
                        </div>
                        <div style="padding:1.5rem;">
                            <div style="background:var(--bg-ivory);border:1px solid var(--border-gold);border-radius:var(--radius-md);padding:1.25rem;text-align:center;margin-bottom:1rem;">
                                <p style="font-size:0.6rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.35rem;">Nama Bank</p>
                                <p style="font-weight:700;font-size:1.25rem;color:var(--text-dark);margin-bottom:1rem;">🏦 SeaBank Indonesia</p>

                                <p style="font-size:0.6rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.25rem;">Nomor Rekening</p>
                                <div style="background:white;border:1.5px solid var(--border-gold);border-radius:var(--radius-sm);padding:0.75rem 1.5rem;display:inline-block;margin-bottom:0.75rem;">
                                    <p style="font-family:monospace;font-weight:700;font-size:1.75rem;color:var(--maroon);letter-spacing:0.12em;">9015 8274 1928</p>
                                </div>

                                <p style="font-size:0.6rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.2rem;">Nama Pemilik Rekening</p>
                                <p style="font-weight:700;font-size:1rem;color:var(--text-dark);">Novan Helmi</p>
                            </div>

                            <div style="background:var(--gold-pale);border:1px solid var(--border-gold);border-radius:var(--radius-sm);padding:0.85rem;text-align:center;margin-bottom:1rem;">
                                <div style="font-size:0.6rem;font-weight:700;text-transform:uppercase;color:var(--gold-dark);margin-bottom:0.2rem;">Total yang Harus Ditransfer</div>
                                <div style="font-family:var(--font-heading);font-size:1.5rem;font-weight:800;color:var(--maroon);"
                                     x-text="'Rp ' + nominalBayar.toLocaleString('id-ID')"></div>
                                <div style="font-size:0.6rem;color:var(--text-muted);margin-top:0.2rem;">Berita transfer: <strong x-text="'SEWA-' + selectedModel.substring(0,10).toUpperCase()"></strong></div>
                            </div>

                            <div style="background:rgba(200,169,110,0.06);border:1px solid var(--border-gold);border-radius:var(--radius-sm);padding:0.6rem;font-size:0.72rem;color:var(--text-medium);margin-bottom:0.75rem;">
                                📸 Setelah transfer, kirim screenshot bukti ke WhatsApp admin: <strong>+62 819-4986-2260</strong>
                            </div>

                            <div class="confirm-grid">
                                <button type="button" @click="konfirmasiDanSubmit(true)" class="confirm-yes" style="background:var(--maroon);border-color:var(--maroon);">✅ Sudah Transfer — Konfirmasi</button>
                                <button type="button" @click="konfirmasiDanSubmit(false)" class="confirm-no">⏳ Bayar Nanti</button>
                            </div>
                            <div style="margin-top:0.75rem;display:flex;gap:0.5rem;">
                                <a href="https://wa.me/6281949862260" target="_blank"
                                   style="flex:1;text-align:center;font-size:0.65rem;padding:0.5rem;background:rgba(21,128,61,0.08);border:1px solid rgba(21,128,61,0.25);border-radius:var(--radius-sm);color:#15803d;font-weight:600;text-decoration:none;display:block;">
                                    💬 Hubungi Admin WA
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ CASH — Ringkasan Nota Tunai ═══ -->
                <div x-show="metodeBayar === 'cash'" class="slide-up">
                    <div style="background:var(--bg-warm-white);border:1px solid var(--border-gold);border-radius:var(--radius-md);overflow:hidden;margin-bottom:0.75rem;">
                        <div style="background:#2C1F15;padding:0.85rem 1.25rem;">
                            <p style="font-weight:700;font-size:0.85rem;color:#E8C98A;">Pembayaran Tunai / Cash di Studio</p>
                            <p style="font-size:0.65rem;color:rgba(250,246,240,0.65);">Bayar langsung saat kunjungan ke studio MS Harmony</p>
                        </div>
                        <div style="padding:1.5rem;">
                            <!-- Ringkasan Nota Cash -->
                            <div style="background:var(--bg-ivory);border:1.5px dashed rgba(212,175,55,0.4);border-radius:var(--radius-md);padding:1.25rem;margin-bottom:1rem;">
                                <div style="text-align:center;border-bottom:1px solid var(--border-gold);padding-bottom:0.75rem;margin-bottom:0.75rem;">
                                    <p style="font-family:var(--font-display);font-size:0.55rem;letter-spacing:0.25em;color:var(--gold-muted);text-transform:uppercase;">Bukti Pesanan</p>
                                    <p style="font-family:var(--font-heading);font-size:1.1rem;font-weight:700;color:var(--text-dark);">MS Harmony Penyewaan Kebaya</p>
                                </div>
                                <div style="display:flex;flex-direction:column;gap:0.5rem;font-size:0.78rem;">
                                    <div style="display:flex;justify-content:space-between;"><span style="color:var(--text-muted);">Penyewa</span><span style="font-weight:600;" x-text="namaUser"></span></div>
                                    <div style="display:flex;justify-content:space-between;"><span style="color:var(--text-muted);">Kebaya</span><span style="font-weight:600;" x-text="selectedModel"></span></div>
                                    <div style="display:flex;justify-content:space-between;"><span style="color:var(--text-muted);">Ukuran</span><span style="font-weight:600;" x-text="ukuran"></span></div>
                                    <div style="display:flex;justify-content:space-between;"><span style="color:var(--text-muted);">Skema Bayar</span><span style="font-weight:600;" x-text="labelSkema"></span></div>
                                    <div style="display:flex;justify-content:space-between;"><span style="color:var(--text-muted);">Total</span><span style="font-weight:700;color:var(--maroon);" x-text="'Rp ' + totalHarga.toLocaleString('id-ID')"></span></div>
                                </div>
                                <div style="margin-top:0.85rem;background:var(--gold-pale);border:1px solid var(--border-gold);border-radius:var(--radius-sm);padding:0.6rem;text-align:center;">
                                    <p style="font-size:0.72rem;font-weight:700;color:var(--maroon);">💵 Pembayaran akan dilakukan secara Tunai/Cash</p>
                                    <p style="font-size:0.65rem;color:var(--text-muted);margin-top:0.2rem;">di Studio / Butik MS Harmony</p>
                                </div>
                            </div>

                            <div style="background:rgba(212,175,55,0.06);border:1px solid var(--border-gold);border-radius:var(--radius-sm);padding:0.6rem;font-size:0.72rem;color:var(--text-medium);margin-bottom:0.75rem;">
                                📍 <strong>Jogja:</strong> Jl Buwosox No.A2, Bangunjiwo, Bantul &nbsp;|&nbsp; <strong>Purworejo:</strong> Sangubanyu, Grabag
                            </div>

                            <button type="button" @click="konfirmasiDanSubmit(false)"
                                    class="btn btn-maroon" style="width:100%;justify-content:center;padding:0.9rem;">
                                ✅ Konfirmasi Pesanan — Bayar Cash di Studio
                            </button>
                            <div style="margin-top:0.75rem;">
                                <a href="https://wa.me/6281949862260" target="_blank"
                                   style="width:100%;display:block;text-align:center;font-size:0.65rem;padding:0.5rem;background:rgba(21,128,61,0.08);border:1px solid rgba(21,128,61,0.25);border-radius:var(--radius-sm);color:#15803d;font-weight:600;text-decoration:none;">
                                    💬 Chat Admin WA untuk Konfirmasi Jadwal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kembali -->
                <div style="text-align:center;margin-top:0.5rem;">
                    <button type="button" @click="modalStep = 1"
                            style="font-size:0.72rem;color:var(--text-muted);text-decoration:underline;background:none;border:none;cursor:pointer;">
                        ← Kembali ke Data Pesanan
                    </button>
                </div>
            </div>
        </div>
    </div>
