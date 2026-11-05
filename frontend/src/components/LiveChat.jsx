import { useState, useEffect, useRef, useCallback } from 'react';
import api from '../services/api';
import { cfg } from '../config';

const TABS = { CHAT: 'chat', FAQ: 'faq', HISTORY: 'history' };

export default function LiveChat() {
  const [open, setOpen] = useState(false);
  const [activeTab, setActiveTab] = useState(TABS.CHAT);
  const [room, setRoom] = useState(null);
  const [messages, setMessages] = useState([]);
  const [rooms, setRooms] = useState([]);
  const [faqs, setFaqs] = useState([]);
  const [input, setInput] = useState('');
  const [customerName, setCustomerName] = useState('');
  const [loading, setLoading] = useState(false);
  const [starting, setStarting] = useState(false);
  const bottomRef = useRef(null);

  // Load FAQ once
  useEffect(() => {
    api.get('/chat/faq').then((r) => setFaqs(r.data?.faqs || [])).catch(() => {});
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
      setMessages(data?.messages || data || []);
    } catch {
      /* silent */
    }
  }, [room]);

  const fetchRooms = useCallback(async () => {
    try {
      const { data } = await api.get('/chat/my-rooms');
      setRooms(data?.rooms || []);
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
    const interval = setInterval(fetchMessages, cfg.CHAT_POLL_MS);
    return () => clearInterval(interval);
  }, [open, room, fetchMessages]);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const handleSend = async (e) => {
    e.preventDefault();
    if (!input.trim() || !room?.id) return;
    setLoading(true);
    try {
      await api.post(`/chat/${room.id}/send`, {
        message: input.trim(),
        sender_name: customerName || 'Pelanggan',
        sender_type: 'customer',
      });
      setInput('');
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

  const statusBadge = (status) => {
    const map = {
      aktif: { label: 'Tersedia', className: 'bg-green-100 text-green-700' },
      nonaktif: { label: 'Tidak Tersedia', className: 'bg-gray-100 text-gray-600' },
      perbaikan: { label: 'Dalam Perbaikan', className: 'bg-yellow-100 text-yellow-700' },
      segera: { label: 'Segera Hadir', className: 'bg-blue-100 text-blue-700' },
    };
    const s = map[status] || { label: status, className: 'bg-gray-100 text-gray-600' };
    return <span className={`text-[10px] px-2 py-0.5 rounded-full font-medium ${s.className}`}>{s.label}</span>;
  };

  return (
    <div className="fixed bottom-5 right-5 z-50 flex flex-col items-end">
      {open && (
        <div className="mb-3 w-[90vw] sm:w-[380px] h-[520px] bg-white rounded-2xl shadow-2xl border border-stone-100 flex flex-col overflow-hidden">
          {/* Header */}
          <div className="bg-[#2C1A0E] text-white px-4 py-3 flex items-center justify-between shrink-0">
            <div>
              <h3 className="font-bold text-sm">Live Chat</h3>
              <p className="text-amber-300 text-xs">Butuh bantuan? kami siap membantu</p>
            </div>
            <button onClick={() => setOpen(false)} className="text-white/70 hover:text-white text-xl leading-none">&times;</button>
          </div>

          {/* Tabs */}
          <div className="flex border-b border-stone-100 shrink-0">
            {[
              { key: TABS.CHAT, label: 'Chat' },
              { key: TABS.FAQ, label: 'FAQ' },
              { key: TABS.HISTORY, label: 'Riwayat' },
            ].map((t) => (
              <button
                key={t.key}
                onClick={() => setActiveTab(t.key)}
                className={`flex-1 py-2.5 text-xs font-semibold transition ${
                  activeTab === t.key
                    ? 'text-[#2C1A0E] border-b-2 border-[#2C1A0E] bg-[#FAF8F5]'
                    : 'text-stone-400 hover:text-stone-600'
                }`}
              >
                {t.label}
              </button>
            ))}
          </div>

          {/* Name input (only before first message if not set) */}
          {activeTab === TABS.CHAT && !customerName && (
            <div className="px-4 py-2 bg-amber-50 border-b border-amber-100 shrink-0">
              <input
                type="text"
                value={customerName}
                onChange={(e) => setCustomerName(e.target.value)}
                placeholder="Nama Anda (opsional)"
                className="w-full text-xs px-3 py-2 rounded-lg border border-amber-200 focus:outline-none focus:border-amber-400"
              />
            </div>
          )}

          {/* Content */}
          <div className="flex-1 overflow-hidden relative">
            {activeTab === TABS.CHAT && (
              <div className="absolute inset-0 flex flex-col">
                <div className="flex-1 overflow-y-auto px-4 py-3 space-y-3">
                  {starting ? (
                    <p className="text-center text-stone-400 text-sm py-6">Memuat chat...</p>
                  ) : messages.length === 0 ? (
                    <div className="text-center text-stone-400 text-sm py-8">
                      <p className="mb-3">Halo! Ada yang bisa kami bantu?</p>
                      <p className="text-xs">Pilih pertanyaan cepat di menu FAQ atau ketik pesan di bawah.</p>
                    </div>
                  ) : (
                    messages.map((msg) => {
                      const isCustomer = msg.sender_type === 'customer';
                      return (
                        <div key={msg.id} className={`flex ${isCustomer ? 'justify-end' : 'justify-start'}`}>
                          <div
                            className={`max-w-[80%] px-3 py-2 rounded-2xl text-xs ${
                              isCustomer
                                ? 'bg-[#2C1A0E] text-white rounded-br-md'
                                : 'bg-stone-100 text-stone-800 rounded-bl-md'
                            }`}
                          >
                            <p>{msg.message}</p>
                            <p className={`text-[10px] mt-1 ${isCustomer ? 'text-white/60' : 'text-stone-400'}`}>
                              {new Date(msg.created_at || msg.createdAt).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit',
                              })}
                            </p>
                          </div>
                        </div>
                      );
                    })
                  )}
                  <div ref={bottomRef} />
                </div>

                <form onSubmit={handleSend} className="px-3 py-2 border-t border-stone-100 flex gap-2 shrink-0">
                  <input
                    type="text"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    placeholder="Ketik pesan..."
                    disabled={loading || !room?.id}
                    className="flex-1 text-xs px-3 py-2 rounded-lg border border-stone-200 focus:outline-none focus:border-[#2C1A0E] disabled:bg-stone-50"
                  />
                  <button
                    type="submit"
                    disabled={loading || !input.trim() || !room?.id}
                    className="px-3 py-2 bg-[#2C1A0E] text-white text-xs font-semibold rounded-lg hover:bg-[#3D2518] disabled:opacity-50"
                  >
                    {loading ? '...' : 'Kirim'}
                  </button>
                </form>
              </div>
            )}

            {activeTab === TABS.FAQ && (
              <div className="absolute inset-0 overflow-y-auto px-4 py-3">
                <p className="text-xs text-stone-500 mb-3">Pilih pertanyaan untuk mendapatkan jawaban otomatis:</p>
                <div className="space-y-2">
                  {faqs.map((faq) => (
                    <button
                      key={faq.id}
                      onClick={() => handleQuickReply(faq)}
                      className="w-full text-left px-3 py-2.5 bg-stone-50 hover:bg-amber-50 border border-stone-100 rounded-xl text-xs text-stone-700 transition"
                    >
                      {faq.question}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {activeTab === TABS.HISTORY && (
              <div className="absolute inset-0 overflow-y-auto px-4 py-3">
                {rooms.length === 0 ? (
                  <p className="text-center text-stone-400 text-sm py-8">Belum ada riwayat chat.</p>
                ) : (
                  <div className="space-y-2">
                    {rooms.map((r) => (
                      <button
                        key={r.id}
                        onClick={() => selectRoom(r)}
                        className="w-full text-left px-3 py-3 bg-white border border-stone-100 rounded-xl hover:bg-[#FAF8F5] transition"
                      >
                        <div className="flex items-center justify-between mb-1">
                          <span className="text-xs font-semibold text-[#2C1A0E]">
                            {r.customer_name || 'Pelanggan'}
                          </span>
                          <span
                            className={`w-2 h-2 rounded-full ${r.status === 'open' ? 'bg-green-500' : 'bg-gray-300'}`}
                          />
                        </div>
                        <p className="text-[11px] text-stone-500 truncate">
                          {r.last_message || 'Belum ada pesan'}
                        </p>
                        {r.transaksi?.kode_transaksi && (
                          <p className="text-[10px] text-amber-600 mt-1">
                            Order: {r.transaksi.kode_transaksi}
                          </p>
                        )}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Floating button */}
      <button
        onClick={() => setOpen(!open)}
        className="w-14 h-14 rounded-full bg-[#2C1A0E] text-white shadow-xl flex items-center justify-center hover:bg-[#3D2518] transition"
        aria-label="Live chat"
      >
        {open ? (
          <span className="text-2xl leading-none">&times;</span>
        ) : (
          <svg xmlns="http://www.w3.org/2000/svg" className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        )}
      </button>
    </div>
  );
}
