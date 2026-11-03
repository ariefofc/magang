import { useState, useEffect } from 'react';
import api from '../services/api';

const roleBadge = { super_admin: 'bg-purple-100 text-purple-700', admin: 'bg-blue-100 text-blue-700', owner: 'bg-amber-100 text-amber-700' };
const emptyUser = { name: '', email: '', role: 'admin', password: '' };

export default function UserManagement() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [modal, setModal] = useState(null); // null | 'add' | 'edit'
  const [form, setForm] = useState(emptyUser);
  const [editId, setEditId] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  const fetchUsers = async () => {
    try {
      const res = await api.get('/admin/users');
      setUsers(res.data.data || res.data);
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal memuat data user');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchUsers(); }, []);

  const openAdd = () => { setForm(emptyUser); setEditId(null); setModal('add'); };
  const openEdit = (u) => { setForm({ name: u.name, email: u.email, role: u.role, password: '' }); setEditId(u.id); setModal('edit'); };
  const closeModal = () => { setModal(null); setForm(emptyUser); setEditId(null); };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      if (modal === 'add') {
        await api.post('/admin/users', form);
      } else {
        const payload = { ...form };
        if (!payload.password) delete payload.password;
        await api.put(`/admin/users/${editId}`, payload);
      }
      closeModal();
      await fetchUsers();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal menyimpan');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (u) => {
    if (!confirm(`Hapus user "${u.name}"? Tindakan ini tidak dapat dibatalkan.`)) return;
    try {
      await api.delete(`/admin/users/${u.id}`);
      await fetchUsers();
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal menghapus user');
    }
  };

  const set = (key) => (e) => setForm(p => ({ ...p, [key]: e.target.value }));

  return (
    <div className="min-h-screen bg-[#FAF8F5] py-10 px-4">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-bold text-[#2C1A0E]">Manajemen User</h1>
            <p className="text-sm text-gray-500">Kelola akun pengguna sistem</p>
          </div>
          <button onClick={openAdd} className="px-5 py-2.5 bg-[#2C1A0E] text-white font-semibold rounded-lg hover:bg-[#3d2517] transition">
            + Tambah User
          </button>
        </div>

        {error && <div className="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">{error}<button onClick={() => setError('')} className="ml-2 font-bold">x</button></div>}

        {/* Table */}
        <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
          {loading ? (
            <div className="p-8 text-center text-gray-500">Memuat...</div>
          ) : (
            <table className="w-full text-sm">
              <thead className="bg-[#2C1A0E] text-white">
                <tr>
                  <th className="text-left px-6 py-3 font-semibold">Nama</th>
                  <th className="text-left px-6 py-3 font-semibold">Email</th>
                  <th className="text-left px-6 py-3 font-semibold">Role</th>
                  <th className="text-right px-6 py-3 font-semibold">Aksi</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {users.map(u => (
                  <tr key={u.id} className="hover:bg-[#FAF8F5] transition">
                    <td className="px-6 py-4 font-medium text-[#2C1A0E]">{u.name}</td>
                    <td className="px-6 py-4 text-gray-600">{u.email}</td>
                    <td className="px-6 py-4">
                      <span className={`px-2.5 py-1 rounded-full text-xs font-semibold ${roleBadge[u.role] || 'bg-gray-100 text-gray-600'}`}>
                        {u.role}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right space-x-2">
                      <button onClick={() => openEdit(u)} className="text-[#2C1A0E] hover:underline font-medium">Edit</button>
                      <button onClick={() => handleDelete(u)} className="text-red-600 hover:underline font-medium">Hapus</button>
                    </td>
                  </tr>
                ))}
                {users.length === 0 && (
                  <tr><td colSpan={4} className="px-6 py-8 text-center text-gray-400">Belum ada user</td></tr>
                )}
              </tbody>
            </table>
          )}
        </div>

        {/* Modal */}
        {modal && (
          <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
            <div className="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
              <h2 className="text-xl font-bold text-[#2C1A0E] mb-6">{modal === 'add' ? 'Tambah User' : 'Edit User'}</h2>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                  <input type="text" required value={form.name} onChange={set('name')} className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30 focus:border-[#2C1A0E] transition" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input type="email" required value={form.email} onChange={set('email')} className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30 focus:border-[#2C1A0E] transition" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Role</label>
                  <select value={form.role} onChange={set('role')} className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30 focus:border-[#2C1A0E] transition">
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="owner">Owner</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Password {modal === 'edit' && <span className="text-gray-400">(kosongkan jika tidak diubah)</span>}</label>
                  <input type="password" value={form.password} onChange={set('password')} {...(modal === 'add' ? { required: true } : {})} className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30 focus:border-[#2C1A0E] transition" />
                </div>
                <div className="flex gap-3 pt-2">
                  <button type="submit" disabled={submitting} className="flex-1 py-2.5 bg-[#2C1A0E] text-white font-semibold rounded-lg hover:bg-[#3d2517] transition disabled:opacity-50">
                    {submitting ? 'Menyimpan...' : 'Simpan'}
                  </button>
                  <button type="button" onClick={closeModal} className="flex-1 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Batal
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
