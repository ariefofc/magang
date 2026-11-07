import { useState, useEffect, useRef, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../services/AuthContext';
import api from '../services/api';
import { cfg } from '../config';

const CANNED_REPLIES = [
  'Halo, kebaya ini tersedia untuk jadwal sewa tersebut.',
  'Silakan datang ke studio butik untuk jadwal fitting gratis.',
  'Pembayaran DP telah kami verifikasi, terima kasih.',
  'Kebaya sudah selesai di-dry clean steril dan siap diambil.',
  'Alamat Butik Mayangsari: Jl. Magelang No. 12, Sleman, DIY.',
];

export default function ChatInbox() {
  const { user } = useAuth();
  const [rooms, setRooms] = useState([]);
  const [selectedRoom, setSelectedRoom] = useState(null);
  const [messages, setMessages] = useState([]);
  const [newMsg, setNewMsg] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [filterStatus, setFilterStatus] = useState('all'); // all | open | closed
  const bottomRef = useRef(null);

  // Fetch all chat rooms
  const fetchRooms = useCallback(async () => {
    try {
      const res = await api.get('/chat/admin/inbox');
      const list = res.data?.rooms || res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setRooms(Array.isArray(list) ? list : []);
    } catch {
      /* silent */
    } finally {
      setLoading(false);
    }
  }, []);

  // Fetch messages for selected room
  const fetchMessages = useCallback(async () => {
    if (!selectedRoom?.id) return;
    try {
      const res = await api.get(`/chat/${selectedRoom.id}/messages`);
      const list = res.data?.messages || res.data?.data || (Array.isArray(res.data) ? res.data : []);
      setMessages(Array.isArray(list) ? list : []);
    } catch {
      /* silent */
    }
  }, [selectedRoom]);

  useEffect(() => { fetchRooms(); }, [fetchRooms]);
  useEffect(() => { fetchMessages(); }, [fetchMessages]);

  // Polling messages every 3s
  useEffect(() => {
    if (!selectedRoom?.id) return;
    const interval = setInterval(fetchMessages, cfg.CHAT_POLL_MS || 3000);
    return () => clearInterval(interval);
  }, [selectedRoom, fetchMessages]);

  // Polling rooms every 6s
  useEffect(() => {
    const interval = setInterval(fetchRooms, 6000);
    return () => clearInterval(interval);
  }, [fetchRooms]);

  // Auto-scroll to bottom of messages
  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const handleSend = async (e, textOverride) => {
    if (e) e.preventDefault();
    const textToSend = textOverride || newMsg.trim();
    if (!textToSend || !selectedRoom?.id) return;

    setSending(true);
    try {
      await api.post(`/chat/${selectedRoom.id}/send`, {
        message: textToSend,
        sender_type: 'admin',
        sender_name: user?.name || 'Admin Butik',
      });
      if (!textOverride) setNewMsg('');
      await fetchMessages();
      await fetchRooms();
    } catch {
      /* silent */
    } finally {
      setSending(false);
    }
  };

  const toggleRoom = async () => {
    if (!selectedRoom?.id) return;
    const action = selectedRoom.status === 'open' ? 'close' : 'reopen';
    try {
      await api.post(`/chat/admin/${selectedRoom.id}/${action}`);
      await fetchRooms();
      setSelectedRoom((prev) => ({
        ...prev,
        status: action === 'close' ? 'closed' : 'open',
      }));
    } catch {
      /* silent */
    }
  };

  const filteredRooms = rooms.filter((r) => {
    if (filterStatus === 'open') return r.status === 'open';
    if (filterStatus === 'closed') return r.status === 'closed';
    return true;
  });

  return (
    <div className="min-h-screen bg-[#F8F9FA] flex flex-col antialiased">
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
          <h1 className="text-base font-bold">Chat Inbox Admin</h1>
          <span className="text-xs px-2.5 py-0.5 rounded-full bg-amber-400 text-[#2C1A0E] font-bold">
            {rooms.filter(r => r.status === 'open').length} Sesi Aktif
          </span>
        </div>
        <div className="text-xs text-white/70">
          Operator: <span className="font-bold text-white">{user?.name || 'Admin'}</span> ({user?.role?.toUpperCase()})
        </div>
      </header>

      {/* ── Main Chat Container ── */}
      <div className="flex-1 flex overflow-hidden max-w-7xl w-full mx-auto p-4 gap-4">
        {/* Left: Conversation List */}
        <aside className="w-80 sm:w-96 bg-white border border-stone-200 rounded-2xl flex flex-col overflow-hidden shadow-xs shrink-0">
          {/* Filter Bar */}
          <div className="p-3.5 border-b border-stone-100 bg-stone-50/50 space-y-2">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-stone-700 uppercase tracking-wider">Kotak Masuk</span>
              <span className="text-[11px] text-stone-400">{filteredRooms.length} percakapan</span>
            </div>
            <div className="grid grid-cols-3 gap-1 bg-stone-200/60 p-0.5 rounded-lg text-xs">
              {[
                { key: 'all', label: 'Semua' },
                { key: 'open', label: 'Aktif' },
                { key: 'closed', label: 'Ditutup' },
              ].map(f => (
                <button
                  key={f.key}
                  type="button"
                  onClick={() => setFilterStatus(f.key)}
                  className={`py-1 rounded font-semibold text-center transition ${
                    filterStatus === f.key ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-600 hover:text-stone-900'
                  }`}
                >
                  {f.label}
                </button>
              ))}
            </div>
          </div>

          {/* Rooms List */}
          <div className="flex-1 overflow-y-auto divide-y divide-stone-100">
            {loading ? (
              <div className="p-8 text-center text-xs text-stone-400">Memuat percakapan...</div>
            ) : filteredRooms.length === 0 ? (
              <div className="p-8 text-center text-xs text-stone-400 space-y-1">
                <p className="font-semibold text-stone-600">Belum ada chat masuk</p>
                <p>Percakapan dari penyewa akan muncul di sini secara otomatis.</p>
              </div>
            ) : (
              filteredRooms.map((room) => {
                const isSelected = selectedRoom?.id === room.id;
                const unread = room.unreadCount ?? room.unread_count ?? 0;
                const lastMsg = room.messages?.[0]?.message || room.last_message || 'Belum ada pesan';
                const timeStr = room.lastMessageAt || room.last_message_at;

                return (
                  <button
                    key={room.id}
                    type="button"
                    onClick={() => setSelectedRoom(room)}
                    className={`w-full text-left p-4 transition flex items-start gap-3 ${
                      isSelected
                        ? 'bg-amber-50/80 border-l-4 border-l-[#2C1A0E]'
                        : 'hover:bg-stone-50'
                    }`}
                  >
                    <div className="w-10 h-10 rounded-full bg-[#2C1A0E] text-white font-bold flex items-center justify-center shrink-0 text-xs">
                      {(room.customerName || room.customer_name || 'P')[0]?.toUpperCase()}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center justify-between mb-0.5">
                        <span className="font-bold text-xs text-stone-900 truncate">
                          {room.customerName || room.customer_name || 'Pelanggan'}
                        </span>
                        {timeStr && (
                          <span className="text-[10px] text-stone-400 shrink-0 ml-1">
                            {new Date(timeStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                          </span>
                        )}
                      </div>
                      <p className="text-xs text-stone-600 truncate mb-1">{lastMsg}</p>
                      <div className="flex items-center gap-2">
                        <span className={`text-[10px] px-2 py-0.5 rounded-full font-semibold ${
                          room.status === 'open'
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-stone-200 text-stone-600'
                        }`}>
                          {room.status === 'open' ? 'Aktif' : 'Selesai'}
                        </span>
                        {unread > 0 && (
                          <span className="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                            {unread} baru
                          </span>
                        )}
                        {room.transaksi?.kodeTransaksi && (
                          <span className="text-[10px] text-amber-800 font-semibold truncate">
                            {room.transaksi.kodeTransaksi}
                          </span>
                        )}
                      </div>
                    </div>
                  </button>
                );
              })
            )}
          </div>
        </aside>

        {/* Right: Message Window */}
        <section className="flex-1 bg-white border border-stone-200 rounded-2xl flex flex-col overflow-hidden shadow-xs">
          {!selectedRoom ? (
            <div className="flex-1 flex flex-col items-center justify-center text-center p-8 text-stone-400">
              <svg className="w-12 h-12 text-stone-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
              <h3 className="font-bold text-sm text-stone-700">Pilih Percakapan</h3>
              <p className="text-xs text-stone-400 max-w-sm mt-1">Pilih salah satu sesi di sebelah kiri untuk melihat riwayat percakapan dan membalas pesan.</p>
            </div>
          ) : (
            <>
              {/* Chat Header */}
              <div className="p-4 bg-stone-50 border-b border-stone-200 flex items-center justify-between shrink-0">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-stone-800 text-white font-bold flex items-center justify-center text-sm">
                    {(selectedRoom.customerName || selectedRoom.customer_name || 'P')[0]?.toUpperCase()}
                  </div>
                  <div>
                    <h2 className="font-bold text-sm text-stone-900 leading-tight">
                      {selectedRoom.customerName || selectedRoom.customer_name || 'Pelanggan'}
                    </h2>
                    <div className="flex items-center gap-2 text-xs text-stone-500">
                      <span className={`inline-flex items-center gap-1 font-semibold ${
                        selectedRoom.status === 'open' ? 'text-emerald-700' : 'text-stone-500'
                      }`}>
                        <span className={`w-1.5 h-1.5 rounded-full ${selectedRoom.status === 'open' ? 'bg-emerald-500' : 'bg-stone-400'}`} />
                        {selectedRoom.status === 'open' ? 'Sesi Chat Terbuka' : 'Sesi Ditutup'}
                      </span>
                      {selectedRoom.transaksi?.namaKebaya && (
                        <span>• Busana: <span className="font-medium text-stone-800">{selectedRoom.transaksi.namaKebaya}</span></span>
                      )}
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={toggleRoom}
                    className={`px-3 py-1.5 text-xs font-bold rounded-lg border transition ${
                      selectedRoom.status === 'open'
                        ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                        : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                    }`}
                  >
                    {selectedRoom.status === 'open' ? 'Tutup Percakapan' : 'Buka Kembali Sesi'}
                  </button>
                </div>
              </div>

              {/* Messages Body */}
              <div className="flex-1 overflow-y-auto p-5 space-y-3.5 bg-[#FAF8F5]/40">
                {messages.length === 0 ? (
                  <div className="text-center py-10 text-xs text-stone-400">Belum ada pesan dalam percakapan ini.</div>
                ) : (
                  messages.map((msg) => {
                    const isAdmin = (msg.senderType || msg.sender_type) === 'admin';
                    return (
                      <div key={msg.id} className={`flex ${isAdmin ? 'justify-end' : 'justify-start'}`}>
                        <div
                          className={`max-w-[75%] px-4 py-2.5 rounded-2xl text-xs leading-relaxed ${
                            isAdmin
                              ? 'bg-[#2C1A0E] text-white rounded-br-xs shadow-xs'
                              : 'bg-white text-stone-800 rounded-bl-xs border border-stone-200 shadow-2xs'
                          }`}
                        >
                          <span className={`block text-[10px] font-bold mb-0.5 ${
                            isAdmin ? 'text-amber-300' : 'text-stone-500'
                          }`}>
                            {msg.senderName || msg.sender_name || (isAdmin ? 'Admin' : 'Pelanggan')}
                          </span>
                          <p className="whitespace-pre-wrap">{msg.message}</p>
                          <span className={`block text-[9px] mt-1 text-right ${
                            isAdmin ? 'text-white/60' : 'text-stone-400'
                          }`}>
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

              {/* Quick Canned Replies for Admin */}
              <div className="px-4 py-2 bg-stone-50 border-t border-stone-200 flex items-center gap-1.5 overflow-x-auto shrink-0 scrollbar-none">
                <span className="text-[10px] font-bold uppercase text-stone-400 shrink-0">Balas Cepat:</span>
                {CANNED_REPLIES.map((rep, i) => (
                  <button
                    key={i}
                    type="button"
                    onClick={() => handleSend(null, rep)}
                    disabled={sending}
                    className="px-2.5 py-1 rounded-full text-[11px] bg-white border border-stone-200 hover:border-stone-400 text-stone-700 shrink-0 transition"
                  >
                    {rep}
                  </button>
                ))}
              </div>

              {/* Message Input Box */}
              <form onSubmit={handleSend} className="p-3 bg-white border-t border-stone-200 flex gap-2 shrink-0">
                <input
                  type="text"
                  value={newMsg}
                  onChange={(e) => setNewMsg(e.target.value)}
                  placeholder="Ketik balasan untuk pelanggan..."
                  disabled={sending}
                  className="flex-1 text-xs px-4 py-2.5 rounded-lg border border-stone-300 focus:outline-none focus:border-[#2C1A0E] focus:ring-1 focus:ring-[#2C1A0E] disabled:bg-stone-50"
                />
                <button
                  type="submit"
                  disabled={sending || !newMsg.trim()}
                  className="px-5 py-2.5 bg-[#2C1A0E] text-white text-xs font-bold rounded-lg hover:bg-[#3D2518] transition disabled:opacity-40"
                >
                  {sending ? 'Mengirim...' : 'Kirim Balasan'}
                </button>
              </form>
            </>
          )}
        </section>
      </div>
    </div>
  );
}
