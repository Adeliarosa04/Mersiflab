<?php

namespace App\Http\Controllers;

use App\Models\Setting;

/**
 * Halaman legal (Terms & Conditions dan Privacy Policy).
 *
 * Isi dokumen diambil dari tabel `settings` bila tersedia
 * (key: `terms_content` / `privacy_content`), sehingga tim legal dapat
 * mengisi teks resmi tanpa mengubah kode. Selama key tersebut belum diisi,
 * halaman tetap dapat dibuka dan menampilkan kerangka dokumen — bukan 404.
 */
class LegalController extends Controller
{
    /**
     * Kerangka bagian Terms & Conditions.
     * Judul bagian saja; isi resmi diisi lewat Setting `terms_content`.
     */
    private const TERMS_SECTIONS = [
        'Ketentuan Umum',
        'Akun Pengguna',
        'Penggunaan Layanan',
        'Kelas, Materi, dan Hak Kekayaan Intelektual',
        'Pembelian dan Langganan',
        'Pembatalan dan Pengembalian Dana',
        'Kewajiban dan Larangan Pengguna',
        'Penangguhan dan Penghentian Akun',
        'Batasan Tanggung Jawab',
        'Perubahan Ketentuan',
        'Hukum yang Berlaku',
    ];

    /**
     * Kerangka bagian Privacy Policy.
     */
    private const PRIVACY_SECTIONS = [
        'Pendahuluan',
        'Data yang Kami Kumpulkan',
        'Cara Kami Menggunakan Data',
        'Dasar Pemrosesan Data',
        'Cookie dan Teknologi Serupa',
        'Berbagi Data dengan Pihak Ketiga',
        'Penyimpanan dan Keamanan Data',
        'Hak Pengguna atas Data Pribadi',
        'Data Anak di Bawah Umur',
        'Perubahan Kebijakan',
        'Kontak',
    ];

    /**
     * Terms & Conditions — route: /syarat-ketentuan
     */
    public function terms()
    {
        return view('legal.terms', [
            'content' => $this->settingContent('terms_content'),
            'sections' => self::TERMS_SECTIONS,
            'updatedAt' => $this->settingUpdatedAt('terms_content'),
        ]);
    }

    /**
     * Privacy Policy — route: /privacy-policy
     */
    public function privacy()
    {
        return view('legal.privacy', [
            'content' => $this->settingContent('privacy_content'),
            'sections' => self::PRIVACY_SECTIONS,
            'updatedAt' => $this->settingUpdatedAt('privacy_content'),
        ]);
    }

    /**
     * Ambil isi dokumen dari settings. Tabel `settings` bisa saja belum ada
     * pada instalasi tertentu, jadi kegagalan query tidak boleh membuat
     * halaman publik ini error.
     */
    private function settingContent(string $key): ?string
    {
        try {
            $value = Setting::get($key);

            return filled($value) ? $value : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function settingUpdatedAt(string $key)
    {
        try {
            return optional(Setting::where('key', $key)->first())->updated_at;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
