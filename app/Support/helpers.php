<?php

use App\Support\BackUrl;

if (!function_exists('back_url')) {
    /**
     * URL tujuan tombol "Kembali" / "Cancel".
     *
     * Mengembalikan halaman sebelumnya bila aman dipakai, atau $fallback bila
     * tidak. Lihat App\Support\BackUrl untuk aturan lengkapnya.
     *
     * Contoh pemakaian di Blade:
     *   <a href="{{ back_url(route('admin.students.index')) }}">Kembali</a>
     *
     * @param  string        $fallback  Rute aman bila halaman sebelumnya tidak diketahui.
     * @param  array<string> $blocked   Segmen path tambahan yang ikut ditolak.
     */
    function back_url(string $fallback, array $blocked = []): string
    {
        return BackUrl::to($fallback, $blocked);
    }
}
