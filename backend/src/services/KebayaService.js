/**
 * KebayaService - Prisma-based (migrated from hardcoded + JSON)
 */
const prisma = require('../prisma');

class KebayaService {
  async getAll() {
    return prisma.kebaya.findMany({ orderBy: { id: 'asc' } });
  }

  async getPublic() {
    return prisma.kebaya.findMany({
      where: { status: { in: ['aktif', 'segera'] } },
      orderBy: { id: 'asc' },
    });
  }

  async findBySlug(slug) {
    return prisma.kebaya.findUnique({ where: { slug } });
  }

  async create(data) {
    const slug = (data.nama || 'kebaya-' + Date.now())
      .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    return prisma.kebaya.create({ data: { ...data, slug } });
  }

  async update(slug, data) {
    // If nama changed, regenerate slug
    if (data.nama) {
      data.slug = data.nama.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
    return prisma.kebaya.update({ where: { slug }, data });
  }

  async delete(slug) {
    return prisma.kebaya.delete({ where: { slug } });
  }

  async setStatus(slug, status) {
    const allowed = ['aktif', 'nonaktif', 'perbaikan', 'segera'];
    if (!allowed.includes(status)) return null;
    return prisma.kebaya.update({ where: { slug }, data: { status } });
  }

  async cycleStatus(slug) {
    const k = await prisma.kebaya.findUnique({ where: { slug } });
    if (!k) return null;
    const next = { aktif: 'nonaktif', nonaktif: 'perbaikan', perbaikan: 'aktif', segera: 'aktif' }[k.status] || 'aktif';
    await prisma.kebaya.update({ where: { slug }, data: { status: next } });
    return next;
  }
}

module.exports = KebayaService;
