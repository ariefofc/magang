<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QRIS Statis Mayangsari / DANA Bisnis
    |--------------------------------------------------------------------------
    | Raw string QRIS atau gambar file QRIS (seperti public/qris_mayangsari.jpeg)
    */
    'static_string' => env('QRIS_STATIC_STRING', ''),

    /*
    | Nama merchant yang akan tampil di QR code / modal
    */
    'merchant_name'  => env('QRIS_MERCHANT_NAME', 'Mayangsari Kebaya'),

    /*
    | Path gambar QRIS default di folder public
    */
    'image_path'     => env('QRIS_IMAGE_PATH', 'qris_mayangsari.jpeg'),

    /*
    | Ukuran QR code (px) yang dirender di browser
    */
    'qr_size'        => env('QRIS_QR_SIZE', 280),
];

