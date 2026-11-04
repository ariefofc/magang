import { useState, useEffect } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import api from '../services/api'
import { cfg, brandParts } from '../config'

const fmt = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })

const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL']
const SCHEMES = [
  { label: 'DP 50%', value: 'dp_50' },
  { label: 'DP 70%', value: 'dp_70' },
  { label: 'Lunas 100%', value: 'lunas' },
]

export default function KebayaDetail() {
  const { slug } = useParams()
  const navigate = useNavigate()
  const [kebaya, setKebaya] = useState(null)
  const [loading, setLoading] = useState(true)
  const [activeImg, setActiveImg] = useState(0)
  const [showSewa, setShowSewa] = useState(false)
  const [others, setOthers] = useState([])
  const [recolorColor, setRecolorColor] = useState('')
  const [recolorResult, setRecolorResult] = useState(null)
  const [recolorLoading, setRecolorLoading] = useState(false)

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

  useEffect(() => { setActiveImg(0); setRecolorResult(null); setRecolorColor('') }, [slug])

  if (loading) return (
    <div className="min-h-screen bg-[#FAF8F5] flex items-center justify-center">
      <div className="animate-spin w-10 h-10 border-4 border-amber-400 border-t-transparent rounded-full" />
    </div>
  )

  if (!kebaya) return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center gap-4">
      <p className="text-stone-500 text-lg">Kebaya tidak ditemukan</p>
      <Link to="/" className="px-6 py-2 rounded-full bg-amber-400 text-[#2C1A0E] font-bold hover:bg-amber-500 transition">Kembali ke Beranda</Link>
    </div>
  )

  const images = [kebaya.foto_url, kebaya.foto_belakang_url].filter(Boolean)
  const placeholder = 'https://placehold.co/600x800/2C1A0E/fbbf24?text=Kebaya'

  const specs = [
    { label: 'Kain Utama', value: kebaya.kain_utama, icon: '🧵' },
    { label: 'Furing', value: kebaya.furing, icon: '🪡' },
    { label: 'Karakteristik', value: kebaya.karakteristik_kain, icon: '✨' },
    { label: 'Detail Payet', value: kebaya.detail_payet, icon: '💎' },
    { label: 'Rekomendasi Acara', value: kebaya.rekomendasi_acara, icon: '🎉' },
  ].filter(s => s.value)

  const handleRecolor = async (colorName) => {
    if (!colorName?.trim()) return
    const currentImg = images[activeImg] || kebaya.foto_url
    if (!currentImg) return
    setRecolorLoading(true)
    setRecolorResult(null)
    try {
      // Fetch the current image as base64
      const imgResp = await fetch(currentImg)
      const blob = await imgResp.blob()
      const b64 = await new Promise(resolve => {
        const reader = new FileReader()
        reader.onloadend = () => resolve(reader.result)
        reader.readAsDataURL(blob)
      })

      // Check if pre-generated mask is available
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
      } catch { /* proceed without mask */ }

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
      } catch (e) {
        console.warn('Backend AI recolor unavailable, using client fallback:', e.message)
      }

      // Fallback to client Canvas HSL engine if backend is not available
      if (!ok) {
        if (window.MSHarmonyRecolor) {
          const clientB64 = await window.MSHarmonyRecolor.recolorImage(b64, colorName.trim(), maskB64 || maskUrl)
          setRecolorResult(clientB64)
          ok = true
        } else {
          alert('Fitur recolor sedang tidak tersedia.')
        }
      }
    } catch (err) {
      alert('Gagal mengubah warna: ' + (err.response?.data?.message || err.message))
    } finally {
      setRecolorLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#FAF8F5]">
      {/* Navbar */}
      <nav className="sticky top-0 z-40 bg-[#2C1A0E]/95 backdrop-blur-md border-b border-white/5">
        <div className="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
          <Link to="/" className="text-xl font-bold tracking-wide text-white">
            {brandParts().first} <span className="text-amber-400">{brandParts().rest}</span>
          </Link>
          <Link to="/" className="text-sm text-stone-300 hover:text-amber-400 transition font-medium flex items-center gap-1">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" /></svg>
            Kembali
          </Link>
        </div>
      </nav>

      {/* Breadcrumb */}
      <div className="max-w-7xl mx-auto px-6 py-4">
        <div className="flex items-center gap-2 text-sm text-stone-400">
          <Link to="/" className="hover:text-amber-600 transition">Beranda</Link>
          <span>/</span>
          <span className="text-stone-600 font-medium truncate">{kebaya.nama_kebaya}</span>
        </div>
      </div>

      {/* Main Content — Tokopedia-style 2-column */}
      <div className="max-w-7xl mx-auto px-6 pb-16">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">

          {/* LEFT — Image Gallery */}
          <div className="lg:col-span-5">
            <div className="sticky top-20 space-y-3">
              {/* Main Image */}
              <div className="relative bg-white rounded-2xl overflow-hidden border border-stone-200 shadow-sm aspect-[3/4]">
                <img
                  src={recolorResult || images[activeImg] || placeholder}
                  alt={kebaya.nama_kebaya}
                  className="w-full h-full object-cover"
                  onError={e => { e.target.src = placeholder }}
                />
                {recolorLoading && (
                  <div className="absolute inset-0 bg-white/70 flex flex-col items-center justify-center gap-3">
                    <div className="animate-spin w-10 h-10 border-4 border-amber-400 border-t-transparent rounded-full" />
                    <p className="text-sm font-medium text-stone-600">AI sedang mengubah warna...</p>
                  </div>
                )}
                {recolorResult && !recolorLoading && (
                  <button
                    onClick={() => setRecolorResult(null)}
                    className="absolute top-3 right-3 bg-black/60 text-white text-xs px-3 py-1.5 rounded-full hover:bg-black/80 transition"
                  >
                    Lihat Asli
                  </button>
                )}
                {!kebaya.tersedia && (
                  <div className="absolute inset-0 bg-black/50 flex items-center justify-center">
                    <span className="bg-red-500 text-white text-sm font-bold px-4 py-2 rounded-full">Tidak Tersedia</span>
                  </div>
                )}
                {/* Image counter */}
                {images.length > 1 && (
                  <div className="absolute bottom-3 right-3 bg-black/60 text-white text-xs px-2.5 py-1 rounded-full">
                    {activeImg + 1} / {images.length}
                  </div>
                )}
              </div>
              {/* Thumbnails */}
              {images.length > 1 && (
                <div className="flex gap-2">
                  {images.map((img, i) => (
                    <button
                      key={i}
                      onClick={() => { setActiveImg(i); setRecolorResult(null) }}
                      className={`w-20 h-20 rounded-xl overflow-hidden border-2 transition-all ${activeImg === i ? 'border-amber-400 shadow-md' : 'border-stone-200 opacity-60 hover:opacity-100'}`}
                    >
                      <img src={img} alt="" className="w-full h-full object-cover" onError={e => { e.target.src = placeholder }} />
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* RIGHT — Product Info */}
          <div className="lg:col-span-7 space-y-6">
            {/* Title + Price Card */}
            <div className="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm space-y-4">
              {/* Status badge */}
              <div className="flex items-center gap-2">
                {kebaya.tersedia ? (
                  <span className="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200">
                    <span className="w-1.5 h-1.5 bg-emerald-500 rounded-full" /> Tersedia
                  </span>
                ) : (
                  <span className="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1 rounded-full bg-red-50 text-red-600 border border-red-200">
                    <span className="w-1.5 h-1.5 bg-red-500 rounded-full" /> Tidak Tersedia
                  </span>
                )}
              </div>

              {/* Title */}
              <h1 className="text-2xl md:text-3xl font-bold text-[#2C1A0E] leading-tight">
                {kebaya.nama_kebaya}
              </h1>

              {/* Price */}
              <div className="flex items-baseline gap-2">
                <span className="text-3xl font-extrabold text-amber-600">{fmt.format(kebaya.harga_sewa_per_hari)}</span>
                <span className="text-stone-400 text-sm">/ hari sewa</span>
              </div>

              <hr className="border-stone-100" />

              {/* Description */}
              <div>
                <h3 className="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-2">Deskripsi</h3>
                <p className="text-stone-700 leading-relaxed">{kebaya.deskripsi}</p>
              </div>
            </div>

            {/* Specs Card */}
            {specs.length > 0 && (
              <div className="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm">
                <h3 className="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-4">Detail Produk</h3>
                <div className="space-y-4">
                  {specs.map((s, i) => (
                    <div key={i} className="flex gap-3">
                      <span className="text-xl flex-shrink-0 mt-0.5">{s.icon}</span>
                      <div>
                        <p className="text-xs font-semibold text-stone-400 uppercase tracking-wide">{s.label}</p>
                        <p className="text-stone-700 text-sm mt-0.5">{s.value}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Skema Pembayaran Card */}
            <div className="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm">
              <h3 className="text-sm font-semibold text-stone-500 uppercase tracking-wide mb-4">Skema Pembayaran</h3>
              <div className="grid grid-cols-3 gap-3">
                {SCHEMES.map(s => (
                  <div key={s.value} className="text-center py-3 px-2 rounded-xl bg-amber-50 border border-amber-200">
                    <p className="font-bold text-amber-700 text-sm">{s.label}</p>
                  </div>
                ))}
              </div>
            </div>

            {/* AI Recolor Card */}
            {kebaya.foto_url && (
              <div className="bg-gradient-to-br from-[#2C1A0E] to-[#3D2518] rounded-2xl p-6 border border-stone-700 shadow-sm text-white">
                <div className="flex items-center gap-2 mb-4">
                  <span className="text-2xl">🎨</span>
                  <div>
                    <h3 className="text-sm font-bold uppercase tracking-wide">AI Recolor</h3>
                    <p className="text-xs text-stone-400">Visualisasikan kebaya dalam warna favoritmu</p>
                  </div>
                </div>

                {/* Preset Colors */}
                <div className="flex flex-wrap gap-2 mb-4">
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
                      onClick={() => { setRecolorColor(c.name); handleRecolor(c.name) }}
                      disabled={recolorLoading}
                      className="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 transition text-xs font-medium disabled:opacity-40"
                    >
                      <span className="w-3 h-3 rounded-full border border-white/30" style={{ backgroundColor: c.hex }} />
                      {c.name}
                    </button>
                  ))}
                </div>

                {/* Custom Color Input */}
                <div className="flex gap-2">
                  <input
                    type="text"
                    value={recolorColor}
                    onChange={e => setRecolorColor(e.target.value)}
                    placeholder="Ketik warna (misal: merah muda, teal, gold)"
                    className="flex-1 rounded-lg bg-white/10 border border-white/20 px-3 py-2 text-sm text-white placeholder-stone-400 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 outline-none transition"
                  />
                  <button
                    onClick={() => handleRecolor(recolorColor)}
                    disabled={recolorLoading || !recolorColor.trim()}
                    className="px-4 py-2 rounded-lg bg-amber-400 text-[#2C1A0E] font-bold text-sm hover:bg-amber-500 transition disabled:opacity-40"
                  >
                    {recolorLoading ? '...' : 'Ubah'}
                  </button>
                </div>

                {recolorResult && (
                  <p className="text-xs text-emerald-400 mt-2">Warna berhasil diubah! Klik "Lihat Asli" pada gambar untuk kembali.</p>
                )}
              </div>
            )}

            {/* CTA — Sticky on mobile */}
            <div className="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm lg:sticky lg:bottom-0">
              <div className="flex flex-col sm:flex-row gap-3">
                <button
                  onClick={() => setShowSewa(true)}
                  disabled={!kebaya.tersedia}
                  className="flex-1 py-3.5 rounded-xl bg-amber-400 text-[#2C1A0E] font-bold text-lg hover:bg-amber-500 shadow-lg shadow-amber-400/20 transition disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  Sewa Sekarang
                </button>
                <a
                  href={`https://wa.me/${cfg.CONTACT_WA.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(`Halo, saya tertarik dengan ${kebaya.nama_kebaya}`)}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex-1 py-3.5 rounded-xl border-2 border-[#2C1A0E] text-[#2C1A0E] font-bold text-lg text-center hover:bg-[#2C1A0E] hover:text-white transition"
                >
                  Chat via WA
                </a>
              </div>
            </div>
          </div>
        </div>

        {/* Kebaya Lainnya */}
        {others.length > 0 && (
          <div className="mt-16">
            <h2 className="text-2xl font-bold text-[#2C1A0E] mb-6">Koleksi Lainnya</h2>
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
              {others.map(k => (
                <Link
                  key={k.slug}
                  to={`/kebaya/${k.slug}`}
                  className="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-stone-100"
                >
                  <div className="aspect-[3/4] bg-stone-100 overflow-hidden">
                    <img
                      src={k.foto_url || placeholder}
                      alt={k.nama_kebaya}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      onError={e => { e.target.src = placeholder }}
                    />
                  </div>
                  <div className="p-3">
                    <h3 className="font-semibold text-[#2C1A0E] text-sm truncate">{k.nama_kebaya}</h3>
                    <p className="text-amber-600 font-bold text-sm mt-1">{fmt.format(k.harga_sewa_per_hari)}</p>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Sewa Modal */}
      {showSewa && (
        <SewaModal
          kebaya={kebaya}
          customColor={recolorColor}
          customImage={recolorResult}
          onClose={() => setShowSewa(false)}
        />
      )}
    </div>
  )
}

/* ── Sewa Modal (inline, same as Welcome) ── */
function SewaModal({ kebaya, customColor, customImage, onClose }) {
  const navigate = useNavigate()
  const [step, setStep] = useState(1)
  const [loading, setLoading] = useState(false)
  const [form, setForm] = useState({
    nama: '', no_wa: '', alamat: '', ukuran: 'M',
    tanggal_sewa: '', tanggal_pengembalian: '',
    metode_pembayaran: 'qris', skema_pembayaran: 'dp_50',
  })
  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }))

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
      alert(err.response?.data?.message || 'Gagal membuat transaksi')
    } finally { setLoading(false) }
  }
  const today = new Date().toISOString().slice(0, 10)
  const inputCls = 'w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition'
  const labelCls = 'block text-sm font-medium text-stone-700 mb-1'

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" onClick={onClose}>
      <div className="bg-[#FAF8F5] rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" onClick={e => e.stopPropagation()}>
        <div className="bg-[#2C1A0E] text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
          <div>
            <h3 className="text-lg font-bold">Sewa Kebaya</h3>
            <p className="text-amber-300 text-sm">
              {kebaya.nama_kebaya} — {fmt.format(kebaya.harga_sewa_per_hari)}/hari
              {customColor && <span className="ml-2 text-xs bg-amber-400/20 text-amber-300 px-2 py-0.5 rounded-full font-medium">Warna: {customColor}</span>}
            </p>
          </div>
          <button onClick={onClose} className="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        <div className="flex items-center justify-center gap-2 py-4 px-6">
          {[1, 2, 3].map(s => (
            <div key={s} className="flex items-center gap-2">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition ${s <= step ? 'bg-amber-400 text-[#2C1A0E]' : 'bg-stone-200 text-stone-400'}`}>{s}</div>
              {s < 3 && <div className={`w-8 h-0.5 ${s < step ? 'bg-amber-400' : 'bg-stone-200'}`} />}
            </div>
          ))}
        </div>
        <div className="px-6 pb-6 space-y-4">
          {step === 1 && (<>
            <div><label className={labelCls}>Nama Lengkap</label><input className={inputCls} value={form.nama} onChange={set('nama')} placeholder="Nama Anda" /></div>
            <div><label className={labelCls}>No. WhatsApp</label><input className={inputCls} value={form.no_wa} onChange={set('no_wa')} placeholder="08xxxxxxxxxx" /></div>
            <div><label className={labelCls}>Alamat</label><textarea className={inputCls} rows={2} value={form.alamat} onChange={set('alamat')} placeholder="Alamat lengkap" /></div>
            <div><label className={labelCls}>Ukuran</label>
              <select className={inputCls} value={form.ukuran} onChange={set('ukuran')}>{SIZES.map(s => <option key={s} value={s}>{s}</option>)}</select>
            </div>
            <button onClick={() => setStep(2)} disabled={!form.nama || !form.no_wa} className="w-full py-2.5 rounded-lg bg-amber-400 text-[#2C1A0E] font-bold hover:bg-amber-500 transition disabled:opacity-40">Lanjut</button>
          </>)}
          {step === 2 && (<>
            <div><label className={labelCls}>Tanggal Sewa</label><input type="date" min={today} className={inputCls} value={form.tanggal_sewa} onChange={set('tanggal_sewa')} /></div>
            <div><label className={labelCls}>Tanggal Pengembalian</label><input type="date" min={form.tanggal_sewa || today} className={inputCls} value={form.tanggal_pengembalian} onChange={set('tanggal_pengembalian')} /></div>
            <div><label className={labelCls}>Metode Pembayaran</label>
              <select className={inputCls} value={form.metode_pembayaran} onChange={set('metode_pembayaran')}>
                <option value="qris">QRIS</option><option value="transfer">Transfer Bank</option><option value="cod">Bayar di Tempat</option>
              </select>
            </div>
            <div><label className={labelCls}>Skema Pembayaran</label>
              <div className="grid grid-cols-3 gap-2">
                {SCHEMES.map(s => (
                  <button key={s.value} type="button" onClick={() => setForm(f => ({ ...f, skema_pembayaran: s.value }))}
                    className={`py-2 rounded-lg border text-sm font-medium transition ${form.skema_pembayaran === s.value ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-stone-300 text-stone-500 hover:border-stone-400'}`}>
                    {s.label}
                  </button>
                ))}
              </div>
            </div>
            <div className="flex gap-3">
              <button onClick={() => setStep(1)} className="flex-1 py-2.5 rounded-lg border border-stone-300 text-stone-600 font-medium hover:bg-stone-100 transition">Kembali</button>
              <button onClick={() => setStep(3)} disabled={!form.tanggal_sewa || !form.tanggal_pengembalian} className="flex-1 py-2.5 rounded-lg bg-amber-400 text-[#2C1A0E] font-bold hover:bg-amber-500 transition disabled:opacity-40">Lanjut</button>
            </div>
          </>)}
          {step === 3 && (<>
            <div className="bg-white rounded-xl p-4 space-y-2 text-sm border border-stone-200">
              <h4 className="font-bold text-stone-800 mb-2">Ringkasan Pesanan</h4>
              <div className="flex justify-between"><span className="text-stone-500">Kebaya</span><span className="font-medium">{kebaya.nama_kebaya}</span></div>
              {customColor && (
                <div className="flex justify-between"><span className="text-stone-500">Warna (Custom AI)</span><span className="font-medium text-amber-600">{customColor}</span></div>
              )}
              <div className="flex justify-between"><span className="text-stone-500">Durasi</span><span className="font-medium">{durasi} hari</span></div>
              <div className="flex justify-between"><span className="text-stone-500">Total Harga</span><span className="font-bold text-amber-600">{fmt.format(totalHarga)}</span></div>
              <div className="flex justify-between"><span className="text-stone-500">Nama</span><span className="font-medium">{form.nama}</span></div>
              <div className="flex justify-between"><span className="text-stone-500">WhatsApp</span><span className="font-medium">{form.no_wa}</span></div>
              <div className="flex justify-between"><span className="text-stone-500">Ukuran</span><span className="font-medium">{form.ukuran}</span></div>
              <div className="flex justify-between"><span className="text-stone-500">Tanggal Sewa</span><span className="font-medium">{form.tanggal_sewa}</span></div>
              <div className="flex justify-between"><span className="text-stone-500">Pengembalian</span><span className="font-medium">{form.tanggal_pengembalian}</span></div>
              <div className="flex justify-between"><span className="text-stone-500">Pembayaran</span><span className="font-medium">{SCHEMES.find(s => s.value === form.skema_pembayaran)?.label}</span></div>
            </div>
            <div className="flex gap-3">
              <button onClick={() => setStep(2)} className="flex-1 py-2.5 rounded-lg border border-stone-300 text-stone-600 font-medium hover:bg-stone-100 transition">Kembali</button>
              <button onClick={submit} disabled={loading} className="flex-1 py-2.5 rounded-lg bg-amber-400 text-[#2C1A0E] font-bold hover:bg-amber-500 transition disabled:opacity-60">
                {loading ? 'Memproses...' : 'Konfirmasi & Bayar'}
              </button>
            </div>
          </>)}
        </div>
      </div>
    </div>
  )
}
