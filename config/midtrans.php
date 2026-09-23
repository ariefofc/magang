<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Snap API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Midtrans Snap Payment Gateway.
    | API Keys diambil dari: https://dashboard.sandbox.midtrans.com
    | Settings → Access Keys
    |
    */

    'merchant_id'   => env('MIDTRANS_MERCHANT_ID', 'M557779387'),

    'server_key'    => env('MIDTRANS_SERVER_KEY', ''),

    'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),

    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
     * Snap JS URL:
     *   Sandbox    : https://app.sandbox.midtrans.com/snap/snap.js
     *   Production : https://app.midtrans.com/snap/snap.js
     */
    'snap_url' => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js'),

    /*
     * Payment methods yang diaktifkan di popup Midtrans Snap.
     * Daftar lengkap: https://docs.midtrans.com/reference/snap-api
     */
    'enabled_payments' => [
        'qris',
        'gopay',
        'shopeepay',
        'dana',
        'ovo',
        'bank_transfer',
        'echannel',     // Mandiri Bill
        'permata_va',
        'bca_va',
        'bni_va',
        'bri_va',
        'cimb_va',
        'other_va',
    ],

    /*
     * Durasi kedaluwarsa Snap token (menit).
     */
    'expiry_minutes' => env('MIDTRANS_EXPIRY_MINUTES', 30),

    /*
     * Callback/finish URL setelah pembayaran selesai.
     * Midtrans akan redirect customer ke URL ini.
     */
    'finish_url'    => env('MIDTRANS_FINISH_URL', null),
    'unfinish_url'  => env('MIDTRANS_UNFINISH_URL', null),
    'error_url'     => env('MIDTRANS_ERROR_URL', null),
];
