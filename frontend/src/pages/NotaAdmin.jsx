import { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import api from '../services/api';
import { cfg } from '../config';

const fmt = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

export default function NotaAdmin() {
  const { id } = useParams();
  const [nota, setNota] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    api.get(`/transaksi/nota/${id}`)
      .then(res => setNota(res.data))
      .catch(err => setError(err.response?.data?.message || 'Gagal memuat nota'))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="min-h-screen flex items-center justify-center bg-[#FAF8F5]"><p className="text-[#2C1A0E]">Memuat nota...</p></div>;
  if (error) return <div className="min-h-screen flex items-center justify-center bg-[#FAF8F5]"><p className="text-red-600">{error}</p></div>;

  const d = nota;
  const statusColor = { selesai: 'bg-green-100 text-green-700', aktif: 'bg-blue-100 text-blue-700', pending: 'bg-yellow-100 text-yellow-700', dibatalkan: 'bg-red-100 text-red-700' };

  return (
    <div className="min-h-screen bg-[#FAF8F5] py-10 px-4">
      <div className="max-w-2xl mx-auto">
        <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
          {/* Header */}
          <div className="bg-[#2C1A0E] text-white px-8 py-6">
            <div className="flex items-center justify-between">
              <div>
                <h1 className="text-2xl font-bold tracking-wide">{cfg.APP_NAME}</h1>
                <p className="text-[#fbbf24] text-sm mt-1">Nota Transaksi</p>
              </div>
              <span className="px-3 py-1 bg-[#fbbf24] text-[#2C1A0E] text-xs font-bold rounded-full uppercase">Admin</span>
            </div>
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

            {/* Customer Detail Grid */}
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Data Pelanggan</h3>
              <div className="grid grid-cols-2 gap-4 text-sm bg-[#FAF8F5] rounded-xl p-4">
                <div><span className="text-gray-500 block">Nama</span><p className="font-medium text-[#2C1A0E]">{d?.nama_pelanggan}</p></div>
                <div><span className="text-gray-500 block">No. WhatsApp</span><p className="font-medium text-[#2C1A0E]">{d?.no_wa}</p></div>
                <div><span className="text-gray-500 block">Email</span><p className="font-medium text-[#2C1A0E]">{d?.email || '-'}</p></div>
                <div><span className="text-gray-500 block">Alamat</span><p className="font-medium text-[#2C1A0E]">{d?.alamat || '-'}</p></div>
              </div>
            </div>

            {/* Kebaya */}
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Detail Kebaya</h3>
              <div className="grid grid-cols-3 gap-3 text-sm">
                <div><span className="text-gray-500">Nama</span><p className="font-medium text-[#2C1A0E]">{d?.kebaya_name}</p></div>
                <div><span className="text-gray-500">Warna Custom</span><p className="font-medium text-[#2C1A0E]">{d?.warna_custom || '-'}</p></div>
                <div><span className="text-gray-500">Ukuran</span><p className="font-medium text-[#2C1A0E]">{d?.ukuran || '-'}</p></div>
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

            {/* Finance Breakdown */}
            <div className="bg-[#FAF8F5] rounded-xl p-5 mb-6">
              <h3 className="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Rincian Keuangan</h3>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between"><span className="text-gray-500">Total Harga</span><span className="font-medium text-[#2C1A0E]">{fmt(d?.total_harga)}</span></div>
                <div className="flex justify-between"><span className="text-gray-500">Skema Bayar</span><span className="font-medium text-[#2C1A0E]">{d?.skema}</span></div>
                {d?.nominal_dp != null && (
                  <div className="flex justify-between"><span className="text-gray-500">DP Dibayar</span><span className="font-medium text-green-700">{fmt(d.nominal_dp)}</span></div>
                )}
                <hr className="border-gray-200" />
                {d?.sisa_pelunasan != null && (
                  <div className="flex justify-between text-base">
                    <span className="font-semibold text-[#2C1A0E]">Sisa Pelunasan</span>
                    <span className="font-bold text-[#2C1A0E]">{fmt(d.sisa_pelunasan)}</span>
                  </div>
                )}
                <div className="flex justify-between text-base border-t border-gray-200 pt-2">
                  <span className="font-semibold text-[#2C1A0E]">Total</span>
                  <span className="font-bold text-[#2C1A0E] text-lg">{fmt(d?.total_harga)}</span>
                </div>
              </div>
            </div>

            {/* Print */}
            <div className="text-center">
              <button onClick={() => window.print()} className="px-8 py-3 bg-[#2C1A0E] text-white font-semibold rounded-lg hover:bg-[#3d2517] transition">
                Cetak Nota
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
