<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menetapkan bahasa aktif aplikasi pada setiap request web.
 *
 * Bahasa disimpan di session (key 'locale') dan diubah lewat
 * App\Http\Controllers\LanguageController. Bila session belum terbentuk -
 * misalnya kunjungan pertama - dipakai Bahasa Indonesia sebagai default,
 * sesuai ketentuan MersifLab.
 *
 * Middleware ini sengaja dibuat sangat ringan dan tidak pernah melempar
 * exception: kegagalan menentukan bahasa tidak boleh sampai menggagalkan
 * request, apalagi mengganggu alur autentikasi.
 */
class SetLocale
{
    /** Bahasa yang didukung aplikasi. */
    public const SUPPORTED = ['id', 'en'];

    /** Bahasa bawaan bila pengguna belum pernah memilih. */
    public const DEFAULT = 'id';

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $locale = $request->session()->get('locale', self::DEFAULT);

            // Nilai di luar daftar yang didukung diabaikan, jangan sampai
            // session yang rusak membuat App::setLocale() memuat berkas aneh.
            if (!is_string($locale) || !in_array($locale, self::SUPPORTED, true)) {
                $locale = self::DEFAULT;
            }

            App::setLocale($locale);
        } catch (\Throwable $e) {
            // Session belum tersedia (mis. request stateless) - pakai default.
            App::setLocale(self::DEFAULT);
        }

        return $next($request);
    }
}
