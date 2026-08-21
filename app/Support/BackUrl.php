<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Penentu tujuan tombol "Kembali" / "Cancel" yang aman.
 *
 * Masalah dengan url()->previous() polos:
 *  - Setelah validasi gagal, halaman form dirender ulang dan referer-nya
 *    menjadi form itu sendiri, sehingga tombol Cancel berputar ke halaman
 *    yang sama (pengguna terasa "tombolnya tidak berfungsi").
 *  - Referer bisa kosong (akses langsung, bookmark, reload) atau berasal
 *    dari situs lain, yang berarti tujuan tombol jadi tidak terkendali.
 *  - Kembali ke halaman form lain (create/edit) juga bukan yang diharapkan;
 *    pengguna ingin keluar dari form, bukan masuk ke form berikutnya.
 *
 * Kelas ini memakai halaman sebelumnya HANYA bila aman, dan jatuh ke rute
 * fallback yang ditentukan pemanggil pada kasus lainnya. Dengan begitu
 * tombol Kembali selalu punya tujuan yang benar dan tidak pernah rusak.
 */
class BackUrl
{
    /**
     * Path yang tidak boleh dijadikan tujuan "kembali".
     *
     * - create/edit : halaman form, pengguna justru ingin keluar dari form.
     * - login/register/logout/password : alur autentikasi.
     */
    /** Penanda internal saat tidak ada halaman sebelumnya sama sekali. */
    private const NO_PREVIOUS_SENTINEL = '__mersif_no_previous__';

    private const BLOCKED_SEGMENTS = [
        '/create',
        '/edit',
        '/login',
        '/register',
        '/logout',
        '/password',
        '/auth/',
    ];

    /**
     * URL tujuan tombol Kembali / Cancel.
     *
     * @param  string        $fallback  URL tujuan bila halaman sebelumnya tidak aman dipakai.
     * @param  array<string> $blocked   Segmen path tambahan yang ikut ditolak.
     */
    public static function to(string $fallback, array $blocked = []): string
    {
        $previous = static::safePrevious($blocked);

        return $previous ?? $fallback;
    }

    /**
     * Halaman sebelumnya bila aman dipakai, atau null.
     *
     * @param  array<string> $blocked
     */
    public static function safePrevious(array $blocked = []): ?string
    {
        try {
            // Sentinel dipakai supaya bisa dibedakan antara "benar-benar tidak
            // ada halaman sebelumnya" dan "halaman sebelumnya adalah beranda".
            // Tanpa ini, url()->previous() mengembalikan URL root pada kedua
            // kasus tersebut, sehingga tombol Kembali melompat ke beranda.
            $previous = url()->previous(self::NO_PREVIOUS_SENTINEL);
        } catch (\Throwable $e) {
            return null;
        }

        if (blank($previous) || Str::endsWith($previous, self::NO_PREVIOUS_SENTINEL)) {
            return null;
        }

        // Hanya terima URL dari aplikasi ini sendiri.
        if (!Str::startsWith($previous, rtrim(url('/'), '/'))) {
            return null;
        }

        $previousPath = static::pathOf($previous);
        $currentPath = static::pathOf(url()->current());

        // Jangan pernah kembali ke halaman yang sedang dibuka (looping).
        if ($previousPath === $currentPath) {
            return null;
        }

        foreach (array_merge(self::BLOCKED_SEGMENTS, $blocked) as $segment) {
            if (Str::contains($previousPath, $segment)) {
                return null;
            }
        }

        return $previous;
    }

    /**
     * Path sebuah URL tanpa query string, selalu diawali garis miring.
     */
    private static function pathOf(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        return '/' . trim($path, '/');
    }
}
