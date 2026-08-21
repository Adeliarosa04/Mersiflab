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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),

        // Redirect URI dibaca dari environment. Jika GOOGLE_REDIRECT_URI tidak
        // di-set, fallback diturunkan dari APP_URL supaya tidak pernah kosong
        // (redirect_uri kosong = "Error 400: invalid_request" dari Google).
        // Nilai ini HARUS sama persis dengan Authorized redirect URI yang
        // didaftarkan di Google Cloud Console (protocol, domain, port, path).
        'redirect' => env('GOOGLE_REDIRECT_URI')
            ?: rtrim(env('APP_URL', 'http://localhost'), '/') . '/auth/google/callback',
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],
];
