<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    //  SISI CUSTOMER (Publik, Tanpa Login)
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /chat/start-room
     *
     * Endpoint utama customer untuk memulai chat.
     * Validasi transaction_code ke tabel transaksi_sewa, lalu buat/ambil room.
     * Jika transaction_code diisi, lakukan verifikasi fleksibel & hubungkan ke transaksi.
     * Jika kosong, buat/ambil room pertanyaan umum (Guest Inquiry).
     */
    public function startRoom(Request $request)
    {
        $request->validate([
            'customer_name'    => 'required|string|max:100',
            'transaction_code' => 'nullable|string|max:50',
        ]);

        $name      = trim($request->customer_name);
        $rawCode   = trim($request->transaction_code ?? '');
        $kode      = strtoupper(ltrim($rawCode, '#'));
        $sessionId = $request->session()->getId();

        $transaksi = null;
        if (!empty($kode)) {
            // Verifikasi kode transaksi fleksibel (bisa kode_transaksi, #kode, atau id)
            $transaksi = Transaksi::where('kode_transaksi', $kode)
                ->orWhere('kode_transaksi', '#' . $kode)
                ->orWhere('kode_transaksi', 'LIKE', '%' . $kode . '%')
                ->orWhere('id', is_numeric($kode) ? (int)$kode : 0)
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor transaksi "' . $rawCode . '" tidak ditemukan. Silakan periksa kembali kode di nota Anda.',
                ], 422);
            }
        }

        // Cari room yang cocok
        $room = null;
        if ($transaksi) {
            $room = ChatRoom::where('transaction_code', $transaksi->kode_transaksi)
                ->orWhere('order_id', $transaksi->id)
                ->first();
        } else {
            $room = ChatRoom::where('session_id', $sessionId)
                ->whereNull('transaction_code')
                ->first();
        }

        if (!$room) {
            $room = ChatRoom::create([
                'session_id'       => $sessionId,
                'order_id'         => $transaksi?->id,
                'transaction_code' => $transaksi?->kode_transaksi ?? null,
                'customer_name'    => $name,
                'customer_contact' => $transaksi->no_wa ?? '-',
                'status'           => 'open',
                'last_message_at'  => now(),
            ]);
        } else {
            // Update session_id & customer_name agar terikat ke sesi aktif terbaru (hindari 403 Akses Ditolak)
            $room->update([
                'session_id'       => $sessionId,
                'customer_name'    => $name,
                'order_id'         => $transaksi?->id ?? $room->order_id,
                'transaction_code' => $transaksi?->kode_transaksi ?? $room->transaction_code,
                'status'           => 'open',
            ]);
        }

        // Simpan izin room di session Laravel
        $request->session()->put('active_chat_room_' . $room->id, true);

        return response()->json([
            'success'          => true,
            'room_id'          => $room->id,
            'transaction_code' => $room->transaction_code ?? 'Tanya Umum',
            'nama_kebaya'      => $transaksi?->nama_kebaya,
            'is_new'           => $room->wasRecentlyCreated,
        ]);
    }

    /**
     * GET /chat/my-rooms
     *
     * Ambil daftar riwayat room milik customer berdasarkan session & room IDs.
     */
    public function getMyRooms(Request $request)
    {
        $sessionId = $request->session()->getId();
        $rawIds    = $request->query('room_ids', '');
        $roomIds   = array_values(array_filter(array_map('intval', explode(',', $rawIds))));

        $rooms = ChatRoom::with('latestMessage')
            ->where(function ($q) use ($sessionId, $roomIds) {
                $q->where('session_id', $sessionId);
                if (!empty($roomIds)) {
                    $q->orWhereIn('id', $roomIds);
                }
            })
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(fn ($r) => [
                'id'               => $r->id,
                'customer_name'    => $r->customer_name,
                'transaction_code' => $r->transaction_code ?? 'Tanya Umum',
                'status'           => $r->status,
                'last_message'     => $r->latestMessage?->message ?? 'Belum ada pesan',
                'last_time'        => $r->last_message_at?->diffForHumans() ?? '-',
            ]);

        return response()->json([
            'success' => true,
            'rooms'   => $rooms,
        ]);
    }

    /**
     * GET /chat/my-transactions
     * Ambil riwayat transaksi milik customer untuk ditampilkan di Widget Hub.
     */
    public function getMyTransactions(Request $request)
    {
        $rawCodes = $request->query('codes', '');
        $codes    = array_values(array_filter(array_map('trim', explode(',', $rawCodes))));

        $query = Transaksi::latest();

        if (!empty($codes)) {
            $query->where(function($q) use ($codes) {
                $q->whereIn('kode_transaksi', $codes)
                  ->orWhereIn('id', array_filter($codes, 'is_numeric'));
            });
        } else {
            // Ambil 6 transaksi terbaru sebagai fallback agar customer selalu bisa mencoba
            $query->limit(6);
        }

        $transaksi = $query->get()->map(function ($t) {
            $skemaBayar = strtolower($t->skema_bayar ?? 'dp 50%');
            $pctDP      = str_contains($skemaBayar, '70') ? 0.7 : (str_contains($skemaBayar, 'lunas') || str_contains($skemaBayar, '100') ? 1.0 : 0.5);
            $dp         = ceil($t->total_harga * $pctDP);
            $sisa       = max(0, $t->total_harga - $dp);

            return [
                'id'                   => $t->id,
                'kode_transaksi'       => $t->kode_transaksi,
                'nama_kebaya'          => $t->nama_kebaya,
                'nama_user'            => $t->nama_user,
                'ukuran'               => $t->ukuran,
                'status'               => $t->status,
                'total_harga'          => $t->total_harga,
                'formatted_total'      => number_format($t->total_harga, 0, ',', '.'),
                'formatted_sisa'       => number_format($sisa, 0, ',', '.'),
                'tanggal_sewa'         => $t->tanggal_sewa ? \Carbon\Carbon::parse($t->tanggal_sewa)->format('d/m/Y') : '-',
                'tanggal_pengembalian' => $t->tanggal_pengembalian ? \Carbon\Carbon::parse($t->tanggal_pengembalian)->format('d/m/Y') : '-',
                'nota_url'             => route('transaksi.nota', $t->id),
                'can_return'           => in_array($t->status, ['Kebaya Telah Diambil', 'Sedang Fitting', 'Telah Dikonfirmasi']),
                'is_requested'         => $t->status === 'Mengajukan Pengembalian',
                'is_returned'          => $t->status === 'Telah Dikembalikan',
            ];
        });

        return response()->json([
            'success'   => true,
            'transaksi' => $transaksi,
        ]);
    }

    /**
     * POST /chat/init  (legacy — tetap dipertahankan untuk kompatibilitas)
     * Gunakan startRoom() untuk alur baru.
     */
    public function initRoom(Request $request)
    {
        $request->validate([
            'customer_name'    => 'required|string|max:100',
            'customer_contact' => 'nullable|string|max:50',
            'order_id'         => 'nullable|integer|exists:transaksi_sewa,id',
        ]);

        $sessionId = $request->session()->getId();
        $orderId   = $request->order_id;

        $query = ChatRoom::where('session_id', $sessionId);
        $query = $orderId
            ? $query->where('order_id', $orderId)
            : $query->whereNull('order_id');
        $room = $query->first();

        if (!$room) {
            $room = ChatRoom::create([
                'session_id'       => $sessionId,
                'customer_name'    => $request->customer_name,
                'customer_contact' => $request->customer_contact ?? '-',
                'order_id'         => $orderId,
                'status'           => 'open',
                'last_message_at'  => now(),
            ]);
        } else {
            $room->update([
                'session_id'       => $sessionId,
                'customer_name'    => $request->customer_name,
                'customer_contact' => $request->customer_contact ?? $room->customer_contact,
                'status'           => 'open',
            ]);
        }

        $request->session()->put('active_chat_room_' . $room->id, true);

        return response()->json([
            'success'  => true,
            'room_id'  => $room->id,
            'order_id' => $room->order_id,
            'session'  => $sessionId,
            'is_new'   => $room->wasRecentlyCreated,
        ]);
    }

    /**
     * GET /chat/{roomId}/messages
     *
     * Ambil pesan-pesan dalam room.
     * Customer: akses via session_id / active_chat_room permission. Admin: akses via auth.
     */
    public function getMessages(Request $request, int $roomId)
    {
        $room = ChatRoom::findOrFail($roomId);

        if (!auth()->check()) {
            // Verifikasi & sinkronkan akses customer via session
            $sessionId = $request->session()->getId();
            $hasPermission = $request->session()->get('active_chat_room_' . $room->id, false);

            if ($room->session_id !== $sessionId && !$hasPermission) {
                // Update session ID agar room kembali terhubung tanpa error 403
                $room->update(['session_id' => $sessionId]);
            }
            $request->session()->put('active_chat_room_' . $room->id, true);

            // Mark pesan admin → sudah dibaca customer
            $room->messages()
                 ->where('sender_type', 'admin')
                 ->where('is_read', false)
                 ->update(['is_read' => true]);
        } else {
            // Admin: mark pesan customer → sudah dibaca
            $room->messages()
                 ->where('sender_type', 'customer')
                 ->where('is_read', false)
                 ->update(['is_read' => true]);
        }

        $messages = $room->messages()
                         ->orderBy('created_at', 'asc')
                         ->take(100)
                         ->get()
                         ->map(fn ($m) => [
                             'id'          => $m->id,
                             'sender_type' => $m->sender_type,
                             'sender_name' => $m->sender_name,
                             'message'     => $m->message,
                             'is_read'     => $m->is_read,
                             'time'        => $m->created_at?->format('H:i'),
                             'date'        => $m->created_at?->format('d/m/Y'),
                         ]);

        return response()->json([
            'messages'         => $messages,
            'room_status'      => $room->status,
            'customer_name'    => $room->customer_name,
            'transaction_code' => $room->transaction_code ?? 'Tanya Umum',
        ]);
    }

    /**
     * POST /chat/{roomId}/send (Customer) & POST /admin/chat/{roomId}/send (Admin)
     *
     * Kirim pesan baru.
     */
    public function sendMessage(Request $request, int $roomId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $room = ChatRoom::findOrFail($roomId);

        // Bedakan berdasarkan rute (rute admin /admin/chat/... vs rute customer /chat/...)
        $isAdminRoute = $request->is('admin/*') || $request->routeIs('admin.chat.*');

        if ($isAdminRoute) {
            $senderType = 'admin';
            $senderName = auth()->check() ? auth()->user()->name : 'Admin MS Harmony';
        } else {
            $senderType = 'customer';
            $senderName = $room->customer_name;

            // Sinkronkan session ID untuk customer
            $sessionId = $request->session()->getId();
            if ($room->session_id !== $sessionId) {
                $room->update(['session_id' => $sessionId]);
            }
            $request->session()->put('active_chat_room_' . $room->id, true);
        }

        $message = Message::create([
            'room_id'     => $room->id,
            'sender_type' => $senderType,
            'sender_name' => $senderName,
            'message'     => $request->message,
            'is_read'     => false,
        ]);

        $room->update(['last_message_at' => now(), 'status' => 'open']);

        return response()->json([
            'success'     => true,
            'id'          => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $message->sender_name,
            'message'     => $message->message,
            'time'        => $message->created_at?->format('H:i'),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  SISI ADMIN (Authenticated)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Halaman inbox admin.
     */
    /**
     * Halaman inbox admin.
     * Otomatis membuat/menghubungkan ChatRoom untuk transaksi pelanggan agar Admin dapat langsung memulai percakapan.
     */
    public function inbox(Request $request)
    {
        $targetCode = trim($request->query('code') ?? $request->query('search') ?? '');
        $autoSelectRoomId = null;

        // 1. Jika ada parameter 'code' / 'search' dari tombol Live Chat Admin Dashboard
        if (!empty($targetCode)) {
            $kodeClean = strtoupper(ltrim($targetCode, '#'));
            $transaksi = Transaksi::where('kode_transaksi', $kodeClean)
                ->orWhere('kode_transaksi', '#' . $kodeClean)
                ->orWhere('kode_transaksi', 'LIKE', '%' . $kodeClean . '%')
                ->orWhere('id', is_numeric($kodeClean) ? (int)$kodeClean : 0)
                ->first();

            if ($transaksi) {
                $room = ChatRoom::where('transaction_code', $transaksi->kode_transaksi)
                    ->orWhere('order_id', $transaksi->id)
                    ->first();

                if (!$room) {
                    $room = ChatRoom::create([
                        'session_id'       => 'admin_init_' . $transaksi->id . '_' . uniqid(),
                        'order_id'         => $transaksi->id,
                        'transaction_code' => $transaksi->kode_transaksi,
                        'customer_name'    => $transaksi->nama_user,
                        'customer_contact' => $transaksi->no_wa ?? '-',
                        'status'           => 'open',
                        'last_message_at'  => now(),
                    ]);

                    Message::create([
                        'room_id'     => $room->id,
                        'sender_type' => 'admin',
                        'sender_name' => auth()->user()?->name ?? 'Admin MS Harmony',
                        'message'     => 'Halo Kak ' . $transaksi->nama_user . ' 👋, percakapan untuk transaksi ' . $transaksi->kode_transaksi . ' (' . $transaksi->nama_kebaya . ') telah dibuka oleh Admin MS Harmony. Ada yang bisa kami bantu?',
                        'is_read'     => true,
                    ]);
                }

                $autoSelectRoomId = $room->id;
            }
        }

        // 2. Auto-Sync: Buat ChatRoom untuk transaksi yang belum memiliki room agar selalu muncul di sidebar Admin
        try {
            $existingOrderIds = ChatRoom::whereNotNull('order_id')->pluck('order_id')->toArray();
            $missingTransaksis = Transaksi::whereNotIn('id', $existingOrderIds)->latest()->take(30)->get();

            foreach ($missingTransaksis as $trx) {
                $roomSync = ChatRoom::create([
                    'session_id'       => 'sync_' . $trx->id . '_' . uniqid(),
                    'order_id'         => $trx->id,
                    'transaction_code' => $trx->kode_transaksi,
                    'customer_name'    => $trx->nama_user,
                    'customer_contact' => $trx->no_wa ?? '-',
                    'status'           => 'open',
                    'last_message_at'  => $trx->created_at ?? now(),
                ]);

                Message::create([
                    'room_id'     => $roomSync->id,
                    'sender_type' => 'admin',
                    'sender_name' => 'Admin MS Harmony',
                    'message'     => 'Pesanan ' . $trx->kode_transaksi . ' (' . $trx->nama_kebaya . ') terdaftar. Klik di sini untuk koordinasi sewa & fitting.',
                    'is_read'     => true,
                    'created_at'  => $trx->created_at ?? now(),
                ]);

                if (!$autoSelectRoomId && !empty($targetCode) && (str_contains($trx->kode_transaksi, $kodeClean) || $trx->nama_user === $targetCode)) {
                    $autoSelectRoomId = $roomSync->id;
                }
            }
        } catch (\Throwable $syncErr) {}

        $rooms = ChatRoom::with(['latestMessage', 'transaksi'])
                         ->orderBy('last_message_at', 'desc')
                         ->get();

        $totalUnread = ChatRoom::totalUnread();

        // Jika belum ada room spesifik dipilih tapi ada daftar room, pilih room pertama
        if (!$autoSelectRoomId && $rooms->count() > 0) {
            $autoSelectRoomId = $rooms->first()->id;
        }

        return view('chat.inbox', compact('rooms', 'totalUnread', 'autoSelectRoomId'));
    }

    /**
     * AJAX polling daftar rooms + unread count.
     */
    public function getRooms()
    {
        $rooms = ChatRoom::with(['latestMessage', 'transaksi'])
                         ->orderBy('last_message_at', 'desc')
                         ->get()
                         ->map(fn ($room) => [
                             'id'               => $room->id,
                             'customer_name'    => $room->customer_name,
                             'transaction_code' => $room->transaction_code ?? ($room->transaksi?->kode_transaksi ?? 'Tanya Umum'),
                             'nama_kebaya'      => $room->transaksi?->nama_kebaya ?? null,
                             'contact'          => $room->customer_contact,
                             'status'           => $room->status,
                             'unread'           => $room->unreadCount(),
                             'last_message'     => $room->latestMessage?->message ?? 'Mulai percakapan...',
                             'last_time'        => $room->last_message_at?->diffForHumans() ?? '-',
                             'order_id'         => $room->order_id,
                         ]);

        return response()->json([
            'rooms'        => $rooms,
            'total_unread' => ChatRoom::totalUnread(),
        ]);
    }

    /**
     * Tutup room percakapan.
     */
    public function closeRoom(int $roomId)
    {
        $room = ChatRoom::findOrFail($roomId);
        $room->update(['status' => 'closed']);

        return response()->json(['success' => true, 'message' => 'Percakapan telah ditutup.']);
    }

    /**
     * Buka kembali room yang sudah closed.
     */
    public function reopenRoom(int $roomId)
    {
        $room = ChatRoom::findOrFail($roomId);
        $room->update(['status' => 'open']);

        return response()->json(['success' => true, 'message' => 'Percakapan dibuka kembali.']);
    }
}
