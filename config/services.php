<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'pix2pix_ai' => [
        'key' => env('PIX2PIX_AI_API_KEY'),
        'model' => env('PIX2PIX_AI_MODEL', 'pix2pix-gan-v2'),
        'image_model' => env('PIX2PIX_AI_IMAGE_MODEL', 'pix2pix-gan-image-recolor'),
        'endpoint' => env('PIX2PIX_AI_ENDPOINT', 'http://127.0.0.1:5001/recolor'),
    ],

    'google_ai' => [
        'key' => env('GOOGLE_AI_API_KEY'),
        'model' => env('GOOGLE_AI_MODEL', 'pix2pix-gan-v2'),
        'image_model' => env('GOOGLE_AI_IMAGE_MODEL', 'pix2pix-gan-image-recolor'),
        'endpoint' => env('GOOGLE_AI_ENDPOINT', 'http://127.0.0.1:5001/recolor'),
    ],

];