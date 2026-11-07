import { useState, useEffect, useCallback } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import api from '../services/api'
import { cfg, brandParts } from '../config'
import LiveChat from '../components/LiveChat'

const fmt = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })

const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL']
const SCHEMES = [
  { label: 'DP 50%', value: 'dp_50', desc: 'Bayar separuh, pelunasan saat ambil' },
  { label: 'DP 70%', value: 'dp_70', desc: 'Uang muka prioritas jadwal' },
  { label: 'Lunas 100%', value: 'lunas', desc: 'Pembayaran penuh instan' },
]

const INDO_MONTHS = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]
const WEEK_DAYS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']

export default function KebayaDetail() {
  const { slug } = useParams()
  const [kebaya, setKebaya] = useState(null)
  const [loading, setLoading] = useState(true)
  const [activeImg, setActiveImg] = useState(0)
  const [selectedSize, setSelectedSize] = useState('M')
  const [showSewa, setShowSewa] = useState(false)
  const [others, setOthers] = useState([])
  const [recolorColor, setRecolorColor] = useState('')
  const [recolorResult, setRecolorResult] = useState(null)
  const [recolorLoading, setRecolorLoading] = useState(false)

  // Interactive booking dates
  const todayStr = new Date().toISOString().slice(0, 10)
  const tomorrowStr = new Date(Date.now() + 86400000).toISOString().slice(0, 10)
  const [tglSewa, setTglSewa] = useState(todayStr)
  const [tglKembali, setTglKembali] = useState(
    new Date(Date.now() + 2 * 24 * 3600 * 1000).toISOString().slice(0, 10)
  )
  const [skemaBayar, setSkemaBayar] = useState('dp_50')
  const [rangeAvailability, setRangeAvailability] = useState(null)
  const [checkingRange, setCheckingRange] = useState(false)

  // Fetch real-time range availability whenever dates or kebaya changes
  useEffect(() => {
    if (!slug || !tglSewa) return
    let active = true
    setCheckingRange(true)
    api.get(`/kebaya/${slug}/check-range`, {
      params: { start: tglSewa, end: tglKembali || tglSewa }
    })
      .then(r => {
        if (active) setRangeAvailability(r.data)
      })
      .catch(() => {})
      .finally(() => {
        if (active) setCheckingRange(false)
      })
    return () => { active = false }
  }, [slug, tglSewa, tglKembali])

  useEffect(() => {
    setLoading(true)
    api.get(`/kebaya/${slug}`)
      .then(r => setKebaya(r.data))
      .catch(() => setKebaya(null))
      .finally(() => setLoading(false))

    api.get('/kebaya')
      .then(r => {
        const list = Array.isArray(r.data) ? r.data : r.data?.kebaya ?? []
        setOthers(list.filter(k => k.slug !== slug).slice(0, 4))
      })
      .catch(() => {})
  }, [slug])

  useEffect(() => {
    setActiveImg(0)
    setRecolorResult(null)
    setRecolorColor('')
  }, [slug])

  if (loading) return (
    <div className="min-h-screen bg-[#F8F9FA] flex items-center justify-center">
      <div className="flex flex-col items-center gap-3">
        <div className="w-10 h-10 border-3 border-stone-800 border-t-transparent rounded-full animate-spin" />
        <span className="text-sm text-stone-500 font-medium">Memuat rincian kebaya...</span>
      </div>
    </div>
  )

  if (!kebaya) return (
    <div className="min-h-screen bg-[#F8F9FA] flex flex-col items-center justify-center gap-4 px-4">
      <div className="text-center">
        <h2 className="text-xl font-bold text-stone-800 mb-1">Kebaya Tidak Ditemukan</h2>
        <p className="text-stone-500 text-sm mb-5">Busana yang Anda cari mungkin telah dinonaktifkan atau tautan salah.</p>
        <Link to="/" className="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-[#2C1A0E] text-white text-sm font-semibold hover:bg-[#3D2518] transition">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Kembali ke Katalog
        </Link>
      </div>
    </div>
  )

  const frontImg = kebaya.foto_url || 'https://placehold.co/600x800/2C1A0E/fbbf24?text=Tampak+Depan'
  const backImg = kebaya.foto_belakang_url || frontImg

  const galleryItems = [
    { label: 'Tampak Depan', src: frontImg },
    { label: 'Tampak Belakang', src: backImg },
  ]

  const activeSrc = recolorResult || galleryItems[activeImg]?.src || frontImg

  // Dynamic synchronized availability
  const isRangeFull = rangeAvailability ? rangeAvailability.isFull : false
  const selectedAvailSlots = rangeAvailability ? rangeAvailability.minAvailableSlots : (kebaya.stok_tersedia ?? 1)
  const isAvailable = kebaya.status === 'aktif' && !isRangeFull && selectedAvailSlots > 0

  // Calculate duration and prices for Tokopedia-style checkout card
  const dSewa = tglSewa ? new Date(tglSewa) : null
  const dKembali = tglKembali ? new Date(tglKembali) : null
  const rentalDays = (dSewa && dKembali && dKembali >= dSewa)
    ? Math.max(1, Math.ceil((dKembali - dSewa) / (1000 * 60 * 60 * 24)))
    : 1
  const subtotalRental = (kebaya.harga_sewa_per_hari || 0) * rentalDays
  const dpPct = skemaBayar === 'dp_70' ? 0.7 : skemaBayar === 'lunas' ? 1.0 : 0.5
  const estimatedFirstPayment = Math.ceil(subtotalRental * dpPct)

  const handleRecolor = async (colorName) => {
    if (!colorName?.trim()) return
    const currentImg = galleryItems[activeImg]?.src || frontImg
    if (!currentImg) return
    setRecolorLoading(true)
    setRecolorResult(null)
    try {
      const imgResp = await fetch(currentImg)
      const blob = await imgResp.blob()
      const b64 = await new Promise(resolve => {
        const reader = new FileReader()
        reader.onloadend = () => resolve(reader.result)
        reader.readAsDataURL(blob)
      })

      const maskUrl = currentImg.replace(/\.(png|jpg|jpeg)$/i, '-mask.png')
      let maskB64 = null
      try {
        const maskResp = await fetch(maskUrl)
        if (maskResp.ok) {
          const ct = maskResp.headers.get('content-type') || ''
          if (!ct.includes('text') && !ct.includes('html')) {
            const mBlob = await maskResp.blob()
            maskB64 = await new Promise(resolve => {
              const reader = new FileReader()
              reader.onloadend = () => resolve(reader.result)
              reader.readAsDataURL(mBlob)
            })
          }
        }
      } catch { /* proceed */ }

      let ok = false
      try {
        const { data } = await api.post('/ai/recolor', {
          image_b64: b64,
          image_url: currentImg,
          color: colorName.trim(),
          mask_b64: maskB64,
        })
        if (data.success && data.result_b64) {
          setRecolorResult(data.result_b64)
          ok = true
        }
      } catch { /* fallback */ }

      if (!ok && window.MSHarmonyRecolor) {
        const clientB64 = await window.MSHarmonyRecolor.recolorImage(b64, colorName.trim(), maskB64 || maskUrl)
        setRecolorResult(clientB64)
      }
    } catch (err) {
      alert('Simulasi warna belum dapat diproses: ' + (err.message || ''))
    } finally {
      setRecolorLoading(false)
    }
  }

  const handleCalendarRangeSelect = (start, end) => {
    setTglSewa(start)
    if (end) setTglKembali(end)
  }

  return (
    <div className="min-h-screen bg-[#F8F9FA] text-stone-800 antialiased">
      {/* ── Top Header Bar ── */}
      <header className="sticky top-0 z-40 bg-white border-b border-stone-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
          <Link to="/" className="flex items-center gap-2">
            <span className="text-xl font-bold tracking-tight text-[#2C1A0E]">
              {brandParts().first} <span className="text-amber-600 font-extrabold">{brandParts().rest}</span>
            </span>
          </Link>
          <div className="flex items-center gap-4">
            <Link to="/" className="text-sm font-semibold text-stone-600 hover:text-stone-900 inline-flex items-center gap-1.5 transition">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
              Semua Koleksi
            </Link>
            <Link to="/login" className="text-xs font-bold px-3.5 py-1.5 rounded-md border border-stone-300 text-stone-700 hover:border-stone-400 transition">
              Akun
            </Link>
          </div>
        </div>
      </header>

      {/* ── Breadcrumb ── */}
      <nav className="max-w-7xl mx-auto px-4 sm:px-6 py-3.5 text-xs text-stone-500 flex items-center gap-1.5 flex-wrap">
        <Link to="/" className="hover:text-stone-900 transition">Beranda</Link>
        <span>/</span>
        <Link to="/" className="hover:text-stone-900 transition">Koleksi Busana</Link>
        <span>/</span>
        <span className="text-stone-800 font-semibold truncate max-w-xs">{kebaya.nama_kebaya}</span>
      </nav>

      {/* ── Tokopedia-style 3-Column Product Detail ── */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 pb-20">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

          {/* ════════ COLUMN 1: STICKY GALLERY (Tokopedia Left) ════════ */}
          <div className="lg:col-span-4 lg:sticky lg:top-20 space-y-3">
            <div className="relative bg-white rounded-xl border border-stone-200 overflow-hidden shadow-xs aspect-[3/4]">
              <img
                src={activeSrc}
                alt={kebaya.nama_kebaya}
                className="w-full h-full object-cover"
                onError={e => { e.target.src = 'https://placehold.co/600x800/2C1A0E/fbbf24?text=Kebaya' }}
              />

              {/* View Overlay Tag */}
              <div className="absolute top-3 left-3 bg-black/60 text-white text-[11px] font-medium px-2.5 py-1 rounded-md backdrop-blur-xs">
                {galleryItems[activeImg]?.label || 'Tampak Depan'}
              </div>

              {/* Availability Indicator Tag */}
              <div className="absolute top-3 right-3">
                {isAvailable ? (
                  <span className="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-md bg-emerald-600 text-white shadow-xs">
                    <span className="w-1.5 h-1.5 rounded-full bg-white animate-pulse" />
                    Tersedia • Sisa {selectedAvailSlots} Unit
                  </span>
                ) : (
                  <span className="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-md bg-red-600 text-white shadow-xs">
                    <span className="w-1.5 h-1.5 rounded-full bg-white" />
                    {isRangeFull ? 'Full Booked' : 'Sedang Disewa'}
                  </span>
                )}
              </div>

              {/* Recolor Loading Overlay */}
              {recolorLoading && (
                <div className="absolute inset-0 bg-white/80 backdrop-blur-xs flex flex-col items-center justify-center gap-2 p-4 text-center">
                  <div className="w-8 h-8 border-3 border-amber-600 border-t-transparent rounded-full animate-spin" />
                  <p className="text-xs font-semibold text-stone-700">Menerapkan simulasi warna...</p>
                </div>
              )}

              {/* Reset Recolor Button */}
              {recolorResult && !recolorLoading && (
                <button
                  type="button"
                  onClick={() => setRecolorResult(null)}
                  className="absolute bottom-3 left-3 bg-stone-900/80 hover:bg-stone-900 text-white text-xs px-3 py-1.5 rounded-md transition"
                >
                  Lihat Foto Asli
                </button>
              )}
            </div>

            {/* Thumbnails Strip: Front & Back */}
            <div className="grid grid-cols-2 gap-2.5">
              {galleryItems.map((item, idx) => (
                <button
                  key={idx}
                  type="button"
                  onClick={() => { setActiveImg(idx); setRecolorResult(null); }}
                  className={`p-1.5 rounded-lg border text-left flex items-center gap-2.5 transition ${
                    activeImg === idx && !recolorResult
                      ? 'border-[#2C1A0E] bg-white ring-2 ring-[#2C1A0E]/10'
                      : 'border-stone-200 bg-white hover:border-stone-300'
                  }`}
                >
                  <div className="w-12 h-14 rounded overflow-hidden shrink-0 bg-stone-100">
                    <img
                      src={item.src}
                      alt={item.label}
                      className="w-full h-full object-cover"
                      onError={e => { e.target.src = 'https://placehold.co/100x120/2C1A0E/fbbf24?text=Kebaya' }}
                    />
                  </div>
                  <div className="min-w-0">
                    <span className="block text-xs font-bold text-stone-800 truncate">{item.label}</span>
                    <span className="text-[11px] text-stone-400">Klik untuk lihat</span>
                  </div>
                </button>
              ))}
            </div>

            {/* Studio Guarantee Box */}
            <div className="bg-stone-50 border border-stone-200 rounded-lg p-3 text-xs text-stone-600 space-y-2">
              <div className="flex items-center gap-2 font-semibold text-stone-800">
                <svg className="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                Jaminan Kualitas Butik
              </div>
              <p className="leading-relaxed text-[11px] text-stone-500">
                Setiap busana melalui proses dry clean steril, inspeksi kerapian payet, dan wangi siap pakai sebelum diserahkan ke penyewa.
              </p>
            </div>
          </div>

          {/* ════════ COLUMN 2: PRODUCT SPECIFICATION & DETAILS (Tokopedia Center) ════════ */}
          <div className="lg:col-span-5 space-y-6">
            {/* Title & Price Header */}
            <div className="bg-white rounded-xl p-6 border border-stone-200 shadow-xs space-y-3">
              <div className="flex items-center gap-2 text-xs text-stone-500 flex-wrap">
                <span className="font-semibold text-stone-700">Koleksi Butik</span>
                <span>•</span>
                <span>Tersewa 24+ kali</span>
                <span>•</span>
                <span className="inline-flex items-center gap-1 text-amber-600 font-semibold">
                  <svg className="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                  4.9 (50+ Ulasan)
                </span>
              </div>

              <h1 className="text-2xl font-bold text-stone-900 leading-snug">
                {kebaya.nama_kebaya}
              </h1>

              <div className="pt-2 pb-1">
                <div className="flex items-baseline gap-2">
                  <span className="text-3xl font-extrabold text-[#2C1A0E]">
                    {fmt.format(kebaya.harga_sewa_per_hari)}
                  </span>
                  <span className="text-xs text-stone-500 font-medium">/ hari sewa</span>
                </div>
              </div>

              {/* Availability Banner — Synchronized with Calendar & Date Range */}
              <div className={`p-4 rounded-xl text-xs space-y-2 border transition-all ${
                isAvailable
                  ? 'bg-emerald-50/90 border-emerald-200 text-emerald-950'
                  : 'bg-red-50/90 border-red-200 text-red-950'
              }`}>
                <div className="flex items-center justify-between gap-2">
                  <div className="flex items-center gap-2">
                    <span className={`w-2.5 h-2.5 rounded-full shrink-0 ${isAvailable ? 'bg-emerald-600' : 'bg-red-600'}`} />
                    <span className="font-bold text-xs sm:text-sm">
                      {isAvailable
                        ? `Tersedia: ${selectedAvailSlots} dari total ${kebaya.stok || 1} unit siap sewa`
                        : `FULL BOOKED: Stok Penuh pada Tanggal Dipilih`}
                    </span>
                  </div>
                  <span className={`text-[10px] font-bold px-2 py-0.5 rounded tracking-wide shrink-0 ${
                    isAvailable ? 'bg-emerald-200/80 text-emerald-800' : 'bg-red-200/90 text-red-800'
                  }`}>
                    {isAvailable ? 'Siap Dipesan' : 'Stok Habis'}
                  </span>
                </div>

                {/* Status Ketersediaan Hari Ini & Besok + Rentang Terpilih */}
                <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] pt-1.5 border-t border-black/5 text-stone-600">
                  <span className="flex items-center gap-1.5">
                    <span className={`w-1.5 h-1.5 rounded-full ${kebaya.is_full_hari_ini ? 'bg-red-500' : 'bg-emerald-500'}`} />
                    Hari Ini ({todayStr}): <strong className={kebaya.is_full_hari_ini ? 'text-red-700' : 'text-emerald-800'}>
                      {kebaya.is_full_hari_ini ? 'Penuh' : `Sisa ${kebaya.stok_hari_ini ?? kebaya.stok_tersedia} unit`}
                    </strong>
                  </span>
                  <span>•</span>
                  <span className="flex items-center gap-1.5">
                    <span className={`w-1.5 h-1.5 rounded-full ${kebaya.is_full_besok ? 'bg-red-500' : 'bg-emerald-500'}`} />
                    Besok ({tomorrowStr}): <strong className={kebaya.is_full_besok ? 'text-red-700' : 'text-emerald-800'}>
                      {kebaya.is_full_besok ? 'Penuh' : `Sisa ${kebaya.stok_besok ?? kebaya.stok_tersedia} unit`}
                    </strong>
                  </span>
                  {tglSewa && (
                    <>
                      <span>•</span>
                      <span className="text-stone-700">
                        Jadwal: <strong>{tglSewa}</strong> s/d <strong>{tglKembali || tglSewa}</strong> ({rentalDays} hari)
                      </span>
                    </>
                  )}
                </div>

                {!isAvailable && (
                  <p className="text-[11px] text-red-700 pt-0.5 leading-relaxed font-medium">
                    {rangeAvailability?.fullDates?.length > 0 ? (
                      <>Tanggal <strong>{rangeAvailability.fullDates.join(', ')}</strong> sudah terisi penuh oleh penyewa lain. Silakan pilih tanggal lain yang berwarna hijau pada kalender.</>
                    ) : (
                      <>Kebaya sedang tidak tersedia pada tanggal yang Anda pilih. Silakan tentukan tanggal lain pada kalender.</>
                    )}
                  </p>
                )}
              </div>
            </div>

            {/* Ukuran Busana (Variant Selector) */}
            <div className="bg-white rounded-xl p-6 border border-stone-200 shadow-xs space-y-3">
              <div className="flex items-center justify-between">
                <h3 className="text-sm font-bold text-stone-900">Pilih Ukuran Busana</h3>
                <span className="text-xs text-amber-800 font-medium">Ukuran standar wanita Indonesia</span>
              </div>
              <div className="flex flex-wrap gap-2">
                {SIZES.map(s => (
                  <button
                    key={s}
                    type="button"
                    onClick={() => setSelectedSize(s)}
                    className={`px-4 py-2 rounded-lg text-xs font-bold border transition ${
                      selectedSize === s
                        ? 'border-[#2C1A0E] bg-[#2C1A0E] text-white shadow-xs'
                        : 'border-stone-300 bg-white text-stone-700 hover:border-stone-400'
                    }`}
                  >
                    {s}
                  </button>
                ))}
              </div>
              <p className="text-[11px] text-stone-400 pt-1">
                Catatan: Anda dapat meminta penyesuaian fitting minor saat jadwal pengambilan di studio.
              </p>
            </div>

            {/* ════════ DATE RANGE PICKER MATRIX (Requirement: Kalender Ketersediaan Interaktif) ════════ */}
            <AvailabilityCalendar
              slug={slug}
              totalStock={kebaya.stok}
              tglSewa={tglSewa}
              tglKembali={tglKembali}
              onRangeSelect={handleCalendarRangeSelect}
            />

            {/* Spesifikasi Produk (Tokopedia Specs Table) */}
            <div className="bg-white rounded-xl p-6 border border-stone-200 shadow-xs space-y-4">
              <h3 className="text-sm font-bold text-stone-900 border-b border-stone-100 pb-2">
                Spesifikasi & Material Kebaya
              </h3>
              <dl className="divide-y divide-stone-100 text-xs">
                {kebaya.kain_utama && (
                  <div className="py-2.5 grid grid-cols-3 gap-2">
                    <dt className="text-stone-500 font-medium">Bahan Utama</dt>
                    <dd className="col-span-2 text-stone-800 font-semibold">{kebaya.kain_utama}</dd>
                  </div>
                )}
                {kebaya.furing && (
                  <div className="py-2.5 grid grid-cols-3 gap-2">
                    <dt className="text-stone-500 font-medium">Lapisan Furing</dt>
                    <dd className="col-span-2 text-stone-800">{kebaya.furing}</dd>
                  </div>
                )}
                {kebaya.karakteristik_kain && (
                  <div className="py-2.5 grid grid-cols-3 gap-2">
                    <dt className="text-stone-500 font-medium">Karakteristik Kain</dt>
                    <dd className="col-span-2 text-stone-800">{kebaya.karakteristik_kain}</dd>
                  </div>
                )}
                {kebaya.detail_payet && (
                  <div className="py-2.5 grid grid-cols-3 gap-2">
                    <dt className="text-stone-500 font-medium">Aksen & Payet</dt>
                    <dd className="col-span-2 text-stone-800">{kebaya.detail_payet}</dd>
                  </div>
                )}
                {kebaya.rekomendasi_acara && (
                  <div className="py-2.5 grid grid-cols-3 gap-2">
                    <dt className="text-stone-500 font-medium">Rekomendasi Acara</dt>
                    <dd className="col-span-2 text-stone-800 font-medium">{kebaya.rekomendasi_acara}</dd>
                  </div>
                )}
                <div className="py-2.5 grid grid-cols-3 gap-2">
                  <dt className="text-stone-500 font-medium">Tampilan Foto</dt>
                  <dd className="col-span-2 text-stone-800">Tersedia Tampak Depan & Tampak Belakang</dd>
                </div>
              </dl>
            </div>

            {/* Deskripsi Lengkap */}
            <div className="bg-white rounded-xl p-6 border border-stone-200 shadow-xs space-y-3">
              <h3 className="text-sm font-bold text-stone-900 border-b border-stone-100 pb-2">
                Deskripsi Busana
              </h3>
              <p className="text-xs text-stone-700 leading-relaxed whitespace-pre-line">
                {kebaya.deskripsi || 'Koleksi busana kebaya eksklusif dari Butik Mayangsari dengan potongan pola presisi, jahitan halus, dan material berkualitas tinggi untuk menyempurnakan hari bahagia Anda.'}
              </p>
            </div>

            {/* Fitur Simulasi Warna Kain (Tokopedia-style custom preview) */}
            <div className="bg-white rounded-xl p-6 border border-stone-200 shadow-xs space-y-3">
              <div className="flex items-center justify-between">
                <div>
                  <h3 className="text-sm font-bold text-stone-900">Simulasi Warna Kain</h3>
                  <p className="text-[11px] text-stone-500">Coba visualisasi warna lain sebelum memutuskan</p>
                </div>
                {recolorResult && (
                  <button
                    type="button"
                    onClick={() => setRecolorResult(null)}
                    className="text-xs font-semibold text-amber-700 hover:underline"
                  >
                    Reset Warna
                  </button>
                )}
              </div>

              <div className="flex flex-wrap gap-2 pt-1">
                {[
                  { name: 'Sage Green', hex: '#8fbc8f' },
                  { name: 'Navy Blue', hex: '#1a3a5c' },
                  { name: 'Dusty Rose', hex: '#c4a0a0' },
                  { name: 'Burgundy', hex: '#722f37' },
                  { name: 'Gold', hex: '#d4a847' },
                  { name: 'Lavender', hex: '#b8a9c9' },
                  { name: 'Coral', hex: '#e07050' },
                  { name: 'Emerald', hex: '#2d8a5e' },
                ].map(c => (
                  <button
                    key={c.name}
                    type="button"
                    onClick={() => { setRecolorColor(c.name); handleRecolor(c.name); }}
                    disabled={recolorLoading}
                    className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition ${
                      recolorColor === c.name
                        ? 'border-[#2C1A0E] bg-stone-100 font-bold'
                        : 'border-stone-200 hover:border-stone-300 bg-white text-stone-700'
                    }`}
                  >
                    <span className="w-3 h-3 rounded-full border border-black/10" style={{ backgroundColor: c.hex }} />
                    {c.name}
                  </button>
                ))}
              </div>

              <div className="flex gap-2 pt-1">
                <input
                  type="text"
                  value={recolorColor}
                  onChange={e => setRecolorColor(e.target.value)}
                  placeholder="Atau ketik nama warna (misal: Maroon, Lilac, Olive)"
                  className="flex-1 rounded-lg border border-stone-300 px-3 py-2 text-xs focus:border-stone-800 focus:outline-none"
                />
                <button
                  type="button"
                  onClick={() => handleRecolor(recolorColor)}
                  disabled={recolorLoading || !recolorColor.trim()}
                  className="px-4 py-2 rounded-lg bg-[#2C1A0E] text-white text-xs font-bold hover:bg-[#3D2518] transition disabled:opacity-50"
                >
                  {recolorLoading ? 'Memproses...' : 'Terapkan'}
                </button>
              </div>
            </div>
          </div>

          {/* ════════ COLUMN 3: STICKY CHECKOUT CARD (Tokopedia Right Floating Card) ════════ */}
          <div className="lg:col-span-3 lg:sticky lg:top-20 space-y-4">
            <div className="bg-white rounded-xl p-5 border border-stone-200 shadow-sm space-y-4">
              <h3 className="text-sm font-bold text-stone-900 border-b border-stone-100 pb-2">
                Atur Jadwal & Sewa
              </h3>

              {/* Mini Item Preview */}
              <div className="flex items-center gap-2.5 pb-2 border-b border-stone-100">
                <img
                  src={activeSrc}
                  alt=""
                  className="w-12 h-14 object-cover rounded-md border border-stone-200 shrink-0"
                />
                <div className="min-w-0">
                  <p className="text-xs font-bold text-stone-800 truncate">{kebaya.nama_kebaya}</p>
                  <p className="text-[11px] text-stone-500">Ukuran dipilih: <span className="font-bold text-stone-800">{selectedSize}</span></p>
                  {recolorColor && <p className="text-[10px] text-amber-700 font-semibold truncate">Warna: {recolorColor}</p>}
                </div>
              </div>

              {/* Tanggal Sewa & Pengembalian Inputs */}
              <div className="space-y-2.5 text-xs">
                <div>
                  <label className="block text-stone-600 font-semibold mb-1">Mulai Tanggal Sewa</label>
                  <input
                    type="date"
                    value={tglSewa}
                    onChange={e => setTglSewa(e.target.value)}
                    className="w-full rounded-lg border border-stone-300 px-3 py-2 text-xs focus:border-stone-800 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="block text-stone-600 font-semibold mb-1">Tanggal Pengembalian</label>
                  <input
                    type="date"
                    min={tglSewa || todayStr}
                    value={tglKembali}
                    onChange={e => setTglKembali(e.target.value)}
                    className="w-full rounded-lg border border-stone-300 px-3 py-2 text-xs focus:border-stone-800 focus:outline-none"
                  />
                </div>
                <div className="flex justify-between text-stone-500 pt-1">
                  <span>Durasi pemakaian:</span>
                  <span className="font-bold text-stone-800">{rentalDays} hari</span>
                </div>
              </div>

              {/* Skema Bayar Selector */}
              <div className="space-y-1.5 pt-1">
                <label className="block text-xs text-stone-600 font-semibold">Skema Pembayaran</label>
                <div className="grid grid-cols-3 gap-1.5">
                  {SCHEMES.map(s => (
                    <button
                      key={s.value}
                      type="button"
                      onClick={() => setSkemaBayar(s.value)}
                      className={`py-1.5 px-1 rounded-md text-[11px] font-bold border transition text-center ${
                        skemaBayar === s.value
                          ? 'border-[#2C1A0E] bg-stone-100 text-stone-900'
                          : 'border-stone-200 text-stone-500 hover:border-stone-300'
                      }`}
                    >
                      {s.label}
                    </button>
                  ))}
                </div>
              </div>

              {/* Price Calculation Box */}
              <div className="bg-stone-50 rounded-lg p-3 text-xs space-y-1.5 border border-stone-200/80">
                <div className="flex justify-between text-stone-500">
                  <span>Biaya Sewa ({rentalDays} hari)</span>
                  <span className="font-medium text-stone-700">{fmt.format(subtotalRental)}</span>
                </div>
                <div className="flex justify-between text-stone-500">
                  <span>Skema ({SCHEMES.find(s => s.value === skemaBayar)?.label})</span>
                  <span className="font-semibold text-amber-700">{fmt.format(estimatedFirstPayment)}</span>
                </div>
                <div className="pt-2 border-t border-stone-200 flex justify-between items-baseline">
                  <span className="font-bold text-stone-900">Total Transaksi</span>
                  <span className="font-extrabold text-base text-[#2C1A0E]">{fmt.format(subtotalRental)}</span>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="space-y-2 pt-1">
                <button
                  type="button"
                  onClick={() => setShowSewa(true)}
                  disabled={!isAvailable || checkingRange}
                  className={`w-full py-3.5 rounded-xl font-bold text-sm transition shadow-xs flex items-center justify-center gap-2 ${
                    isAvailable
                      ? 'bg-[#2C1A0E] hover:bg-[#3D2518] text-white cursor-pointer active:scale-[0.99]'
                      : 'bg-red-50 border border-red-300 text-red-600 cursor-not-allowed opacity-90'
                  }`}
                >
                  {checkingRange ? (
                    'Memeriksa Ketersediaan...'
                  ) : isAvailable ? (
                    `Sewa Sekarang (${rentalDays} Hari)`
                  ) : isRangeFull ? (
                    'Stok Habis pada Tanggal Ini (Full Booked)'
                  ) : (
                    'Kebaya Sedang Tidak Tersedia'
                  )}
                </button>

                <a
                  href={`https://wa.me/${cfg.CONTACT_WA.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(
                    `Halo Butik Mayangsari, saya ingin bertanya ketersediaan ${kebaya.nama_kebaya} untuk tanggal ${tglSewa} s/d ${tglKembali}`
                  )}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full py-2.5 rounded-lg border border-stone-300 hover:bg-stone-50 text-stone-700 font-semibold text-xs flex items-center justify-center gap-1.5 transition"
                >
                  <svg className="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z" /></svg>
                  Tanya Admin Butik
                </a>
              </div>
            </div>
          </div>

        </div>

        {/* ── Koleksi Rekomendasi Lainnya ── */}
        {others.length > 0 && (
          <section className="mt-16 pt-10 border-t border-stone-200">
            <div className="flex items-center justify-between mb-6">
              <div>
                <h2 className="text-xl font-bold text-stone-900">Koleksi Terkait Lainnya</h2>
                <p className="text-xs text-stone-500">Pilihan kebaya lain yang mungkin sesuai untuk momen Anda</p>
              </div>
              <Link to="/" className="text-xs font-semibold text-amber-800 hover:underline">
                Lihat Semua &rarr;
              </Link>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
              {others.map(k => (
                <Link
                  key={k.slug}
                  to={`/kebaya/${k.slug}`}
                  className="group bg-white rounded-xl overflow-hidden border border-stone-200 hover:shadow-md transition flex flex-col"
                >
                  <div className="aspect-[3/4] bg-stone-100 overflow-hidden relative">
                    <img
                      src={k.foto_url || frontImg}
                      alt={k.nama_kebaya}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      onError={e => { e.target.src = 'https://placehold.co/400x533/2C1A0E/fbbf24?text=Kebaya' }}
                    />
                    <div className="absolute top-2 left-2">
                      <span className="text-[10px] font-semibold px-2 py-0.5 rounded bg-black/60 text-white backdrop-blur-xs">
                        {k.tersedia ? 'Tersedia' : 'Disewa'}
                      </span>
                    </div>
                  </div>
                  <div className="p-3.5 flex-1 flex flex-col justify-between">
                    <div>
                      <h4 className="font-bold text-stone-800 text-xs truncate group-hover:text-amber-800 transition">
                        {k.nama_kebaya}
                      </h4>
                      <p className="text-[11px] text-stone-400 line-clamp-1 mt-0.5">{k.kain_utama || 'Material premium'}</p>
                    </div>
                    <p className="text-amber-800 font-extrabold text-xs mt-2">{fmt.format(k.harga_sewa_per_hari)}</p>
                  </div>
                </Link>
              ))}
            </div>
          </section>
        )}
      </main>

      {/* Sewa Modal */}
      {showSewa && (
        <SewaModal
          kebaya={kebaya}
          selectedSize={selectedSize}
          initialTglSewa={tglSewa}
          initialTglKembali={tglKembali}
          initialSkema={skemaBayar}
          customColor={recolorColor}
          customImage={recolorResult}
          onClose={() => setShowSewa(false)}
        />
      )}

      <LiveChat />
    </div>
  )
}

/* ═════════════════════════════════════════════════════════════════
   AVAILABILITY CALENDAR (DATE RANGE PICKER MATRIX)
   Displays Red (Full) and Green (Available) per kebaya
═════════════════════════════════════════════════════════════════ */
function AvailabilityCalendar({ slug, totalStock, tglSewa, tglKembali, onRangeSelect }) {
  const todayStr = new Date().toISOString().slice(0, 10);
  const tomorrowStr = new Date(Date.now() + 86400000).toISOString().slice(0, 10);

  const [targetYear, setTargetYear] = useState(() => {
    if (tglSewa) return new Date(tglSewa).getFullYear();
    return 2026;
  });
  const [targetMonth, setTargetMonth] = useState(() => {
    if (tglSewa) return new Date(tglSewa).getMonth() + 1;
    return 8; // Default August 2026 where transactions are active
  });
  const [calData, setCalData] = useState(null);
  const [loadingCal, setLoadingCal] = useState(false);
  const [warningMsg, setWarningMsg] = useState('');
  const [pickingStart, setPickingStart] = useState(null);

  // Sync calendar target month when tglSewa is updated from external inputs
  useEffect(() => {
    if (tglSewa) {
      const d = new Date(tglSewa);
      if (!isNaN(d.getTime())) {
        setTargetYear(d.getFullYear());
        setTargetMonth(d.getMonth() + 1);
      }
    }
  }, [tglSewa]);

  const fetchCalendar = useCallback(async (y, m) => {
    setLoadingCal(true);
    setWarningMsg('');
    try {
      const res = await api.get(`/kebaya/${slug}/calendar`, {
        params: { year: y, month: m },
      });
      setCalData(res.data);
    } catch {
      /* silent */
    } finally {
      setLoadingCal(false);
    }
  }, [slug]);

  useEffect(() => {
    fetchCalendar(targetYear, targetMonth);
  }, [fetchCalendar, targetYear, targetMonth]);

  const handlePrevMonth = () => {
    if (targetMonth === 1) {
      setTargetYear(y => y - 1);
      setTargetMonth(12);
    } else {
      setTargetMonth(m => m - 1);
    }
  };

  const handleNextMonth = () => {
    if (targetMonth === 12) {
      setTargetYear(y => y + 1);
      setTargetMonth(1);
    } else {
      setTargetMonth(m => m + 1);
    }
  };

  // Generate matrix grid
  const daysInMonth = new Date(targetYear, targetMonth, 0).getDate();
  // Monday-based offset (0: Monday, ..., 6: Sunday)
  const firstDayOfWeek = (new Date(targetYear, targetMonth - 1, 1).getDay() + 6) % 7;

  const handleDayClick = (dayStr, dayObj) => {
    setWarningMsg('');
    if (!dayObj || dayObj.isFull) {
      setWarningMsg(`Tanggal ${dayStr} sudah terisi penuh (stok habis). Silakan pilih tanggal yang masih berwarna hijau.`);
      return;
    }

    // If no start date being picked or both dates are already chosen: start picking start date
    if (!pickingStart || (tglSewa && tglKembali && !pickingStart)) {
      setPickingStart(dayStr);
      onRangeSelect(dayStr, '');
      return;
    }

    // If picking 2nd date
    if (dayStr < pickingStart) {
      // User clicked an earlier date, make it the new start date
      setPickingStart(dayStr);
      onRangeSelect(dayStr, '');
      return;
    }

    // Check if any date in between is full
    const daysObj = calData?.days || {};
    let hasFull = false;
    const curr = new Date(pickingStart);
    const end = new Date(dayStr);

    while (curr <= end) {
      const currStr = curr.toISOString().slice(0, 10);
      if (daysObj[currStr] && daysObj[currStr].isFull) {
        hasFull = true;
        break;
      }
      curr.setDate(curr.getDate() + 1);
    }

    if (hasFull) {
      setWarningMsg('Rentang tanggal melewati tanggal yang terisi penuh (tanggal merah). Silakan tentukan rentang tanggal lain.');
      return;
    }

    // Valid range selected!
    onRangeSelect(pickingStart, dayStr);
    setPickingStart(null);
  };

  const daysMap = calData?.days || {};

  return (
    <div className="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs space-y-4">
      {/* Header with Title and Month Controls */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-4">
        <div>
          <div className="flex items-center gap-2">
            <h3 className="text-sm font-bold text-stone-900">
              Kalender Ketersediaan & Pilih Jadwal Sewa
            </h3>
            <span className="text-[10px] bg-stone-100 text-stone-700 font-bold px-2 py-0.5 rounded-full border border-stone-200">
              Total Stok: {totalStock || calData?.totalStock || 1} Unit
            </span>
          </div>
          <p className="text-xs text-stone-500 mt-0.5">
            Klik tanggal langsung untuk memilih jadwal mulai & pengembalian busana.
          </p>
        </div>

        {/* Month Navigation */}
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <button
            type="button"
            onClick={handlePrevMonth}
            className="w-8 h-8 rounded-lg border border-stone-300 hover:bg-stone-50 flex items-center justify-center text-stone-600 transition"
            title="Bulan Sebelumnya"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" /></svg>
          </button>
          <span className="text-xs font-bold text-stone-800 min-w-[120px] text-center">
            {INDO_MONTHS[targetMonth - 1]} {targetYear}
          </span>
          <button
            type="button"
            onClick={handleNextMonth}
            className="w-8 h-8 rounded-lg border border-stone-300 hover:bg-stone-50 flex items-center justify-center text-stone-600 transition"
            title="Bulan Berikutnya"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
          </button>
        </div>
      </div>

      {warningMsg && (
        <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center justify-between">
          <span>{warningMsg}</span>
          <button type="button" onClick={() => setWarningMsg('')} className="font-bold text-sm px-1 leading-none">&times;</button>
        </div>
      )}

      {/* Calendar Matrix */}
      <div className="relative">
        {loadingCal && (
          <div className="absolute inset-0 bg-white/70 backdrop-blur-2xs flex items-center justify-center z-10 rounded-xl">
            <div className="w-6 h-6 border-2 border-stone-800 border-t-transparent rounded-full animate-spin" />
          </div>
        )}

        {/* Day of Week Header */}
        <div className="grid grid-cols-7 gap-1.5 text-center mb-1.5">
          {WEEK_DAYS.map((d, i) => (
            <div key={d} className={`text-[11px] font-bold py-1 ${i === 6 ? 'text-red-500' : 'text-stone-500'}`}>
              {d}
            </div>
          ))}
        </div>

        {/* Day Cells Grid */}
        <div className="grid grid-cols-7 gap-1.5">
          {/* Empty cells before month start */}
          {Array.from({ length: firstDayOfWeek }).map((_, i) => (
            <div key={`empty-${i}`} className="h-16 rounded-lg bg-stone-50/40 border border-transparent" />
          ))}

          {/* Month days */}
          {Array.from({ length: daysInMonth }).map((_, i) => {
            const dayNum = i + 1;
            const dayStr = `${targetYear}-${String(targetMonth).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
            const dayObj = daysMap[dayStr] || {
              day: dayNum,
              date: dayStr,
              isFull: false,
              availableSlots: totalStock || 1,
              status: 'available',
            };

            const isStart = dayStr === tglSewa || dayStr === pickingStart;
            const isEnd = dayStr === tglKembali;
            const isInRange = tglSewa && tglKembali && dayStr > tglSewa && dayStr < tglKembali;
            const isFull = dayObj.isFull;
            const isLimited = dayObj.status === 'limited';

            let cellStyle = 'bg-white border-stone-200 text-stone-800 hover:border-stone-400';
            let badge = (
              <span className="text-[9px] font-semibold text-emerald-700 block truncate">
                {dayObj.availableSlots} slot
              </span>
            );

            const isToday = dayStr === todayStr;
            const isTomorrow = dayStr === tomorrowStr;

            if (isFull) {
              cellStyle = 'bg-red-50/90 border-red-200 text-red-600 cursor-not-allowed';
              badge = <span className="text-[9px] font-bold text-red-600 block">Penuh (0 slot)</span>;
            } else if (isLimited) {
              cellStyle = 'bg-amber-50/90 border-amber-300 text-amber-900 hover:border-amber-500';
              badge = <span className="text-[9px] font-bold text-amber-700 block">Sisa 1</span>;
            }

            if (isStart || isEnd) {
              cellStyle = 'bg-[#2C1A0E] text-white border-[#2C1A0E] shadow-xs';
              badge = <span className="text-[9px] font-bold text-amber-300 block">{isStart ? 'Mulai' : 'Kembali'}</span>;
            } else if (isInRange) {
              cellStyle = 'bg-amber-100/90 text-stone-900 border-amber-200 font-semibold';
              badge = <span className="text-[9px] font-medium text-amber-900 block">Terpilih</span>;
            }

            return (
              <button
                key={dayStr}
                type="button"
                onClick={() => handleDayClick(dayStr, dayObj)}
                className={`h-16 p-1.5 rounded-lg border text-left flex flex-col justify-between transition-all ${cellStyle}`}
                title={isFull ? 'Slot sewa pada tanggal ini sudah penuh (Full Booked)' : `Tersedia ${dayObj.availableSlots} slot unit`}
              >
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-1">
                    <span className={`text-xs font-bold ${isStart || isEnd ? 'text-white' : ''}`}>
                      {dayNum}
                    </span>
                    {isToday && (
                      <span className={`text-[8px] font-extrabold px-1 rounded ${isStart || isEnd ? 'bg-amber-400 text-stone-900' : 'bg-stone-200 text-stone-700'}`}>
                        Hari Ini
                      </span>
                    )}
                    {isTomorrow && (
                      <span className={`text-[8px] font-semibold px-1 rounded ${isStart || isEnd ? 'bg-amber-300 text-stone-900' : 'bg-stone-100 text-stone-600'}`}>
                        Besok
                      </span>
                    )}
                  </div>
                  {isFull && <span className="w-1.5 h-1.5 rounded-full bg-red-500" />}
                  {!isFull && isLimited && <span className="w-1.5 h-1.5 rounded-full bg-amber-500" />}
                  {!isFull && !isLimited && !isStart && !isEnd && (
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                  )}
                </div>
                {badge}
              </button>
            );
          })}
        </div>
      </div>

      {/* Selected Range Notification Bar */}
      <div className="p-3.5 bg-stone-50 border border-stone-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div>
          <span className="text-stone-500 block text-[11px]">Jadwal Sewa Dipilih:</span>
          {tglSewa && tglKembali ? (
            <span className="font-bold text-stone-900 text-sm">
              {tglSewa} s/d {tglKembali} ({Math.max(1, Math.ceil((new Date(tglKembali) - new Date(tglSewa)) / (1000 * 60 * 60 * 24)))} hari)
            </span>
          ) : tglSewa ? (
            <span className="font-bold text-amber-800">
              Mulai: {tglSewa} — Silakan klik tanggal selesai/pengembalian
            </span>
          ) : (
            <span className="text-stone-400">Silakan klik tanggal pada kalender di atas untuk memilih</span>
          )}
        </div>

        {tglSewa && (
          <button
            type="button"
            onClick={() => { onRangeSelect('', ''); setPickingStart(null); }}
            className="text-stone-500 hover:text-stone-800 font-semibold underline text-xs self-start sm:self-auto"
          >
            Reset Pilihan
          </button>
        )}
      </div>

      {/* Legend */}
      <div className="flex flex-wrap items-center gap-4 text-xs text-stone-600 pt-1 border-t border-stone-100">
        <div className="flex items-center gap-1.5">
          <span className="w-3 h-3 rounded-sm bg-white border border-stone-300 inline-block" />
          <span>Tersedia / Kosong</span>
        </div>
        <div className="flex items-center gap-1.5">
          <span className="w-3 h-3 rounded-sm bg-amber-100 border border-amber-300 inline-block" />
          <span>Stok Menipis (Sisa 1)</span>
        </div>
        <div className="flex items-center gap-1.5">
          <span className="w-3 h-3 rounded-sm bg-red-100 border border-red-300 inline-block" />
          <span className="font-semibold text-red-600">Terisi Penuh (Tanggal Merah)</span>
        </div>
        <div className="flex items-center gap-1.5">
          <span className="w-3 h-3 rounded-sm bg-[#2C1A0E] inline-block" />
          <span className="font-semibold text-[#2C1A0E]">Jadwal Pilihan Anda</span>
        </div>
      </div>
    </div>
  );
}

/* ── Sewa Modal (Tokopedia-style Clean Stepper with Interactive Calendar) ── */
function SewaModal({ kebaya, selectedSize, initialTglSewa, initialTglKembali, initialSkema, customColor, customImage, onClose }) {
  const navigate = useNavigate()
  const [step, setStep] = useState(1)
  const [loading, setLoading] = useState(false)
  const [form, setForm] = useState({
    nama: '',
    no_wa: '',
    alamat: '',
    ukuran: selectedSize || 'M',
    tanggal_sewa: initialTglSewa || '',
    tanggal_pengembalian: initialTglKembali || '',
    metode_pembayaran: 'qris',
    skema_pembayaran: initialSkema || 'dp_50',
  })
  const [modalRangeAvail, setModalRangeAvail] = useState(null)

  useEffect(() => {
    if (!kebaya?.slug || !form.tanggal_sewa) return
    api.get(`/kebaya/${kebaya.slug}/check-range`, {
      params: { start: form.tanggal_sewa, end: form.tanggal_pengembalian || form.tanggal_sewa }
    }).then(r => setModalRangeAvail(r.data)).catch(() => {})
  }, [kebaya?.slug, form.tanggal_sewa, form.tanggal_pengembalian])

  const set = (k) => (e) => setForm(f => ({ ...f, [k]: e.target.value }))

  const tSewa = form.tanggal_sewa ? new Date(form.tanggal_sewa) : null
  const tKembali = form.tanggal_pengembalian ? new Date(form.tanggal_pengembalian) : null
  const durasi = (tSewa && tKembali && tKembali >= tSewa)
    ? Math.max(1, Math.ceil((tKembali - tSewa) / (1000 * 60 * 60 * 24)))
    : 1
  const totalHarga = (kebaya.harga_sewa_per_hari || 0) * durasi

  const submit = async () => {
    setLoading(true)
    try {
      const { data } = await api.post('/transaksi', {
        ...form,
        nama_user: form.nama,
        nama_kebaya: kebaya.nama_kebaya,
        kebaya_id: kebaya.slug,
        warna_custom_ai: customColor || 'Default',
        gambar_ai_result: customImage || null,
        total_harga: totalHarga,
      })
      navigate(`/payment/${data.id || data.transaksi?.id}`)
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal membuat transaksi sewa')
    } finally {
      setLoading(false)
    }
  }

  const inputCls = 'w-full rounded-lg border border-stone-300 bg-white px-3.5 py-2 text-xs text-stone-800 focus:border-[#2C1A0E] focus:ring-1 focus:ring-[#2C1A0E] outline-none transition'
  const labelCls = 'block text-xs font-semibold text-stone-700 mb-1'

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4" onClick={onClose}>
      <div className="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[92vh] overflow-y-auto" onClick={e => e.stopPropagation()}>
        {/* Modal Header */}
        <div className="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
          <div>
            <h3 className="text-base font-bold text-stone-900">Formulir Pemesanan Sewa</h3>
            <p className="text-xs text-stone-500">{kebaya.nama_kebaya} • {fmt.format(kebaya.harga_sewa_per_hari)}/hari</p>
          </div>
          <button onClick={onClose} className="text-stone-400 hover:text-stone-700 text-2xl leading-none">&times;</button>
        </div>

        {/* Stepper Progress */}
        <div className="flex items-center justify-center gap-2 py-3 px-6 bg-stone-50 border-b border-stone-100">
          {[
            { step: 1, label: 'Data Penyewa' },
            { step: 2, label: 'Jadwal & Bayar' },
            { step: 3, label: 'Konfirmasi' },
          ].map(s => (
            <div key={s.step} className="flex items-center gap-2">
              <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold ${
                s.step <= step ? 'bg-[#2C1A0E] text-white' : 'bg-stone-200 text-stone-500'
              }`}>
                {s.step}
              </span>
              <span className={`text-xs ${s.step <= step ? 'font-semibold text-stone-800' : 'text-stone-400'}`}>
                {s.label}
              </span>
              {s.step < 3 && <div className="w-4 h-px bg-stone-300" />}
            </div>
          ))}
        </div>

        <div className="p-6 space-y-4">
          {step === 1 && (
            <>
              <div>
                <label className={labelCls}>Nama Lengkap *</label>
                <input className={inputCls} value={form.nama} onChange={set('nama')} placeholder="Nama sesuai identitas" required />
              </div>
              <div>
                <label className={labelCls}>Nomor WhatsApp Aktif *</label>
                <input className={inputCls} value={form.no_wa} onChange={set('no_wa')} placeholder="Contoh: 081234567890" required />
              </div>
              <div>
                <label className={labelCls}>Alamat Pengambilan / Domisili</label>
                <textarea className={inputCls} rows={2} value={form.alamat} onChange={set('alamat')} placeholder="Alamat lengkap kota domisili" />
              </div>
              <div>
                <label className={labelCls}>Pilihan Ukuran</label>
                <select className={inputCls} value={form.ukuran} onChange={set('ukuran')}>
                  {SIZES.map(s => <option key={s} value={s}>{s}</option>)}
                </select>
              </div>
              <button
                type="button"
                onClick={() => setStep(2)}
                disabled={!form.nama || !form.no_wa}
                className="w-full py-2.5 rounded-lg bg-[#2C1A0E] text-white font-bold text-xs hover:bg-[#3D2518] transition disabled:opacity-40"
              >
                Lanjutkan ke Jadwal & Pembayaran &rarr;
              </button>
            </>
          )}

          {step === 2 && (
            <>
              {/* Interactive Calendar inside Modal */}
              <AvailabilityCalendar
                slug={kebaya.slug}
                totalStock={kebaya.stok}
                tglSewa={form.tanggal_sewa}
                tglKembali={form.tanggal_pengembalian}
                onRangeSelect={(start, end) => {
                  setForm(f => ({ ...f, tanggal_sewa: start, tanggal_pengembalian: end }));
                }}
              />

              <div>
                <label className={labelCls}>Metode Pembayaran</label>
                <select className={inputCls} value={form.metode_pembayaran} onChange={set('metode_pembayaran')}>
                  <option value="qris">QRIS (Otomatis & Terverifikasi Cepat)</option>
                  <option value="transfer">Transfer Bank (BCA / Mandiri / BRI)</option>
                  <option value="cod">Bayar di Tempat (COD Butik Mayangsari)</option>
                </select>
              </div>
              <div>
                <label className={labelCls}>Skema Pembayaran</label>
                <div className="grid grid-cols-3 gap-2">
                  {SCHEMES.map(s => (
                    <button
                      key={s.value}
                      type="button"
                      onClick={() => setForm(f => ({ ...f, skema_pembayaran: s.value }))}
                      className={`py-2 px-1 rounded-lg border text-xs font-semibold text-center transition ${
                        form.skema_pembayaran === s.value
                          ? 'border-[#2C1A0E] bg-stone-100 text-stone-900 ring-1 ring-[#2C1A0E]'
                          : 'border-stone-200 text-stone-500 hover:border-stone-300'
                      }`}
                    >
                      {s.label}
                    </button>
                  ))}
                </div>
              </div>
              {modalRangeAvail?.isFull && (
                <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium flex items-start gap-2">
                  <svg className="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  <span>
                    Stok kebaya ini penuh pada jadwal {form.tanggal_sewa} s/d {form.tanggal_pengembalian}. Silakan pilih tanggal lain yang bertanda hijau pada kalender di atas.
                  </span>
                </div>
              )}

              <div className="flex gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setStep(1)}
                  className="flex-1 py-2.5 rounded-lg border border-stone-300 text-stone-700 text-xs font-semibold hover:bg-stone-50 transition"
                >
                  Kembali
                </button>
                <button
                  type="button"
                  onClick={() => setStep(3)}
                  disabled={!form.tanggal_sewa || !form.tanggal_pengembalian || modalRangeAvail?.isFull}
                  className="flex-1 py-2.5 rounded-lg bg-[#2C1A0E] text-white text-xs font-bold hover:bg-[#3D2518] transition disabled:opacity-40"
                >
                  {modalRangeAvail?.isFull ? 'Jadwal Penuh (Pilih Tanggal Lain)' : 'Lihat Ringkasan →'}
                </button>
              </div>
            </>
          )}

          {step === 3 && (
            <>
              <div className="bg-stone-50 rounded-xl p-4 space-y-2 text-xs border border-stone-200">
                <h4 className="font-bold text-stone-800 border-b border-stone-200 pb-2 mb-2">Ringkasan Pesanan Sewa</h4>
                <div className="flex justify-between"><span className="text-stone-500">Busana</span><span className="font-semibold text-stone-800">{kebaya.nama_kebaya}</span></div>
                {customColor && <div className="flex justify-between"><span className="text-stone-500">Warna Simulasi</span><span className="font-semibold text-amber-800">{customColor}</span></div>}
                <div className="flex justify-between"><span className="text-stone-500">Ukuran</span><span className="font-semibold text-stone-800">{form.ukuran}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Periode Sewa</span><span className="font-semibold text-stone-800">{form.tanggal_sewa} s/d {form.tanggal_pengembalian} ({durasi} hari)</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Metode</span><span className="font-semibold uppercase text-stone-800">{form.metode_pembayaran}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Skema</span><span className="font-semibold text-stone-800">{SCHEMES.find(s => s.value === form.skema_pembayaran)?.label}</span></div>
                <div className="pt-2 border-t border-stone-200 flex justify-between items-baseline">
                  <span className="font-bold text-stone-800">Total Biaya Sewa</span>
                  <span className="font-extrabold text-sm text-[#2C1A0E]">{fmt.format(totalHarga)}</span>
                </div>
              </div>

              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => setStep(2)}
                  className="flex-1 py-2.5 rounded-lg border border-stone-300 text-stone-700 text-xs font-semibold hover:bg-stone-50 transition"
                >
                  Kembali
                </button>
                <button
                  type="button"
                  onClick={submit}
                  disabled={loading}
                  className="flex-1 py-2.5 rounded-lg bg-[#2C1A0E] text-white text-xs font-bold hover:bg-[#3D2518] transition disabled:opacity-50"
                >
                  {loading ? 'Menyimpan...' : 'Konfirmasi & Lanjut Bayar'}
                </button>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  )
}
