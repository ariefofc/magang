<?php

namespace App\Services;

/**
 * KebayaService
 * Mengelola status master katalog kebaya menggunakan file JSON
 * (storage/app/kebaya_status.json) karena katalog belum di-database.
 */
class KebayaService
{
    protected string $storagePath;

    /**
     * Data master katalog kebaya (hardcoded — sumber kebenaran tunggal)
     */
    public static array $masterKatalog = [
        [
            'slug'               => 'kebaya-modern-brukat-gown',
            'nama'               => 'Kebaya Modern Brukat Gown',
            'harga'              => 850000,
            'foto'               => 'Kebaya Modern Brukat Gown.png',
            'foto_belakang'      => 'Kebaya Modern Brukat Gown-belakang.jpg',
            'deskripsi'          => 'Gaun modern brokat premium dengan siluet elegan dan aksen mewah bergaya tinggi.',
            'kain_utama'         => 'Brokat Chantilly Perancis & Soft Tulle',
            'furing'             => 'Katun Hero Super (Adem & Menyerap Keringat)',
            'karakteristik_kain' => 'Tekstur sangat lembut, drape jatuh anggun, bertekstur empuk, tidak gatal & tidak panas.',
            'detail_payet'       => 'Payet Kristal Swarovski sintetik & Mutiara Jepang di leher & lingkar dada.',
            'rekomendasi_acara'  => 'Resepsi Pernikahan, Wisuda, Gala Dinner, Evening Event',
        ],
        [
            'slug'               => 'kebaya-aurora-slit-gown',
            'nama'               => 'Kebaya Aurora Slit Gown',
            'harga'              => 900000,
            'foto'               => 'Kebaya Aurora Slit Gown.png',
            'foto_belakang'      => 'Kebaya Aurora Slit Gown-belakang.jpg',
            'deskripsi'          => 'Gaun aurora eksklusif belahan modern yang anggun untuk setiap momen spesial Anda.',
            'kain_utama'         => 'Brokat Cornely High-Grade & Silk Satin Velvet',
            'furing'             => 'Full Furing Silky Cotton',
            'karakteristik_kain' => 'Tampilan mewah mengkilap elegan, elastisitas pas, belahan slit modern yang tetap sopan.',
            'detail_payet'       => 'Aplikasi renda bordir 3D dengan payet tabur berkilau di pinggang & punggung.',
            'rekomendasi_acara'  => 'Engagement / Lamaran, Event Red Carpet, Prewedding, Party',
        ],
        [
            'slug'               => 'kebaya-kartini-beludru',
            'nama'               => 'Kebaya Kartini Beludru',
            'harga'              => 750000,
            'foto'               => 'Kebaya Kartini Beludru.png',
            'foto_belakang'      => 'Kebaya Kartini Beludru-belakang.jpg',
            'deskripsi'          => 'Kebaya Kartini klasik berbahan beludru premium bernuansa royal penuh wibawa.',
            'kain_utama'         => 'Beludru (Velvet) Silk Stretch Royal',
            'furing'             => 'Lapisan Tricot Halus (Menjaga bentuk presisi)',
            'karakteristik_kain' => 'Permukaan beludru lembut berkilau mewah, cukup fleksibel di badan, hangat & nyaman.',
            'detail_payet'       => 'Bordir benang emas keraton di sepanjang kelim depan, lengan, dan kerah Kartini.',
            'rekomendasi_acara'  => 'Acara Adat Jawa, Midodareni, Wisuda, Resepsi Keraton',
        ],
        [
            'slug'               => 'kebaya-kutubaru-klasik-batik',
            'nama'               => 'Kebaya Kutubaru Klasik Batik',
            'harga'              => 650000,
            'foto'               => 'Kebaya Kutubaru Klasik Batik.png',
            'foto_belakang'      => 'Kebaya Kutubaru Klasik Batik-belakang.jpg',
            'deskripsi'          => 'Kebaya kutubaru tradisional dipadu bawahan kain batik otentik khas Jawa Tengah.',
            'kain_utama'         => 'Katun Jacquard Emboss (Atasan) & Batik Tulis Solo (Bawahan)',
            'furing'             => 'Katun Prima Adem 100% Cotton',
            'karakteristik_kain' => 'Sangat sejuk, ringan, mudah bergerak, serat kain katun bernafas baik.',
            'detail_payet'       => 'Kutubaru klasik dengan kancing bungkus rapi & bros etnik benang emas.',
            'rekomendasi_acara'  => 'Hari Kartini, Wisuda, Upacara Adat, Acara Formal Kebudayaan',
        ],
        [
            'slug'               => 'kebaya-encim-peranakan',
            'nama'               => 'Kebaya Encim Peranakan',
            'harga'              => 600000,
            'foto'               => 'Kebaya Encim Peranakan.png',
            'foto_belakang'      => 'Kebaya Encim Peranakan-belakang.jpg',
            'deskripsi'          => 'Kebaya encim klasik berhias bordir halus nan cantik khas budaya Peranakan.',
            'kain_utama'         => 'Katun Voile Silky Premium',
            'furing'             => 'Unlined / Tanpa Furing Tebal (Sejuk & Ringan)',
            'karakteristik_kain' => 'Kain katun halus, serat rapat, sejuk dipakai di cuaca hangat & iklim tropis.',
            'detail_payet'       => 'Bordir keranjang & flora buatan tangan (hand-made embroidery) di sepanjang tepi bawah.',
            'rekomendasi_acara'  => 'Kebaya Encim Day, Sangjit, Festival Budaya, Acara Semi-Formal',
        ],
        [
            'slug'               => 'kebaya-peplum-brokat',
            'nama'               => 'Kebaya Peplum Brokat',
            'harga'              => 700000,
            'foto'               => 'Kebaya Peplum Brokat.png',
            'foto_belakang'      => 'Kebaya Peplum Brokat-belakang.jpg',
            'deskripsi'          => 'Kebaya peplum feminin berhias renda brokat pilihan cocok untuk momen wisuda.',
            'kain_utama'         => 'Brokat Tile Semi-Perancis & Organza Premium',
            'furing'             => 'Furing Satin Maxmara Silk',
            'karakteristik_kain' => 'Struktur peplum mengembang anggun di pinggang, memberi siluet tubuh ramping & tinggi.',
            'detail_payet'       => 'Renda Guipure melingkar di pinggang & payet mutiara sintetis di pergelangan lengan.',
            'rekomendasi_acara'  => 'Wisuda Universitas, Pengajian Formal, Kondangan, Syukuran',
        ],
        [
            'slug'               => 'kebaya-songket-palembang',
            'nama'               => 'Kebaya Songket Palembang',
            'harga'              => 800000,
            'foto'               => 'Kebaya Songket Palembang.jpg',
            'foto_belakang'      => 'Kebaya Songket Palembang-belakang.jpg',
            'deskripsi'          => 'Kebaya mewah dengan kain songket Palembang asli bermotif emas yang indah memukau.',
            'kain_utama'         => 'Tenun Songket Palembang Benang Emas (Bawahan) & Taffeta Silk (Atasan)',
            'furing'             => 'Tricot & Full Furing Asahi Silky',
            'karakteristik_kain' => 'Tekstur kain tegas & bermotif timbul emas megah, sangat mewah dan regal.',
            'detail_payet'       => 'Motif Songket Lepus benang emas otentik dengan hiasan bros dada khas Sumatera.',
            'rekomendasi_acara'  => 'Resepsi Pernikahan Adat Sumatera, Akad Nikah, Gala Dinner VIP',
        ],
        [
            'slug'               => 'kebaya-sutra-solo',
            'nama'               => 'Kebaya Sutra Solo',
            'harga'              => 950000,
            'foto'               => 'Kebaya Sutra Solo.jpg',
            'foto_belakang'      => 'Kebaya Sutra Solo-belakang.jpg',
            'deskripsi'          => 'Kebaya sutra pilihan khas Solo dengan tekstur lembut dan tampilan sangat elegan.',
            'kain_utama'         => '100% Sutra Alam Solo (Natural Silk Premium)',
            'furing'             => 'Sutra Habutai Furing',
            'karakteristik_kain' => 'Super halus, kilau sutra alami memukau, jatuh sangat mengalir (flowy), sangat sejuk.',
            'detail_payet'       => 'Hand-painted motif batik sutra halus dengan aksen sentuhan prada emas tipis.',
            'rekomendasi_acara'  => 'Royal Wedding Guest, VIP Official Reception, Gala Night',
        ],
        [
            'slug'               => 'kebaya-batik-tulis-jawa',
            'nama'               => 'Kebaya Batik Tulis Jawa',
            'harga'              => 720000,
            'foto'               => null,
            'foto_belakang'      => null,
            'deskripsi'          => 'Kebaya berbahan dasar batik tulis tangan bermotif Jawa klasik yang otentik.',
            'kain_utama'         => 'Katun Primissima Batik Tulis Hand-crafted',
            'furing'             => 'Katun Hero Super',
            'karakteristik_kain' => 'Halus, berbobot mantap, pewarnaan malam alami khas batik klasik Jawa.',
            'detail_payet'       => 'Motif batik tulis Parang Gurdo tradisional dengan sentuhan kerah modern.',
            'rekomendasi_acara'  => 'Acara Resmi Kebudayaan, Seminar Nasional, Event Diplomatik',
        ],
        [
            'slug'               => 'kebaya-brokat-evening',
            'nama'               => 'Kebaya Brokat Evening',
            'harga'              => 1000000,
            'foto'               => null,
            'foto_belakang'      => null,
            'deskripsi'          => 'Kebaya eksklusif untuk gala malam formal dengan material brokat premium pilihan.',
            'kain_utama'         => 'Heavy French Corded Lace & Crepe Silk',
            'furing'             => 'Full Satin Silk',
            'karakteristik_kain' => 'Kain bertekstur tegas, pembentukan pola presisi, berkilau di bawah lampu malam.',
            'detail_payet'       => 'Full payet jepang di sekeliling bodice dan lengan.',
            'rekomendasi_acara'  => 'Gala Night Dinner, Awarding Night, Formal Party',
        ],
        [
            'slug'               => 'kebaya-tenun-ntt',
            'nama'               => 'Kebaya Tenun NTT',
            'harga'              => 680000,
            'foto'               => null,
            'foto_belakang'      => null,
            'deskripsi'          => 'Kebaya tenun ikat asli dari Nusa Tenggara Timur penuh warna dan kekayaan budaya.',
            'kain_utama'         => 'Tenun Ikat Handwoven NTT (Nusa Tenggara Timur)',
            'furing'             => 'Katun Prima',
            'karakteristik_kain' => 'Tenunan benang katun tebal bermotif etnik khas pulau NTT.',
            'detail_payet'       => 'Pewarnaan benang alami dengan garis tenun etnik kontras.',
            'rekomendasi_acara'  => 'Festival Etnik, Pameran Seni, Acara Kebudayaan',
        ],
        [
            'slug'               => 'kebaya-batik-madura',
            'nama'               => 'Kebaya Batik Madura',
            'harga'              => 600000,
            'foto'               => null,
            'foto_belakang'      => null,
            'deskripsi'          => 'Kebaya khas Madura motif batik cerah penuh semangat dengan warna berani dan cerah.',
            'kain_utama'         => 'Katun Bergas Batik Madura Pesisiran',
            'furing'             => 'Katun Adem',
            'karakteristik_kain' => 'Katun tebal sejuk dengan perpaduan warna berani khas pesisir Madura.',
            'detail_payet'       => 'Motif flora fauna Madura bergaris tegas dan ekspresif.',
            'rekomendasi_acara'  => 'Acara Santai Formal, Festival Seni, Parade Busana Etnik',
        ],
        [
            'slug'               => 'kebaya-bordiran-sumatera',
            'nama'               => 'Kebaya Bordiran Sumatera',
            'harga'              => 750000,
            'foto'               => null,
            'foto_belakang'      => null,
            'deskripsi'          => 'Kebaya Sumatera berhias bordiran tangan benang emas dengan detail mewah menawan.',
            'kain_utama'         => 'Katun Balotelli Premium & Benang Emas Metallic',
            'furing'             => 'Furing Katun',
            'karakteristik_kain' => 'Bordir tebal benang emas di atas kain bertekstur struktural yang anggun.',
            'detail_payet'       => 'Bordiran tangan motif suji caia khas Sumatera Barat/Utara.',
            'rekomendasi_acara'  => 'Resepsi Pernikahan, Acara Adat Sumatera, Perayaan Resmi',
        ],
    ];

    /** Status yang tersedia */
    public const STATUS_AKTIF     = 'aktif';
    public const STATUS_NONAKTIF  = 'nonaktif';
    public const STATUS_PERBAIKAN = 'perbaikan';

    public function __construct()
    {
        $this->storagePath = storage_path('app/kebaya_status.json');
        $this->ensureFileExists();
    }

    // ─── Internal ─────────────────────────────────────────────

    protected function ensureFileExists(): void
    {
        if (!file_exists($this->storagePath)) {
            // Inisialisasi: kebaya dengan foto = aktif, sisanya = segera
            $default = [];
            foreach (self::$masterKatalog as $k) {
                $default[$k['slug']] = !empty($k['foto']) ? self::STATUS_AKTIF : 'segera';
            }
            file_put_contents($this->storagePath, json_encode($default, JSON_PRETTY_PRINT));
        }
    }

    protected function readStatuses(): array
    {
        try {
            $data = json_decode(file_get_contents($this->storagePath), true);
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function writeStatuses(array $statuses): void
    {
        file_put_contents($this->storagePath, json_encode($statuses, JSON_PRETTY_PRINT));
    }

    // ─── Public API ───────────────────────────────────────────

    /**
     * Ambil semua kebaya: master + custom dari JSON, dengan status masing-masing
     */
    public function getAll(): array
    {
        $statuses   = $this->readStatuses();
        $customPath = storage_path('app/kebaya_custom.json');
        $customs    = [];
        if (file_exists($customPath)) {
            $raw     = json_decode(file_get_contents($customPath), true);
            $customs = is_array($raw) ? $raw : [];
        }

        // Merge: master dulu, lalu custom
        $all = array_merge(self::$masterKatalog, $customs);

        return array_map(function ($k) use ($statuses) {
            $defaultStatus = ($k['foto'] ?? null) ? self::STATUS_AKTIF : 'segera';
            $k['status']   = $statuses[$k['slug']] ?? $defaultStatus;
            return $k;
        }, $all);
    }

    /**
     * Ambil hanya kebaya yang status = aktif (untuk filter internal)
     */
    public function getAktif(): array
    {
        return array_filter($this->getAll(), fn($k) => $k['status'] === self::STATUS_AKTIF);
    }

    /**
     * Ambil kebaya untuk katalog PUBLIK (aktif + segera).
     * - aktif   → tersedia = true  (bisa dipesan)
     * - segera  → tersedia = false (badge "Segera Hadir", tidak bisa dipesan)
     * - nonaktif / perbaikan → TIDAK tampil di publik
     */
    public function getPublic(): array
    {
        $all = $this->getAll();
        return array_values(array_filter($all, fn($k) =>
            in_array($k['status'], [self::STATUS_AKTIF, 'segera'])
        ));
    }

    /**
     * Ubah status kebaya berdasarkan slug
     */
    public function setStatus(string $slug, string $status): bool
    {
        $allowed = [self::STATUS_AKTIF, self::STATUS_NONAKTIF, self::STATUS_PERBAIKAN, 'segera'];
        if (!in_array($status, $allowed)) return false;

        $statuses = $this->readStatuses();
        $statuses[$slug] = $status;
        $this->writeStatuses($statuses);
        return true;
    }

    /**
     * Cycle status: aktif → nonaktif → perbaikan → aktif
     */
    public function cycleStatus(string $slug): string
    {
        $statuses = $this->readStatuses();
        $current  = $statuses[$slug] ?? self::STATUS_AKTIF;

        $next = match($current) {
            self::STATUS_AKTIF     => self::STATUS_NONAKTIF,
            self::STATUS_NONAKTIF  => self::STATUS_PERBAIKAN,
            self::STATUS_PERBAIKAN => self::STATUS_AKTIF,
            default                => self::STATUS_AKTIF,
        };

        $statuses[$slug] = $next;
        $this->writeStatuses($statuses);
        return $next;
    }

    /**
     * Tandai kebaya sebagai tersembunyi (hapus dari tampilan)
     * (Data asli tetap ada di $masterKatalog — hanya disembunyikan)
     */
    public function hide(string $slug): bool
    {
        return $this->setStatus($slug, self::STATUS_NONAKTIF);
    }

    /**
     * Reset semua ke default
     */
    public function resetAll(): void
    {
        if (file_exists($this->storagePath)) {
            unlink($this->storagePath);
        }
        $this->ensureFileExists();
    }

    /**
     * Cari kebaya berdasarkan slug
     */
    public static function findBySlug(string $slug): ?array
    {
        foreach (self::$masterKatalog as $k) {
            if ($k['slug'] === $slug) return $k;
        }
        return null;
    }

    /**
     * Label & warna badge status
     */
    public static function statusBadge(string $status): array
    {
        return match($status) {
            self::STATUS_AKTIF     => ['label' => 'AKTIF',     'class' => 'db-status-aktif',     'icon' => '🟢'],
            self::STATUS_NONAKTIF  => ['label' => 'NONAKTIF',  'class' => 'db-status-nonaktif',  'icon' => '🔴'],
            self::STATUS_PERBAIKAN => ['label' => 'PERBAIKAN', 'class' => 'db-status-perbaikan', 'icon' => '🔧'],
            'segera'               => ['label' => 'SEGERA',    'class' => 'db-status-segera',    'icon' => '⏳'],
            default                => ['label' => 'AKTIF',     'class' => 'db-status-aktif',     'icon' => '🟢'],
        };
    }
}
