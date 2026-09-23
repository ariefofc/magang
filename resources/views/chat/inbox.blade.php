<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Chat — MS Harmony Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    {{-- CSS Modular Eksternal --}}
    <link rel="stylesheet" href="{{ asset('css/chat-inbox.css') }}">
</head>
<body>
<div class="inbox-root">

    {{-- ═══ SIDEBAR KIRI: Daftar Room ═══════════════════════════════ --}}
    <aside class="inbox-sidebar" id="inbox-sidebar">

        {{-- Brand Header --}}
        <div class="inbox-sidebar-header">
            <div class="inbox-brand">
                <div class="inbox-brand-icon">💬</div>
                <div>
                    <div class="inbox-brand-title">Live Chat Admin</div>
                    <div class="inbox-brand-sub">MS HARMONY SUPPORT</div>
                </div>
                <span class="inbox-total-badge" id="total-unread-badge"
                      style="{{ $totalUnread > 0 ? '' : 'display:none;' }}">
                    {{ $totalUnread }}
                </span>
            </div>
            <input type="text" class="inbox-search" id="room-search"
                   placeholder="🔍 Cari customer..." oninput="filterRooms(this.value)">
        </div>

        {{-- Daftar Room --}}
        <div class="inbox-room-list" id="room-list">
            @forelse($rooms as $room)
                <div class="room-item {{ $room->status === 'closed' ? 'closed' : '' }}"
                     id="room-item-{{ $room->id }}"
                     data-room-id="{{ $room->id }}"
                     data-name="{{ strtolower($room->customer_name) }}"
                     onclick="selectRoom({{ $room->id }}, '{{ e($room->customer_name) }}', '{{ $room->customer_contact }}', '{{ $room->status }}')">
                    <div class="room-avatar">{{ strtoupper(substr($room->customer_name, 0, 1)) }}</div>
                    <div class="room-info">
                        <div class="room-name">{{ $room->customer_name }}</div>
                        <div style="font-size:10px;color:#8B5A2B;font-weight:600;margin-top:1px;margin-bottom:2px;">
                            📋 {{ $room->transaction_code ?? 'Tanya Umum' }}
                            @if($room->transaksi?->nama_kebaya)
                                ({{ $room->transaksi->nama_kebaya }})
                            @endif
                        </div>
                        <div class="room-preview">{{ Str::limit($room->latestMessage?->message ?? 'Mulai percakapan...', 35) }}</div>
                    </div>
                    <div class="room-meta">
                        <div class="room-time">{{ $room->last_message_at?->diffForHumans() ?? '-' }}</div>
                        @php $uc = $room->unreadCount(); @endphp
                        @if($uc > 0)
                            <div class="room-unread" id="badge-{{ $room->id }}">{{ $uc }}</div>
                        @else
                            <div class="room-unread" id="badge-{{ $room->id }}" style="display:none;">0</div>
                        @endif
                        @if($room->status === 'closed')
                            <div class="room-closed-badge">Closed</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="inbox-empty">
                    💬<br>Belum ada percakapan masuk.<br>Tunggu customer menghubungi Anda.
                </div>
            @endforelse
        </div>

        {{-- Back Link --}}
        <div class="inbox-back-link">
            <a href="{{ route('dashboard') }}">⬅ Kembali ke Dashboard</a>
        </div>
    </aside>

    {{-- ═══ MAIN: Area Obrolan ════════════════════════════════════════ --}}
    <main class="inbox-main" id="inbox-main">

        {{-- No Room Selected Placeholder --}}
        <div class="no-room-placeholder" id="no-room-placeholder">
            <div style="font-size:3.5rem;margin-bottom:16px;">💬</div>
            <h3 style="font-size:1.1rem;color:#3D342F;margin-bottom:8px;">Pilih Percakapan</h3>
            <p style="font-size:0.78rem;">Klik salah satu customer di sebelah kiri untuk mulai membalas.</p>
        </div>

        {{-- Active Chat Panel (hidden by default) --}}
        <div id="active-chat" style="display:none;flex-direction:column;height:100%;">

            {{-- Topbar --}}
            <div class="chat-topbar">
                <div class="topbar-avatar" id="active-avatar">?</div>
                <div class="topbar-info">
                    <div class="topbar-name" id="active-name">—</div>
                    <div class="topbar-sub" id="active-contact">—</div>
                </div>
                <div class="topbar-actions">
                    <button class="topbar-btn topbar-btn-close" id="btn-close-room" onclick="closeCurrentRoom()">
                        🔒 Tutup Room
                    </button>
                    <button class="topbar-btn topbar-btn-open" id="btn-reopen-room" onclick="reopenCurrentRoom()" style="display:none;">
                        🔓 Buka Kembali
                    </button>
                </div>
            </div>

            {{-- Messages --}}
            <div class="chat-body" id="chat-body">
                <div class="chat-empty" id="chat-loading">
                    <div class="chat-empty-icon">⏳</div>
                    <p>Memuat pesan...</p>
                </div>
            </div>

            {{-- Closed Notice --}}
            <div class="room-closed-notice" id="room-closed-notice" style="display:none;">
                🔒 Percakapan ini sudah ditutup. Klik <strong>Buka Kembali</strong> untuk melanjutkan.
            </div>

            {{-- Input --}}
            <div class="chat-input-area" id="chat-input-area">
                <textarea class="chat-textarea" id="admin-chat-input"
                          placeholder="Ketik balasan..." rows="2" maxlength="2000"></textarea>
                <button class="chat-send-btn" id="admin-send-btn" onclick="adminSend()">
                    ✉️ Kirim
                </button>
            </div>

        </div>

    </main>
</div>

{{-- Toast Notifikasi --}}
<div class="chat-toast" id="chat-toast"></div>

<script>
(function () {
    'use strict';

    const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
    const POLL_MS  = 3000;

    let currentRoomId   = null;
    let currentStatus   = 'open';
    let pollInterval    = null;
    let lastRoomPoll    = null;
    let isFirstLoad     = true;

    // ─── Select Room ──────────────────────────────────────────────
    window.selectRoom = function (roomId, name, contact, status) {
        currentRoomId = roomId;
        currentStatus = status;

        // Highlight active
        document.querySelectorAll('.room-item').forEach(el => el.classList.remove('active'));
        const ri = document.getElementById('room-item-' + roomId);
        if (ri) ri.classList.add('active');

        // Update topbar
        document.getElementById('active-avatar').textContent  = name.charAt(0).toUpperCase();
        document.getElementById('active-name').textContent    = name;
        document.getElementById('active-contact').textContent = contact || 'Tidak ada kontak';

        // Show/hide close & reopen buttons
        document.getElementById('btn-close-room').style.display  = status === 'open' ? ''     : 'none';
        document.getElementById('btn-reopen-room').style.display = status === 'closed' ? ''   : 'none';

        // Closed input
        document.getElementById('chat-input-area').style.display     = status === 'open' ? ''    : 'none';
        document.getElementById('room-closed-notice').style.display  = status === 'closed' ? '' : 'none';

        // Show panel
        document.getElementById('no-room-placeholder').style.display = 'none';
        const ac = document.getElementById('active-chat');
        ac.style.display = 'flex';

        // Load messages
        isFirstLoad = true;
        loadMessages(roomId);
        startPolling(roomId);

        // Clear badge
        const badge = document.getElementById('badge-' + roomId);
        if (badge) badge.style.display = 'none';
    };

    // ─── Load Messages ────────────────────────────────────────────
    function loadMessages(roomId) {
        fetch(`/admin/chat/${roomId}/messages`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            renderMessages(data.messages || []);
            isFirstLoad = false;
        })
        .catch(() => {});
    }

    function renderMessages(messages) {
        const body = document.getElementById('chat-body');
        body.innerHTML = '';

        if (!messages.length) {
            body.innerHTML = `<div class="chat-empty">
                <div class="chat-empty-icon">💬</div>
                <h3>Belum ada pesan</h3>
                <p>Tunggu customer mengirim pesan pertama.</p>
            </div>`;
            return;
        }

        let lastDate = '';
        messages.forEach(m => {
            const msgDate = m.date || '';
            if (msgDate && msgDate !== lastDate) {
                const sep = document.createElement('div');
                sep.className = 'date-sep';
                sep.textContent = msgDate;
                body.appendChild(sep);
                lastDate = msgDate;
            }
            appendBubble(m, body);
        });
        scrollBottom();
    }

    function appendBubble(m, body) {
        body = body || document.getElementById('chat-body');
        const isCustomer = m.sender_type === 'customer';
        const wrap = document.createElement('div');
        wrap.className = 'bubble-wrap ' + (isCustomer ? 'from-customer' : 'from-admin');
        wrap.innerHTML = `
            <div class="bubble-sender">${isCustomer ? '👤 ' + escHtml(m.sender_name || 'Customer') : '👑 ' + escHtml(m.sender_name || 'Admin')}</div>
            <div class="bubble">${escHtml(m.message)}</div>
            <div class="bubble-time">${m.time || ''}</div>
        `;
        body.appendChild(wrap);
    }

    function scrollBottom() {
        const body = document.getElementById('chat-body');
        body.scrollTop = body.scrollHeight;
    }

    // ─── Kirim Pesan (Admin) ──────────────────────────────────────
    window.adminSend = function () {
        if (!currentRoomId) return;
        const input = document.getElementById('admin-chat-input');
        const msg   = input.value.trim();
        if (!msg) return;

        const btn = document.getElementById('admin-send-btn');
        btn.disabled = true;

        fetch(`/admin/chat/${currentRoomId}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept':        'application/json',
            },
            body: JSON.stringify({ message: msg }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                input.style.height = 'auto';
                appendBubble({ ...data, sender_type: 'admin' });
                scrollBottom();
            }
        })
        .finally(() => { btn.disabled = false; });
    };

    // Enter to send
    document.getElementById('admin-chat-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); adminSend(); }
    });
    document.getElementById('admin-chat-input').addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // ─── Close / Reopen Room ──────────────────────────────────────
    window.closeCurrentRoom = function () {
        if (!currentRoomId) return;
        fetch(`/admin/chat/${currentRoomId}/close`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(() => {
            currentStatus = 'closed';
            document.getElementById('btn-close-room').style.display  = 'none';
            document.getElementById('btn-reopen-room').style.display = '';
            document.getElementById('chat-input-area').style.display = 'none';
            document.getElementById('room-closed-notice').style.display = '';
            const ri = document.getElementById('room-item-' + currentRoomId);
            if (ri) ri.classList.add('closed');
            showToast('✅ Percakapan berhasil ditutup.');
        });
    };

    window.reopenCurrentRoom = function () {
        if (!currentRoomId) return;
        fetch(`/admin/chat/${currentRoomId}/reopen`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(() => {
            currentStatus = 'open';
            document.getElementById('btn-close-room').style.display  = '';
            document.getElementById('btn-reopen-room').style.display = 'none';
            document.getElementById('chat-input-area').style.display = '';
            document.getElementById('room-closed-notice').style.display = 'none';
            const ri = document.getElementById('room-item-' + currentRoomId);
            if (ri) ri.classList.remove('closed');
            showToast('🔓 Percakapan dibuka kembali.');
        });
    };

    // ─── Polling ──────────────────────────────────────────────────
    function startPolling(roomId) {
        stopPolling();
        pollInterval = setInterval(() => {
            if (currentRoomId) loadMessages(currentRoomId);
        }, POLL_MS);
    }
    function stopPolling() {
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
    }

    // Polling room list (sidebar) setiap 5 detik
    setInterval(pollRoomList, 5000);
    function pollRoomList() {
        fetch('/admin/chat/rooms', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            // Update badge unread di sidebar
            (data.rooms || []).forEach(room => {
                const badge = document.getElementById('badge-' + room.id);
                if (badge) {
                    if (room.unread > 0 && room.id !== currentRoomId) {
                        badge.textContent = room.unread;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            });

            // Update total badge header
            const totalBadge = document.getElementById('total-unread-badge');
            if (totalBadge) {
                if (data.total_unread > 0) {
                    totalBadge.textContent    = data.total_unread;
                    totalBadge.style.display  = '';
                } else {
                    totalBadge.style.display  = 'none';
                }
            }

            // Tampilkan toast jika ada room baru (belum ada di list)
            const existingIds = Array.from(document.querySelectorAll('.room-item')).map(el => el.dataset.roomId);
            (data.rooms || []).forEach(room => {
                if (!existingIds.includes(String(room.id))) {
                    showToast('💬 Customer baru: ' + room.customer_name);
                    addRoomToSidebar(room);
                }
            });
        })
        .catch(() => {});
    }

    function addRoomToSidebar(room) {
        const list = document.getElementById('room-list');
        const existingEmpty = list.querySelector('.inbox-empty');
        if (existingEmpty) existingEmpty.remove();

        const div = document.createElement('div');
        div.className = 'room-item';
        div.id = 'room-item-' + room.id;
        div.dataset.roomId = room.id;
        div.dataset.name   = room.customer_name.toLowerCase();
        div.onclick = () => selectRoom(room.id, room.customer_name, room.contact, room.status);
        div.innerHTML = `
            <div class="room-avatar">${room.customer_name.charAt(0).toUpperCase()}</div>
            <div class="room-info">
                <div class="room-name">${escHtml(room.customer_name)}</div>
                <div class="room-preview">${escHtml(room.last_message || 'Mulai percakapan...')}</div>
            </div>
            <div class="room-meta">
                <div class="room-time">${room.last_time || 'Baru'}</div>
                <div class="room-unread" id="badge-${room.id}" ${room.unread > 0 ? '' : 'style="display:none;"'}>${room.unread || 0}</div>
            </div>
        `;
        list.prepend(div);
    }

    // ─── Search Filter ────────────────────────────────────────────
    window.filterRooms = function (q) {
        const query = q.toLowerCase();
        document.querySelectorAll('.room-item').forEach(el => {
            const text = el.textContent.toLowerCase();
            el.style.display = text.includes(query) ? '' : 'none';
        });
    };

    // Auto Select / Search on page load
    document.addEventListener('DOMContentLoaded', function () {
        const autoSelectId = "{{ $autoSelectRoomId ?? '' }}";
        if (autoSelectId) {
            const targetEl = document.getElementById('room-item-' + autoSelectId);
            if (targetEl) {
                targetEl.click();
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        const urlParams = new URLSearchParams(window.location.search);
        const searchCode = urlParams.get('code') || urlParams.get('search');
        if (searchCode) {
            const searchInput = document.getElementById('room-search');
            if (searchInput) {
                searchInput.value = searchCode;
                filterRooms(searchCode);
            }
            const rooms = document.querySelectorAll('.room-item');
            for (let el of rooms) {
                if (el.style.display !== 'none') {
                    el.click();
                    break;
                }
            }
        } else {
            const firstRoom = document.querySelector('.room-item');
            if (firstRoom) {
                firstRoom.click();
            }
        }
    });

    // ─── Toast ────────────────────────────────────────────────────
    let toastTimer;
    function showToast(msg) {
        const t = document.getElementById('chat-toast');
        t.textContent = msg;
        t.style.display = '';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { t.style.display = 'none'; }, 4000);
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
    }
})();
</script>
</body>
</html>
