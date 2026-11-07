import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../services/AuthContext';
import api from '../services/api';

const roleBadge = {
  super_admin: 'bg-purple-100 text-purple-800 border-purple-200',
  admin: 'bg-blue-100 text-blue-800 border-blue-200',
  owner: 'bg-amber-100 text-amber-800 border-amber-200',
};

const emptyUser = { name: '', email: '', role: 'admin', password: '' };

export default function UserManagement() {
  const { user: currentUser } = useAuth();
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [modal, setModal] = useState(null); // null | 'add' | 'edit'
  const [form, setForm] = useState(emptyUser);
  const [editId, setEditId] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  const fetchUsers = useCallback(async () => {
    setLoading(true);
    try {
      const res = await api.get('/admin/users');
      const list = res.data?.users || res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setUsers(Array.isArray(list) ? list : []);
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal memuat data pengguna sistem.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchUsers();
  }, [fetchUsers]);

  const openAdd = () => {
    setForm(emptyUser);
    setEditId(null);
    setError('');
    setSuccess('');
    setModal('add');
  };

  const openEdit = (u) => {
    setForm({ name: u.name, email: u.email, role: u.role, password: '' });
    setEditId(u.id);
    setError('');
    setSuccess('');
    setModal('edit');
  };

  const closeModal = () => {
    setModal(null);
    setForm(emptyUser);
    setEditId(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setSubmitting(true);
    try {
      if (modal === 'add') {
        await api.post('/admin/users', form);
        setSuccess('Pengguna baru berhasil ditambahkan.');
      } else {
        const payload = { ...form };
        if (!payload.password) delete payload.password;
        await api.put(`/admin/users/${editId}`, payload);
        setSuccess('Data pengguna berhasil diperbarui.');
      }
      closeModal();
      await fetchUsers();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal menyimpan data pengguna.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (u) => {
    if (currentUser?.id === u.id) {
      alert('Anda tidak dapat menghapus akun Anda sendiri.');
      return;
    }
    if (!confirm(`Hapus pengguna "${u.name}" (${u.email})? Tindakan ini tidak dapat dibatalkan.`)) return;
    setError('');
    setSuccess('');
    try {
      await api.delete(`/admin/users/${u.id}`);
      setSuccess(`Pengguna "${u.name}" berhasil dihapus.`);
      await fetchUsers();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal menghapus pengguna.');
    }
  };

  const set = (key) => (e) => setForm((p) => ({ ...p, [key]: e.target.value }));

  return (
    <div className="min-h-screen bg-[#F8F9FA] text-stone-800 antialiased">
      {/* ── Top Header Bar ── */}
      <header className="bg-[#2C1A0E] text-white px-6 py-3.5 flex items-center justify-between border-b border-white/10 shrink-0">
        <div className="flex items-center gap-3">
          <Link
            to="/dashboard"
            className="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold flex items-center gap-1.5 transition"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali ke Dashboard
          </Link>
          <div className="h-4 w-px bg-white/20" />
          <h1 className="text-base font-bold">Manajemen Pengguna Sistem</h1>
        </div>
        <div className="text-xs text-white/70">
          Super Admin: <span className="font-bold text-white">{currentUser?.name || 'Super Admin'}</span>
        </div>
      </header>

      {/* ── Main Container ── */}
      <div className="max-w-5xl mx-auto py-8 px-4 sm:px-6">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
          <div>
            <h2 className="text-xl font-bold text-stone-900">Daftar Akun Pengelola</h2>
            <p className="text-xs text-stone-500 mt-0.5">
              Kelola akses akun Super Admin, Admin Operasional, dan Owner Butik Mayangsari.
            </p>
          </div>
          <button
            onClick={openAdd}
            className="px-4 py-2.5 bg-[#2C1A0E] text-white text-xs font-bold rounded-lg hover:bg-[#3D2518] shadow-xs flex items-center gap-2 transition"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" /></svg>
            Tambah Pengguna Baru
          </button>
        </div>

        {error && (
          <div className="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center justify-between">
            <span>{error}</span>
            <button onClick={() => setError('')} className="font-bold text-sm px-1 leading-none">&times;</button>
          </div>
        )}

        {success && (
          <div className="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
            <span>{success}</span>
            <button onClick={() => setSuccess('')} className="font-bold text-sm px-1 leading-none">&times;</button>
          </div>
        )}

        {/* Users Table */}
        <div className="bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
          {loading ? (
            <div className="p-12 text-center text-xs text-stone-400">Memuat data pengguna...</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-xs">
                <thead>
                  <tr className="bg-stone-50 text-stone-600 border-b border-stone-200 text-left font-semibold">
                    <th className="px-5 py-3.5">Nama Petugas</th>
                    <th className="px-5 py-3.5">Email Akses</th>
                    <th className="px-5 py-3.5">Hak Akses (Role)</th>
                    <th className="px-5 py-3.5">Terdaftar Sejak</th>
                    <th className="px-5 py-3.5 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-stone-100">
                  {users.map((u) => {
                    const isSelf = currentUser?.id === u.id;
                    return (
                      <tr key={u.id} className="hover:bg-stone-50/70 transition">
                        <td className="px-5 py-4 font-bold text-stone-900">
                          <div className="flex items-center gap-2">
                            <span>{u.name}</span>
                            {isSelf && (
                              <span className="text-[10px] bg-stone-200 text-stone-700 font-semibold px-2 py-0.5 rounded-full">
                                Akun Anda
                              </span>
                            )}
                          </div>
                        </td>
                        <td className="px-5 py-4 text-stone-600 font-mono text-[11px]">{u.email}</td>
                        <td className="px-5 py-4">
                          <span className={`px-2.5 py-1 rounded-full text-[11px] font-bold border ${roleBadge[u.role] || 'bg-stone-100 text-stone-700'}`}>
                            {u.role?.replace('_', ' ').toUpperCase()}
                          </span>
                        </td>
                        <td className="px-5 py-4 text-stone-500">
                          {u.createdAt ? new Date(u.createdAt).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'}
                        </td>
                        <td className="px-5 py-4 text-right space-x-2">
                          <button
                            onClick={() => openEdit(u)}
                            className="px-2.5 py-1 rounded border border-stone-200 text-stone-700 font-bold hover:bg-stone-100 transition"
                          >
                            Edit
                          </button>
                          {!isSelf && (
                            <button
                              onClick={() => handleDelete(u)}
                              className="px-2.5 py-1 rounded border border-red-200 text-red-600 font-bold hover:bg-red-50 transition"
                            >
                              Hapus
                            </button>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                  {users.length === 0 && (
                    <tr>
                      <td colSpan={5} className="px-5 py-12 text-center text-stone-400">
                        Belum ada pengguna terdaftar.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Modal Add / Edit */}
      {modal && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4" onClick={closeModal}>
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden" onClick={(e) => e.stopPropagation()}>
            <div className="px-6 py-4 bg-[#2C1A0E] text-white flex items-center justify-between">
              <h3 className="font-bold text-sm">
                {modal === 'add' ? 'Tambah Pengguna Baru' : 'Perbarui Data Pengguna'}
              </h3>
              <button onClick={closeModal} className="text-white/70 hover:text-white text-xl leading-none">&times;</button>
            </div>

            <form onSubmit={handleSubmit} className="p-6 space-y-4 text-xs">
              <div>
                <label className="block text-stone-700 font-semibold mb-1">Nama Lengkap *</label>
                <input
                  type="text"
                  required
                  value={form.name}
                  onChange={set('name')}
                  placeholder="Nama lengkap petugas"
                  className="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-[#2C1A0E]"
                />
              </div>

              <div>
                <label className="block text-stone-700 font-semibold mb-1">Alamat Email *</label>
                <input
                  type="email"
                  required
                  value={form.email}
                  onChange={set('email')}
                  placeholder="email@msharmony.id"
                  className="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-[#2C1A0E]"
                />
              </div>

              <div>
                <label className="block text-stone-700 font-semibold mb-1">Role / Hak Akses *</label>
                <select
                  value={form.role}
                  onChange={set('role')}
                  className="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-[#2C1A0E]"
                >
                  <option value="admin">Admin Operasional</option>
                  <option value="super_admin">Super Admin</option>
                  <option value="owner">Owner (Hanya Dashboard Analitik)</option>
                </select>
              </div>

              <div>
                <label className="block text-stone-700 font-semibold mb-1">
                  Kata Sandi {modal === 'edit' && <span className="font-normal text-stone-400">(kosongkan jika tidak diganti)</span>} *
                </label>
                <input
                  type="password"
                  value={form.password}
                  onChange={set('password')}
                  {...(modal === 'add' ? { required: true } : {})}
                  placeholder={modal === 'add' ? 'Minimal 6 karakter' : 'Isi untuk ganti sandi'}
                  className="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg focus:outline-none focus:border-[#2C1A0E]"
                />
              </div>

              <div className="flex gap-2.5 pt-3">
                <button
                  type="button"
                  onClick={closeModal}
                  className="flex-1 py-2.5 border border-stone-300 text-stone-700 font-semibold rounded-lg hover:bg-stone-50 transition"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={submitting}
                  className="flex-1 py-2.5 bg-[#2C1A0E] text-white font-bold rounded-lg hover:bg-[#3D2518] transition disabled:opacity-50"
                >
                  {submitting ? 'Menyimpan...' : 'Simpan Data'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
