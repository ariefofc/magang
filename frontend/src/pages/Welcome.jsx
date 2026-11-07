import { useState, useEffect } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import api from '../services/api'
import { cfg, brandParts } from '../config'
import LiveChat from '../components/LiveChat'

const fmt = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })

const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL']
const SCHEMES = [
  { label: 'DP 50%', value: 'dp_50' },
  { label: 'DP 70%', value: 'dp_70' },
  { label: 'Lunas 100%', value: 'lunas' },
]
const STEPS_SEWA = [
  {
    icon: (
      <svg className="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    ),
    title: 'Pilih Kebaya',
    desc: 'Jelajahi katalog koleksi kami dengan ketersediaan stok aktual dan tampak depan & belakang.',
  },
  {
    icon: (
      <svg className="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
    ),
    title: 'Isi Data Sewa',
    desc: 'Pilih ukuran busana, tentukan tanggal sewa dan pengembalian, serta lengkapi kontak.',
  },
  {
    icon: (
      <svg className="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
      </svg>
    ),
    title: 'Pembayaran Fleksibel',
    desc: 'Pilihan DP 50%, DP 70%, atau Lunas via QRIS, Transfer Bank, maupun Bayar di Tempat.',
  },
  {
    icon: (
      <svg className="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M5 13l4 4L19 7" />
      </svg>
    ),
    title: 'Fitting & Ambil',
    desc: 'Jadwalkan fitting di butik kami atau langsung ambil kebaya dalam kondisi siap pakai.',
  },
]

const SERVICES = [
  {
    icon: (
      <svg className="w-7 h-7 text-[#2C1A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M7 21a4 4 0 01-4-4 5 5 0 014-4h4a5 5 0 014 4 4 4 0 01-4 4H7zm0 0a4 4 0 004-4v-1a5 5 0 00-5-5H6a5 5 0 00-5 5v1a4 4 0 004 4h2z" />
      </svg>
    ),
    title: 'Simulasi Warna Kain',
    desc: 'Visualisasikan kain kebaya dalam berbagai varian warna sebelum memesan.',
  },
  {
    icon: (
      <svg className="w-7 h-7 text-[#2C1A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
      </svg>
    ),
    title: 'Konsultasi Live Chat',
    desc: 'Bantuan instan mengenai fitting, rekomendasi busana, dan FAQ operasional.',
  },
  {
    icon: (
      <svg className="w-7 h-7 text-[#2C1A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
      </svg>
    ),
    title: 'Pembayaran QRIS & Bank',
    desc: 'Pembayaran digital terverifikasi otomatis dengan bukti transaksi sah.',
  },
  {
    icon: (
      <svg className="w-7 h-7 text-[#2C1A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
    ),
    title: 'Nota & Bukti Sewa Digital',
    desc: 'Invoice digital resmi lengkap dengan rincian jadwal sewa dan barcode status.',
  },
]

function SewaModal({ kebaya, onClose }) {
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
  const totalHarga = (kebaya.harga || 0) * durasi

  const submit = async () => {
    setLoading(true)
    try {
      const { data } = await api.post('/transaksi', {
        ...form,
        nama_user: form.nama,
        nama_kebaya: kebaya.nama,
        kebaya_id: kebaya.id || kebaya.slug,
        total_harga: totalHarga,
      })
      navigate(`/payment/${data.id || data.transaksi?.id}`)
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal membuat transaksi')
    } finally {
      setLoading(false)
    }
  }

  const today = new Date().toISOString().slice(0, 10)

  const inputCls = 'w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm text-stone-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 outline-none transition'
  const labelCls = 'block text-sm font-medium text-stone-700 mb-1'

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" onClick={onClose}>
      <div className="bg-[#FAF8F5] rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
        {/* Header */}
        <div className="bg-[#2C1A0E] text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
          <div>
            <h3 className="text-lg font-bold">Sewa Kebaya</h3>
            <p className="text-amber-300 text-sm">{kebaya.nama} — {fmt.format(kebaya.harga)}</p>
          </div>
          <button onClick={onClose} className="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
        </div>

        {/* Step indicator */}
        <div className="flex items-center justify-center gap-2 py-4 px-6">
          {[1, 2, 3].map((s) => (
            <div key={s} className="flex items-center gap-2">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition ${s <= step ? 'bg-amber-400 text-[#2C1A0E]' : 'bg-stone-200 text-stone-400'}`}>{s}</div>
              {s < 3 && <div className={`w-8 h-0.5 ${s < step ? 'bg-amber-400' : 'bg-stone-200'}`} />}
            </div>
          ))}
        </div>

        <div className="px-6 pb-6 space-y-4">
          {step === 1 && (
            <>
              <div><label className={labelCls}>Nama Lengkap</label><input className={inputCls} value={form.nama} onChange={set('nama')} placeholder="Nama Anda" required /></div>
              <div><label className={labelCls}>No. WhatsApp</label><input className={inputCls} value={form.no_wa} onChange={set('no_wa')} placeholder="08xxxxxxxxxx" /></div>
              <div><label className={labelCls}>Alamat</label><textarea className={inputCls} rows={2} value={form.alamat} onChange={set('alamat')} placeholder="Alamat lengkap" /></div>
              <div><label className={labelCls}>Ukuran</label>
                <select className={inputCls} value={form.ukuran} onChange={set('ukuran')}>
                  {SIZES.map((s) => <option key={s} value={s}>{s}</option>)}
                </select>
              </div>
              <button onClick={() => setStep(2)} disabled={!form.nama || !form.no_wa} className="w-full py-2.5 rounded-lg bg-amber-400 text-[#2C1A0E] font-bold hover:bg-amber-500 transition disabled:opacity-40">Lanjut</button>
            </>
          )}

          {step === 2 && (
            <>
              <div><label className={labelCls}>Tanggal Sewa</label><input type="date" min={today} className={inputCls} value={form.tanggal_sewa} onChange={set('tanggal_sewa')} /></div>
              <div><label className={labelCls}>Tanggal Pengembalian</label><input type="date" min={form.tanggal_sewa || today} className={inputCls} value={form.tanggal_pengembalian} onChange={set('tanggal_pengembalian')} /></div>
              <div><label className={labelCls}>Metode Pembayaran</label>
                <select className={inputCls} value={form.metode_pembayaran} onChange={set('metode_pembayaran')}>
                  <option value="qris">QRIS</option>
                  <option value="transfer">Transfer Bank</option>
                  <option value="cod">Bayar di Tempat</option>
                </select>
              </div>
              <div><label className={labelCls}>Skema Pembayaran</label>
                <div className="grid grid-cols-3 gap-2">
                  {SCHEMES.map((s) => (
                    <button key={s.value} type="button" onClick={() => setForm((f) => ({ ...f, skema_pembayaran: s.value }))}
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
            </>
          )}

          {step === 3 && (
            <>
              <div className="bg-white rounded-xl p-4 space-y-2 text-sm border border-stone-200">
                <h4 className="font-bold text-stone-800 mb-2">Ringkasan Pesanan</h4>
                <div className="flex justify-between"><span className="text-stone-500">Kebaya</span><span className="font-medium">{kebaya.nama}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Durasi</span><span className="font-medium">{durasi} hari</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Total Harga</span><span className="font-bold text-amber-600">{fmt.format(totalHarga)}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Nama</span><span className="font-medium">{form.nama}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">WhatsApp</span><span className="font-medium">{form.no_wa}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Ukuran</span><span className="font-medium">{form.ukuran}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Tanggal Sewa</span><span className="font-medium">{form.tanggal_sewa}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Pengembalian</span><span className="font-medium">{form.tanggal_pengembalian}</span></div>
                <div className="flex justify-between"><span className="text-stone-500">Pembayaran</span><span className="font-medium">{SCHEMES.find((s) => s.value === form.skema_pembayaran)?.label}</span></div>
              </div>
              <div className="flex gap-3">
                <button onClick={() => setStep(2)} className="flex-1 py-2.5 rounded-lg border border-stone-300 text-stone-600 font-medium hover:bg-stone-100 transition">Kembali</button>
                <button onClick={submit} disabled={loading} className="flex-1 py-2.5 rounded-lg bg-amber-400 text-[#2C1A0E] font-bold hover:bg-amber-500 transition disabled:opacity-60">
                  {loading ? 'Memproses...' : 'Konfirmasi & Bayar'}
                </button>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  )
}

function KebayaCard({ k }) {
  const [showBack, setShowBack] = useState(false);
  const isAvailable = k.tersedia && (k.stok_tersedia ?? 1) > 0;
  const currentImg = showBack && k.gambarBelakang ? k.gambarBelakang : k.gambar;

  return (
    <div className="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-stone-200/90 flex flex-col">
      <div className="relative overflow-hidden aspect-[3/4] bg-stone-100">
        <Link to={`/kebaya/${k.slug}`} className="block w-full h-full">
          <img
            src={currentImg || 'https://placehold.co/400x533/2C1A0E/fbbf24?text=Kebaya'}
            alt={k.nama}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            onError={(e) => { e.target.src = 'https://placehold.co/400x533/2C1A0E/fbbf24?text=Kebaya' }}
          />
        </Link>

        {/* Top Floating Badges */}
        <div className="absolute top-3 inset-x-3 flex items-center justify-between pointer-events-none">
          {isAvailable ? (
            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-600 text-white shadow-md">
              <span className="w-2 h-2 rounded-full bg-white animate-pulse" />
              Tersedia • Sisa {k.stok_tersedia}
            </span>
          ) : (
            <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-stone-900/90 text-white shadow-md">
              <span className="w-2 h-2 rounded-full bg-red-400" />
              Habis / Disewa
            </span>
          )}

          {k.gambarBelakang && (
            <span className="text-[11px] font-medium bg-black/60 text-white px-2 py-0.5 rounded backdrop-blur-xs">
              {showBack ? 'Belakang' : 'Depan'}
            </span>
          )}
        </div>

        {/* Front / Back Toggle Buttons on Image */}
        {k.gambarBelakang && (
          <div className="absolute bottom-3 right-3 flex items-center gap-1 bg-white/95 backdrop-blur-sm p-1 rounded-lg shadow-md border border-stone-200">
            <button
              type="button"
              onClick={(e) => { e.preventDefault(); e.stopPropagation(); setShowBack(false); }}
              className={`px-2.5 py-1 text-[11px] font-bold rounded transition ${!showBack ? 'bg-[#2C1A0E] text-white' : 'text-stone-600 hover:text-stone-900'}`}
            >
              Depan
            </button>
            <button
              type="button"
              onClick={(e) => { e.preventDefault(); e.stopPropagation(); setShowBack(true); }}
              className={`px-2.5 py-1 text-[11px] font-bold rounded transition ${showBack ? 'bg-[#2C1A0E] text-white' : 'text-stone-600 hover:text-stone-900'}`}
            >
              Belakang
            </button>
          </div>
        )}
      </div>

      <div className="p-5 flex-1 flex flex-col justify-between">
        <div>
          <div className="flex items-center justify-between text-xs mb-2">
            <span className={`inline-flex items-center gap-1 font-semibold ${isAvailable ? 'text-emerald-700' : 'text-stone-500'}`}>
              <span className={`w-1.5 h-1.5 rounded-full ${isAvailable ? 'bg-emerald-500' : 'bg-stone-400'}`} />
              {isAvailable ? `Tersedia (${k.stok_tersedia} siap sewa)` : 'Sedang disewa'}
            </span>
            <span className="text-stone-400">Total: {k.stok || 1} unit</span>
          </div>

          <Link to={`/kebaya/${k.slug}`}>
            <h3 className="font-bold text-[#2C1A0E] text-lg mb-1.5 hover:text-amber-700 transition truncate">{k.nama}</h3>
          </Link>
          <p className="text-stone-500 text-sm mb-4 line-clamp-2 leading-relaxed">{k.deskripsi || 'Kebaya premium berkualitas tinggi'}</p>
        </div>

        <div className="pt-3 border-t border-stone-100 flex items-center justify-between">
          <div>
            <span className="text-[11px] text-stone-400 block uppercase tracking-wider font-semibold">Harga Sewa</span>
            <span className="text-amber-700 font-extrabold text-lg">{fmt.format(k.harga)}</span>
          </div>
          <Link
            to={`/kebaya/${k.slug}`}
            className="px-4 py-2 rounded-lg bg-[#2C1A0E] text-white text-xs font-bold hover:bg-[#3D2518] transition inline-flex items-center gap-1.5 shadow-sm"
          >
            Detail & Sewa
            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default function Welcome() {
  const [kebayas, setKebayas] = useState([])
  const [selected, setSelected] = useState(null) // ponytail: kept for SewaModal if re-enabled
  const [menuOpen, setMenuOpen] = useState(false)

  useEffect(() => {
    api.get('/kebaya').then((r) => {
      const list = Array.isArray(r.data) ? r.data : r.data?.kebaya ?? []
      setKebayas(list.map((k) => ({
        ...k,
        nama: k.nama_kebaya ?? k.nama,
        harga: k.harga_sewa_per_hari ?? k.harga,
        gambar: k.foto_url ?? k.gambar,
        gambarBelakang: k.foto_belakang_url ?? null,
        stok: k.stok ?? 1,
        stok_tersedia: k.stok_tersedia ?? 1,
        tersedia: k.tersedia ?? true,
      })))
    }).catch(() => {})
  }, [])

  const scrollTo = (id) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
    setMenuOpen(false)
  }

  const NAV = [
    { label: 'Beranda', id: 'hero' },
    { label: 'Koleksi', id: 'koleksi' },
    { label: 'Cara Sewa', id: 'cara-sewa' },
    { label: 'Tentang', id: 'tentang' },
  ]

  return (
    <div className="min-h-screen bg-[#FAF8F5] text-stone-800">
      {/* ── Navbar ── */}
      <nav className="fixed top-0 inset-x-0 z-40 bg-[#2C1A0E]/95 backdrop-blur-md border-b border-white/5">
        <div className="max-w-7xl mx-auto flex items-center justify-between px-6 py-3">
          <button onClick={() => scrollTo('hero')} className="text-xl font-bold tracking-wide text-white">
            {brandParts().first} <span className="text-amber-400">{brandParts().rest}</span>
          </button>
          {/* Desktop nav */}
          <div className="hidden md:flex items-center gap-8">
            {NAV.map((n) => (
              <button key={n.id} onClick={() => scrollTo(n.id)} className="text-sm text-stone-300 hover:text-amber-400 transition font-medium">{n.label}</button>
            ))}
            <Link to="/login" className="px-5 py-2 rounded-full bg-amber-400 text-[#2C1A0E] text-sm font-bold hover:bg-amber-500 transition">Login</Link>
          </div>
          {/* Mobile hamburger */}
          <button onClick={() => setMenuOpen(!menuOpen)} className="md:hidden text-white text-2xl">&#9776;</button>
        </div>
        {menuOpen && (
          <div className="md:hidden bg-[#2C1A0E] border-t border-white/10 px-6 pb-4 space-y-3">
            {NAV.map((n) => (
              <button key={n.id} onClick={() => scrollTo(n.id)} className="block w-full text-left text-stone-300 hover:text-amber-400 text-sm py-1">{n.label}</button>
            ))}
            <Link to="/login" className="block text-center px-5 py-2 rounded-full bg-amber-400 text-[#2C1A0E] text-sm font-bold">Login</Link>
          </div>
        )}
      </nav>

      {/* ── Hero ── */}
      <section id="hero" className="pt-28 pb-20 md:pt-40 md:pb-32 px-6 bg-gradient-to-b from-[#2C1A0E] via-[#3D2518] to-[#FAF8F5]">
        <div className="max-w-4xl mx-auto text-center">
          <p className="text-amber-400 font-semibold tracking-widest uppercase text-sm mb-4">{cfg.APP_TAGLINE}</p>
          <h1 className="text-4xl md:text-6xl font-extrabold text-white leading-tight mb-6">
            Keanggunan Kebaya,<br />Sentuhan <span className="text-amber-400">Modern</span>
          </h1>
          <p className="text-stone-300 text-lg md:text-xl max-w-2xl mx-auto mb-10">
            {cfg.APP_DESCRIPTION}
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <button onClick={() => scrollTo('koleksi')} className="px-8 py-3 rounded-full bg-amber-400 text-[#2C1A0E] font-bold text-lg hover:bg-amber-500 shadow-lg shadow-amber-400/20 transition">
              Lihat Koleksi
            </button>
            <button onClick={() => scrollTo('cara-sewa')} className="px-8 py-3 rounded-full border-2 border-white/30 text-white font-semibold text-lg hover:border-amber-400 hover:text-amber-400 transition">
              Cara Sewa
            </button>
          </div>
        </div>
      </section>

      {/* ── Koleksi ── */}
      <section id="koleksi" className="py-20 px-6">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-14">
            <p className="text-amber-600 font-semibold tracking-widest uppercase text-sm mb-2">Katalog</p>
            <h2 className="text-3xl md:text-4xl font-bold text-[#2C1A0E]">Koleksi Kebaya Kami</h2>
          </div>
          {kebayas.length === 0 ? (
            <p className="text-center text-stone-400 py-12">Memuat koleksi...</p>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {kebayas.map((k) => (
                <KebayaCard key={k.slug || k.id} k={k} />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* ── Cara Sewa ── */}
      <section id="cara-sewa" className="py-20 px-6 bg-[#2C1A0E]">
        <div className="max-w-5xl mx-auto">
          <div className="text-center mb-14">
            <p className="text-amber-400 font-semibold tracking-widest uppercase text-sm mb-2">Panduan</p>
            <h2 className="text-3xl md:text-4xl font-bold text-white">Cara Sewa Kebaya</h2>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            {STEPS_SEWA.map((s, i) => (
              <div key={i} className="text-center">
                <div className="w-14 h-14 rounded-full bg-amber-400/10 border-2 border-amber-400/80 flex items-center justify-center mx-auto mb-4">{s.icon}</div>
                <div className="text-amber-400 text-xs font-bold mb-2">STEP {i + 1}</div>
                <h3 className="text-white font-bold text-lg mb-2">{s.title}</h3>
                <p className="text-stone-400 text-sm">{s.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Layanan ── */}
      <section id="tentang" className="py-20 px-6">
        <div className="max-w-6xl mx-auto">
          <div className="text-center mb-14">
            <p className="text-amber-600 font-semibold tracking-widest uppercase text-sm mb-2">Layanan</p>
            <h2 className="text-3xl md:text-4xl font-bold text-[#2C1A0E]">Fitur Unggulan Kami</h2>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {SERVICES.map((s, i) => (
              <div key={i} className="bg-white rounded-2xl p-6 shadow-sm border border-stone-200/80 hover:shadow-md hover:-translate-y-1 transition-all duration-300 text-center">
                <div className="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200/70 flex items-center justify-center mx-auto mb-4">{s.icon}</div>
                <h3 className="font-bold text-[#2C1A0E] text-lg mb-2">{s.title}</h3>
                <p className="text-stone-500 text-sm">{s.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Footer ── */}
      <footer className="bg-[#2C1A0E] text-stone-400 py-12 px-6">
        <div className="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10">
          <div>
            <h3 className="text-xl font-bold text-white mb-3">{brandParts().first} <span className="text-amber-400">{brandParts().rest}</span></h3>
            <p className="text-sm leading-relaxed">Penyewaan kebaya premium untuk berbagai acara istimewa. Kualitas terbaik dengan harga terjangkau.</p>
          </div>
          <div>
            <h4 className="font-bold text-white mb-3">Kontak</h4>
            <p className="text-sm mb-1">WhatsApp: {cfg.CONTACT_WA}</p>
            <p className="text-sm mb-1">Email: {cfg.CONTACT_EMAIL}</p>
            <p className="text-sm">Instagram: {cfg.CONTACT_IG}</p>
          </div>
          <div>
            <h4 className="font-bold text-white mb-3">Alamat</h4>
            <p className="text-sm leading-relaxed">{cfg.STUDIO_ADDRESS}</p>
          </div>
        </div>
        <div className="max-w-6xl mx-auto border-t border-white/10 mt-10 pt-6 text-center text-xs text-stone-500">
          &copy; {new Date().getFullYear()} {cfg.APP_NAME} Kebaya Rental. All rights reserved.
        </div>
      </footer>

      {/* ── Modal ── */}
      {selected && <SewaModal kebaya={selected} onClose={() => setSelected(null)} />}

      {/* ── Live Chat ── */}
      <LiveChat />
    </div>
  )
}
