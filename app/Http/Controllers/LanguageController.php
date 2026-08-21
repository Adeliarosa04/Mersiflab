<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

/**
 * Pemilih bahasa (language switcher).
 *
 * Menyimpan pilihan bahasa pengguna ke session, lalu mengembalikannya ke
 * halaman asal supaya konteks yang sedang dibuka tidak hilang.
 */
class LanguageController extends Controller
{
    /**
     * Ubah bahasa aktif, lalu kembali ke halaman sebelumnya.
     */
    public function switch(Request $request, string $locale)
    {
        // Hanya bahasa yang didukung yang boleh disimpan. Nilai lain diabaikan
        // diam-diam agar URL yang diutak-atik tidak bisa merusak apa pun.
        if (in_array($locale, SetLocale::SUPPORTED, true)) {
            $request->session()->put('locale', $locale);
        }

        // Kembali ke halaman asal. back_url() menolak referer dari luar situs
        // dan halaman form, dengan fallback ke beranda.
        return redirect()->to(back_url(route('home')));
    }
}
