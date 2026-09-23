<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Webhook Midtrans dipindahkan ke routes/web.php agar URL-nya lebih bersih:
|   POST /webhook/midtrans  (bukan /api/webhook/midtrans)
| CSRF dikecualikan di web.php via ->withoutMiddleware(VerifyCsrfToken::class)
*/

// Kosong — tidak ada route API untuk saat ini
