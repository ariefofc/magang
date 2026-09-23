<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Helpers\QrisDynamicHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }

    // ═══════════════════════════════════════════════════════════════
    //  HALAMAN PEMBAYARAN UTAMA
    // ═══════════════════════════════════════════════════════════════

    /**
     * Halaman pembayaran.
     * — Jika Midtrans keys tersedia → tampilkan tombol Snap Midtrans (PRIMARY)
     * — Jika QRIS DANA tersedia → tampilkan QRIS Dinamis (SECONDARY)
     */
    public function show(int $id)
    {
        $transaksi = Transaksi::findOrFail($id);

        // ── Hitung nominal sesuai skema bayar ──────────────────────
        $nominal = (int) $transaksi->nominalDP();
        $skema   = $transaksi->labelSkema();

        // ── Cek ketersediaan Midtrans ──────────────────────────────
        $midtransReady = $this->isMidtransReady();

        // ── Siapkan Snap Token (generate langsung saat halaman dibuka) ──
        $snapToken  = null;
        $snapError  = null;

        if ($midtransReady && !$transaksi->isPaid()) {
            try {
                $snapToken = $this->generateSnapToken($transaksi, $nominal);
            } catch (\Throwable $e) {
                $snapError = $e->getMessage();
                Log::error('[Midtrans] Gagal generate snap token saat show(): ' . $e->getMessage());
            }
        }

        // ── Generate QRIS Dinamis Resmi dari Midtrans Core API (qr_string) ──
        $midtransQrisImageData = null;
        $midtransQrString      = null;

        if ($midtransReady && !$transaksi->isPaid()) {
            try {
                $qrisData = $this->generateMidtransQris($transaksi, $nominal);
                if ($qrisData) {
                    $midtransQrString      = $qrisData['qr_string'];
                    $midtransQrisImageData = $qrisData['image_data'];
                }
            } catch (\Throwable $e) {
                Log::error('[Midtrans Core API QRIS] Gagal generate: ' . $e->getMessage());
            }
        }

        // ── QRIS DANA Statis → Dinamis (fallback jika Midtrans tidak aktif) ──
        $qrisImageData = $midtransQrisImageData;
        $qrisNominal   = $nominal;
        $qrisSkema     = $skema;
        $qrisError     = null;
        $qrisMode      = !empty($midtransQrisImageData);

        if (!$qrisMode) {
            $staticString = config('qris.static_string', '');
            $isQrisReady  = !empty($staticString)
                         && $staticString !== 'TEMPEL_RAW_STRING_QRIS_DANA_BISNIS_ANDA_DI_SINI';

            if ($isQrisReady && !$transaksi->isPaid()) {
                try {
                    $payload       = QrisDynamicHelper::generate($staticString, $nominal);
                    $size          = (int) config('qris.qr_size', 280);
                    $qrisImageData = QrisDynamicHelper::toBase64Png($payload, $size);
                    $qrisNominal   = $nominal;
                    $qrisSkema     = $skema;
                    $qrisMode      = true;
                } catch (\Throwable $e) {
                    $qrisError = $e->getMessage();
                    Log::error('[QRIS Statis] Gagal generate: ' . $e->getMessage());
                }
            }

            // Fallback ke gambar QRIS Mayangsari dari folder public/ jika dynamic string belum diisi
            if (!$qrisMode && !$transaksi->isPaid()) {
                $customImg = config('qris.image_path', 'qris_mayangsari.jpeg');
                if (file_exists(public_path($customImg))) {
                    $qrisImageData = asset($customImg);
                } elseif (file_exists(public_path('qris_mayangsari.jpeg'))) {
                    $qrisImageData = asset('qris_mayangsari.jpeg');
                } elseif (file_exists(public_path('qris mayangsari.jpeg'))) {
                    $qrisImageData = asset('qris mayangsari.jpeg');
                }

                if ($qrisImageData) {
                    $qrisNominal = $nominal;
                    $qrisSkema   = $skema;
                    $qrisMode    = true;
                }
            }
        }

        return view('payment.qris_modal', compact(
            'transaksi',
            'nominal',
            'skema',
            // Midtrans
            'midtransReady',
            'snapToken',
            'snapError',
            'midtransQrString',
            // QRIS Display
            'qrisImageData',
            'qrisNominal',
            'qrisSkema',
            'qrisError',
            'qrisMode'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    //  MIDTRANS SNAP
    // ═══════════════════════════════════════════════════════════════

    /**
     * AJAX: Generate / ambil ulang Snap Token.
     * Dipanggil oleh tombol "Bayar Sekarang" via fetch().
     */
    public function initiate(Request $request, int $id)
    {
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini sudah lunas.',
                'paid'    => true,
            ]);
        }

        // Jika sudah ada snap_token, kembalikan langsung
        if ($transaksi->hasSnapToken() && $transaksi->snap_token) {
            return response()->json([
                'success'    => true,
                'snap_token' => $transaksi->snap_token,
                'client_key' => config('midtrans.client_key'),
                'snap_url'   => config('midtrans.snap_url'),
            ]);
        }

        try {
            $nominal   = (int) $transaksi->nominalDP();
            $snapToken = $this->generateSnapToken($transaksi, $nominal);

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken,
                'client_key' => config('midtrans.client_key'),
                'snap_url'   => config('midtrans.snap_url'),
            ]);
        } catch (\Throwable $e) {
            Log::error('[Midtrans] initiate() error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi payment gateway: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: generate Snap Token & simpan ke database.
     */
    private function generateSnapToken(Transaksi $transaksi, int $nominal): string
    {
        $orderId = $transaksi->kode_transaksi . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $nominal,
            ],
            'customer_details' => [
                'first_name' => $transaksi->nama_user,
                'phone'      => $transaksi->no_wa,
            ],
            'item_details' => [[
                'id'       => 'KEBAYA-' . $transaksi->id,
                'price'    => $nominal,
                'quantity' => 1,
                'name'     => 'Sewa ' . $transaksi->nama_kebaya . ' (' . $transaksi->labelSkema() . ')',
            ]],
            'enabled_payments' => config('midtrans.enabled_payments', ['qris', 'gopay', 'bank_transfer']),
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit'       => 'minutes',
                'duration'   => (int) config('midtrans.expiry_minutes', 30),
            ],
            'callbacks' => [
                'finish'   => config('midtrans.finish_url')   ?? route('payment.show', $transaksi->id),
                'unfinish' => config('midtrans.unfinish_url') ?? route('payment.show', $transaksi->id),
                'error'    => config('midtrans.error_url')    ?? route('payment.show', $transaksi->id),
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        $transaksi->update([
            'snap_token'     => $snapToken,
            'transaction_id' => $orderId,
            'payment_status' => 'pending',
        ]);

        Log::info('[Midtrans] Snap token dibuat untuk transaksi #' . $transaksi->id
            . ' order_id=' . $orderId
            . ' nominal=' . $nominal);

        return $snapToken;
    }

    /**
     * Generate QRIS Dinamis via Midtrans Core API (mengambil qr_string).
     */
    private function generateMidtransQris(Transaksi $transaksi, int $nominal): ?array
    {
        $orderId = $transaksi->kode_transaksi . '-qris-' . time();

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $nominal,
            ],
            'customer_details' => [
                'first_name' => $transaksi->nama_user,
                'phone'      => $transaksi->no_wa ?? '081234567890',
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        $charge = \Midtrans\CoreApi::charge($params);

        $qrString = $charge->qr_string ?? null;
        if (!$qrString) {
            return null;
        }

        $qrUrl = null;
        if (!empty($charge->actions)) {
            foreach ($charge->actions as $act) {
                if (($act->name ?? '') === 'generate-qr-code') {
                    $qrUrl = $act->url ?? null;
                    break;
                }
            }
        }

        $transaksi->update([
            'transaction_id' => $orderId,
            'payment_status' => 'pending',
        ]);

        $size = (int) config('qris.qr_size', 280);
        $imageData = $qrUrl ?: QrisDynamicHelper::toBase64Png($qrString, $size);

        Log::info('[Midtrans Core API] Dynamic QRIS generated for transaction #' . $transaksi->id . ' order_id=' . $orderId . ' qrUrl=' . ($qrUrl ?? 'none'));

        return [
            'qr_string'  => $qrString,
            'qr_url'     => $qrUrl,
            'image_data' => $imageData,
            'order_id'   => $orderId,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    //  QRIS DANA (refresh server-side)
    // ═══════════════════════════════════════════════════════════════

    /**
     * AJAX: regenerate QRIS DANA (setelah timer expired).
     */
    public function refreshQris(int $id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $nominal   = (int) $transaksi->nominalDP();

        try {
            if ($this->isMidtransReady()) {
                $qrisData = $this->generateMidtransQris($transaksi, $nominal);
                if ($qrisData) {
                    return response()->json([
                        'success'    => true,
                        'image_data' => $qrisData['image_data'],
                        'qr_string'  => $qrisData['qr_string'],
                        'nominal'    => $nominal,
                        'skema'      => $transaksi->labelSkema(),
                    ]);
                }
            }

            $static  = config('qris.static_string', '');
            if (!empty($static) && $static !== 'TEMPEL_RAW_STRING_QRIS_DANA_BISNIS_ANDA_DI_SINI') {
                $payload   = QrisDynamicHelper::generate($static, $nominal);
                $imageData = QrisDynamicHelper::toBase64Png($payload, (int) config('qris.qr_size', 280));
            } else {
                $customImg = config('qris.image_path', 'qris_mayangsari.jpeg');
                if (file_exists(public_path($customImg))) {
                    $imageData = asset($customImg);
                } elseif (file_exists(public_path('qris_mayangsari.jpeg'))) {
                    $imageData = asset('qris_mayangsari.jpeg');
                } else {
                    $imageData = asset('qris mayangsari.jpeg');
                }
            }

            return response()->json([
                'success'    => true,
                'image_data' => $imageData,
                'nominal'    => $nominal,
                'skema'      => $transaksi->labelSkema(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  STATUS POLLING
    // ═══════════════════════════════════════════════════════════════

    /**
     * Polling status pembayaran (dipanggil JS setiap 5 detik).
     */
    public function checkStatus(int $id)
    {
        $transaksi = Transaksi::findOrFail($id);
        return response()->json([
            'payment_status' => $transaksi->payment_status,
            'status_label'   => $transaksi->paymentStatusLabel(),
            'is_paid'        => $transaksi->isPaid(),
            'nota_url'       => $transaksi->isPaid()
                                    ? route('transaksi.nota', $transaksi->id)
                                    : null,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  ADMIN KONFIRMASI MANUAL (QRIS DANA)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Admin konfirmasi pembayaran QRIS manual (DANA tidak ada webhook otomatis).
     */
    public function confirmQris(Request $request, int $id)
    {
        $transaksi = Transaksi::findOrFail($id);

        $transaksi->update([
            'payment_status' => 'paid',
            'sudah_bayar'    => true,
            'paid_at'        => now(),
            'jumlah_dibayar' => $transaksi->nominalDP(),
            'status'         => 'Menunggu Konfirmasi',
        ]);

        Log::info('[QRIS Manual] Admin konfirmasi transaksi #' . $id
                . ' oleh user #' . auth()->id());

        return response()->json([
            'success'  => true,
            'message'  => 'Pembayaran berhasil dikonfirmasi.',
            'nota_url' => route('transaksi.nota', $transaksi->id),
        ]);
    }

    /**
     * Uji coba simulasi pembayaran lunas 1-Click (Khusus mode Sandbox).
     */
    public function simulateSandboxPayment(int $id)
    {
        if (config('midtrans.is_production')) {
            return response()->json([
                'success' => false,
                'message' => 'Simulasi hanya tersedia dalam mode Sandbox.',
            ], 403);
        }

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->update([
            'payment_status' => 'paid',
            'sudah_bayar'    => true,
            'paid_at'        => now(),
            'jumlah_dibayar' => $transaksi->nominalDP(),
            'status'         => 'Menunggu Konfirmasi',
        ]);

        Log::info('[Sandbox Simulation] Transaksi #' . $id . ' berhasil disimulasikan lunas.');

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => '✅ Pembayaran berhasil disimulasikan! Mengalihkan ke Nota...',
                'nota_url' => route('transaksi.nota', $transaksi->id),
            ]);
        }

        return redirect()->route('transaksi.nota', $transaksi->id);
    }

    // ═══════════════════════════════════════════════════════════════
    //  WEBHOOK MIDTRANS — Notification Handler
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /webhook/midtrans
     *
     * Dipanggil otomatis oleh server Midtrans setiap kali status pembayaran berubah.
     * CSRF dikecualikan di routes/web.php.
     *
     * Cara setup di Midtrans Dashboard:
     *   Settings → Configuration → Payment Notification URL
     *   → Isi: https://DOMAIN_ANDA/webhook/midtrans
     *
     * Untuk pengujian lokal gunakan ngrok:
     *   ngrok http 8000
     *   → copy URL ngrok ke Midtrans Dashboard
     */
    public function webhook(Request $request)
    {
        try {
            $notification      = new Notification();
            $transactionStatus = $notification->transaction_status;
            $fraudStatus       = $notification->fraud_status;
            $orderId           = $notification->order_id;
            $grossAmount       = $notification->gross_amount;
            $paymentType       = $notification->payment_type ?? 'unknown';
            $signatureKey      = $notification->signature_key ?? '';

            // ── Verifikasi signature Midtrans ──────────────────────
            $expectedSignature = hash(
                'sha512',
                $orderId
                    . $notification->status_code
                    . $grossAmount
                    . config('midtrans.server_key')
            );
            if ($signatureKey && $signatureKey !== $expectedSignature) {
                Log::warning('[Midtrans Webhook] Invalid signature! order_id=' . $orderId);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            Log::info('[Midtrans Webhook] '
                . 'order_id=' . $orderId
                . ' status=' . $transactionStatus
                . ' fraud=' . $fraudStatus
                . ' type=' . $paymentType
                . ' amount=' . $grossAmount);

            // ── Cari transaksi berdasarkan transaction_id ──────────
            $transaksi = Transaksi::where('transaction_id', $orderId)->first();
            if (!$transaksi) {
                Log::warning('[Midtrans Webhook] Transaksi tidak ditemukan: ' . $orderId);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // ── Mapping status Midtrans → payment_status lokal ─────
            $paymentStatus = match (true) {
                ($transactionStatus === 'capture'    && $fraudStatus === 'accept') => 'paid',
                ($transactionStatus === 'settlement')                              => 'paid',
                ($transactionStatus === 'pending')                                 => 'pending',
                (in_array($transactionStatus, ['deny', 'cancel']))                 => 'failed',
                ($transactionStatus === 'expire')                                  => 'expired',
                ($transactionStatus === 'challenge')                               => 'challenge',
                default                                                            => 'pending',
            };

            // ── Update database ────────────────────────────────────
            $data = [
                'payment_status' => $paymentStatus,
                'jumlah_dibayar' => (float) $grossAmount,
            ];
            if ($paymentStatus === 'paid') {
                $data['sudah_bayar'] = true;
                $data['paid_at']     = now();
                $data['status']      = 'Menunggu Konfirmasi'; // Admin masih perlu konfirmasi pengambilan
            }
            $transaksi->update($data);

            Log::info('[Midtrans Webhook] Berhasil update transaksi #'
                . $transaksi->id . ' → ' . $paymentStatus);

            return response()->json(['message' => 'OK'], 200);

        } catch (\Throwable $e) {
            Log::error('[Midtrans Webhook] Error: ' . $e->getMessage()
                . ' | Trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  HELPER PRIVATE
    // ═══════════════════════════════════════════════════════════════

    private function isMidtransReady(): bool
    {
        $key = config('midtrans.server_key', '');
        return !empty($key)
            && !str_contains($key, 'GANTI')
            && !str_contains($key, 'xxxx');
    }
}
