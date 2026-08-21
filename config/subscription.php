<?php

/*
|--------------------------------------------------------------------------
| Aturan Bisnis Langganan MersifLab
|--------------------------------------------------------------------------
|
| Dibaca oleh App\Services\SubscriptionPlanService. Semua nilai punya default
| yang sama dengan perilaku sebelumnya, jadi file ini aman ditambahkan tanpa
| mengubah alur yang sudah berjalan.
|
| CATATAN PENTING soal min_active_days_before_cancel:
| Paket langganan berdurasi 1 bulan kalender (28-31 hari, tergantung bulan),
| sementara aturan pembatalan mensyaratkan masa aktif minimal 30 hari. Artinya
| jendela waktu untuk membatalkan sangat sempit - bahkan nol pada bulan
| Februari (28 hari < 30 hari). Nilai ini sengaja dibuat bisa diatur supaya
| tim bisnis dapat menyesuaikannya (misalnya ke 25 hari) tanpa mengubah kode.
|
*/

return [

    // Harga resmi tiap paket, dalam Rupiah.
    'prices' => [
        'standard' => (int) env('SUBSCRIPTION_PRICE_STANDARD', 50000),
        'premium' => (int) env('SUBSCRIPTION_PRICE_PREMIUM', 150000),
    ],

    // Minimal usia langganan (hari) sebelum boleh dibatalkan pengguna.
    'min_active_days_before_cancel' => (int) env('SUBSCRIPTION_MIN_DAYS_BEFORE_CANCEL', 30),

    // Pesan baku aturan pembatalan. Dipakai backend maupun frontend supaya
    // kalimatnya konsisten di semua tempat.
    'cancel_blocked_message' => 'Langganan tidak dapat dibatalkan sebelum masa aktif mencapai 1 bulan.',
];
