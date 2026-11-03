/**
 * KebayaService - Port dari PHP ke Node.js
 * Master katalog hardcoded + JSON status management
 */
const fs = require('fs');
const path = require('path');

const STATUS_AKTIF = 'aktif';
const STATUS_NONAKTIF = 'nonaktif';
const STATUS_PERBAIKAN = 'perbaikan';

const masterKatalog = [
  {
    slug: 'kebaya-modern-brukat-gown',
    nama: 'Kebaya Modern Brukat Gown',
    harga: 850000,
    foto: 'Kebaya Modern Brukat Gown.png',
    foto_belakang: 'Kebaya Modern Brukat Gown-belakang.jpg',
    deskripsi: 'Gaun modern brokat premium dengan siluet elegan dan aksen mewah bergaya tinggi.',
    kain_utama: 'Brokat Chantilly Perancis & Soft Tulle',
    furing: 'Katun Hero Super (Adem & Menyerap Keringat)',
    karakteristik_kain: 'Tekstur sangat lembut, drape jatuh anggun, bertekstur empuk, tidak gatal & tidak panas.',
    detail_payet: 'Payet Kristal Swarovski sintetik & Mutiara Jepang di leher & lingkar dada.',
    rekomendasi_acara: 'Resepsi Pernikahan, Wisuda, Gala Dinner, Evening Event',
  },
  {
    slug: 'kebaya-aurora-slit-gown',
    nama: 'Kebaya Aurora Slit Gown',
    harga: 900000,
    foto: 'Kebaya Aurora Slit Gown.png',
    foto_belakang: 'Kebaya Aurora Slit Gown-belakang.jpg',
    deskripsi: 'Gaun aurora eksklusif belahan modern yang anggun untuk setiap momen spesial Anda.',
    kain_utama: 'Brokat Cornely High-Grade & Silk Satin Velvet',
    furing: 'Full Furing Silky Cotton',
    karakteristik_kain: 'Tampilan mewah mengkilap elegan, elastisitas pas, belahan slit modern yang tetap sopan.',
    detail_payet: 'Aplikasi renda bordir 3D dengan payet tabur berkilau di pinggang & punggung.',
    rekomendasi_acara: 'Engagement / Lamaran, Event Red Carpet, Prewedding, Party',
  },
  {
    slug: 'kebaya-kartini-beludru',
    nama: 'Kebaya Kartini Beludru',
    harga: 750000,
    foto: 'Kebaya Kartini Beludru.png',
    foto_belakang: 'Kebaya Kartini Beludru-belakang.jpg',
    deskripsi: 'Kebaya Kartini klasik berbahan beludru premium bernuansa royal penuh wibawa.',
    kain_utama: 'Beludru (Velvet) Silk Stretch Royal',
    furing: 'Lapisan Tricot Halus (Menjaga bentuk presisi)',
    karakteristik_kain: 'Permukaan beludru lembut berkilau mewah, cukup fleksibel di badan, hangat & nyaman.',
    detail_payet: 'Bordir benang emas keraton di sepanjang kelim depan, lengan, dan kerah Kartini.',
    rekomendasi_acara: 'Acara Adat Jawa, Midodareni, Wisuda, Resepsi Keraton',
  },
  {
    slug: 'kebaya-kutubaru-klasik-batik',
    nama: 'Kebaya Kutubaru Klasik Batik',
    harga: 650000,
    foto: 'Kebaya Kutubaru Klasik Batik.png',
    foto_belakang: 'Kebaya Kutubaru Klasik Batik-belakang.jpg',
    deskripsi: 'Kebaya kutubaru tradisional dipadu bawahan kain batik otentik khas Jawa Tengah.',
    kain_utama: 'Katun Jacquard Emboss (Atasan) & Batik Tulis Solo (Bawahan)',
    furing: 'Katun Prima Adem 100% Cotton',
    karakteristik_kain: 'Sangat sejuk, ringan, mudah bergerak, serat kain katun bernafas baik.',
    detail_payet: 'Kutubaru klasik dengan kancing bungkus rapi & bros etnik benang emas.',
    rekomendasi_acara: 'Hari Kartini, Wisuda, Upacara Adat, Acara Formal Kebudayaan',
  },
  {
    slug: 'kebaya-encim-peranakan', nama: 'Kebaya Encim Peranakan', harga: 600000,
    foto: 'Kebaya Encim Peranakan.png', foto_belakang: 'Kebaya Encim Peranakan-belakang.jpg',
    deskripsi: 'Kebaya encim klasik berhias bordir halus nan cantik khas budaya Peranakan.',
    kain_utama: 'Katun Voile Silky Premium', furing: 'Unlined / Tanpa Furing Tebal',
    karakteristik_kain: 'Kain katun halus, serat rapat, sejuk dipakai di cuaca hangat & iklim tropis.',
    detail_payet: 'Bordir keranjang & flora buatan tangan di sepanjang tepi bawah.',
    rekomendasi_acara: 'Kebaya Encim Day, Sangjit, Festival Budaya, Acara Semi-Formal',
  },
  {
    slug: 'kebaya-peplum-brokat', nama: 'Kebaya Peplum Brokat', harga: 700000,
    foto: 'Kebaya Peplum Brokat.png', foto_belakang: 'Kebaya Peplum Brokat-belakang.jpg',
    deskripsi: 'Kebaya peplum feminin berhias renda brokat pilihan cocok untuk momen wisuda.',
    kain_utama: 'Brokat Tile Semi-Perancis & Organza Premium', furing: 'Furing Satin Maxmara Silk',
    karakteristik_kain: 'Struktur peplum mengembang anggun di pinggang, memberi siluet tubuh ramping & tinggi.',
    detail_payet: 'Renda Guipure melingkar di pinggang & payet mutiara sintetis di pergelangan lengan.',
    rekomendasi_acara: 'Wisuda Universitas, Pengajian Formal, Kondangan, Syukuran',
  },
  {
    slug: 'kebaya-songket-palembang', nama: 'Kebaya Songket Palembang', harga: 800000,
    foto: 'Kebaya Songket Palembang.jpg', foto_belakang: 'Kebaya Songket Palembang-belakang.jpg',
    deskripsi: 'Kebaya mewah dengan kain songket Palembang asli bermotif emas yang indah memukau.',
    kain_utama: 'Tenun Songket Palembang Benang Emas & Taffeta Silk', furing: 'Tricot & Full Furing Asahi Silky',
    karakteristik_kain: 'Tekstur kain tegas & bermotif timbul emas megah, sangat mewah dan regal.',
    detail_payet: 'Motif Songket Lepus benang emas otentik dengan hiasan bros dada khas Sumatera.',
    rekomendasi_acara: 'Resepsi Pernikahan Adat Sumatera, Akad Nikah, Gala Dinner VIP',
  },
  {
    slug: 'kebaya-sutra-solo', nama: 'Kebaya Sutra Solo', harga: 950000,
    foto: 'Kebaya Sutra Solo.jpg', foto_belakang: 'Kebaya Sutra Solo-belakang.jpg',
    deskripsi: 'Kebaya sutra pilihan khas Solo dengan tekstur lembut dan tampilan sangat elegan.',
    kain_utama: '100% Sutra Alam Solo (Natural Silk Premium)', furing: 'Sutra Habutai Furing',
    karakteristik_kain: 'Super halus, kilau sutra alami memukau, jatuh sangat mengalir (flowy), sangat sejuk.',
    detail_payet: 'Hand-painted motif batik sutra halus dengan aksen sentuhan prada emas tipis.',
    rekomendasi_acara: 'Royal Wedding Guest, VIP Official Reception, Gala Night',
  },
  { slug: 'kebaya-batik-tulis-jawa', nama: 'Kebaya Batik Tulis Jawa', harga: 720000, foto: null, foto_belakang: null, deskripsi: 'Kebaya berbahan dasar batik tulis tangan bermotif Jawa klasik yang otentik.', kain_utama: 'Katun Primissima Batik Tulis', furing: 'Katun Hero Super', karakteristik_kain: 'Halus, berbobot mantap, pewarnaan malam alami khas batik klasik Jawa.', detail_payet: 'Motif batik tulis Parang Gurdo tradisional dengan sentuhan kerah modern.', rekomendasi_acara: 'Acara Resmi Kebudayaan, Seminar Nasional, Event Diplomatik' },
  { slug: 'kebaya-brokat-evening', nama: 'Kebaya Brokat Evening', harga: 1000000, foto: null, foto_belakang: null, deskripsi: 'Kebaya eksklusif untuk gala malam formal dengan material brokat premium pilihan.', kain_utama: 'Heavy French Corded Lace & Crepe Silk', furing: 'Full Satin Silk', karakteristik_kain: 'Kain bertekstur tegas, pembentukan pola presisi, berkilau di bawah lampu malam.', detail_payet: 'Full payet jepang di sekeliling bodice dan lengan.', rekomendasi_acara: 'Gala Night Dinner, Awarding Night, Formal Party' },
  { slug: 'kebaya-tenun-ntt', nama: 'Kebaya Tenun NTT', harga: 680000, foto: null, foto_belakang: null, deskripsi: 'Kebaya tenun ikat asli dari Nusa Tenggara Timur penuh warna dan kekayaan budaya.', kain_utama: 'Tenun Ikat Handwoven NTT', furing: 'Katun Prima', karakteristik_kain: 'Tenunan benang katun tebal bermotif etnik khas pulau NTT.', detail_payet: 'Pewarnaan benang alami dengan garis tenun etnik kontras.', rekomendasi_acara: 'Festival Etnik, Pameran Seni, Acara Kebudayaan' },
  { slug: 'kebaya-batik-madura', nama: 'Kebaya Batik Madura', harga: 600000, foto: null, foto_belakang: null, deskripsi: 'Kebaya khas Madura motif batik cerah penuh semangat.', kain_utama: 'Katun Bergas Batik Madura Pesisiran', furing: 'Katun Adem', karakteristik_kain: 'Katun tebal sejuk dengan perpaduan warna berani khas pesisir Madura.', detail_payet: 'Motif flora fauna Madura bergaris tegas.', rekomendasi_acara: 'Acara Santai Formal, Festival Seni, Parade Busana Etnik' },
  { slug: 'kebaya-bordiran-sumatera', nama: 'Kebaya Bordiran Sumatera', harga: 750000, foto: null, foto_belakang: null, deskripsi: 'Kebaya Sumatera berhias bordiran tangan benang emas.', kain_utama: 'Katun Balotelli Premium & Benang Emas Metallic', furing: 'Furing Katun', karakteristik_kain: 'Bordir tebal benang emas di atas kain bertekstur struktural.', detail_payet: 'Bordiran tangan motif suji caia khas Sumatera.', rekomendasi_acara: 'Resepsi Pernikahan, Acara Adat Sumatera, Perayaan Resmi' },
];

class KebayaService {
  constructor() {
    this.storagePath = path.join(__dirname, '..', '..', 'data', 'kebaya_status.json');
    this.customPath = path.join(__dirname, '..', '..', 'data', 'kebaya_custom.json');
    this.ensureFileExists();
  }

  ensureFileExists() {
    const dir = path.dirname(this.storagePath);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });

    if (!fs.existsSync(this.storagePath)) {
      const defaults = {};
      for (const k of masterKatalog) {
        defaults[k.slug] = k.foto ? STATUS_AKTIF : 'segera';
      }
      fs.writeFileSync(this.storagePath, JSON.stringify(defaults, null, 2));
    }
  }

  readStatuses() {
    try {
      return JSON.parse(fs.readFileSync(this.storagePath, 'utf8'));
    } catch { return {}; }
  }

  writeStatuses(statuses) {
    fs.writeFileSync(this.storagePath, JSON.stringify(statuses, null, 2));
  }

  getAll() {
    const statuses = this.readStatuses();
    let customs = [];
    if (fs.existsSync(this.customPath)) {
      try { customs = JSON.parse(fs.readFileSync(this.customPath, 'utf8')); } catch {}
    }

    return [...masterKatalog, ...customs].map(k => ({
      ...k,
      status: statuses[k.slug] || (k.foto ? STATUS_AKTIF : 'segera'),
    }));
  }

  getPublic() {
    return this.getAll().filter(k => ['aktif', 'segera'].includes(k.status));
  }

  setStatus(slug, status) {
    const allowed = [STATUS_AKTIF, STATUS_NONAKTIF, STATUS_PERBAIKAN, 'segera'];
    if (!allowed.includes(status)) return false;
    const statuses = this.readStatuses();
    statuses[slug] = status;
    this.writeStatuses(statuses);
    return true;
  }

  cycleStatus(slug) {
    const statuses = this.readStatuses();
    const current = statuses[slug] || STATUS_AKTIF;
    const next = { aktif: STATUS_NONAKTIF, nonaktif: STATUS_PERBAIKAN, perbaikan: STATUS_AKTIF }[current] || STATUS_AKTIF;
    statuses[slug] = next;
    this.writeStatuses(statuses);
    return next;
  }

  hide(slug) { return this.setStatus(slug, STATUS_NONAKTIF); }

  resetAll() {
    if (fs.existsSync(this.storagePath)) fs.unlinkSync(this.storagePath);
    this.ensureFileExists();
  }

  addCustom(data) {
    let customs = [];
    if (fs.existsSync(this.customPath)) {
      try { customs = JSON.parse(fs.readFileSync(this.customPath, 'utf8')); } catch {}
    }
    const slug = (data.nama || data.slug || 'custom-' + Date.now())
      .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    customs.push({ ...data, slug });
    fs.writeFileSync(this.customPath, JSON.stringify(customs, null, 2));
    const statuses = this.readStatuses();
    statuses[slug] = STATUS_AKTIF;
    this.writeStatuses(statuses);
  }

  updateCustom(slug, data) {
    if (!fs.existsSync(this.customPath)) return;
    let customs = JSON.parse(fs.readFileSync(this.customPath, 'utf8'));
    customs = customs.map(k => k.slug === slug ? { ...k, ...data } : k);
    fs.writeFileSync(this.customPath, JSON.stringify(customs, null, 2));
  }

  deleteCustom(slug) {
    if (!fs.existsSync(this.customPath)) return;
    let customs = JSON.parse(fs.readFileSync(this.customPath, 'utf8'));
    customs = customs.filter(k => k.slug !== slug);
    fs.writeFileSync(this.customPath, JSON.stringify(customs, null, 2));
    const statuses = this.readStatuses();
    delete statuses[slug];
    this.writeStatuses(statuses);
  }

  static findBySlug(slug) {
    return masterKatalog.find(k => k.slug === slug) || null;
  }
}

module.exports = KebayaService;
