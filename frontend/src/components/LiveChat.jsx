import { useState, useEffect, useRef, useCallback } from 'react';
import api from '../services/api';
import { cfg } from '../config';

const TABS = { CHAT: 'chat', FAQ: 'faq', HISTORY: 'history' };

const QUICK_FAQS = [
  { id: 'q1', label: 'Cara Sewa?', question: 'Bagaimana cara menyewa kebaya?', answer: 'Pilih kebaya di katalog → klik "Sewa Sekarang" → tentukan tanggal & ukuran → bayar DP via QRIS/Transfer → busana siap diambil di studio sesuai jadwal.' },
  { id: 'q3', label: 'Skema DP?', question: 'Apakah bisa bayar DP terlebih dahulu?', answer: 'Bisa. Tersedia pilihan DP 50%, DP 70%, maupun Lunas 100%. Pelunasan dapat dilakukan saat fitting atau pengambilan busana.' },
  { id: 'q5', label: 'Fitting Ukuran?', question: 'Bagaimana jika kebaya tidak sesuai ukuran?', answer: 'Kami menyediakan layanan fitting gratis di studio Butik Mayangsari sebelum hari H. Penyesuaian minor dapat langsung dikerjakan penjahit kami.' },
  { id: 'q4', label: 'Metode Bayar?', question: 'Metode pembayaran apa saja yang tersedia?', answer: 'Kami menerima QRIS (DANA/GoPay/OVO/BCA), Transfer Virtual Account/Bank, dan Bayar Tunai (COD) langsung di butik.' },
  { id: 'q2', label: 'Durasi Sewa?', question: 'Berapa lama durasi penyewaan?', answer: 'Durasi standar sewa mulai dari 1 hari hingga sesuai kebutuhan acara Anda. Tanggal pengembalian dapat Anda atur langsung saat checkout.' },
];

export default function LiveChat() {
  const [open, setOpen] = useState(false);
  const [activeTab, setActiveTab] = useState(TABS.CHAT);
  const [room, setRoom] = useState(null);
  const [messages, setMessages] = useState([]);
  const [rooms, setRooms] = useState([]);
  const [faqs, setFaqs] = useState(QUICK_FAQS);
  const [expandedFaq, setExpandedFaq] = useState(null);
  const [input, setInput] = useState('');
  const [customerName, setCustomerName] = useState('');
  const [loading, setLoading] = useState(false);
  const [starting, setStarting] = useState(false);
  const bottomRef = useRef(null);

  // Load backend FAQ
  useEffect(() => {
    api.get('/chat/faq')
      .then((r) => {
        const list = r.data?.faqs;
        if (Array.isArray(list) && list.length > 0) setFaqs(list);
      })
      .catch(() => {});
  }, []);

  // Start room on first open
  const startRoom = useCallback(async () => {
    setStarting(true);
    try {
      const { data } = await api.post('/chat/start-room', {
        nama: customerName || 'Pelanggan',
      });
      setRoom(data?.room || null);
    } catch {
      /* silent */
    } finally {
      setStarting(false);
    }
  }, [customerName]);

  useEffect(() => {
    if (open && !room && !starting) {
      startRoom();
    }
  }, [open, room, starting, startRoom]);

  const fetchMessages = useCallback(async () => {
    if (!room?.id) return;
    try {
      const { data } = await api.get(`/chat/${room.id}/messages`);
      const msgList = data?.messages || data?.data || data || [];
      setMessages(Array.isArray(msgList) ? msgList : []);
    } catch {
      /* silent */
    }
  }, [room]);

  const fetchRooms = useCallback(async () => {
    try {
      const { data } = await api.get('/chat/my-rooms');
      const rList = data?.rooms || data?.data || data || [];
      setRooms(Array.isArray(rList) ? rList : []);
    } catch {
      /* silent */
    }
  }, []);

  useEffect(() => {
    if (room?.id) {
      fetchMessages();
      fetchRooms();
    }
  }, [room, fetchMessages, fetchRooms]);

  useEffect(() => {
    if (!open || !room?.id) return;
    const interval = setInterval(fetchMessages, cfg.CHAT_POLL_MS || 3000);
    return () => clearInterval(interval);
  }, [open, room, fetchMessages]);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages, activeTab]);

  const handleSend = async (e) => {
    if (e) e.preventDefault();
    if (!input.trim() || !room?.id) return;
    const userText = input.trim();
    setInput('');
    setLoading(true);

    try {
      await api.post(`/chat/${room.id}/send`, {
        message: userText,
        sender_name: customerName || 'Pelanggan',
        sender_type: 'customer',
      });

      // Check if user question matches a FAQ for instant direct automated answer
      const lower = userText.toLowerCase();
      const matchedFaq = faqs.find(f => {
        const q = (f.question || '').toLowerCase();
        return lower.includes('cara sewa') && q.includes('cara') ||
               lower.includes('dp') && q.includes('dp') ||
               lower.includes('fitting') && q.includes('fitting') ||
               lower.includes('metode') && q.includes('metode') ||
               lower.includes('bayar') && q.includes('bayar') ||
               lower.includes('durasi') && q.includes('durasi') ||
               lower.includes('batal') && q.includes('batal') ||
               lower.includes('warna') && q.includes('warna') ||
               lower.includes('antar') && q.includes('antar') ||
               lower.includes('aksesoris') && q.includes('aksesoris');
      });

      if (matchedFaq) {
        // Send instant automated response
        setTimeout(async () => {
          try {
            await api.post(`/chat/${room.id}/quick-reply`, { faq_id: matchedFaq.id });
            await fetchMessages();
          } catch { /* silent */ }
        }, 500);
      }

      await fetchMessages();
      await fetchRooms();
    } catch {
      /* silent */
    } finally {
      setLoading(false);
    }
  };

  const handleQuickReply = async (faq) => {
    if (!room?.id) return;
    setLoading(true);
    try {
      await api.post(`/chat/${room.id}/quick-reply`, { faq_id: faq.id });
      await fetchMessages();
      await fetchRooms();
      setActiveTab(TABS.CHAT);
    } catch {
      /* silent */
    } finally {
      setLoading(false);
    }
  };

  const selectRoom = (r) => {
    setRoom(r);
    setActiveTab(TABS.CHAT);
  };

  return (
    <div className="fixed bottom-5 right-5 z-50 flex flex-col items-end">
      {open && (
        <div className="mb-3 w-[92vw] sm:w-[400px] h-[550px] bg-white rounded-2xl shadow-2xl border border-stone-200 flex flex-col overflow-hidden">
          {/* Header */}
          <div className="bg-[#2C1A0E] text-white px-5 py-3.5 flex items-center justify-between shrink-0">
            <div className="flex items-center gap-2.5">
              <div className="w-8 h-8 rounded-full bg-amber-400 text-[#2C1A0E] font-bold flex items-center justify-center text-xs">
                MS
              </div>
              <div>
                <h3 className="font-bold text-sm leading-tight">Live Chat & FAQ</h3>
                <p className="text-[11px] text-amber-300">Layanan Pelanggan Butik Mayangsari</p>
              </div>
            </div>
            <button
              onClick={() => setOpen(false)}
              className="text-white/70 hover:text-white text-2xl leading-none px-1"
              aria-label="Tutup Chat"
            >
              &times;
            </button>
          </div>

          {/* Navigation Tabs */}
          <div className="flex border-b border-stone-200 bg-stone-50 shrink-0">
            {[
              { key: TABS.CHAT, label: 'Chat Langsung' },
              { key: TABS.FAQ, label: 'Tanya Jawab (FAQ)' },
              { key: TABS.HISTORY, label: 'Riwayat' },
            ].map((t) => (
              <button
                key={t.key}
                onClick={() => setActiveTab(t.key)}
                className={`flex-1 py-2.5 text-xs font-bold transition border-b-2 ${
                  activeTab === t.key
                    ? 'text-[#2C1A0E] border-[#2C1A0E] bg-white'
                    : 'text-stone-500 border-transparent hover:text-stone-800'
                }`}
              >
                {t.label}
              </button>
            ))}
          </div>

          {/* Optional Name Header for anonymous visitor */}
          {activeTab === TABS.CHAT && !customerName && (
            <div className="px-4 py-2 bg-stone-100 border-b border-stone-200 flex items-center gap-2 shrink-0">
              <span className="text-[11px] text-stone-500 font-medium">Nama:</span>
              <input
                type="text"
                value={customerName}
                onChange={(e) => setCustomerName(e.target.value)}
                placeholder="Ketik nama Anda (opsional)"
                className="flex-1 text-xs px-2.5 py-1 rounded border border-stone-300 bg-white focus:outline-none focus:border-stone-700"
              />
            </div>
          )}

          {/* Tab Content Body */}
          <div className="flex-1 overflow-hidden relative">
            {/* ── TAB 1: ACTIVE CHAT ── */}
            {activeTab === TABS.CHAT && (
              <div className="absolute inset-0 flex flex-col justify-between">
                {/* Messages Area */}
                <div className="flex-1 overflow-y-auto px-4 py-3 space-y-3">
                  {starting ? (
                    <div className="text-center py-10 text-stone-400 text-xs">Menyiapkan percakapan...</div>
                  ) : messages.length === 0 ? (
                    <div className="text-center py-6 text-stone-500 space-y-2">
                      <div className="w-12 h-12 rounded-full bg-stone-100 text-stone-600 flex items-center justify-center mx-auto text-lg font-bold">
                        ?
                      </div>
                      <p className="text-xs font-semibold text-stone-700">Selamat datang di Butik Mayangsari!</p>
                      <p className="text-[11px] text-stone-500 max-w-xs mx-auto">
                        Pilih pertanyaan instan di bawah ini atau ketik pertanyaan langsung untuk dibantu admin kami.
                      </p>
                    </div>
                  ) : (
                    messages.map((msg) => {
                      const isCustomer = (msg.senderType || msg.sender_type) === 'customer';
                      return (
                        <div key={msg.id} className={`flex ${isCustomer ? 'justify-end' : 'justify-start'}`}>
                          <div
                            className={`max-w-[82%] px-3.5 py-2.5 rounded-2xl text-xs leading-relaxed ${
                              isCustomer
                                ? 'bg-[#2C1A0E] text-white rounded-br-xs'
                                : 'bg-stone-100 text-stone-800 rounded-bl-xs border border-stone-200'
                            }`}
                          >
                            {!isCustomer && (
                              <span className="block text-[10px] font-bold text-amber-800 mb-0.5">
                                {msg.senderName || msg.sender_name || 'Admin Butik'}
                              </span>
                            )}
                            <p className="whitespace-pre-wrap">{msg.message}</p>
                            <span
                              className={`block text-[9px] mt-1 text-right ${
                                isCustomer ? 'text-white/60' : 'text-stone-400'
                              }`}
                            >
                              {new Date(msg.createdAt || msg.created_at).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit',
                              })}
                            </span>
                          </div>
                        </div>
                      );
                    })
                  )}
                  <div ref={bottomRef} />
                </div>

                {/* Instant Quick FAQ Chips Row */}
                <div className="px-3 py-2 bg-stone-50 border-t border-stone-200 shrink-0">
                  <div className="text-[10px] font-bold text-stone-500 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                    <span>Pertanyaan Langsung (Jawaban Instan):</span>
                    <button
                      type="button"
                      onClick={() => setActiveTab(TABS.FAQ)}
                      className="text-amber-800 hover:underline"
                    >
                      Lihat Semua &rarr;
                    </button>
                  </div>
                  <div className="flex gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                    {QUICK_FAQS.map((qf) => (
                      <button
                        key={qf.id}
                        type="button"
                        onClick={() => handleQuickReply(qf)}
                        disabled={loading || !room?.id}
                        className="px-2.5 py-1 rounded-full text-[11px] font-medium bg-white border border-stone-300 text-stone-700 hover:border-[#2C1A0E] hover:text-[#2C1A0E] shrink-0 transition"
                      >
                        {qf.label}
                      </button>
                    ))}
                  </div>
                </div>

                {/* Input Form */}
                <form onSubmit={handleSend} className="p-3 bg-white border-t border-stone-200 flex gap-2 shrink-0">
                  <input
                    type="text"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    placeholder="Ketik pertanyaan Anda..."
                    disabled={loading || !room?.id}
                    className="flex-1 text-xs px-3.5 py-2.5 rounded-lg border border-stone-300 focus:outline-none focus:border-[#2C1A0E] focus:ring-1 focus:ring-[#2C1A0E] disabled:bg-stone-50"
                  />
                  <button
                    type="submit"
                    disabled={loading || !input.trim() || !room?.id}
                    className="px-4 py-2 bg-[#2C1A0E] text-white text-xs font-bold rounded-lg hover:bg-[#3D2518] transition disabled:opacity-40"
                  >
                    {loading ? '...' : 'Kirim'}
                  </button>
                </form>
              </div>
            )}

            {/* ── TAB 2: FAQ DIRECT ANSWERS (Requirement 5) ── */}
            {activeTab === TABS.FAQ && (
              <div className="absolute inset-0 overflow-y-auto p-4 space-y-2.5">
                <div className="p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900 leading-relaxed mb-3">
                  <span className="font-bold">Pertanyaan Populer:</span> Klik pertanyaan untuk membaca jawaban langsung, atau kirimkan ke chat.
                </div>

                {faqs.map((faq) => {
                  const isExpanded = expandedFaq === faq.id;
                  return (
                    <div
                      key={faq.id}
                      className="border border-stone-200 rounded-xl overflow-hidden bg-white shadow-2xs transition"
                    >
                      <button
                        type="button"
                        onClick={() => setExpandedFaq(isExpanded ? null : faq.id)}
                        className="w-full text-left px-4 py-3 flex items-center justify-between gap-2 hover:bg-stone-50 transition"
                      >
                        <span className="text-xs font-bold text-stone-800">{faq.question}</span>
                        <span className="text-stone-400 text-xs shrink-0">{isExpanded ? '−' : '+'}</span>
                      </button>

                      {isExpanded && (
                        <div className="px-4 pb-3.5 pt-1 text-xs text-stone-600 border-t border-stone-100 bg-stone-50/60 space-y-2">
                          <p className="leading-relaxed">{faq.answer}</p>
                          <div className="pt-1 flex justify-end">
                            <button
                              type="button"
                              onClick={() => handleQuickReply(faq)}
                              disabled={loading || !room?.id}
                              className="text-[11px] font-bold px-3 py-1 bg-[#2C1A0E] text-white rounded-md hover:bg-[#3D2518] transition"
                            >
                              Tanyakan di Chat &rarr;
                            </button>
                          </div>
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            )}

            {/* ── TAB 3: CHAT HISTORY ── */}
            {activeTab === TABS.HISTORY && (
              <div className="absolute inset-0 overflow-y-auto p-4">
                {rooms.length === 0 ? (
                  <div className="text-center py-12 text-stone-400 text-xs">Belum ada riwayat sesi percakapan.</div>
                ) : (
                  <div className="space-y-2">
                    {rooms.map((r) => (
                      <button
                        key={r.id}
                        type="button"
                        onClick={() => selectRoom(r)}
                        className={`w-full text-left p-3 rounded-xl border transition ${
                          room?.id === r.id
                            ? 'border-[#2C1A0E] bg-stone-50'
                            : 'border-stone-200 bg-white hover:border-stone-300'
                        }`}
                      >
                        <div className="flex items-center justify-between mb-1">
                          <span className="text-xs font-bold text-stone-900">
                            {r.customerName || r.customer_name || 'Pelanggan'}
                          </span>
                          <span className={`text-[10px] px-2 py-0.5 rounded-full font-semibold ${
                            r.status === 'open' ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600'
                          }`}>
                            {r.status === 'open' ? 'Aktif' : 'Selesai'}
                          </span>
                        </div>
                        <p className="text-[11px] text-stone-500 line-clamp-1">
                          {r.last_message || r.lastMessage || 'Lihat percakapan'}
                        </p>
                      </button>
                    ))}
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Floating Toggle Button */}
      <button
        type="button"
        onClick={() => setOpen(!open)}
        className="h-14 px-5 rounded-full bg-[#2C1A0E] text-white shadow-xl flex items-center gap-2 hover:bg-[#3D2518] hover:shadow-2xl transition"
        aria-label="Buka Live Chat"
      >
        {open ? (
          <span className="text-2xl leading-none">&times;</span>
        ) : (
          <>
            <svg className="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span className="text-xs font-bold">Chat Butik</span>
          </>
        )}
      </button>
    </div>
  );
}
