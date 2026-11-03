import { useState, useEffect, useRef, useCallback } from 'react';
import api from '../services/api';
import { cfg } from '../config';

export default function ChatInbox() {
  const [rooms, setRooms] = useState([]);
  const [selectedRoom, setSelectedRoom] = useState(null);
  const [messages, setMessages] = useState([]);
  const [newMsg, setNewMsg] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const bottomRef = useRef(null);

  // Fetch rooms
  const fetchRooms = useCallback(async () => {
    try {
      const res = await api.get('/chat/admin/inbox');
      setRooms(res.data.data || res.data);
    } catch { /* silent */ } finally {
      setLoading(false);
    }
  }, []);

  // Fetch messages
  const fetchMessages = useCallback(async () => {
    if (!selectedRoom) return;
    try {
      const res = await api.get(`/chat/${selectedRoom.id}/messages`);
      setMessages(res.data.data || res.data);
    } catch { /* silent */ }
  }, [selectedRoom]);

  useEffect(() => { fetchRooms(); }, [fetchRooms]);
  useEffect(() => { fetchMessages(); }, [fetchMessages]);

  // Poll messages every 3s
  useEffect(() => {
    if (!selectedRoom) return;
    const interval = setInterval(fetchMessages, cfg.CHAT_POLL_MS);
    return () => clearInterval(interval);
  }, [selectedRoom, fetchMessages]);

  // Poll rooms every 10s
  useEffect(() => {
    const interval = setInterval(fetchRooms, 10000);
    return () => clearInterval(interval);
  }, [fetchRooms]);

  // Auto-scroll
  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const handleSend = async (e) => {
    e.preventDefault();
    if (!newMsg.trim() || !selectedRoom) return;
    setSending(true);
    try {
      await api.post(`/chat/${selectedRoom.id}/messages`, { message: newMsg.trim() });
      setNewMsg('');
      await fetchMessages();
    } catch { /* silent */ } finally {
      setSending(false);
    }
  };

  const toggleRoom = async () => {
    if (!selectedRoom) return;
    const action = selectedRoom.status === 'open' ? 'close' : 'reopen';
    try {
      await api.patch(`/chat/${selectedRoom.id}/${action}`);
      await fetchRooms();
      setSelectedRoom(prev => ({ ...prev, status: action === 'close' ? 'closed' : 'open' }));
    } catch { /* silent */ }
  };

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex">
      {/* Left Panel - Room List */}
      <div className="w-80 bg-white border-r border-gray-200 flex flex-col">
        <div className="px-5 py-4 border-b border-gray-100">
          <h1 className="text-lg font-bold text-[#2C1A0E]">Chat Inbox</h1>
        </div>
        <div className="flex-1 overflow-y-auto">
          {loading ? (
            <p className="p-5 text-gray-400 text-sm">Memuat...</p>
          ) : rooms.length === 0 ? (
            <p className="p-5 text-gray-400 text-sm">Belum ada percakapan</p>
          ) : (
            rooms.map(room => (
              <button
                key={room.id}
                onClick={() => setSelectedRoom(room)}
                className={`w-full text-left px-5 py-4 border-b border-gray-50 hover:bg-[#FAF8F5] transition ${selectedRoom?.id === room.id ? 'bg-[#FAF8F5] border-l-4 border-l-[#2C1A0E]' : ''}`}
              >
                <div className="flex items-center justify-between mb-1">
                  <span className="font-semibold text-[#2C1A0E] text-sm truncate">{room.customer_name}</span>
                  <div className="flex items-center gap-2">
                    {room.unread_count > 0 && (
                      <span className="bg-[#fbbf24] text-[#2C1A0E] text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{room.unread_count}</span>
                    )}
                    <span className={`w-2 h-2 rounded-full ${room.status === 'open' ? 'bg-green-500' : 'bg-gray-300'}`} />
                  </div>
                </div>
                <p className="text-xs text-gray-500 truncate">{room.last_message || 'Belum ada pesan'}</p>
              </button>
            ))
          )}
        </div>
      </div>

      {/* Right Panel - Messages */}
      <div className="flex-1 flex flex-col">
        {!selectedRoom ? (
          <div className="flex-1 flex items-center justify-center">
            <p className="text-gray-400">Pilih percakapan untuk mulai</p>
          </div>
        ) : (
          <>
            {/* Chat Header */}
            <div className="px-6 py-4 bg-white border-b border-gray-200 flex items-center justify-between">
              <div>
                <h2 className="font-semibold text-[#2C1A0E]">{selectedRoom.customer_name}</h2>
                <span className={`text-xs ${selectedRoom.status === 'open' ? 'text-green-600' : 'text-gray-400'}`}>
                  {selectedRoom.status === 'open' ? 'Aktif' : 'Ditutup'}
                </span>
              </div>
              <button
                onClick={toggleRoom}
                className={`px-4 py-1.5 text-xs font-semibold rounded-lg transition ${
                  selectedRoom.status === 'open'
                    ? 'bg-red-50 text-red-600 hover:bg-red-100'
                    : 'bg-green-50 text-green-600 hover:bg-green-100'
                }`}
              >
                {selectedRoom.status === 'open' ? 'Tutup Chat' : 'Buka Kembali'}
              </button>
            </div>

            {/* Messages */}
            <div className="flex-1 overflow-y-auto px-6 py-4 space-y-3">
              {messages.map(msg => {
                const isAdmin = msg.sender_type === 'admin';
                return (
                  <div key={msg.id} className={`flex ${isAdmin ? 'justify-end' : 'justify-start'}`}>
                    <div className={`max-w-[70%] px-4 py-2.5 rounded-2xl text-sm ${
                      isAdmin
                        ? 'bg-[#2C1A0E] text-white rounded-br-md'
                        : 'bg-gray-100 text-gray-800 rounded-bl-md'
                    }`}>
                      <p>{msg.message}</p>
                      <p className={`text-[10px] mt-1 ${isAdmin ? 'text-white/60' : 'text-gray-400'}`}>
                        {new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                      </p>
                    </div>
                  </div>
                );
              })}
              <div ref={bottomRef} />
            </div>

            {/* Send Form */}
            <form onSubmit={handleSend} className="px-6 py-4 bg-white border-t border-gray-200 flex gap-3">
              <input
                type="text"
                value={newMsg}
                onChange={e => setNewMsg(e.target.value)}
                placeholder="Ketik pesan..."
                className="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2C1A0E]/30 focus:border-[#2C1A0E] transition"
              />
              <button
                type="submit"
                disabled={sending || !newMsg.trim()}
                className="px-6 py-2.5 bg-[#2C1A0E] text-white font-semibold rounded-xl hover:bg-[#3d2517] transition disabled:opacity-50"
              >
                {sending ? '...' : 'Kirim'}
              </button>
            </form>
          </>
        )}
      </div>
    </div>
  );
}
