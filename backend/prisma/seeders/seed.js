const { PrismaClient } = require('@prisma/client');
const bcrypt = require('bcryptjs');

const prisma = new PrismaClient();

async function seedUsers() {
  const users = [
    {
      name: 'Super Admin MS Harmony',
      email: 'superadmin@msharmony.id',
      role: 'super_admin',
      password: await bcrypt.hash('SuperAdmin123!', 10),
      emailVerifiedAt: new Date(),
    },
    {
      name: 'Admin MS Harmony',
      email: 'admin@msharmony.id',
      role: 'admin',
      password: await bcrypt.hash('Admin123!', 10),
      emailVerifiedAt: new Date(),
    },
    {
      name: 'Owner MS Harmony',
      email: 'owner@msharmony.id',
      role: 'owner',
      password: await bcrypt.hash('Owner123!', 10),
      emailVerifiedAt: new Date(),
    },
    {
      name: 'Arief Harmoni',
      email: 'ariefharmoni@gmail.com',
      role: 'admin',
      password: await bcrypt.hash('Harmoni123', 10),
      emailVerifiedAt: new Date(),
    },
  ];

  for (const data of users) {
    await prisma.user.upsert({
      where: { email: data.email },
      update: data,
      create: data,
    });
  }

  console.log('✅ UserSeeder: 4 akun berhasil dibuat/diperbarui.');
  console.table([
    { Name: 'Super Admin MS Harmony', Email: 'superadmin@msharmony.id', Role: 'super_admin', Password: 'SuperAdmin123!' },
    { Name: 'Admin MS Harmony', Email: 'admin@msharmony.id', Role: 'admin', Password: 'Admin123!' },
    { Name: 'Owner MS Harmony', Email: 'owner@msharmony.id', Role: 'owner', Password: 'Owner123!' },
    { Name: 'Arief Harmoni', Email: 'ariefharmoni@gmail.com', Role: 'admin', Password: 'Harmoni123' },
  ]);
}

async function seedTransaksi() {
  const kebayaList = [
    { nama: 'Kebaya Modern Brukat Gown', harga_dasar: 850000 },
    { nama: 'Kebaya Encim Peach Blossom', harga_dasar: 750000 },
    { nama: 'Kebaya Kutubaru Hijau Tosca', harga_dasar: 650000 },
    { nama: 'Kebaya Pengantin Ivory Gold', harga_dasar: 1200000 },
    { nama: 'Kebaya Kartini Biru Dongker', harga_dasar: 700000 },
    { nama: 'Kebaya Wisuda Marun Beludru', harga_dasar: 800000 },
    { nama: 'Kebaya Sundanese Pink Dusty', harga_dasar: 600000 },
    { nama: 'Kebaya Javanese Sidomukti', harga_dasar: 950000 },
    { nama: 'Kebaya Pesta Silver Glitter', harga_dasar: 1100000 },
    { nama: 'Kebaya Batik Kawung Coklat', harga_dasar: 550000 },
  ];

  const namaCustomer = [
    'Anisa Rahmawati', 'Berliana Putri', 'Cindy Maharani', 'Desy Kartika',
    'Elisa Wulandari', 'Fina Kusuma', 'Gilang Permata', 'Hana Safitri',
    'Indah Lestari', 'Julia Maulida', 'Kirana Salsabila', 'Layla Nur Azizah',
    'Maya Sari', 'Nadia Fitriani', 'Olivia Santoso', 'Putri Cahayani',
    'Qory Andriani', 'Rara Kencana', 'Sinta Dewi', 'Tiara Agustin',
    'Ulfa Ramadhani', 'Vira Oktaviani', 'Wulan Septiana', 'Xena Anggraini',
    'Yuli Prasetya', 'Zara Maullana', 'Aulia Hamidah', 'Bella Indriani',
    'Cahya Melati', 'Dwi Lestariningsih',
  ];

  const noWaList = [
    '081234567890', '082345678901', '083456789012', '084567890123',
    '085678901234', '086789012345', '087890123456', '088901234567',
    '089012345678', '081123456789', '082234567890', '083345678901',
    '084456789012', '085567890123', '086678901234', '087789012345',
  ];

  const alamatList = [
    'Jl. Magelang No. 12, Sleman, DIY',
    'Jl. Parangtritis Km 5, Bantul, DIY',
    'Jl. Godean Raya No. 34, Sleman, DIY',
    'Perumahan Griyo Ageng Blok B5, Bangunjiwo, Bantul',
    'Jl. Veteran No. 8, Kota Yogyakarta',
    'Jl. Affandi No. 20, Sleman, DIY',
    'Kledokan RT 03, Depok, Sleman, DIY',
    'Jl. Wates Km 7, Gamping, Sleman',
    'Jl. Solo Km 10, Kalasan, Sleman',
    'Jl. Kaliurang Km 12, Ngaglik, Sleman',
    'Maguwoharjo, Depok, Sleman',
    'Jl. Ring Road Utara No. 5, Sleman',
  ];

  const ukuranList = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
  const skemaList = ['DP 50%', 'DP 70%', 'Lunas 100%'];
  const metodeList = ['QRIS', 'Transfer Bank BCA', 'Transfer Bank BRI', 'Transfer Bank Mandiri', 'COD'];

  const batches = [
    // JANUARI 2026
    ['2026-01-03', '2026-01-05', '2026-01-07', 'Telah Dikembalikan', true, 'DP 50%', 0],
    ['2026-01-08', '2026-01-10', '2026-01-11', 'Telah Dikembalikan', true, 'Lunas 100%', 1],
    ['2026-01-12', '2026-01-14', '2026-01-16', 'Telah Dikembalikan', true, 'DP 70%', 2],
    ['2026-01-15', '2026-01-17', '2026-01-18', 'Telah Dikembalikan', true, 'DP 50%', 3],
    ['2026-01-20', '2026-01-22', '2026-01-24', 'Telah Dikembalikan', true, 'Lunas 100%', 4],
    ['2026-01-25', '2026-01-27', '2026-01-29', 'Dibatalkan', false, 'DP 50%', 5],
    // FEBRUARI 2026
    ['2026-02-02', '2026-02-04', '2026-02-05', 'Telah Dikembalikan', true, 'DP 50%', 6],
    ['2026-02-07', '2026-02-09', '2026-02-11', 'Telah Dikembalikan', true, 'DP 70%', 7],
    ['2026-02-10', '2026-02-12', '2026-02-14', 'Telah Dikembalikan', true, 'Lunas 100%', 8],
    ['2026-02-14', '2026-02-16', '2026-02-18', 'Telah Dikembalikan', true, 'DP 50%', 9],
    ['2026-02-18', '2026-02-20', '2026-02-22', 'Telah Dikembalikan', true, 'DP 70%', 0],
    ['2026-02-22', '2026-02-24', '2026-02-26', 'Dibatalkan', false, 'DP 50%', 1],
    // MARET 2026
    ['2026-03-01', '2026-03-03', '2026-03-05', 'Telah Dikembalikan', true, 'DP 50%', 2],
    ['2026-03-05', '2026-03-07', '2026-03-09', 'Telah Dikembalikan', true, 'DP 70%', 3],
    ['2026-03-10', '2026-03-12', '2026-03-14', 'Telah Dikembalikan', true, 'Lunas 100%', 4],
    ['2026-03-14', '2026-03-16', '2026-03-18', 'Telah Dikembalikan', true, 'DP 50%', 5],
    ['2026-03-20', '2026-03-22', '2026-03-24', 'Telah Dikembalikan', true, 'DP 70%', 6],
    ['2026-03-25', '2026-03-27', '2026-03-29', 'Dibatalkan', false, 'DP 50%', 7],
    // APRIL 2026
    ['2026-04-01', '2026-04-03', '2026-04-05', 'Telah Dikembalikan', true, 'Lunas 100%', 8],
    ['2026-04-06', '2026-04-08', '2026-04-10', 'Telah Dikembalikan', true, 'DP 50%', 9],
    ['2026-04-10', '2026-04-12', '2026-04-14', 'Telah Dikembalikan', true, 'DP 70%', 0],
    ['2026-04-15', '2026-04-17', '2026-04-19', 'Telah Dikembalikan', true, 'DP 50%', 1],
    ['2026-04-20', '2026-04-22', '2026-04-24', 'Telah Dikembalikan', true, 'Lunas 100%', 2],
    ['2026-04-25', '2026-04-27', '2026-04-29', 'Dibatalkan', false, 'DP 50%', 3],
    // MEI 2026
    ['2026-05-02', '2026-05-04', '2026-05-06', 'Telah Dikembalikan', true, 'DP 50%', 4],
    ['2026-05-07', '2026-05-09', '2026-05-11', 'Telah Dikembalikan', true, 'DP 70%', 5],
    ['2026-05-10', '2026-05-12', '2026-05-14', 'Telah Dikembalikan', true, 'Lunas 100%', 6],
    ['2026-05-15', '2026-05-17', '2026-05-19', 'Telah Dikembalikan', true, 'DP 50%', 7],
    ['2026-05-20', '2026-05-22', '2026-05-24', 'Telah Dikembalikan', true, 'DP 70%', 8],
    ['2026-05-25', '2026-05-27', '2026-05-29', 'Dibatalkan', false, 'DP 50%', 9],
    // JUNI 2026
    ['2026-06-01', '2026-06-03', '2026-06-05', 'Telah Dikembalikan', true, 'DP 50%', 0],
    ['2026-06-05', '2026-06-07', '2026-06-09', 'Telah Dikembalikan', true, 'DP 70%', 1],
    ['2026-06-10', '2026-06-12', '2026-06-14', 'Telah Dikembalikan', true, 'Lunas 100%', 2],
    ['2026-06-15', '2026-06-17', '2026-06-19', 'Telah Dikembalikan', true, 'DP 50%', 3],
    ['2026-06-20', '2026-06-22', '2026-06-24', 'Telah Dikembalikan', true, 'DP 70%', 4],
    ['2026-06-25', '2026-06-27', '2026-06-29', 'Dibatalkan', false, 'DP 50%', 5],
    // JULI 2026
    ['2026-07-01', '2026-07-03', '2026-07-05', 'Telah Dikembalikan', true, 'DP 50%', 6],
    ['2026-07-06', '2026-07-08', '2026-07-10', 'Telah Dikembalikan', true, 'DP 70%', 7],
    ['2026-07-10', '2026-07-12', '2026-07-14', 'Telah Dikembalikan', true, 'Lunas 100%', 8],
    ['2026-07-15', '2026-07-17', '2026-07-19', 'Telah Dikembalikan', true, 'DP 50%', 9],
    ['2026-07-20', '2026-07-22', '2026-07-24', 'Kebaya Telah Diambil', true, 'DP 70%', 0],
    ['2026-07-25', '2026-07-27', '2026-07-29', 'Dibatalkan', false, 'DP 50%', 1],
    // AGUSTUS 2026
    ['2026-08-01', '2026-08-03', '2026-08-05', 'Telah Dikonfirmasi', true, 'DP 50%', 2],
    ['2026-08-05', '2026-08-07', '2026-08-09', 'Sedang Fitting', true, 'DP 70%', 3],
    ['2026-08-08', '2026-08-10', '2026-08-12', 'Menunggu Konfirmasi', true, 'Lunas 100%', 4],
    ['2026-08-10', '2026-08-12', '2026-08-15', 'Menunggu Pembayaran DP', false, 'DP 50%', 5],
    ['2026-08-12', '2026-08-14', '2026-08-16', 'Menunggu Fitting', false, 'DP 50%', 6],
    ['2026-08-14', '2026-08-16', '2026-08-18', 'Telah Dikonfirmasi', true, 'DP 70%', 7],
    ['2026-08-16', '2026-08-18', '2026-08-20', 'Sedang Fitting', true, 'DP 50%', 8],
    ['2026-08-18', '2026-08-20', '2026-08-22', 'Kebaya Telah Diambil', true, 'Lunas 100%', 9],
    ['2026-08-20', '2026-08-22', '2026-08-24', 'Mengajukan Pengembalian', true, 'DP 50%', 0],
    ['2026-08-22', '2026-08-24', '2026-08-26', 'Menunggu Pembayaran DP', false, 'DP 50%', 1],
    ['2026-08-24', '2026-08-26', '2026-08-28', 'Menunggu Konfirmasi', true, 'DP 70%', 2],
    ['2026-08-26', '2026-08-28', '2026-08-30', 'Telah Dikonfirmasi', true, 'Lunas 100%', 3],
    ['2026-08-28', '2026-08-30', '2026-09-01', 'Dibatalkan', false, 'DP 50%', 4],
  ];

  const crypto = require('crypto');
  let idx = 0;

  for (const [createdStr, sewaStr, kembaliStr, status, sudahBayar, skema, kebayaIdx] of batches) {
    const kebaya = kebayaList[kebayaIdx];
    const created = new Date(createdStr);
    const sewa = new Date(sewaStr);
    const kembali = new Date(kembaliStr);
    const durasi = Math.max(1, Math.ceil((kembali - sewa) / (1000 * 60 * 60 * 24)));
    const totalHarga = kebaya.harga_dasar * durasi;
    const kode = 'TRX-' + crypto.createHash('md5').update(String(Date.now() + idx)).digest('hex').substring(0, 8).toUpperCase();

    await prisma.transaksiSewa.create({
      data: {
        kodeTransaksi: kode,
        namaKebaya: kebaya.nama,
        warnaCustomAi: 'Default',
        namaUser: namaCustomer[idx % namaCustomer.length],
        noWa: noWaList[idx % noWaList.length],
        ukuran: ukuranList[idx % ukuranList.length],
        metodePembayaran: metodeList[idx % metodeList.length],
        skemaBayar: skema,
        alamat: alamatList[idx % alamatList.length],
        tanggalSewa: sewa,
        tanggalPengembalian: kembali,
        durasiHari: durasi,
        status,
        totalHarga,
        sudahBayar,
        paymentStatus: sudahBayar ? 'paid' : 'pending',
        paidAt: sudahBayar ? created : null,
        jumlahDibayar: sudahBayar ? totalHarga : 0,
        createdAt: created,
      },
    });

    idx++;
  }

  console.log(`✅ TransaksiSeeder: ${idx} transaksi dummy berhasil dibuat.`);
}

async function seedKebaya() {
  const katalog = [
    { slug: 'kebaya-modern-brukat-gown', nama: 'Kebaya Modern Brukat Gown', harga: 850000, foto: 'Kebaya Modern Brukat Gown.png', fotoBelakang: 'Kebaya Modern Brukat Gown-belakang.jpg', deskripsi: 'Gaun modern brokat premium dengan siluet elegan dan aksen mewah bergaya tinggi.', kainUtama: 'Brokat Chantilly Perancis & Soft Tulle', furing: 'Katun Hero Super (Adem & Menyerap Keringat)', karakteristikKain: 'Tekstur sangat lembut, drape jatuh anggun, bertekstur empuk, tidak gatal & tidak panas.', detailPayet: 'Payet Kristal Swarovski sintetik & Mutiara Jepang di leher & lingkar dada.', rekomendasiAcara: 'Resepsi Pernikahan, Wisuda, Gala Dinner, Evening Event', status: 'aktif' },
    { slug: 'kebaya-aurora-slit-gown', nama: 'Kebaya Aurora Slit Gown', harga: 900000, foto: 'Kebaya Aurora Slit Gown.png', fotoBelakang: 'Kebaya Aurora Slit Gown-belakang.jpg', deskripsi: 'Gaun aurora eksklusif belahan modern yang anggun untuk setiap momen spesial Anda.', kainUtama: 'Brokat Cornely High-Grade & Silk Satin Velvet', furing: 'Full Furing Silky Cotton', karakteristikKain: 'Tampilan mewah mengkilap elegan, elastisitas pas, belahan slit modern yang tetap sopan.', detailPayet: 'Aplikasi renda bordir 3D dengan payet tabur berkilau di pinggang & punggung.', rekomendasiAcara: 'Engagement / Lamaran, Event Red Carpet, Prewedding, Party', status: 'aktif' },
    { slug: 'kebaya-kartini-beludru', nama: 'Kebaya Kartini Beludru', harga: 750000, foto: 'Kebaya Kartini Beludru.png', fotoBelakang: 'Kebaya Kartini Beludru-belakang.jpg', deskripsi: 'Kebaya Kartini klasik berbahan beludru premium bernuansa royal penuh wibawa.', kainUtama: 'Beludru (Velvet) Silk Stretch Royal', furing: 'Lapisan Tricot Halus (Menjaga bentuk presisi)', karakteristikKain: 'Permukaan beludru lembut berkilau mewah, cukup fleksibel di badan, hangat & nyaman.', detailPayet: 'Bordir benang emas keraton di sepanjang kelim depan, lengan, dan kerah Kartini.', rekomendasiAcara: 'Acara Adat Jawa, Midodareni, Wisuda, Resepsi Keraton', status: 'aktif' },
    { slug: 'kebaya-kutubaru-klasik-batik', nama: 'Kebaya Kutubaru Klasik Batik', harga: 650000, foto: 'Kebaya Kutubaru Klasik Batik.png', fotoBelakang: 'Kebaya Kutubaru Klasik Batik-belakang.jpg', deskripsi: 'Kebaya kutubaru tradisional dipadu bawahan kain batik otentik khas Jawa Tengah.', kainUtama: 'Katun Jacquard Emboss (Atasan) & Batik Tulis Solo (Bawahan)', furing: 'Katun Prima Adem 100% Cotton', karakteristikKain: 'Sangat sejuk, ringan, mudah bergerak, serat kain katun bernafas baik.', detailPayet: 'Kutubaru klasik dengan kancing bungkus rapi & bros etnik benang emas.', rekomendasiAcara: 'Hari Kartini, Wisuda, Upacara Adat, Acara Formal Kebudayaan', status: 'aktif' },
    { slug: 'kebaya-encim-peranakan', nama: 'Kebaya Encim Peranakan', harga: 600000, foto: 'Kebaya Encim Peranakan.png', fotoBelakang: 'Kebaya Encim Peranakan-belakang.jpg', deskripsi: 'Kebaya encim klasik berhias bordir halus nan cantik khas budaya Peranakan.', kainUtama: 'Katun Voile Silky Premium', furing: 'Unlined / Tanpa Furing Tebal', karakteristikKain: 'Kain katun halus, serat rapat, sejuk dipakai di cuaca hangat & iklim tropis.', detailPayet: 'Bordir keranjang & flora buatan tangan di sepanjang tepi bawah.', rekomendasiAcara: 'Kebaya Encim Day, Sangjit, Festival Budaya, Acara Semi-Formal', status: 'aktif' },
    { slug: 'kebaya-peplum-brokat', nama: 'Kebaya Peplum Brokat', harga: 700000, foto: 'Kebaya Peplum Brokat.png', fotoBelakang: 'Kebaya Peplum Brokat-belakang.jpg', deskripsi: 'Kebaya peplum feminin berhias renda brokat pilihan cocok untuk momen wisuda.', kainUtama: 'Brokat Tile Semi-Perancis & Organza Premium', furing: 'Furing Satin Maxmara Silk', karakteristikKain: 'Struktur peplum mengembang anggun di pinggang, memberi siluet tubuh ramping & tinggi.', detailPayet: 'Renda Guipure melingkar di pinggang & payet mutiara sintetis di pergelangan lengan.', rekomendasiAcara: 'Wisuda Universitas, Pengajian Formal, Kondangan, Syukuran', status: 'aktif' },
    { slug: 'kebaya-songket-palembang', nama: 'Kebaya Songket Palembang', harga: 800000, foto: 'Kebaya Songket Palembang.jpg', fotoBelakang: 'Kebaya Songket Palembang-belakang.jpg', deskripsi: 'Kebaya mewah dengan kain songket Palembang asli bermotif emas yang indah memukau.', kainUtama: 'Tenun Songket Palembang Benang Emas & Taffeta Silk', furing: 'Tricot & Full Furing Asahi Silky', karakteristikKain: 'Tekstur kain tegas & bermotif timbul emas megah, sangat mewah dan regal.', detailPayet: 'Motif Songket Lepus benang emas otentik dengan hiasan bros dada khas Sumatera.', rekomendasiAcara: 'Resepsi Pernikahan Adat Sumatera, Akad Nikah, Gala Dinner VIP', status: 'aktif' },
    { slug: 'kebaya-sutra-solo', nama: 'Kebaya Sutra Solo', harga: 950000, foto: 'Kebaya Sutra Solo.jpg', fotoBelakang: 'Kebaya Sutra Solo-belakang.jpg', deskripsi: 'Kebaya sutra pilihan khas Solo dengan tekstur lembut dan tampilan sangat elegan.', kainUtama: '100% Sutra Alam Solo (Natural Silk Premium)', furing: 'Sutra Habutai Furing', karakteristikKain: 'Super halus, kilau sutra alami memukau, jatuh sangat mengalir (flowy), sangat sejuk.', detailPayet: 'Hand-painted motif batik sutra halus dengan aksen sentuhan prada emas tipis.', rekomendasiAcara: 'Royal Wedding Guest, VIP Official Reception, Gala Night', status: 'aktif' },
    { slug: 'kebaya-batik-tulis-jawa', nama: 'Kebaya Batik Tulis Jawa', harga: 720000, foto: null, fotoBelakang: null, deskripsi: 'Kebaya berbahan dasar batik tulis tangan bermotif Jawa klasik yang otentik.', kainUtama: 'Katun Primissima Batik Tulis', furing: 'Katun Hero Super', karakteristikKain: 'Halus, berbobot mantap, pewarnaan malam alami khas batik klasik Jawa.', detailPayet: 'Motif batik tulis Parang Gurdo tradisional dengan sentuhan kerah modern.', rekomendasiAcara: 'Acara Resmi Kebudayaan, Seminar Nasional, Event Diplomatik', status: 'segera' },
    { slug: 'kebaya-brokat-evening', nama: 'Kebaya Brokat Evening', harga: 1000000, foto: null, fotoBelakang: null, deskripsi: 'Kebaya eksklusif untuk gala malam formal dengan material brokat premium pilihan.', kainUtama: 'Heavy French Corded Lace & Crepe Silk', furing: 'Full Satin Silk', karakteristikKain: 'Kain bertekstur tegas, pembentukan pola presisi, berkilau di bawah lampu malam.', detailPayet: 'Full payet jepang di sekeliling bodice dan lengan.', rekomendasiAcara: 'Gala Night Dinner, Awarding Night, Formal Party', status: 'segera' },
    { slug: 'kebaya-tenun-ntt', nama: 'Kebaya Tenun NTT', harga: 680000, foto: null, fotoBelakang: null, deskripsi: 'Kebaya tenun ikat asli dari Nusa Tenggara Timur penuh warna dan kekayaan budaya.', kainUtama: 'Tenun Ikat Handwoven NTT', furing: 'Katun Prima', karakteristikKain: 'Tenunan benang katun tebal bermotif etnik khas pulau NTT.', detailPayet: 'Pewarnaan benang alami dengan garis tenun etnik kontras.', rekomendasiAcara: 'Festival Etnik, Pameran Seni, Acara Kebudayaan', status: 'segera' },
    { slug: 'kebaya-batik-madura', nama: 'Kebaya Batik Madura', harga: 600000, foto: null, fotoBelakang: null, deskripsi: 'Kebaya khas Madura motif batik cerah penuh semangat.', kainUtama: 'Katun Bergas Batik Madura Pesisiran', furing: 'Katun Adem', karakteristikKain: 'Katun tebal sejuk dengan perpaduan warna berani khas pesisir Madura.', detailPayet: 'Motif flora fauna Madura bergaris tegas.', rekomendasiAcara: 'Acara Santai Formal, Festival Seni, Parade Busana Etnik', status: 'segera' },
    { slug: 'kebaya-bordiran-sumatera', nama: 'Kebaya Bordiran Sumatera', harga: 750000, foto: null, fotoBelakang: null, deskripsi: 'Kebaya Sumatera berhias bordiran tangan benang emas.', kainUtama: 'Katun Balotelli Premium & Benang Emas Metallic', furing: 'Furing Katun', karakteristikKain: 'Bordir tebal benang emas di atas kain bertekstur struktural.', detailPayet: 'Bordiran tangan motif suji caia khas Sumatera.', rekomendasiAcara: 'Resepsi Pernikahan, Acara Adat Sumatera, Perayaan Resmi', status: 'segera' },
  ];

  for (const data of katalog) {
    await prisma.kebaya.upsert({
      where: { slug: data.slug },
      update: data,
      create: data,
    });
  }

  console.log(`✅ KebayaSeeder: ${katalog.length} kebaya berhasil dibuat/diperbarui.`);
}

async function main() {
  console.log('🌱 Menjalankan seeder...');
  await seedUsers();
  await seedKebaya();
  await seedTransaksi();
  console.log('✅ Semua seeder selesai!');
}

main()
  .catch((e) => {
    console.error('❌ Seeder error:', e);
    process.exit(1);
  })
  .finally(() => prisma.$disconnect());
