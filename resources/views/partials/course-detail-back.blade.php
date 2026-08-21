{{--
    Tombol "Kembali ke Courses" untuk halaman Detail Course.

    Dua lapis, sengaja:

    1. href server-side  - hasil perhitungan di bawah. Ini yang dipakai bila
       JavaScript mati, bila tautan dibuka di tab baru, dan bila riwayat
       browser kosong. Selalu menunjuk katalog yang benar.

    2. history.back()    - dipakai saat pengguna memang datang dari katalog.
       Hanya riwayat browser yang bisa memulihkan posisi gulir, nomor halaman,
       dan filter katalog persis seperti yang ditinggalkan; href biasa akan
       memuat ulang katalog dari kondisi awal.

    Lapis kedua hanya diaktifkan bila halaman sebelumnya memenuhi SEMUA syarat:
      a. berasal dari situs ini sendiri (bukan tautan luar),
      b. bukan halaman ini sendiri (refresh mengirim Referer berisi URL yang
         sama - tanpa penjagaan ini tombol akan berputar di tempat),
      c. benar-benar halaman katalog (/courses atau /search).

    Bila salah satu tidak terpenuhi, tombol turun ke lapis pertama dengan
    tujuan route('courses'). Jadi tombol tidak pernah buntu.

    Membutuhkan variabel $course dari view pemanggil.
--}}
@php
    use Illuminate\Support\Str;

    $backFallback = route('courses');
    $backPrevious = url()->previous();

    $backPreviousPath = '/' . trim((string) (parse_url($backPrevious, PHP_URL_PATH) ?? ''), '/');
    $backCurrentPath = '/' . trim((string) (parse_url(url()->current(), PHP_URL_PATH) ?? ''), '/');

    // (a) Harus satu origin dengan aplikasi ini.
    $backSameOrigin = filled($backPrevious)
        && Str::startsWith($backPrevious, rtrim(url('/'), '/'));

    // (b) Tidak boleh menunjuk halaman ini sendiri.
    $backIsSelf = $backPreviousPath === $backCurrentPath;

    // (c) Hanya katalog Courses / hasil pencarian yang dianggap "halaman asal".
    //     Detail course lain (/course/{id}) sengaja TIDAK termasuk, supaya
    //     tombol tidak melompat antar detail course.
    $backIsCatalog = (bool) preg_match('#^/(courses|search)(/|$)#', $backPreviousPath);

    $backFromCatalog = $backSameOrigin && ! $backIsSelf && $backIsCatalog;

    // href tetap membawa query katalog (filter/kategori/halaman) supaya jalur
    // tanpa JavaScript pun sedekat mungkin dengan kondisi yang ditinggalkan.
    $backUrl = $backFromCatalog ? $backPrevious : $backFallback;
@endphp

{{-- Hanya SATU kontrol keluar dipakai: tombol "Kembali" berlabel, karena
     labelnya langsung memberi tahu tujuan. Tombol silang (X) sengaja tidak
     ditampilkan agar tidak ada dua tombol dengan fungsi yang sama.

     Tetap <a href>, bukan <button>: pengguna masih bisa membukanya di tab
     baru, menyalin tautannya, dan menjangkaunya lewat keyboard. --}}
<div class="course-detail-topbar">
    <a href="{{ $backUrl }}"
       class="btn-course-back"
       @if ($backFromCatalog) data-history-back="1" @endif>
        <i class="fas fa-arrow-left"></i>
        <span>{{ __('Kembali ke Courses') }}</span>
    </a>
</div>

@once
<script>
/**
 * Peningkatan progresif untuk tombol "Kembali ke Courses".
 *
 * Menukar navigasi href dengan history.back() supaya katalog kembali persis
 * seperti yang ditinggalkan: posisi gulir, filter, dan nomor halaman utuh.
 * Tanpa ini, href biasa memuat katalog dari kondisi awal.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var link = event.target.closest
            ? event.target.closest('a.btn-course-back[data-history-back="1"]')
            : null;

        if (!link || event.defaultPrevented) {
            return;
        }

        // Klik tengah / Ctrl / Cmd / Shift = pengguna ingin tab atau jendela
        // baru. Di sana riwayat kosong, jadi biarkan href yang bekerja.
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        // Tidak ada riwayat untuk dituju (tab baru, bookmark, tautan langsung):
        // href tetap mengantar pengguna ke katalog.
        if (window.history.length <= 1) {
            return;
        }

        event.preventDefault();
        window.history.back();
    });
})();
</script>
@endonce
