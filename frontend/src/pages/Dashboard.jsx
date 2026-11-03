import { useState, useEffect, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../services/AuthContext';
import api from '../services/api';
import { cfg } from '../config';

const TABS = [
  { key: 'pemesanan', label: 'Pemesanan' },
  { key: 'katalog', label: 'Katalog' },
  { key: 'fitting', label: 'Fitting' },
  { key: 'laporan', label: 'Laporan' },
  { key: 'pengembalian', label: 'Pengembalian' },
  { key: 'konfigurasi', label: 'Konfigurasi', superOnly: true },
  { key: 'log_audit', label: 'Log Audit', superOnly: true },
];

const ALL_STATUSES = [
  'Menunggu Pembayaran DP',
  'Menunggu Konfirmasi',
  'Telah Dikonfirmasi',
  'Menunggu Fitting',
  'Sedang Fitting',
  'Kebaya Telah Diambil',
  'Mengajukan Pengembalian',
  'Telah Dikembalikan',
  'Dibatalkan',
];

const STATUS_COLORS = {
  'Menunggu Pembayaran DP': 'bg-yellow-100 text-yellow-800',
  'Menunggu Konfirmasi': 'bg-orange-100 text-orange-800',
  'Telah Dikonfirmasi': 'bg-blue-100 text-blue-800',
  'Menunggu Fitting': 'bg-purple-100 text-purple-800',
  'Sedang Fitting': 'bg-indigo-100 text-indigo-800',
  'Kebaya Telah Diambil': 'bg-cyan-100 text-cyan-800',
  'Mengajukan Pengembalian': 'bg-pink-100 text-pink-800',
  'Telah Dikembalikan': 'bg-green-100 text-green-800',
  'Dibatalkan': 'bg-red-100 text-red-800',
};

const KEBAYA_STATUS_COLORS = {
  aktif: 'bg-green-100 text-green-800',
  nonaktif: 'bg-gray-200 text-gray-600',
  perbaikan: 'bg-yellow-100 text-yellow-800',
  segera: 'bg-red-100 text-red-800',
};

function fmtRp(n) {
  return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
}

function fmtDate(d) {
  if (!d) return '-';
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// ═════════════════════════════════════════════
//  AUDIT LOG (hardcoded sample entries)
// ═════════════════════════════════════════════
function buildAuditLogs(userName) {
  const now = new Date();
  const fmt = (d) => d.toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
  return [
    { timestamp: fmt(now), user: userName || 'Super Admin', role: 'SUPER_ADMIN', action: 'Akses Dashboard & Verifikasi Sistem Log Audit', ip: '127.0.0.1', level: 'SECURITY' },
    { timestamp: fmt(new Date(now - 12 * 60000)), user: 'System Engine', role: 'SYSTEM', action: 'Webhook Midtrans Payment Notification Listener Active', ip: '127.0.0.1', level: 'INFO' },
    { timestamp: fmt(new Date(now - 35 * 60000)), user: 'Customer AI Engine', role: 'OPAL_AI', action: 'Proses Recolor Canvas AI: Kebaya Modern Brukat (Sage Green)', ip: '127.0.0.1', level: 'AI_LOG' },
    { timestamp: fmt(new Date(now - 2 * 3600000)), user: 'Admin Operasional', role: 'ADMIN', action: 'Pembaruan Status Transaksi TRX-89DEB76F -> Telah Dikembalikan', ip: '127.0.0.1', level: 'AUDIT' },
    { timestamp: fmt(new Date(now - 5 * 3600000)), user: `Owner ${cfg.APP_NAME}`, role: 'OWNER', action: 'Cetak PDF Rekapitulasi Laporan Penjualan', ip: '127.0.0.1', level: 'REPORT' },
    { timestamp: fmt(new Date(now - 86400000)), user: 'Super Admin', role: 'SUPER_ADMIN', action: 'Konfigurasi Midtrans Snap API Key updated (Sandbox Mode)', ip: '127.0.0.1', level: 'CONFIG' },
  ];
}

const LEVEL_COLORS = {
  SECURITY: 'bg-red-100 text-red-700',
  INFO: 'bg-blue-100 text-blue-700',
  AI_LOG: 'bg-purple-100 text-purple-700',
  AUDIT: 'bg-yellow-100 text-yellow-700',
  REPORT: 'bg-green-100 text-green-700',
  CONFIG: 'bg-orange-100 text-orange-700',
};

// ═════════════════════════════════════════════
//  DASHBOARD COMPONENT
// ═════════════════════════════════════════════
export default function Dashboard() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const isSuperAdmin = user?.role === 'super_admin';

  const [activeTab, setActiveTab] = useState('pemesanan');
  const [data, setData] = useState({ daftarTransaksi: [], pagination: {}, totalPendapatan: 0, kebayaDipesan: 0, totalKoleksi: 0 });
  const [kebaya, setKebaya] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  // Filters (for laporan)
  const [filters, setFilters] = useState({ year: '', month: '', week: '', start_date: '', end_date: '', status: '' });

  // Config state
  const [config, setConfig] = useState({
    midtrans_server_key: '', midtrans_client_key: '', midtrans_is_production: 'false',
    qris_static_string: '', qris_merchant_name: cfg.APP_NAME,
  });
  const [configMsg, setConfigMsg] = useState('');

  // ── Fetch dashboard data ──
  const fetchDashboard = useCallback(async (p = page, f = filters) => {
    setLoading(true);
    try {
      const params = { page: p };
      Object.entries(f).forEach(([k, v]) => { if (v) params[k] = v; });
      const res = await api.get('/transaksi/dashboard', { params });
      setData(res.data);
    } catch { /* ignore */ }
    setLoading(false);
  }, [page, filters]);

  const fetchKebaya = useCallback(async () => {
    try {
      const res = await api.get('/kebaya/all');
      setKebaya(res.data.kebaya || []);
    } catch { setKebaya([]); }
  }, []);

  useEffect(() => { fetchDashboard(); }, [page]); // eslint-disable-line
  useEffect(() => { if (activeTab === 'katalog') fetchKebaya(); }, [activeTab]); // eslint-disable-line

  // ── Actions ──
  const updateStatus = async (id, status) => {
    try {
      await api.post(`/transaksi/${id}/status`, { status });
      fetchDashboard();
    } catch { /* ignore */ }
  };

  const deleteTransaksi = async (id) => {
    if (!confirm('Hapus transaksi ini?')) return;
    try {
      await api.delete(`/transaksi/${id}`);
      fetchDashboard();
    } catch { /* ignore */ }
  };

  const toggleKebaya = async (slug) => {
    try {
      await api.post(`/kebaya/${slug}/toggle`);
      fetchKebaya();
    } catch { /* ignore */ }
  };

  const deleteKebaya = async (slug) => {
    if (!confirm('Hapus kebaya ini? Data tidak bisa dikembalikan.')) return;
    try {
      await api.delete(`/kebaya/${slug}`);
      fetchKebaya();
    } catch { /* ignore */ }
  };

  const saveKebaya = async (formData, slug) => {
    try {
      if (slug) {
        await api.put(`/kebaya/${slug}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
      } else {
        await api.post('/kebaya', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
      }
      fetchKebaya();
      return true;
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menyimpan kebaya.');
      return false;
    }
  };

  const applyFilters = () => { setPage(1); fetchDashboard(1, filters); };

  const saveConfig = async () => {
    setConfigMsg('');
    try {
      const res = await api.post('/admin/config/api-ai', config);
      setConfigMsg(res.data.message || 'Berhasil disimpan!');
    } catch (err) {
      setConfigMsg(err.response?.data?.message || 'Gagal menyimpan konfigurasi.');
    }
  };

  const downloadPdf = () => {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => { if (v) params.set(k, v); });
    window.open(`/api/transaksi/laporan/pdf?${params.toString()}`, '_blank');
  };

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  // ── Derived lists ──
  const allTransaksi = data.allLaporanTransaksi || data.daftarTransaksi || [];
  const fittingList = allTransaksi.filter(t => ['Menunggu Fitting', 'Sedang Fitting'].includes(t.status));
  const pengembalianList = allTransaksi.filter(t => t.status === 'Mengajukan Pengembalian');
  const pagination = data.pagination || {};

  // ═════════════════════════════════════════════
  //  RENDER
  // ═════════════════════════════════════════════
  return (
    <div className="flex min-h-screen bg-[#FAF8F5]">
      {/* ─── SIDEBAR ─── */}
      <aside className="w-64 bg-[#2C1A0E] text-white flex flex-col shrink-0">
        <div className="p-6 border-b border-white/10">
          <h1 className="text-xl font-bold tracking-wide">{cfg.APP_NAME}</h1>
          <p className="text-xs text-white/50 mt-1">Kebaya Rental Admin</p>
        </div>

        <nav className="flex-1 py-4 space-y-1 px-3">
          {TABS.filter(t => !t.superOnly || isSuperAdmin).map(t => (
            <button
              key={t.key}
              onClick={() => setActiveTab(t.key)}
              className={`w-full text-left px-4 py-2.5 rounded-lg text-sm transition ${
                activeTab === t.key ? 'bg-white/20 font-semibold' : 'hover:bg-white/10'
              }`}
            >
              {t.label}
            </button>
          ))}

          <hr className="border-white/10 my-3" />

          {isSuperAdmin && (
            <Link to="/admin/users" className="block px-4 py-2.5 rounded-lg text-sm hover:bg-white/10 transition">
              User Management
            </Link>
          )}
          <Link to="/admin/chat" className="block px-4 py-2.5 rounded-lg text-sm hover:bg-white/10 transition">
            Chat Inbox
          </Link>
        </nav>

        <div className="p-4 border-t border-white/10">
          <button onClick={handleLogout} className="w-full py-2 rounded-lg bg-red-600/80 hover:bg-red-600 text-sm font-medium transition">
            Logout
          </button>
        </div>
      </aside>

      {/* ─── MAIN ─── */}
      <main className="flex-1 overflow-auto">
        {/* TOPBAR */}
        <header className="bg-white shadow-sm px-8 py-5 flex items-center justify-between">
          <div>
            <h2 className="text-lg font-bold text-[#2C1A0E]">Selamat Datang, {user?.name || 'Admin'}</h2>
            <span className={`inline-block mt-1 text-xs px-2.5 py-0.5 rounded-full font-medium ${
              user?.role === 'super_admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'
            }`}>
              {user?.role?.replace('_', ' ').toUpperCase() || 'ADMIN'}
            </span>
          </div>
        </header>

        {/* STATS */}
        <div className="px-8 py-6 grid grid-cols-1 md:grid-cols-3 gap-5">
          <StatCard label="Total Pendapatan" value={fmtRp(data.totalPendapatan)} color="emerald" />
          <StatCard label="Kebaya Dipesan" value={data.kebayaDipesan} color="blue" />
          <StatCard label="Total Koleksi" value={data.totalKoleksi} color="amber" />
        </div>

        {/* CONTENT */}
        <div className="px-8 pb-10">
          {loading && <div className="text-center py-10 text-gray-400">Memuat data...</div>}

          {!loading && activeTab === 'pemesanan' && (
            <TabPemesanan
              rows={data.daftarTransaksi}
              pagination={pagination}
              page={page}
              setPage={setPage}
              onStatusChange={updateStatus}
              onDelete={deleteTransaksi}
            />
          )}

          {!loading && activeTab === 'katalog' && (
            <TabKatalog items={kebaya} onToggle={toggleKebaya} onDelete={deleteKebaya} onSave={saveKebaya} />
          )}

          {!loading && activeTab === 'fitting' && (
            <TabFitting rows={fittingList} onStatusChange={updateStatus} />
          )}

          {!loading && activeTab === 'laporan' && (
            <TabLaporan
              filters={filters}
              setFilters={setFilters}
              onApply={applyFilters}
              data={data}
              allTransaksi={allTransaksi}
              onDownloadPdf={downloadPdf}
            />
          )}

          {!loading && activeTab === 'pengembalian' && (
            <TabPengembalian rows={pengembalianList} onStatusChange={updateStatus} />
          )}

          {!loading && activeTab === 'konfigurasi' && isSuperAdmin && (
            <TabKonfigurasi config={config} setConfig={setConfig} onSave={saveConfig} msg={configMsg} />
          )}

          {!loading && activeTab === 'log_audit' && isSuperAdmin && (
            <TabLogAudit userName={user?.name} />
          )}
        </div>
      </main>
    </div>
  );
}

// ═════════════════════════════════════════════
//  SUB-COMPONENTS
// ═════════════════════════════════════════════

function StatCard({ label, value, color }) {
  const bg = { emerald: 'bg-emerald-50 border-emerald-200', blue: 'bg-blue-50 border-blue-200', amber: 'bg-amber-50 border-amber-200' }[color];
  const txt = { emerald: 'text-emerald-700', blue: 'text-blue-700', amber: 'text-amber-700' }[color];
  return (
    <div className={`rounded-xl border p-5 shadow-sm ${bg}`}>
      <p className="text-sm text-gray-500">{label}</p>
      <p className={`text-2xl font-bold mt-1 ${txt}`}>{value}</p>
    </div>
  );
}

function StatusBadge({ status }) {
  return (
    <span className={`inline-block text-xs px-2.5 py-1 rounded-full font-medium whitespace-nowrap ${STATUS_COLORS[status] || 'bg-gray-100 text-gray-700'}`}>
      {status}
    </span>
  );
}

// ── TAB: Pemesanan ──
function TabPemesanan({ rows, pagination, page, setPage, onStatusChange, onDelete }) {
  return (
    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
      <div className="px-6 py-4 border-b"><h3 className="font-bold text-[#2C1A0E]">Daftar Pemesanan</h3></div>
      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              {['No', 'Kode Transaksi', 'Kebaya', 'Customer', 'Ukuran', 'Tanggal Sewa', 'Status', 'Harga', 'Aksi'].map(h => (
                <th key={h} className="px-4 py-3 text-left font-medium">{h}</th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y">
            {rows.length === 0 && (
              <tr><td colSpan={9} className="text-center py-10 text-gray-400">Tidak ada data transaksi.</td></tr>
            )}
            {rows.map((t, i) => (
              <tr key={t.id} className="hover:bg-gray-50 transition">
                <td className="px-4 py-3 text-gray-500">{(page - 1) * (pagination.perPage || 15) + i + 1}</td>
                <td className="px-4 py-3 font-mono text-xs">{t.kodeTransaksi}</td>
                <td className="px-4 py-3">{t.namaKebaya}</td>
                <td className="px-4 py-3">
                  <div>{t.namaUser}</div>
                  <div className="text-xs text-gray-400">{t.noWa}</div>
                </td>
                <td className="px-4 py-3">{t.ukuran}</td>
                <td className="px-4 py-3 whitespace-nowrap">{fmtDate(t.tanggalSewa)}</td>
                <td className="px-4 py-3"><StatusBadge status={t.status} /></td>
                <td className="px-4 py-3 whitespace-nowrap font-medium">{fmtRp(t.totalHarga)}</td>
                <td className="px-4 py-3">
                  <div className="flex items-center gap-2">
                    <select
                      className="text-xs border rounded px-2 py-1 bg-white"
                      value={t.status}
                      onChange={e => onStatusChange(t.id, e.target.value)}
                    >
                      {ALL_STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
                    </select>
                    <button onClick={() => onDelete(t.id)} className="text-red-500 hover:text-red-700 text-xs font-medium">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      {pagination.totalPages > 1 && (
        <div className="px-6 py-4 border-t flex items-center justify-between text-sm">
          <span className="text-gray-500">Hal {pagination.page} dari {pagination.totalPages} ({pagination.total} data)</span>
          <div className="flex gap-2">
            <button disabled={page <= 1} onClick={() => setPage(page - 1)}
              className="px-3 py-1 rounded border text-sm disabled:opacity-40 hover:bg-gray-50 transition">Prev</button>
            <button disabled={page >= pagination.totalPages} onClick={() => setPage(page + 1)}
              className="px-3 py-1 rounded border text-sm disabled:opacity-40 hover:bg-gray-50 transition">Next</button>
          </div>
        </div>
      )}
    </div>
  );
}

// ── TAB: Katalog ──
function TabKatalog({ items, onToggle, onDelete, onSave }) {
  const [showModal, setShowModal] = useState(false);
  const [editing, setEditing] = useState(null); // null = add, object = edit
  const [form, setForm] = useState({});
  const [fotoFile, setFotoFile] = useState(null);
  const [fotoBelakangFile, setFotoBelakangFile] = useState(null);
  const [saving, setSaving] = useState(false);

  const openAdd = () => { setEditing(null); setForm({ nama: '', deskripsi: '', harga: '', kain_utama: '', furing: '', karakteristik_kain: '', detail_payet: '', rekomendasi_acara: '', status: 'aktif' }); setFotoFile(null); setFotoBelakangFile(null); setShowModal(true); };
  const openEdit = (k) => { setEditing(k); setForm({ nama: k.nama, deskripsi: k.deskripsi || '', harga: k.harga || '', kain_utama: k.kain_utama || '', furing: k.furing || '', karakteristik_kain: k.karakteristik_kain || '', detail_payet: k.detail_payet || '', rekomendasi_acara: k.rekomendasi_acara || '', status: k.status || 'aktif' }); setFotoFile(null); setFotoBelakangFile(null); setShowModal(true); };
  const close = () => { setShowModal(false); setEditing(null); };

  const handleSave = async () => {
    if (!form.nama) return alert('Nama kebaya wajib diisi.');
    setSaving(true);
    const fd = new FormData();
    Object.entries(form).forEach(([k, v]) => fd.append(k, v));
    if (fotoFile) fd.append('foto', fotoFile);
    if (fotoBelakangFile) fd.append('foto_belakang', fotoBelakangFile);
    const ok = await onSave(fd, editing?.slug || null);
    setSaving(false);
    if (ok) close();
  };

  const set = (k) => (e) => setForm(f => ({ ...f, [k]: e.target.value }));
  const inputCls = 'w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition';
  const labelCls = 'block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1';

  return (
    <div>
      <div className="flex items-center justify-between mb-4">
        <h3 className="font-bold text-[#2C1A0E]">Katalog Kebaya</h3>
        <button onClick={openAdd} className="px-4 py-2 rounded-lg bg-amber-400 text-[#2C1A0E] text-sm font-bold hover:bg-amber-500 transition">+ Tambah Kebaya</button>
      </div>

      {items.length === 0 && <p className="text-gray-400">Tidak ada data katalog.</p>}

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        {items.map(k => (
          <div key={k.slug} className="bg-white rounded-xl shadow-sm overflow-hidden border">
            <div className="aspect-[3/4] bg-stone-100 overflow-hidden">
              {k.foto ? (
                <img src={`/images/${k.foto}`} alt={k.nama} className="w-full h-full object-cover" />
              ) : (
                <div className="w-full h-full flex items-center justify-center text-stone-300 text-sm">Belum ada foto</div>
              )}
            </div>
            <div className="p-4 space-y-2">
              <h4 className="font-semibold text-sm text-[#2C1A0E] truncate">{k.nama}</h4>
              <p className="text-xs text-gray-500 line-clamp-2">{k.deskripsi}</p>
              <p className="text-amber-600 font-bold text-sm">Rp {(k.harga || 0).toLocaleString('id-ID')}</p>
              <div className="flex items-center justify-between gap-2">
                <span className={`text-xs px-2 py-0.5 rounded-full font-medium capitalize ${KEBAYA_STATUS_COLORS[k.status] || 'bg-gray-100 text-gray-600'}`}>
                  {k.status}
                </span>
                <div className="flex gap-1">
                  <button onClick={() => onToggle(k.slug)} title="Toggle Status" className="text-xs px-2 py-1 rounded bg-stone-100 hover:bg-stone-200 transition">
                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                  </button>
                  <button onClick={() => openEdit(k)} title="Edit" className="text-xs px-2 py-1 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  </button>
                  <button onClick={() => onDelete(k.slug)} title="Hapus" className="text-xs px-2 py-1 rounded bg-red-50 text-red-600 hover:bg-red-100 transition">
                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* ── Add/Edit Modal ── */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" onClick={close}>
          <div className="bg-[#FAF8F5] rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" onClick={e => e.stopPropagation()}>
            <div className="bg-[#2C1A0E] text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
              <h3 className="text-lg font-bold">{editing ? 'Edit Kebaya' : 'Tambah Kebaya Baru'}</h3>
              <button onClick={close} className="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            <div className="px-6 py-5 space-y-4">
              {/* Row 1: Nama + Harga */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label className={labelCls}>Nama Kebaya *</label><input className={inputCls} value={form.nama} onChange={set('nama')} placeholder="Nama kebaya" /></div>
                <div><label className={labelCls}>Harga Sewa / Hari (Rp)</label><input type="number" className={inputCls} value={form.harga} onChange={set('harga')} placeholder="850000" /></div>
              </div>

              {/* Deskripsi */}
              <div><label className={labelCls}>Deskripsi</label><textarea className={inputCls} rows={2} value={form.deskripsi} onChange={set('deskripsi')} placeholder="Deskripsi singkat kebaya" /></div>

              {/* Row 2: Kain + Furing */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label className={labelCls}>Kain Utama</label><input className={inputCls} value={form.kain_utama} onChange={set('kain_utama')} /></div>
                <div><label className={labelCls}>Furing</label><input className={inputCls} value={form.furing} onChange={set('furing')} /></div>
              </div>

              {/* Karakteristik + Payet */}
              <div><label className={labelCls}>Karakteristik Kain</label><textarea className={inputCls} rows={2} value={form.karakteristik_kain} onChange={set('karakteristik_kain')} /></div>
              <div><label className={labelCls}>Detail Payet</label><textarea className={inputCls} rows={2} value={form.detail_payet} onChange={set('detail_payet')} /></div>

              {/* Rekomendasi + Status */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label className={labelCls}>Rekomendasi Acara</label><input className={inputCls} value={form.rekomendasi_acara} onChange={set('rekomendasi_acara')} /></div>
                <div><label className={labelCls}>Status</label>
                  <select className={inputCls} value={form.status} onChange={set('status')}>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                    <option value="perbaikan">Perbaikan</option>
                    <option value="segera">Segera Hadir</option>
                  </select>
                </div>
              </div>

              {/* Foto Upload */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className={labelCls}>Foto Depan</label>
                  {editing?.foto && !fotoFile && <p className="text-xs text-stone-400 mb-1">Saat ini: {editing.foto}</p>}
                  <input type="file" accept="image/*" onChange={e => setFotoFile(e.target.files[0] || null)} className="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-amber-50 file:text-amber-700 file:font-medium file:cursor-pointer hover:file:bg-amber-100" />
                </div>
                <div>
                  <label className={labelCls}>Foto Belakang</label>
                  {editing?.foto_belakang && !fotoBelakangFile && <p className="text-xs text-stone-400 mb-1">Saat ini: {editing.foto_belakang}</p>}
                  <input type="file" accept="image/*" onChange={e => setFotoBelakangFile(e.target.files[0] || null)} className="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-amber-50 file:text-amber-700 file:font-medium file:cursor-pointer hover:file:bg-amber-100" />
                </div>
              </div>

              {/* Actions */}
              <div className="flex gap-3 pt-2">
                <button onClick={close} className="flex-1 py-2.5 rounded-lg border border-stone-300 text-stone-600 font-medium hover:bg-stone-100 transition">Batal</button>
                <button onClick={handleSave} disabled={saving} className="flex-1 py-2.5 rounded-lg bg-amber-400 text-[#2C1A0E] font-bold hover:bg-amber-500 transition disabled:opacity-50">
                  {saving ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Kebaya'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

// ── TAB: Fitting ──
function TabFitting({ rows, onStatusChange }) {
  return (
    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
      <div className="px-6 py-4 border-b"><h3 className="font-bold text-[#2C1A0E]">Daftar Fitting</h3></div>
      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              {['Kode', 'Kebaya', 'Customer', 'Ukuran', 'Status', 'Catatan', 'Aksi'].map(h => (
                <th key={h} className="px-4 py-3 text-left font-medium">{h}</th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y">
            {rows.length === 0 && (
              <tr><td colSpan={7} className="text-center py-10 text-gray-400">Tidak ada request fitting.</td></tr>
            )}
            {rows.map(t => (
              <tr key={t.id} className="hover:bg-gray-50">
                <td className="px-4 py-3 font-mono text-xs">{t.kodeTransaksi}</td>
                <td className="px-4 py-3">{t.namaKebaya}</td>
                <td className="px-4 py-3">{t.namaUser} <span className="text-xs text-gray-400">({t.noWa})</span></td>
                <td className="px-4 py-3">{t.ukuran}</td>
                <td className="px-4 py-3"><StatusBadge status={t.status} /></td>
                <td className="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">{t.catatanAdmin || '-'}</td>
                <td className="px-4 py-3">
                  <select className="text-xs border rounded px-2 py-1 bg-white" value={t.status}
                    onChange={e => onStatusChange(t.id, e.target.value)}>
                    <option value="Menunggu Fitting">Menunggu Fitting</option>
                    <option value="Sedang Fitting">Sedang Fitting</option>
                    <option value="Telah Dikonfirmasi">Telah Dikonfirmasi</option>
                    <option value="Kebaya Telah Diambil">Kebaya Telah Diambil</option>
                  </select>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

// ── TAB: Laporan ──
function TabLaporan({ filters, setFilters, onApply, data, allTransaksi, onDownloadPdf }) {
  const set = (k, v) => setFilters(prev => ({ ...prev, [k]: v }));

  const totalSelesai = allTransaksi.filter(t => t.status === 'Telah Dikembalikan').length;
  const totalBerjalan = allTransaksi.filter(t => ['Telah Dikonfirmasi', 'Sedang Fitting', 'Kebaya Telah Diambil'].includes(t.status)).length;
  const totalBelum = allTransaksi.filter(t => ['Menunggu Pembayaran DP', 'Menunggu Konfirmasi', 'Menunggu Fitting'].includes(t.status)).length;

  return (
    <div className="space-y-6">
      {/* Filters */}
      <div className="bg-white rounded-xl shadow-sm p-6">
        <h3 className="font-bold text-[#2C1A0E] mb-4">Filter Laporan</h3>
        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <div>
            <label className="block text-xs text-gray-500 mb-1">Tahun</label>
            <select className="w-full border rounded-lg px-3 py-2 text-sm" value={filters.year} onChange={e => set('year', e.target.value)}>
              <option value="">Semua</option>
              {[2024, 2025, 2026].map(y => <option key={y} value={y}>{y}</option>)}
            </select>
          </div>
          <div>
            <label className="block text-xs text-gray-500 mb-1">Bulan</label>
            <select className="w-full border rounded-lg px-3 py-2 text-sm" value={filters.month} onChange={e => set('month', e.target.value)}>
              <option value="">Semua</option>
              {['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'].map((m, i) => (
                <option key={i} value={i + 1}>{m}</option>
              ))}
            </select>
          </div>
          <div>
            <label className="block text-xs text-gray-500 mb-1">Minggu</label>
            <select className="w-full border rounded-lg px-3 py-2 text-sm" value={filters.week} onChange={e => set('week', e.target.value)}>
              <option value="">Semua</option>
              <option value="1">Minggu 1</option>
              <option value="2">Minggu 2</option>
              <option value="3">Minggu 3</option>
              <option value="4">Minggu 4</option>
            </select>
          </div>
          <div>
            <label className="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" className="w-full border rounded-lg px-3 py-2 text-sm" value={filters.start_date} onChange={e => set('start_date', e.target.value)} />
          </div>
          <div>
            <label className="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" className="w-full border rounded-lg px-3 py-2 text-sm" value={filters.end_date} onChange={e => set('end_date', e.target.value)} />
          </div>
          <div>
            <label className="block text-xs text-gray-500 mb-1">Status</label>
            <select className="w-full border rounded-lg px-3 py-2 text-sm" value={filters.status} onChange={e => set('status', e.target.value)}>
              <option value="">Semua</option>
              {ALL_STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>
        </div>
        <div className="mt-4 flex gap-3">
          <button onClick={onApply} className="px-5 py-2 rounded-lg bg-[#2C1A0E] text-white text-sm font-medium hover:bg-[#3d2517] transition">
            Terapkan Filter
          </button>
          <button onClick={onDownloadPdf} className="px-5 py-2 rounded-lg border border-[#2C1A0E] text-[#2C1A0E] text-sm font-medium hover:bg-[#2C1A0E]/5 transition">
            Download PDF
          </button>
        </div>
      </div>

      {/* Summary */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <StatCard label="Total Transaksi" value={allTransaksi.length} color="blue" />
        <StatCard label="Selesai" value={totalSelesai} color="emerald" />
        <StatCard label="Berjalan" value={totalBerjalan} color="amber" />
        <StatCard label="Menunggu" value={totalBelum} color="blue" />
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl shadow-sm overflow-hidden">
        <div className="px-6 py-4 border-b flex items-center justify-between">
          <h3 className="font-bold text-[#2C1A0E]">Data Laporan</h3>
          {data.periodeLabel && <span className="text-xs text-gray-500">Periode: {data.periodeLabel}</span>}
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
              <tr>
                {['No', 'Kode', 'Kebaya', 'Customer', 'Ukuran', 'Status', 'Harga', 'Tanggal'].map(h => (
                  <th key={h} className="px-4 py-3 text-left font-medium">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y">
              {allTransaksi.length === 0 && (
                <tr><td colSpan={8} className="text-center py-10 text-gray-400">Tidak ada data.</td></tr>
              )}
              {allTransaksi.map((t, i) => (
                <tr key={t.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3 text-gray-500">{i + 1}</td>
                  <td className="px-4 py-3 font-mono text-xs">{t.kodeTransaksi}</td>
                  <td className="px-4 py-3">{t.namaKebaya}</td>
                  <td className="px-4 py-3">{t.namaUser}</td>
                  <td className="px-4 py-3">{t.ukuran}</td>
                  <td className="px-4 py-3"><StatusBadge status={t.status} /></td>
                  <td className="px-4 py-3 font-medium">{fmtRp(t.totalHarga)}</td>
                  <td className="px-4 py-3 whitespace-nowrap">{fmtDate(t.createdAt)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ── TAB: Pengembalian ──
function TabPengembalian({ rows, onStatusChange }) {
  return (
    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
      <div className="px-6 py-4 border-b"><h3 className="font-bold text-[#2C1A0E]">Pengajuan Pengembalian</h3></div>
      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              {['Kode', 'Kebaya', 'Customer', 'WA', 'Tanggal Sewa', 'Status', 'Aksi'].map(h => (
                <th key={h} className="px-4 py-3 text-left font-medium">{h}</th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y">
            {rows.length === 0 && (
              <tr><td colSpan={7} className="text-center py-10 text-gray-400">Tidak ada pengajuan pengembalian.</td></tr>
            )}
            {rows.map(t => (
              <tr key={t.id} className="hover:bg-gray-50">
                <td className="px-4 py-3 font-mono text-xs">{t.kodeTransaksi}</td>
                <td className="px-4 py-3">{t.namaKebaya}</td>
                <td className="px-4 py-3">{t.namaUser}</td>
                <td className="px-4 py-3 text-xs">{t.noWa}</td>
                <td className="px-4 py-3 whitespace-nowrap">{fmtDate(t.tanggalSewa)}</td>
                <td className="px-4 py-3"><StatusBadge status={t.status} /></td>
                <td className="px-4 py-3">
                  <button onClick={() => onStatusChange(t.id, 'Telah Dikembalikan')}
                    className="text-xs px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700 transition">
                    Konfirmasi Dikembalikan
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

// ── TAB: Konfigurasi ──
function TabKonfigurasi({ config, setConfig, onSave, msg }) {
  const set = (k, v) => setConfig(prev => ({ ...prev, [k]: v }));

  return (
    <div className="max-w-2xl space-y-6">
      <div className="bg-white rounded-xl shadow-sm p-6">
        <h3 className="font-bold text-[#2C1A0E] mb-4">Konfigurasi Midtrans</h3>
        <div className="space-y-4">
          <div>
            <label className="block text-sm text-gray-600 mb-1">Server Key</label>
            <input className="w-full border rounded-lg px-4 py-2.5 text-sm" value={config.midtrans_server_key}
              onChange={e => set('midtrans_server_key', e.target.value)} placeholder="SB-Mid-server-..." />
          </div>
          <div>
            <label className="block text-sm text-gray-600 mb-1">Client Key</label>
            <input className="w-full border rounded-lg px-4 py-2.5 text-sm" value={config.midtrans_client_key}
              onChange={e => set('midtrans_client_key', e.target.value)} placeholder="SB-Mid-client-..." />
          </div>
          <div className="flex items-center gap-3">
            <label className="text-sm text-gray-600">Mode Production</label>
            <button
              onClick={() => set('midtrans_is_production', config.midtrans_is_production === 'true' ? 'false' : 'true')}
              className={`relative w-12 h-6 rounded-full transition ${config.midtrans_is_production === 'true' ? 'bg-green-500' : 'bg-gray-300'}`}
            >
              <span className={`absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform ${config.midtrans_is_production === 'true' ? 'translate-x-6' : 'translate-x-0.5'}`} />
            </button>
            <span className="text-xs text-gray-500">{config.midtrans_is_production === 'true' ? 'Production' : 'Sandbox'}</span>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm p-6">
        <h3 className="font-bold text-[#2C1A0E] mb-4">Konfigurasi QRIS</h3>
        <div className="space-y-4">
          <div>
            <label className="block text-sm text-gray-600 mb-1">QRIS Static String</label>
            <input className="w-full border rounded-lg px-4 py-2.5 text-sm" value={config.qris_static_string}
              onChange={e => set('qris_static_string', e.target.value)} placeholder="00020101021126..." />
          </div>
          <div>
            <label className="block text-sm text-gray-600 mb-1">Merchant Name</label>
            <input className="w-full border rounded-lg px-4 py-2.5 text-sm" value={config.qris_merchant_name}
              onChange={e => set('qris_merchant_name', e.target.value)} />
          </div>
        </div>
      </div>

      {msg && (
        <div className={`p-3 rounded-lg text-sm ${msg.includes('Gagal') ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'}`}>
          {msg}
        </div>
      )}

      <button onClick={onSave} className="px-6 py-2.5 rounded-lg bg-[#2C1A0E] text-white font-medium hover:bg-[#3d2517] transition">
        Simpan Konfigurasi
      </button>
    </div>
  );
}

// ── TAB: Log Audit ──
function TabLogAudit({ userName }) {
  const logs = buildAuditLogs(userName);

  return (
    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
      <div className="px-6 py-4 border-b"><h3 className="font-bold text-[#2C1A0E]">System & Audit Log</h3></div>
      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              {['Timestamp', 'User', 'Role', 'Action', 'IP', 'Level'].map(h => (
                <th key={h} className="px-4 py-3 text-left font-medium">{h}</th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y">
            {logs.map((l, i) => (
              <tr key={i} className="hover:bg-gray-50">
                <td className="px-4 py-3 text-xs whitespace-nowrap font-mono">{l.timestamp}</td>
                <td className="px-4 py-3">{l.user}</td>
                <td className="px-4 py-3 text-xs font-medium">{l.role}</td>
                <td className="px-4 py-3 text-xs">{l.action}</td>
                <td className="px-4 py-3 text-xs font-mono">{l.ip}</td>
                <td className="px-4 py-3">
                  <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${LEVEL_COLORS[l.level] || 'bg-gray-100 text-gray-600'}`}>
                    {l.level}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
