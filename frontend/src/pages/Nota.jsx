import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../services/api';
import { cfg } from '../config';

const fmt = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

export default function Nota() {
  const { id } = useParams();
  const [nota, setNota] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [fittingForm, setFittingForm] = useState({ tanggal: '', jam: '', lokasi: '', catatan: '' });
  const [showFitting, setShowFitting] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [fittingMsg, setFittingMsg] = useState('');

  useEffect(() => {
    api.get(`/transaksi/nota/${id}`)
      .then(res => setNota(res.data))
      .catch(err => setError(err.response?.data?.message || 'Gagal memuat nota'))
      .finally(() => setLoading(false));
  }, [id]);

  const handleFittingSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setFittingMsg('');
    try {
      await api.post(`/transaksi/${id}/fitting`, fittingForm);
      setFittingMsg('Permintaan fitting berhasil dikirim!');
      setShowFitting(false);
    } catch (err) {
      setFittingMsg(err.response?.data?.message || 'Gagal mengirim permintaan');
    } finally {
      setSubmitting(false);
    }
  };

  const handlePengembalian = async () => {
    if (!confirm('Ajukan pengembalian untuk transaksi ini?')) return;
    try {
      await api.post(`/transaksi/${id}/pengembalian`);
      alert('Permintaan pengembalian berhasil dikirim');
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal mengajukan pengembalian');
    }
  };

  if (loading) return <div className="min-h-screen flex items-center justify-center bg-[#FAF8F5]"><p className="text-[#2C1A0E]">Memuat nota...</p></div>;
  if (error) return <div className="min-h-screen flex items-center justify-center bg-[#FAF8F5]"><p className="text-red-600">{error}</p></div>;

  const d = nota;
  const statusColor = { selesai: 'bg-green-100 text-green-700', aktif: 'bg-blue-100 text-blue-700', pending: 'bg-yellow-100 text-yellow-700', dibatalkan: 'bg-red-100 text-red-700' };

  return (
    <div className="min-h-screen bg-[#FAF8F5] py-10 px-4">
      <div className="max-w-2xl mx-auto">
        <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
          {/* Header */}
          <div className="bg-[#2C1A0E] text-white px-8 py-6 text-center">
            <h1 className="text-2xl font-bold tracking-wide">{cfg.APP_NAME}</h1>
            <p className="text-[#fbbf24] text-sm mt-1">Nota Transaksi</p>
          </div>

          <div className="p-8">
            {/* Kode & Status */}
            <div className="flex items-center justify-between mb-6">
              <div>
                <p className="text-xs text-gray-400 uppercase tracking-wider">Kode Transaksi</p>
                <p className="text-lg font-bold text-[#2C1A0E]">{d?.kode_transaksi}</p>
              </div>
              <span className={`px-3 py-1 rounded-full text-xs font-semibold ${statusColor[d?.status] || 'bg-gray-100 text-gray-600'}`}>
                {d?.status?.toUpperCase()}
              </span>
            </div>

            <hr className="border-gray-100 mb-6" />

            {/* Kebaya */}
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Detail Kebaya</h3>
              <div className="grid grid-cols-2 gap-3 text-sm">
                <div><span className="text-gray-500">Nama</span><p className="font-medium text-[#2C1A0E]">{d?.kebaya_name}</p></div>
                {d?.warna_custom && <div><span className="text-gray-500">Warna Custom</span><p className="font-medium text-[#2C1A0E]">{d.warna_custom}</p></div>}
                {d?.ukuran && <div><span className="text-gray-500">Ukuran</span><p className="font-medium text-[#2C1A0E]">{d.ukuran}</p></div>}
              </div>
            </div>

            {/* Customer */}
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Data Pelanggan</h3>
              <div className="grid grid-cols-2 gap-3 text-sm">
                <div><span className="text-gray-500">Nama</span><p className="font-medium text-[#2C1A0E]">{d?.nama_pelanggan}</p></div>
                <div><span className="text-gray-500">No. WhatsApp</span><p className="font-medium text-[#2C1A0E]">{d?.no_wa}</p></div>
                {d?.alamat && <div className="col-span-2"><span className="text-gray-500">Alamat</span><p className="font-medium text-[#2C1A0E]">{d.alamat}</p></div>}
              </div>
            </div>

            {/* Rental Period */}
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Periode Sewa</h3>
              <div className="grid grid-cols-3 gap-3 text-sm">
                <div><span className="text-gray-500">Tanggal Sewa</span><p className="font-medium text-[#2C1A0E]">{d?.tanggal_sewa}</p></div>
                <div><span className="text-gray-500">Pengembalian</span><p className="font-medium text-[#2C1A0E]">{d?.tanggal_pengembalian}</p></div>
                <div><span className="text-gray-500">Durasi</span><p className="font-medium text-[#2C1A0E]">{d?.durasi} hari</p></div>
              </div>
            </div>

            {/* Payment */}
            <div className="bg-[#FAF8F5] rounded-xl p-5 mb-6">
              <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Pembayaran</h3>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between"><span className="text-gray-500">Total Harga</span><span className="font-bold text-[#2C1A0E]">{fmt(d?.total_harga)}</span></div>
                <div className="flex justify-between"><span className="text-gray-500">Skema</span><span className="font-medium text-[#2C1A0E]">{d?.skema}</span></div>
                {d?.nominal_dp != null && <div className="flex justify-between"><span className="text-gray-500">DP</span><span className="font-medium text-[#2C1A0E]">{fmt(d.nominal_dp)}</span></div>}
                {d?.sisa_pelunasan != null && (
                  <div className="flex justify-between border-t border-gray-200 pt-2 mt-2">
                    <span className="text-gray-500 font-semibold">Sisa Pelunasan</span>
                    <span className="font-bold text-[#2C1A0E]">{fmt(d.sisa_pelunasan)}</span>
                  </div>
                )}
              </div>
            </div>

            {/* Messages */}
            {fittingMsg && <div className="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm">{fittingMsg}</div>}

            {/* Actions */}
            <div className="flex flex-wrap gap-3 mb-6">
              <button onClick={() => window.print()} className="px-5 py-2.5 bg-[#2C1A0E] text-white font-semibold rounded-lg hover:bg-[#3d2517] transition">
                Cetak Nota
              </button>
              <button onClick={() => setShowFitting(!showFitting)} className="px-5 py-2.5 border border-[#2C1A0E] text-[#2C1A0E] font-semibold rounded-lg hover:bg-[#2C1A0E] hover:text-white transition">
                Request Fitting
              </button>
              <button onClick={handlePengembalian} className="px-5 py-2.5 border border-red-300 text-red-600 font-semibold rounded-lg hover:bg-red-50 transition">
                Ajukan Pengembalian
              </button>
            </div>

            {/* Fitting Form */}
            {showFitting && (
              <form onSubmit={handleFittingSubmit} className="bg-[#FAF8F5] rounded-xl p-5 mb-6 space-y-4">
                <h4 className="font-semibold text-[#2C1A0E]">Form Request Fitting</h4>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm text-gray-600 mb-1">Tanggal</label>
                    <input type="date" required value={fittingForm.tanggal} onChange={e => setFittingForm(p => ({ ...p, tanggal: e.target.value }))} className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600 mb-1">Jam</label>
                    <input type="time" required value={fittingForm.jam} onChange={e => setFittingForm(p => ({ ...p, jam: e.target.value }))} className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30" />
                  </div>
                </div>
                <div>
                  <label className="block text-sm text-gray-600 mb-1">Lokasi</label>
                  <input type="text" required value={fittingForm.lokasi} onChange={e => setFittingForm(p => ({ ...p, lokasi: e.target.value }))} className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30" placeholder="Alamat fitting" />
                </div>
                <div>
                  <label className="block text-sm text-gray-600 mb-1">Catatan</label>
                  <textarea value={fittingForm.catatan} onChange={e => setFittingForm(p => ({ ...p, catatan: e.target.value }))} className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30" rows={2} placeholder="Catatan tambahan (opsional)" />
                </div>
                <button type="submit" disabled={submitting} className="px-5 py-2.5 bg-[#fbbf24] text-[#2C1A0E] font-bold rounded-lg hover:bg-[#f59e0b] transition disabled:opacity-50">
                  {submitting ? 'Mengirim...' : 'Kirim Request Fitting'}
                </button>
              </form>
            )}

            {/* Back */}
            <div className="text-center">
              <Link to="/" className="text-sm text-[#2C1A0E] font-semibold hover:underline">Kembali ke Beranda</Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
