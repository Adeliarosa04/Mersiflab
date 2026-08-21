<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi LLM (AI Assistant "Mersy")
|--------------------------------------------------------------------------
|
| Semua kredensial dibaca lewat env() DI FILE CONFIG INI SAJA, supaya
| `php artisan config:cache` tetap aman (env() tidak boleh dipanggil dari
| controller/service). Di runtime, pakai config('llm.*').
|
| GEMINI_API_KEY dipertahankan sebagai fallback agar instalasi lama yang
| sudah mengisi variabel tersebut tidak ikut rusak.
|
*/

return [

    // Provider aktif. Saat ini hanya 'gemini' yang diimplementasikan.
    'provider' => env('LLM_PROVIDER', 'gemini'),

    // API key. Urutan: LLM_API_KEY -> GEMINI_API_KEY (legacy).
    'api_key' => env('LLM_API_KEY') ?: env('GEMINI_API_KEY'),

    // Nama model. Contoh: gemini-2.5-flash, gemini-2.5-pro.
    'model' => env('LLM_MODEL', 'gemini-2.5-flash'),

    // Base URL endpoint. Path model + ":generateContent" ditambahkan otomatis
    // oleh LlmService, jadi cukup isi base URL-nya saja.
    'endpoint' => rtrim(env('LLM_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta'), '/'),

    // Timeout HTTP (detik) dan jumlah percobaan ulang saat error sementara.
    'timeout' => (int) env('LLM_TIMEOUT', 45),
    'retries' => (int) env('LLM_RETRIES', 2),

    'generation' => [
        'temperature' => (float) env('LLM_TEMPERATURE', 0.6),
        'max_output_tokens' => (int) env('LLM_MAX_OUTPUT_TOKENS', 2048),
        'top_p' => (float) env('LLM_TOP_P', 0.95),
        'top_k' => (int) env('LLM_TOP_K', 40),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kuota
    |--------------------------------------------------------------------------
    | guest        : jumlah pesan gratis untuk pengunjung yang belum login.
    | free_user    : user login tanpa subscription & tanpa kursus.
    | student      : user login tanpa subscription tapi punya kursus.
    | subscriber   : null = tanpa batas.
    */
    'quota' => [
        'guest' => (int) env('LLM_GUEST_QUOTA', 3),
        'free_user' => (int) env('LLM_FREE_USER_QUOTA', 5),
        'student' => (int) env('LLM_STUDENT_QUOTA', 15),
    ],

    // Umur cookie token tamu (menit). Dipakai untuk menghitung kuota tamu dan
    // memigrasikan riwayat chat tamu ke akun saat user login/register.
    'guest_cookie' => [
        'name' => 'mersy_guest_token',
        'lifetime' => (int) env('LLM_GUEST_COOKIE_LIFETIME', 60 * 24 * 30), // 30 hari
    ],

    // Pesan fallback yang ramah pengguna. Tidak pernah menampilkan error mentah.
    'fallback_messages' => [
        'not_configured' => 'Maaf, layanan Mersy AI sedang belum aktif di server ini. Tim MersifLab sudah diberi tahu, silakan coba beberapa saat lagi ya.',
        'unavailable' => 'Maaf, saya sedang tidak bisa terhubung ke layanan AI saat ini. Silakan coba beberapa saat lagi ya.',
        'timeout' => 'Maaf, jawaban saya memakan waktu terlalu lama. Coba kirim ulang pertanyaannya dengan lebih ringkas ya.',
        'empty' => 'Maaf, saya belum bisa menyusun jawaban untuk pertanyaan itu. Coba tanyakan dengan kalimat lain ya.',
    ],
];
