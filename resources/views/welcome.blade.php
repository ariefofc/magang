<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MS Harmony — Sewa Kebaya Eksklusif &amp; Mewah | Royal Elegance</title>
    <meta name="description" content="Studio persewaan kebaya premium MS Harmony. Koleksi eksklusif untuk wisuda, pernikahan, dan acara formal. Kustomisasi warna via AI, fitting langsung di studio.">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/msharmony-recolor.js') }}"></script>
    <script>window._csrfToken = '{{ csrf_token() }}';</script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('appData', () => ({
                // ===== MODAL =====
                modalSewa: false,
                modalStep: 1,

                // ===== KEBAYA & VIEW =====
                selectedModel: 'Kebaya Modern Brukat Gown',
                hargaPerHari: 850000,
                fotoDepanSrc: '{{ asset('Kebaya Modern Brukat Gown.png') }}',
                fotoBelakangSrc: '{{ asset('Kebaya Modern Brukat Gown-belakang.jpg') }}',
                activeView: 'depan',
                selectedImage: '{{ asset('Kebaya Modern Brukat Gown.png') }}',
                originalImageSrc: '{{ asset('Kebaya Modern Brukat Gown.png') }}',

                // ===== DETAIL KAIN & MATERIAL =====
                kainUtama: 'Brokat Chantilly Perancis & Soft Tulle',
                furing: 'Katun Hero Super (Adem & Menyerap Keringat)',
                karakteristikKain: 'Tekstur sangat lembut, drape jatuh anggun, bertekstur empuk, tidak gatal & tidak panas.',
                detailPayet: 'Payet Kristal Swarovski sintetik & Mutiara Jepang di leher & lingkar dada.',
                rekomendasiAcara: 'Resepsi Pernikahan, Wisuda, Gala Dinner, Evening Event',

                // ===== AI =====
                isGenerating: false,
                aiPrompt: '',
                selectedColor: 'Original',
                recolorProgress: 0,
                recolorError: '',
                hasRecolored: false,

                // ===== FORM DATA & REAL-TIME CALENDAR =====
                namaUser: '',
                noWa: '',
                ukuran: 'M',
                alamat: '',
                tanggalSewa: '',
                tanggalKembali: '',
                durasiHari: 1,
                totalHarga: 850000,
                metodeBayar: 'qris',
                skemaBayar: 'dp50',
                isLoading: false,
                sudahBayar: null,

                // Real-Time Calendar Date Locking State
                bookedDates: [],
                bookedRanges: [],
                isLoadingBookedDates: false,
                dateOverlapError: '',
                calYear: new Date().getFullYear(),
                calMonth: new Date().getMonth(),

                // ===== KATALOG PAGINATION =====
                halamanKatalog: 1,
                itemPerHalaman: 6,

                // ===== METHODS =====
                async fetchBookedDates(namaKebaya) {
                    this.isLoadingBookedDates = true;
                    this.bookedDates = [];
                    this.bookedRanges = [];
                    this.dateOverlapError = '';
                    try {
                        const resp = await fetch('/api/kebaya/booked-dates?nama=' + encodeURIComponent(namaKebaya));
                        if (resp.ok) {
                            const data = await resp.json();
                            this.bookedDates = data.disabled_dates || [];
                            this.bookedRanges = data.booked_ranges || [];
                        }
                    } catch (e) {
                        console.error('Gagal mengambil jadwal booked:', e);
                    } finally {
                        this.isLoadingBookedDates = false;
                        this.checkDateOverlap();
                    }
                },

                isDateBooked(dateStr) {
                    return this.bookedDates.includes(dateStr);
                },

                checkDateOverlap() {
                    this.dateOverlapError = '';
                    if (!this.tanggalSewa) return false;

                    const startStr = this.tanggalSewa;
                    const endStr = this.tanggalKembali || this.tanggalSewa;

                    const curr = new Date(startStr);
                    const end = new Date(endStr);

                    const hitList = [];

                    while (curr <= end) {
                        const yyyy = curr.getFullYear();
                        const mm = String(curr.getMonth() + 1).padStart(2, '0');
                        const dd = String(curr.getDate()).padStart(2, '0');
                        const dStr = `${yyyy}-${mm}-${dd}`;

                        if (this.isDateBooked(dStr)) {
                            hitList.push(dStr);
                        }
                        curr.setDate(curr.getDate() + 1);
                    }

                    if (hitList.length > 0) {
                        this.dateOverlapError = '⚠️ Kebaya "' + this.selectedModel + '" sudah TERKUNCI (disewa pelanggan lain) pada tanggal: ' + hitList.join(', ') + '. Silakan pilih tanggal lain pada kalender.';
                        return true;
                    }

                    return false;
                },

                selectCalendarDate(dateStr) {
                    if (this.isDateBooked(dateStr) || dateStr < this.minDate()) return;

                    if (!this.tanggalSewa || (this.tanggalSewa && this.tanggalKembali) || dateStr < this.tanggalSewa) {
                        this.tanggalSewa = dateStr;
                        this.tanggalKembali = '';
                        this.durasiHari = 1;
                        this.totalHarga = this.hargaPerHari;
                    } else if (dateStr > this.tanggalSewa) {
                        this.tanggalKembali = dateStr;
                        this.hitungDurasi();
                    }
                    this.checkDateOverlap();
                },

                get calMonthName() {
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    return months[this.calMonth] || '';
                },

                prevMonth() {
                    if (this.calMonth === 0) {
                        this.calMonth = 11;
                        this.calYear--;
                    } else {
                        this.calMonth--;
                    }
                },

                nextMonth() {
                    if (this.calMonth === 11) {
                        this.calMonth = 0;
                        this.calYear++;
                    } else {
                        this.calMonth++;
                    }
                },

                get calendarDays() {
                    const daysInMonth = new Date(this.calYear, this.calMonth + 1, 0).getDate();
                    const firstDayIndex = new Date(this.calYear, this.calMonth, 1).getDay(); // 0 = Sun

                    const days = [];

                    for (let i = 0; i < firstDayIndex; i++) {
                        days.push({ isPadding: true });
                    }

                    const todayStr = this.minDate();

                    for (let d = 1; d <= daysInMonth; d++) {
                        const mm = String(this.calMonth + 1).padStart(2, '0');
                        const dd = String(d).padStart(2, '0');
                        const dateStr = `${this.calYear}-${mm}-${dd}`;

                        const isPast = dateStr < todayStr;
                        const isBooked = this.isDateBooked(dateStr);
                        const isStart = dateStr === this.tanggalSewa;
                        const isEnd = dateStr === this.tanggalKembali;
                        const isInRange = this.tanggalSewa && this.tanggalKembali && (dateStr > this.tanggalSewa && dateStr < this.tanggalKembali);

                        days.push({
                            isPadding: false,
                            dayNum: d,
                            dateStr: dateStr,
                            isPast: isPast,
                            isBooked: isBooked,
                            isStart: isStart,
                            isEnd: isEnd,
                            isInRange: isInRange,
                        });
                    }

                    return days;
                },

                openSewaModal(nama, harga, foto, fotoBelakang, kainUtama, furing, karakteristikKain, detailPayet, rekomendasiAcara) {
                    this.selectedModel      = nama;
                    this.hargaPerHari       = harga;
                    this.totalHarga         = harga;
                    this.fotoDepanSrc       = foto;
                    this.fotoBelakangSrc    = fotoBelakang || foto;
                    this.activeView         = 'depan';
                    this.selectedImage      = foto;
                    this.originalImageSrc   = foto;

                    this.kainUtama          = kainUtama || 'Brokat / Katun Premium';
                    this.furing             = furing || 'Katun Hero Super';
                    this.karakteristikKain  = karakteristikKain || 'Tekstur lembut, adem, & nyaman dipakai';
                    this.detailPayet        = detailPayet || 'Payet & Bordir Eksklusif';
                    this.rekomendasiAcara   = rekomendasiAcara || 'Wisuda & Resepsi Pernikahan';

                    this.aiPrompt        = '';
                    this.selectedColor   = 'Original';
                    this.recolorProgress = 0;
                    this.recolorError    = '';
                    this.hasRecolored    = false;
                    this.modalStep       = 1;

                    const now = new Date();
                    this.calYear = now.getFullYear();
                    this.calMonth = now.getMonth();
                    this.tanggalSewa     = '';
                    this.tanggalKembali  = '';
                    this.durasiHari      = 1;
                    this.dateOverlapError= '';

                    this.metodeBayar     = 'qris';
                    this.skemaBayar      = 'dp50';
                    this.isLoading       = false;
                    this.namaUser        = '';
                    this.noWa            = '';
                    this.alamat          = '';
                    this.sudahBayar      = null;
                    this.modalSewa       = true;

                    // Load real-time booked dates for this kebaya
                    this.fetchBookedDates(nama);
                },

                switchModalView(view) {
                    this.activeView = view;
                    if (!this.hasRecolored) {
                        this.selectedImage = (view === 'belakang' && this.fotoBelakangSrc) ? this.fotoBelakangSrc : this.fotoDepanSrc;
                        this.originalImageSrc = this.selectedImage;
                    }
                },

                resetToOriginal() {
                    this.selectedImage   = (this.activeView === 'belakang' && this.fotoBelakangSrc) ? this.fotoBelakangSrc : this.fotoDepanSrc;
                    this.originalImageSrc= this.selectedImage;
                    this.hasRecolored    = false;
                    this.recolorProgress = 0;
                    this.recolorError    = '';
                    this.aiPrompt        = '';
                },

                async processAIGenerate() {
                    const prompt = this.aiPrompt.trim();
                    if (!prompt) { this.recolorError = 'Masukkan deskripsi warna terlebih dahulu.'; return; }
                    if (this.isGenerating) return;

                    this.isGenerating    = true;
                    this.recolorError    = '';
                    this.recolorProgress = 0;

                    const csrfToken = window._csrfToken || '';

                    try {
                        let used = false;
                        try {
                            this.recolorProgress = 5;
                            const imageUrl = this.originalImageSrc.startsWith('http')
                                ? this.originalImageSrc
                                : window.location.origin + (this.originalImageSrc.startsWith('/') ? '' : '/') + this.originalImageSrc;

                            const flaskResp = await fetch('http://127.0.0.1:5000/recolor', {
                                method : 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body   : JSON.stringify({ image_url: imageUrl, color_prompt: prompt }),
                                signal: AbortSignal.timeout(8000),
                            });
                            if (flaskResp.ok) {
                                const json = await flaskResp.json();
                                if (json.success && json.image) {
                                    this.selectedImage = json.image; this.hasRecolored = true;
                                    this.selectedColor = prompt; this.recolorProgress = 100; used = true;
                                }
                            }
                        } catch (flaskErr) { console.warn('[MSHarmony] Flask unavailable:', flaskErr.message); }

                        if (!used) {
                            try {
                                this.recolorProgress = 15;
                                let imagePath = this.originalImageSrc;
                                if (imagePath.startsWith(window.location.origin)) imagePath = imagePath.slice(window.location.origin.length);
                                if (!imagePath.startsWith('/')) imagePath = '/' + imagePath;

                                const laravelResp = await fetch('/api/ai-recolor', {
                                    method : 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                    body   : JSON.stringify({ image_path: imagePath, color_prompt: prompt }),
                                    signal: AbortSignal.timeout(60000),
                                });
                                this.recolorProgress = 60;
                                const json = await laravelResp.json();
                                if (json.success && json.image && !json.client_side) {
                                    this.selectedImage = json.image; this.hasRecolored = true;
                                    this.selectedColor = prompt; this.recolorProgress = 100; used = true;
                                }
                            } catch (pix2pixErr) { console.warn('[MSHarmony] Laravel/Pix2Pix GAN error:', pix2pixErr.message); }
                        }

                        if (!used) {
                            const result = await MSHarmonyRecolor.recolorImage(
                                this.originalImageSrc, prompt,
                                (p) => { this.recolorProgress = Math.max(this.recolorProgress, p); }
                            );
                            this.selectedImage = result; this.hasRecolored = true; this.selectedColor = prompt;
                        }

                    } catch (err) {
                        this.recolorError = 'Gagal memproses: ' + (err.message || 'Error tidak diketahui');
                        console.error('MSHarmony Recolor Error:', err);
                    } finally {
                        this.isGenerating = false;
                    }
                },

                hitungDurasi() {
                    if (this.tanggalSewa && this.tanggalKembali) {
                        const t1 = new Date(this.tanggalSewa);
                        const t2 = new Date(this.tanggalKembali);
                        const diff = Math.ceil((t2 - t1) / (1000*60*60*24));
                        this.durasiHari = diff > 0 ? diff : 1;
                        this.totalHarga = this.hargaPerHari * this.durasiHari;
                    }
                },

                get nominalBayar() {
                    if (this.skemaBayar === 'dp50')  return Math.ceil(this.totalHarga * 0.5);
                    if (this.skemaBayar === 'dp70')  return Math.ceil(this.totalHarga * 0.7);
                    return this.totalHarga;
                },

                get labelSkema() {
                    if (this.skemaBayar === 'dp50') return 'DP 50%';
                    if (this.skemaBayar === 'dp70') return 'DP 70%';
                    return 'Pelunasan Langsung (100%)';
                },

                lanjutBayar() {
                    if (!this.namaUser.trim()) { alert('Harap isi nama penyewa!'); return; }
                    if (!this.noWa.trim())    { alert('Harap isi nomor WhatsApp!'); return; }
                    if (!this.tanggalSewa)    { alert('Harap pilih tanggal sewa!'); return; }
                    if (!this.tanggalKembali) { alert('Harap pilih tanggal pengembalian!'); return; }
                    if (new Date(this.tanggalKembali) <= new Date(this.tanggalSewa)) {
                        alert('Tanggal pengembalian harus setelah tanggal sewa!'); return;
                    }
                    if (this.checkDateOverlap() || this.dateOverlapError) {
                        alert(this.dateOverlapError || 'Jadwal yang Anda pilih sudah TERKUNCI/disewa. Silakan pilih tanggal lain.');
                        return;
                    }
                    this.sudahBayar = null;
                    this.isLoading = true;
                    setTimeout(() => { this.isLoading = false; this.modalStep = 2; }, 1500);
                },

                konfirmasiDanSubmit(statusBayar) {
                    this.sudahBayar = statusBayar;
                    document.getElementById('input-sudah-bayar').value = statusBayar ? '1' : '0';
                    setTimeout(() => { document.getElementById('form-sewa').submit(); }, 300);
                },

                submitPesanan() { document.getElementById('form-sewa').submit(); },

                get totalHalamanKatalog() { return Math.ceil(13 / this.itemPerHalaman); },

                minDate() { return new Date().toISOString().split('T')[0]; }
            }));
        });
    </script>
</head>
<body x-data="appData()">

    {{-- Navbar --}}
    @include('welcome._navbar')

    {{-- Hero Section --}}
    @include('welcome._hero')

    {{-- Section Layanan --}}
    @include('welcome._layanan')

    {{-- Section Cara Sewa --}}
    @include('welcome._cara_sewa')

    {{-- Section Katalog + Modal Sewa --}}
    @include('welcome._katalog')

    {{-- Footer --}}
    @include('welcome._footer')

    {{-- Floating Chat Widget --}}
    @include('chat.widget')

</body>
</html>