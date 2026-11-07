/**
 * KebayaService - Prisma-based (migrated from hardcoded + JSON)
 */
const prisma = require('../prisma');

const ACTIVE_RENTAL_STATUSES = [
  'Menunggu Pembayaran DP',
  'Menunggu Konfirmasi',
  'Telah Dikonfirmasi',
  'Menunggu Fitting',
  'Sedang Fitting',
  'Kebaya Telah Diambil',
  'Mengajukan Pengembalian',
];

class KebayaService {
  async enrichWithStock(kebayaList) {
    if (!kebayaList || kebayaList.length === 0) return [];

    const activeRentals = await prisma.transaksiSewa.groupBy({
      by: ['namaKebaya'],
      where: {
        status: { in: ACTIVE_RENTAL_STATUSES },
      },
      _count: {
        id: true,
      },
    });

    const rentalMap = {};
    activeRentals.forEach((r) => {
      rentalMap[r.namaKebaya] = r._count.id;
    });

    return kebayaList.map((k) => {
      const totalStok = k.stok ?? 1;
      const stokDisewa = rentalMap[k.nama] || 0;
      const stokTersedia = Math.max(0, totalStok - stokDisewa);

      let displayStatus = k.status;
      let tersedia = k.status === 'aktif';
      if (k.status === 'aktif' && stokTersedia <= 0) {
        displayStatus = 'disewa';
        tersedia = false;
      }

      return {
        ...k,
        stok: totalStok,
        stokDisewa,
        stokTersedia,
        tersedia,
        displayStatus,
      };
    });
  }

  async getAll() {
    const list = await prisma.kebaya.findMany({ orderBy: { id: 'asc' } });
    return this.enrichWithStock(list);
  }

  async getPublic() {
    const list = await prisma.kebaya.findMany({
      where: { status: { in: ['aktif', 'segera'] } },
      orderBy: { id: 'asc' },
    });
    return this.enrichWithStock(list);
  }

  async findBySlug(slug) {
    const k = await prisma.kebaya.findUnique({ where: { slug } });
    if (!k) return null;
    const [enriched] = await this.enrichWithStock([k]);
    return enriched;
  }

  async create(data) {
    const slug = (data.nama || 'kebaya-' + Date.now())
      .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    const created = await prisma.kebaya.create({ data: { ...data, slug } });
    const [enriched] = await this.enrichWithStock([created]);
    return enriched;
  }

  async update(slug, data) {
    // If nama changed, regenerate slug
    if (data.nama) {
      data.slug = data.nama.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
    const updated = await prisma.kebaya.update({ where: { slug }, data });
    const [enriched] = await this.enrichWithStock([updated]);
    return enriched;
  }

  async delete(slug) {
    return prisma.kebaya.delete({ where: { slug } });
  }

  async setStatus(slug, status, { createdById, catatan } = {}) {
    const allowed = ['aktif', 'nonaktif', 'perbaikan', 'segera'];
    if (!allowed.includes(status)) return null;

    const existing = await prisma.kebaya.findUnique({ where: { slug } });
    if (!existing) return null;

    const updated = await prisma.kebaya.update({ where: { slug }, data: { status } });

    if (existing.status !== status) {
      await prisma.kebayaAvailabilityHistory.create({
        data: {
          kebayaId: existing.id,
          statusLama: existing.status,
          statusBaru: status,
          catatan: catatan || null,
          createdById: createdById || null,
        },
      });
    }

    return updated;
  }

  async cycleStatus(slug, { createdById, catatan } = {}) {
    const k = await prisma.kebaya.findUnique({ where: { slug } });
    if (!k) return null;
    const next = { aktif: 'nonaktif', nonaktif: 'perbaikan', perbaikan: 'aktif', segera: 'aktif' }[k.status] || 'aktif';

    await prisma.kebaya.update({ where: { slug }, data: { status: next } });

    await prisma.kebayaAvailabilityHistory.create({
      data: {
        kebayaId: k.id,
        statusLama: k.status,
        statusBaru: next,
        catatan: catatan || null,
        createdById: createdById || null,
      },
    });

    return next;
  }
}

module.exports = KebayaService;
