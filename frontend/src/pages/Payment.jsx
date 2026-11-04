import { useState, useEffect, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../services/api';
import { cfg } from '../config';

const fmt = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);

export default function Payment() {
  const { id } = useParams();
  const [data, setData] = useState(null);
  const [status, setStatus] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [simulating, setSimulating] = useState(false);

  const fetchPayment = useCallback(async () => {
    try {
      const res = await api.get(`/payment/${id}`);
      setData(res.data);
    } catch (err) {
      setError(err.response?.data?.message || 'Gagal memuat data pembayaran');
    } finally {
      setLoading(false);
    }
  }, [id]);

  const pollStatus = useCallback(async () => {
    try {
      const res = await api.get(`/payment/${id}/status`);
      setStatus(res.data);
    } catch { /* silent */ }
  }, [id]);

  useEffect(() => {
    fetchPayment();
  }, [fetchPayment]);

  useEffect(() => {
    const interval = setInterval(pollStatus, cfg.PAYMENT_POLL_MS);
    return () => clearInterval(interval);
  }, [pollStatus]);

  const handleMidtransPay = () => {
    if (!data?.snapUrl || !data?.snapToken) return;
    const existing = document.getElementById('midtrans-snap');
    if (!existing) {
      const script = document.createElement('script');
      script.id = 'midtrans-snap';
      script.src = data.snapUrl;
      script.setAttribute('data-client-key', data.clientKey || '');
      script.onload = () => window.snap.pay(data.snapToken);
      document.head.appendChild(script);
    } else {
      window.snap.pay(data.snapToken);
    }
  };

  const handleRefreshQris = async () => {
    setLoading(true);
    await fetchPayment();
  };

  const handleSimulate = async () => {
    setSimulating(true);
    try {
      await api.post(`/payment/${id}/simulate-sandbox`);
      await pollStatus();
    } catch (err) {
      setError(err.response?.data?.message || 'Simulasi gagal');
    } finally {
      setSimulating(false);
    }
  };

  if (loading) return (
    <div className="min-h-screen flex items-center justify-center bg-[#FAF8F5]">
      <p className="text-[#2C1A0E] text-lg">Memuat...</p>
    </div>
  );

  if (error) return (
    <div className="min-h-screen flex items-center justify-center bg-[#FAF8F5]">
      <div className="bg-white rounded-2xl shadow-lg p-8 max-w-md text-center">
        <p className="text-red-600 mb-4">{error}</p>
        <button onClick={fetchPayment} className="px-6 py-2 bg-[#2C1A0E] text-white rounded-lg hover:bg-[#3d2517] transition">Coba Lagi</button>
      </div>
    </div>
  );

  const paid = status?.status === 'paid' || status?.status === 'settlement';

  return (
    <div className="min-h-screen bg-[#FAF8F5] py-10 px-4">
      <div className="max-w-lg mx-auto">
        <div className="bg-white rounded-2xl shadow-lg p-8">
          <h1 className="text-2xl font-bold text-[#2C1A0E] mb-1">Pembayaran</h1>
          <p className="text-sm text-gray-500 mb-6">Transaksi #{data?.kode_transaksi || id}</p>

          {/* Transaction Info */}
          <div className="space-y-3 mb-6 text-sm">
            {data?.gambar_ai && (
              <div className="flex justify-center mb-3">
                <img
                  src={data.gambar_ai}
                  alt={data.kebaya_name || 'Kebaya'}
                  className="w-28 h-36 object-cover rounded-xl border border-stone-200 shadow-sm"
                />
              </div>
            )}
            {data?.kebaya_name && (
              <div className="flex justify-between"><span className="text-gray-500">Kebaya</span><span className="font-medium text-[#2C1A0E]">{data.kebaya_name}</span></div>
            )}
            {data?.warna_custom && (
              <div className="flex justify-between"><span className="text-gray-500">Warna (Custom AI)</span><span className="font-medium text-amber-600">{data.warna_custom}</span></div>
            )}
            {data?.customer && (
              <div className="flex justify-between"><span className="text-gray-500">Pelanggan</span><span className="font-medium text-[#2C1A0E]">{data.customer}</span></div>
            )}
            {data?.nominal != null && (
              <div className="flex justify-between"><span className="text-gray-500">Nominal</span><span className="font-semibold text-[#2C1A0E]">{fmt(data.nominal)}</span></div>
            )}
            {data?.skema && (
              <div className="flex justify-between"><span className="text-gray-500">Skema</span><span className="font-medium text-[#2C1A0E]">{data.skema}</span></div>
            )}
          </div>

          {/* Paid State */}
          {paid ? (
            <div className="text-center py-6">
              <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
              </div>
              <h2 className="text-xl font-bold text-green-700 mb-2">Pembayaran Berhasil!</h2>
              <p className="text-gray-500 text-sm mb-6">Terima kasih, pembayaran Anda telah dikonfirmasi.</p>
              <Link to={`/nota/${id}`} className="inline-block px-6 py-2.5 bg-[#2C1A0E] text-white font-semibold rounded-lg hover:bg-[#3d2517] transition">
                Lihat Nota
              </Link>
            </div>
          ) : (
            <>
              {/* Midtrans Snap Button */}
              {data?.midtransReady && data?.snapToken && (
                <button onClick={handleMidtransPay} className="w-full py-3 bg-[#fbbf24] text-[#2C1A0E] font-bold rounded-lg hover:bg-[#f59e0b] transition mb-4">
                  Bayar via Midtrans
                </button>
              )}

              {/* QRIS */}
              {data?.qrisImageData && (
                <div className="text-center mb-4">
                  <p className="text-sm font-medium text-gray-700 mb-3">Scan QRIS untuk membayar</p>
                  <img src={data.qrisImageData} alt="QRIS" className="mx-auto rounded-lg border border-gray-200 max-w-[280px]" />
                  <button onClick={handleRefreshQris} className="mt-3 text-sm text-[#2C1A0E] font-semibold hover:underline">
                    Refresh QRIS
                  </button>
                </div>
              )}

              {/* Sandbox Simulate */}
              <button onClick={handleSimulate} disabled={simulating} className="w-full py-2.5 border-2 border-dashed border-gray-300 text-gray-500 text-sm rounded-lg hover:border-[#2C1A0E] hover:text-[#2C1A0E] transition disabled:opacity-50 mt-2">
                {simulating ? 'Memproses...' : 'Simulasi Pembayaran (Sandbox)'}
              </button>

              <p className="text-xs text-gray-400 text-center mt-4">Status akan diperbarui otomatis setiap {cfg.PAYMENT_POLL_MS / 1000} detik</p>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
