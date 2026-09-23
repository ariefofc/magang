@php
    $pageTrxData = isset($transaksi) && $transaksi ? [
        'code' => $transaksi->kode_transaksi,
        'name' => $transaksi->nama_user ?? '',
    ] : null;
@endphp

<link rel="stylesheet" href="{{ asset('css/chat-widget.css') }}">

{{-- ── Tombol Trigger Floating (Customer Care & Assistant Hub) ──────────────── --}}
<div id="chat-trigger-wrapper">
    <div id="chat-trigger-badge" title="Pusat Bantuan MS Harmony: Nota, Pengembalian & Chat CS">
        <span style="font-size:13px;">💎</span>
        <span>Customer Care Hub</span>
    </div>
    <button id="chat-trigger" title="MS Harmony Customer Care: Nota, Pengembalian & Chat CS" aria-label="Buka Hub">
        <div id="chat-icon-open" style="display:flex;align-items:center;justify-content:center;">
            <span style="font-size:1.3rem;">💎</span>
        </div>
        <span id="chat-icon-close" style="display:none;">✕</span>
        <span id="chat-unread-dot"></span>
    </button>
</div>

{{-- ── Panel Chat ───────────────────────────────────────────────── --}}
<div id="chat-panel" role="dialog" aria-label="Live Chat MS Harmony">

    {{-- Header --}}
    <div class="chat-header">
        <div class="chat-header-avatar">💎</div>
        <div class="chat-header-info">
            <div class="chat-header-name" id="cw-header-name">MS Harmony Support</div>
            <div class="chat-header-status">
                <span class="chat-status-dot"></span>
                <span id="cw-header-status">Admin siap membantu</span>
            </div>
            <div class="chat-header-trx" id="cw-header-trx" style="display:none;"></div>
        </div>
        <div class="chat-header-actions">
            <button class="chat-close-btn" id="chat-close-btn" title="Tutup chat">✕</button>
        </div>
    </div>

    {{-- ── Navigasi Sub-Tab ────────────────────────────────────────── --}}
    <div class="chat-subnav" id="cw-subnav" style="display:flex;background:#FAF6F0;border-bottom:1px solid #EADCC9;padding:6px 12px;gap:6px;font-size:11px;font-weight:600;">
        <button id="tab-btn-hub" onclick="cwSwitchTab('hub')" style="padding:4px 10px;background:#6B0012;color:#fff;border:none;border-radius:4px;cursor:pointer;">🏠 Hub Utama</button>
        <button id="tab-btn-chat" onclick="cwSwitchTab('chat')" style="padding:4px 10px;background:#E8D5C4;color:#4A3728;border:none;border-radius:4px;cursor:pointer;">💬 Chat CS</button>
        <button id="tab-btn-history" onclick="cwSwitchTab('history')" style="padding:4px 10px;background:#E8D5C4;color:#4A3728;border:none;border-radius:4px;cursor:pointer;">📜 Riwayat</button>
        <button id="tab-btn-new" onclick="cwResetSession()" style="padding:4px 8px;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;margin-left:auto;">➕ Chat Baru</button>
    </div>

    {{-- ── HUB UTAMA: Self-Service Customer Care (Bubbble Cards) ───── --}}
    <div id="chat-hub-area" style="display:flex;flex-direction:column;flex:1;overflow-y:auto;padding:12px;background:#FAF6F0;gap:10px;">
        <div style="background:linear-gradient(135deg,#6B0012,#3A0809);color:#FFF;padding:12px 14px;border-radius:10px;font-size:11px;border:1px solid rgba(200,169,110,0.3);">
            <div style="font-weight:700;font-size:12px;margin-bottom:3px;color:#E8C98A;">💎 Customer Assistant Hub</div>
            <div style="opacity:0.9;line-height:1.4;">Pilih layanan cepat di bawah ini untuk melihat nota, cek pengembalian, atau chat langsung dengan Admin.</div>
        </div>

        {{-- BUBBLE 1: Riwayat Transaksi & Lihat Nota --}}
        <div onclick="cwShowSubView('transactions')" style="display:flex;align-items:center;gap:10px;padding:11px 12px;background:#FFF;border:1.5px solid #EADCC9;border-radius:10px;cursor:pointer;transition:all 0.2s;box-shadow:0 2px 6px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#6B0012';this.style.transform='translateY(-1px)'" onmouseout="this.style.borderColor='#EADCC9';this.style.transform='none'">
            <div style="width:36px;height:36px;background:rgba(107,0,18,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                📋
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:12px;color:#4A3728;">Riwayat Transaksi &amp; Lihat Nota</div>
                <div style="font-size:10px;color:#8A7060;">Cek nota sewa &amp; rincian pembayaran Anda</div>
            </div>
            <div style="color:#B8965A;font-weight:bold;font-size:14px;">›</div>
        </div>

        {{-- BUBBLE 2: Status & Pengajuan Pengembalian Kebaya --}}
        <div onclick="cwShowSubView('return')" style="display:flex;align-items:center;gap:10px;padding:11px 12px;background:#FFF;border:1.5px solid #EADCC9;border-radius:10px;cursor:pointer;transition:all 0.2s;box-shadow:0 2px 6px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#15803d';this.style.transform='translateY(-1px)'" onmouseout="this.style.borderColor='#EADCC9';this.style.transform='none'">
            <div style="width:36px;height:36px;background:rgba(21,128,61,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                🔁
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:12px;color:#4A3728;">Status &amp; Pengembalian Kebaya</div>
                <div style="font-size:10px;color:#15803d;font-weight:600;">Cek status kembali &amp; kirim pemberitahuan</div>
            </div>
            <div style="color:#15803d;font-weight:bold;font-size:14px;">›</div>
        </div>

        {{-- BUBBLE 3: Chat CS Admin Support --}}
        <div onclick="cwSwitchTab('chat')" style="display:flex;align-items:center;gap:10px;padding:11px 12px;background:#FFF;border:1.5px solid #EADCC9;border-radius:10px;cursor:pointer;transition:all 0.2s;box-shadow:0 2px 6px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#6B0012';this.style.transform='translateY(-1px)'" onmouseout="this.style.borderColor='#EADCC9';this.style.transform='none'">
            <div style="width:36px;height:36px;background:rgba(180,140,80,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                💬
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:12px;color:#4A3728;">Chat CS Admin Support</div>
                <div style="font-size:10px;color:#8A7060;">Tanya seputar kebaya &amp; konsultasi fitting</div>
            </div>
            <div style="color:#6B0012;font-weight:bold;font-size:14px;">›</div>
        </div>
    </div>

    {{-- ── SUB-VIEW: Daftar Transaksi & Nota ────────────────────────── --}}
    <div id="chat-transactions-area" style="display:none;flex-direction:column;flex:1;overflow-y:auto;padding:12px;background:#FFF;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="font-size:12px;font-weight:700;color:#6B0012;">📋 Riwayat &amp; Nota Pemesanan</div>
            <button onclick="cwSwitchTab('hub')" style="font-size:10px;background:none;border:none;color:#8B5A2B;cursor:pointer;font-weight:700;">‹ Kembali ke Hub</button>
        </div>
        <div id="cw-trx-list" style="display:flex;flex-direction:column;gap:8px;">
            <div style="font-size:11px;color:#888;text-align:center;padding:20px 0;">Memuat riwayat nota...</div>
        </div>
    </div>

    {{-- ── SUB-VIEW: Status & Ajukan Pengembalian ───────────────────── --}}
    <div id="chat-return-area" style="display:none;flex-direction:column;flex:1;overflow-y:auto;padding:12px;background:#FFF;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div style="font-size:12px;font-weight:700;color:#15803d;">🔁 Status &amp; Pengembalian Kebaya</div>
            <button onclick="cwSwitchTab('hub')" style="font-size:10px;background:none;border:none;color:#8B5A2B;cursor:pointer;font-weight:700;">‹ Kembali ke Hub</button>
        </div>
        <div id="cw-return-list" style="display:flex;flex-direction:column;gap:8px;">
            <div style="font-size:11px;color:#888;text-align:center;padding:20px 0;">Memuat status pengembalian...</div>
        </div>
    </div>

    {{-- ── STEP 1: Form Verifikasi / Identitas Chat ─────────────────── --}}
    <div id="chat-form-identity" style="display:none;">
        <p class="chat-form-title">Mulai Chat Pelanggan</p>
        <p class="chat-form-subtitle">
            Tanyakan seputar persewaan kebaya atau lacak pesanan Anda.
        </p>
        <div class="chat-form-field">
            <label class="chat-form-label" for="cw-name">Nama Lengkap *</label>
            <input id="cw-name" class="chat-form-input" type="text"
                   placeholder="Contoh: Siti Rahayu" maxlength="100" autocomplete="name">
        </div>
        <div class="chat-form-field">
            <label class="chat-form-label" for="cw-trx-code">No. Transaksi / Kode Nota <span style="font-weight:normal;color:#888;">(Opsional)</span></label>
            <input id="cw-trx-code" class="chat-form-input" type="text"
                   placeholder="Contoh: TRX-CE2547AE atau kosongkan" maxlength="50"
                   style="text-transform:uppercase;">
            <p class="chat-form-hint">Kosongkan jika hanya ingin bertanya umum.</p>
        </div>
        <div class="chat-form-error" id="cw-form-error" style="display:none;"></div>
        <button class="chat-start-btn" id="cw-start-btn" onclick="cwStartRoom()">
            💬 Mulai Chat Sekarang
        </button>
    </div>

    {{-- ── TAB RIWAYAT CHAT (History List) ────────────────────────── --}}
    <div id="chat-history-area" style="display:none;flex-direction:column;flex:1;overflow-y:auto;padding:12px;background:#fff;">
        <div style="font-size:12px;font-weight:700;color:#6B0012;margin-bottom:8px;">📜 Riwayat Percakapan Anda</div>
        <div id="cw-history-list" style="display:flex;flex-direction:column;gap:8px;">
            <div style="font-size:11px;color:#888;text-align:center;padding:20px 0;">Memuat riwayat...</div>
        </div>
    </div>

    {{-- ── STEP 2: Area Percakapan ──────────────────────────────────── --}}
    <div id="chat-messages-area" style="display:none;">
        <div class="chat-messages-scroll" id="cw-scroll"></div>
        <div id="cw-closed-notice" class="chat-closed-notice" style="display:none;">
            🔒 Percakapan ini telah ditutup. Terima kasih sudah menghubungi kami!
        </div>
        <div class="chat-input-area" id="cw-input-wrapper">
            <textarea id="cw-input" class="chat-input" placeholder="Ketik pesan..."
                      rows="1" maxlength="2000"></textarea>
            <button class="chat-send-btn" id="cw-send-btn" title="Kirim pesan">
                <span style="color:white;">➤</span>
            </button>
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';

    const SS_ROOM_KEY   = 'cw_active_room';     // { roomId, trxCode, name }
    const LS_MY_IDS_KEY = 'cw_my_room_ids';     // Array of room IDs [1, 2, 3]
    const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const BASE_URL      = '{{ url("") }}';
    const PAGE_TRX      = @json($pageTrxData);

    // ── State ─────────────────────────────────────────────────────
    let chatRoomId   = null;
    let isPanelOpen  = false;
    let pollInterval = null;
    const POLL_MS    = 3000;

    // ── DOM refs ──────────────────────────────────────────────────
    const trigger      = document.getElementById('chat-trigger');
    const panel        = document.getElementById('chat-panel');
    const iconOpen     = document.getElementById('chat-icon-open');
    const iconClose    = document.getElementById('chat-icon-close');
    const hubArea      = document.getElementById('chat-hub-area');
    const trxSubArea   = document.getElementById('chat-transactions-area');
    const retSubArea   = document.getElementById('chat-return-area');
    const formArea     = document.getElementById('chat-form-identity');
    const historyArea  = document.getElementById('chat-history-area');
    const msgArea      = document.getElementById('chat-messages-area');
    const scroll       = document.getElementById('cw-scroll');
    const inputEl      = document.getElementById('cw-input');
    const sendBtn      = document.getElementById('cw-send-btn');
    const unreadDot    = document.getElementById('chat-unread-dot');
    const closedNotice = document.getElementById('cw-closed-notice');
    const inputWrapper = document.getElementById('cw-input-wrapper');
    const formError    = document.getElementById('cw-form-error');
    const hdrTrx       = document.getElementById('cw-header-trx');
    const hdrStatus    = document.getElementById('cw-header-status');
    const tabHub       = document.getElementById('tab-btn-hub');
    const tabChat      = document.getElementById('tab-btn-chat');
    const tabHistory   = document.getElementById('tab-btn-history');

    // ── Restore atau Deteksi Sesi Halaman ─────────────────────────
    (function initSession() {
        try {
            const saved = JSON.parse(sessionStorage.getItem(SS_ROOM_KEY) || 'null');
            if (saved && saved.roomId) {
                if (PAGE_TRX && PAGE_TRX.code && saved.trxCode !== PAGE_TRX.code) {
                    sessionStorage.removeItem(SS_ROOM_KEY);
                    prefillForm(PAGE_TRX.name, PAGE_TRX.code);
                } else {
                    chatRoomId = saved.roomId;
                    updateHeaderInfo(saved.name, saved.trxCode);
                }
            } else if (PAGE_TRX && PAGE_TRX.code) {
                prefillForm(PAGE_TRX.name, PAGE_TRX.code);
            }
        } catch (e) { sessionStorage.removeItem(SS_ROOM_KEY); }
    })();

    function prefillForm(name, code) {
        const nameInp = document.getElementById('cw-name');
        const codeInp = document.getElementById('cw-trx-code');
        if (nameInp && name) nameInp.value = name;
        if (codeInp && code) codeInp.value = code;
    }

    // ── Save Room ID to LocalStorage (History) ───────────────────
    function saveRoomToHistory(roomId) {
        try {
            let ids = JSON.parse(localStorage.getItem(LS_MY_IDS_KEY) || '[]');
            if (!Array.isArray(ids)) ids = [];
            if (!ids.includes(roomId)) {
                ids.push(roomId);
                localStorage.setItem(LS_MY_IDS_KEY, JSON.stringify(ids));
            }
        } catch (e) {}
    }

    function getSavedRoomIds() {
        try {
            return JSON.parse(localStorage.getItem(LS_MY_IDS_KEY) || '[]');
        } catch (e) { return []; }
    }

    // ── Tab Switcher ──────────────────────────────────────────────
    function resetTabStyles() {
        if (tabHub)     { tabHub.style.background = '#E8D5C4'; tabHub.style.color = '#4A3728'; }
        if (tabChat)    { tabChat.style.background = '#E8D5C4'; tabChat.style.color = '#4A3728'; }
        if (tabHistory) { tabHistory.style.background = '#E8D5C4'; tabHistory.style.color = '#4A3728'; }
    }

    function hideAllAreas() {
        if (hubArea)     hubArea.style.display = 'none';
        if (trxSubArea)  trxSubArea.style.display = 'none';
        if (retSubArea)  retSubArea.style.display = 'none';
        if (formArea)    formArea.style.display = 'none';
        if (historyArea) historyArea.style.display = 'none';
        if (msgArea)     msgArea.style.display = 'none';
    }

    window.cwSwitchTab = function(tab) {
        resetTabStyles();
        hideAllAreas();

        if (tab === 'hub') {
            if (tabHub) { tabHub.style.background = '#6B0012'; tabHub.style.color = '#fff'; }
            if (hubArea) hubArea.style.display = 'flex';
        } else if (tab === 'chat') {
            if (tabChat) { tabChat.style.background = '#6B0012'; tabChat.style.color = '#fff'; }
            if (chatRoomId) {
                showChatArea();
            } else {
                if (formArea) formArea.style.display = 'block';
            }
        } else if (tab === 'history') {
            if (tabHistory) { tabHistory.style.background = '#6B0012'; tabHistory.style.color = '#fff'; }
            if (historyArea) historyArea.style.display = 'flex';
            loadHistoryList();
        }
    };

    window.cwShowSubView = function(sub) {
        resetTabStyles();
        hideAllAreas();

        if (sub === 'transactions') {
            if (trxSubArea) trxSubArea.style.display = 'flex';
            loadTransactionsList('cw-trx-list', 'all');
        } else if (sub === 'return') {
            if (retSubArea) retSubArea.style.display = 'flex';
            loadTransactionsList('cw-return-list', 'return');
        }
    };

    function loadTransactionsList(containerId, mode) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '<div style="font-size:11px;color:#888;text-align:center;padding:15px 0;">Memuat data...</div>';

        let savedCodes = [];
        try {
            savedCodes = JSON.parse(localStorage.getItem('msh_customer_trxs') || '[]');
        } catch (e) {}

        fetch(`${BASE_URL}/chat/my-transactions?codes=${savedCodes.join(',')}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.transaksi || data.transaksi.length === 0) {
                container.innerHTML = '<div style="font-size:11px;color:#888;text-align:center;padding:20px 0;">Belum ada riwayat transaksi ditemukan.</div>';
                return;
            }

            container.innerHTML = '';
            data.transaksi.forEach(t => {
                const item = document.createElement('div');
                item.style.cssText = 'background:#FAF6F0;border:1px solid #EADCC9;border-radius:8px;padding:10px;margin-bottom:8px;font-size:11px;';

                if (mode === 'all') {
                    item.innerHTML = `
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-weight:700;color:#6B0012;">${escHtml(t.kode_transaksi)}</span>
                            <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:#fff8f6;color:#6B0012;border:1px solid rgba(107,0,18,0.2);font-weight:700;">
                                ${escHtml(t.status)}
                            </span>
                        </div>
                        <div style="font-weight:600;color:#1c1917;">${escHtml(t.nama_kebaya)} (Size ${escHtml(t.ukuran)})</div>
                        <div style="color:#78716c;font-size:10px;margin:2px 0 6px 0;">a.n. ${escHtml(t.nama_user)} · Tgl Sewa: ${escHtml(t.tanggal_sewa)}</div>
                        <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px dashed #EADCC9;padding-top:6px;margin-top:4px;">
                            <span style="font-weight:700;color:#166534;">Rp ${escHtml(t.formatted_total)}</span>
                            <a href="${t.nota_url}" target="_blank" style="background:#6B0012;color:#fff;text-decoration:none;padding:3px 8px;border-radius:4px;font-size:10px;font-weight:700;">
                                🧾 Lihat Nota
                            </a>
                        </div>
                    `;
                } else if (mode === 'return') {
                    let statusBadge = '';
                    let actionButton = '';

                    if (t.is_returned) {
                        statusBadge = '<span style="color:#15803d;font-weight:700;">✅ Telah Dikembalikan</span>';
                    } else if (t.is_requested) {
                        statusBadge = '<span style="color:#0284c7;font-weight:700;">📩 Pengajuan Dikirim</span>';
                    } else {
                        statusBadge = '<span style="color:#b45309;font-weight:700;">📦 Sedang Disewa</span>';
                        actionButton = `
                            <button onclick="cwSendReturnRequest(${t.id}, this)" style="background:linear-gradient(135deg,#6B0012,#8B1522);color:#fff;border:none;padding:6px 10px;border-radius:5px;font-size:10px;font-weight:700;cursor:pointer;margin-top:6px;width:100%;">
                                📩 Ajukan Pengembalian Kebaya Ini
                            </button>
                        `;
                    }

                    item.innerHTML = `
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-weight:700;color:#4A3728;">${escHtml(t.nama_kebaya)}</span>
                            <span style="font-size:10px;">${statusBadge}</span>
                        </div>
                        <div style="color:#78716c;font-size:10px;">Kode: <strong>${escHtml(t.kode_transaksi)}</strong> · Batas: <strong>${escHtml(t.tanggal_pengembalian)}</strong></div>
                        <div style="color:#b45309;font-size:10px;margin-top:2px;">Sisa Pelunasan: <strong>Rp ${escHtml(t.formatted_sisa)}</strong></div>
                        ${actionButton}
                    `;
                }

                container.appendChild(item);
            });
        })
        .catch(() => {
            container.innerHTML = '<div style="font-size:11px;color:#dc2626;text-align:center;padding:15px 0;">Gagal memuat data.</div>';
        });
    }

    window.cwSendReturnRequest = function(trxId, btnEl) {
        if (!confirm('Apakah Anda ingin mengirimkan pengajuan pengembalian untuk kebaya ini ke Admin MS Harmony?')) return;

        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerText = 'Mengirim...';
        }

        fetch(`${BASE_URL}/transaksi/${trxId}/ajukan-pengembalian`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(() => {
            alert('✅ Pengajuan pengembalian berhasil dikirim! Silakan datang ke Studio MS Harmony untuk penyerahan fisik kebaya & verifikasi pelunasan oleh Admin.');
            loadTransactionsList('cw-return-list', 'return');
        })
        .catch(() => {
            alert('Gagal mengirim pengajuan. Silakan coba lagi.');
            if (btnEl) btnEl.disabled = false;
        });
    };

    // ── Load History List ────────────────────────────────────────
    function loadHistoryList() {
        const listContainer = document.getElementById('cw-history-list');
        listContainer.innerHTML = '<div style="font-size:11px;color:#888;text-align:center;padding:15px 0;">Memuat riwayat...</div>';

        const roomIds = getSavedRoomIds();
        fetch(`${BASE_URL}/chat/my-rooms?room_ids=${roomIds.join(',')}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.rooms || data.rooms.length === 0) {
                listContainer.innerHTML = '<div style="font-size:11px;color:#888;text-align:center;padding:20px 0;">Belum ada riwayat percakapan.</div>';
                return;
            }

            listContainer.innerHTML = '';
            data.rooms.forEach(r => {
                const isCurrent = (r.id === chatRoomId);
                const item = document.createElement('div');
                item.style.cssText = `padding:10px;border-radius:8px;border:1px solid ${isCurrent ? '#6B0012' : '#EADCC9'};background:${isCurrent ? '#FAF2EB' : '#FFF8F2'};cursor:pointer;transition:all 0.2s;`;
                item.onclick = () => cwOpenRoomFromHistory(r.id, r.customer_name, r.transaction_code);

                item.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="font-weight:700;color:#6B0012;font-size:12px;">${escHtml(r.customer_name)}</span>
                        <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:${r.status === 'open' ? '#dcfce7' : '#f3f4f6'};color:${r.status === 'open' ? '#15803d' : '#6b7280'};">
                            ${r.status === 'open' ? '🟢 Aktif' : '🔒 Selesai'}
                        </span>
                    </div>
                    <div style="font-size:10px;color:#8B5A2B;margin-bottom:4px;">📋 Nota: ${escHtml(r.transaction_code)}</div>
                    <div style="font-size:11px;color:#4A3728;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escHtml(r.last_message)}</div>
                `;
                listContainer.appendChild(item);
            });
        })
        .catch(() => {
            listContainer.innerHTML = '<div style="font-size:11px;color:#dc2626;text-align:center;padding:15px 0;">Gagal memuat riwayat.</div>';
        });
    }

    function cwOpenRoomFromHistory(roomId, name, trxCode) {
        chatRoomId = roomId;
        sessionStorage.setItem(SS_ROOM_KEY, JSON.stringify({
            roomId: chatRoomId,
            trxCode: trxCode,
            name: name,
        }));
        updateHeaderInfo(name, trxCode);
        cwSwitchTab('chat');
        showChatArea();
        loadMessages();
        startPolling();
    }

    // ── Toggle Panel ──────────────────────────────────────────────
    const triggerBadge = document.getElementById('chat-trigger-badge');
    if (triggerBadge) {
        triggerBadge.addEventListener('click', () => trigger.click());
    }

    trigger.addEventListener('click', () => {
        isPanelOpen = !isPanelOpen;
        panel.classList.toggle('open', isPanelOpen);
        iconOpen.style.display  = isPanelOpen ? 'none' : '';
        iconClose.style.display = isPanelOpen ? '' : 'none';

        if (isPanelOpen) {
            unreadDot.style.display = 'none';
            if (chatRoomId) {
                showChatArea();
                loadMessages();
                startPolling();
            } else {
                cwSwitchTab('hub');
            }
        } else {
            stopPolling();
        }
    });

    document.getElementById('chat-close-btn').addEventListener('click', () => {
        isPanelOpen = false;
        panel.classList.remove('open');
        iconOpen.style.display  = '';
        iconClose.style.display = 'none';
        stopPolling();
    });

    // ── Ganti Nota / Chat Baru (reset sesi) ───────────────────────
    window.cwResetSession = function () {
        sessionStorage.removeItem(SS_ROOM_KEY);
        chatRoomId = null;
        stopPolling();
        scroll.innerHTML       = '';
        formArea.style.display  = '';
        msgArea.style.display   = 'none';
        historyArea.style.display = 'none';
        hdrTrx.style.display    = 'none';
        hdrStatus.textContent   = 'Admin siap membantu';
        document.getElementById('cw-name').value     = '';
        document.getElementById('cw-trx-code').value = '';
        hideError();
        cwSwitchTab('chat');
    };

    // ── Step 1: Mulai Room (verifikasi kode transaksi / tanya umum) ────────────
    window.cwStartRoom = function () {
        const nameInp = document.getElementById('cw-name');
        const codeInp = document.getElementById('cw-trx-code');

        const name = nameInp ? nameInp.value.trim() : '';
        const code = codeInp ? codeInp.value.trim().toUpperCase() : '';

        if (!name) { showError('Nama lengkap wajib diisi!'); return; }
        hideError();

        const btn = document.getElementById('cw-start-btn');
        btn.disabled = true;
        btn.textContent = 'Memulai Chat...';

        fetch(BASE_URL + '/chat/start-room', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ customer_name: name, transaction_code: code }),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '💬 Mulai Chat Sekarang';

            if (!data.success) {
                showError(data.message || data.errors?.transaction_code?.[0] || 'Gagal memulai chat.');
                return;
            }

            chatRoomId = data.room_id;
            saveRoomToHistory(chatRoomId);

            // Simpan ke sessionStorage
            sessionStorage.setItem(SS_ROOM_KEY, JSON.stringify({
                roomId: chatRoomId,
                trxCode: data.transaction_code,
                name: name,
            }));

            updateHeaderInfo(name, data.transaction_code);
            showChatArea();

            if (data.is_new) {
                const welcomeMsg = 'Halo Admin MS Harmony! Saya ' + name
                    + (data.transaction_code && data.transaction_code !== 'Tanya Umum'
                        ? '. Saya ingin bertanya mengenai pesanan ' + data.transaction_code
                            + (data.nama_kebaya ? ' (' + data.nama_kebaya + ')' : '')
                        : '. Saya ingin bertanya seputar sewa kebaya. 😊');
                cwSendToServer(welcomeMsg);
            } else {
                loadMessages();
            }

            startPolling();
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '💬 Mulai Chat Sekarang';
            showError('Kesalahan koneksi. Silakan coba lagi.');
        });
    };

    function updateHeaderInfo(name, trxCode) {
        hdrStatus.textContent = 'Pelanggan: ' + (name || 'Umum');
        if (trxCode) {
            hdrTrx.textContent  = '📋 ' + trxCode;
            hdrTrx.style.display = '';
        }
    }

    function showChatArea() {
        formArea.style.display    = 'none';
        historyArea.style.display = 'none';
        msgArea.style.display     = 'flex';
        if (inputEl) inputEl.focus();
    }

    // ── Load & Render Pesan ───────────────────────────────────────
    function loadMessages() {
        if (!chatRoomId) return;
        fetch(`${BASE_URL}/chat/${chatRoomId}/messages`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            renderMessages(data.messages || []);
            const isClosed = data.room_status === 'closed';
            closedNotice.style.display  = isClosed ? '' : 'none';
            inputWrapper.style.display  = isClosed ? 'none' : '';
            if (isClosed) stopPolling();
        })
        .catch(() => {});
    }

    function renderMessages(messages) {
        scroll.innerHTML = '';
        let lastDate = '';
        messages.forEach(m => {
            if (m.date && m.date !== lastDate) {
                const sep = document.createElement('div');
                sep.className = 'msg-date-sep';
                sep.textContent = m.date;
                scroll.appendChild(sep);
                lastDate = m.date;
            }
            appendBubble(m);
        });
        scrollToBottom();
    }

    function appendBubble(m) {
        const isMe = (m.sender_type === 'customer');

        const row = document.createElement('div');
        row.className = 'msg-row ' + (isMe ? 'msg-outgoing' : 'msg-incoming');

        const nameEl = document.createElement('div');
        nameEl.className = 'msg-name';
        nameEl.textContent = isMe ? 'Kamu' : 'Admin MS Harmony';

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.innerHTML = escHtml(m.message);

        const time = document.createElement('div');
        time.className = 'msg-time';
        time.textContent = m.time || '';

        row.appendChild(nameEl);
        row.appendChild(bubble);
        row.appendChild(time);
        scroll.appendChild(row);

        if (m.id) lastMsgId = Math.max(lastMsgId, m.id);
    }

    let lastMsgId = 0;
    function scrollToBottom() { scroll.scrollTop = scroll.scrollHeight; }

    // ── Kirim Pesan ───────────────────────────────────────────────
    sendBtn.addEventListener('click', cwDoSend);
    inputEl.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); cwDoSend(); }
    });
    inputEl.addEventListener('input', () => {
        inputEl.style.height = 'auto';
        inputEl.style.height = Math.min(inputEl.scrollHeight, 80) + 'px';
    });

    function cwDoSend() {
        const msg = inputEl.value.trim();
        if (!msg || !chatRoomId) return;
        inputEl.value = '';
        inputEl.style.height = 'auto';
        cwSendToServer(msg);
    }

    function cwSendToServer(msg) {
        fetch(`${BASE_URL}/chat/${chatRoomId}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: msg }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                appendBubble({ ...data, sender_type: 'customer' });
                scrollToBottom();
            }
        })
        .catch(() => {});
    }

    // ── Polling ───────────────────────────────────────────────────
    function startPolling() {
        stopPolling();
        pollInterval = setInterval(() => {
            if (isPanelOpen && chatRoomId) loadMessages();
        }, POLL_MS);
    }
    function stopPolling() {
        if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
    }

    // ── Unread badge ──────────────────────────────────────────────
    setInterval(() => {
        if (!chatRoomId || isPanelOpen) return;
        fetch(`${BASE_URL}/chat/${chatRoomId}/messages`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            const hasNew = (data.messages || []).some(
                m => m.sender_type === 'admin' && !m.is_read
            );
            unreadDot.style.display = hasNew ? '' : 'none';
        })
        .catch(() => {});
    }, 6000);

    // ── Helpers ───────────────────────────────────────────────────
    function showError(msg) {
        formError.textContent = msg;
        formError.style.display = 'block';
    }
    function hideError() { formError.style.display = 'none'; }
    function escHtml(s) {
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/\n/g,'<br>');
    }

    window._cwChat = {
        open  : () => trigger.click(),
        reset : cwResetSession,
        roomId: () => chatRoomId,
    };

})();
</script>
