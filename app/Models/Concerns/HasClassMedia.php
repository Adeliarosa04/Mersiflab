<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Helper media bersama untuk FreeClass dan FreeClassLevel.
 *
 * Dikumpulkan di satu trait agar logika URL berkas, deteksi YouTube/Vimeo,
 * dan penamaan berkas tidak ditulis dua kali di dua model.
 */
trait HasClassMedia
{
    /**
     * URL publik untuk berkas di disk `public`.
     *
     * Sengaja memakai asset() dan BUKAN Storage::disk('public')->url().
     * Disk `public` dikonfigurasi dengan 'url' => env('APP_URL').'/storage',
     * sehingga Storage::url() selalu menempel pada APP_URL. Ketika situs
     * diakses dari host/port lain, URL yang dihasilkan menunjuk ke host yang
     * salah dan berujung 404. asset() memakai root request yang sedang
     * berjalan, jadi berkas selalu diambil dari host yang sama.
     *
     * Catatan: berkas tetap butuh symlink `public/storage`
     * (`php artisan storage:link`, sekali per environment).
     */
    protected function publicFileUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    /* ---------------------------------------------------------------------
     | Video
     * ------------------------------------------------------------------- */

    public function hasVideo(): bool
    {
        return filled($this->video_path) || filled($this->video_url);
    }

    /**
     * URL berkas video yang diunggah admin (null bila memakai tautan).
     */
    public function getUploadedVideoUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->video_path);
    }

    /**
     * Video ditampilkan lewat <iframe> (YouTube/Vimeo) atau <video>
     * (berkas unggahan / URL langsung ke mp4).
     */
    public function getIsEmbeddableAttribute(): bool
    {
        return $this->embed_url !== null;
    }

    /**
     * Ubah tautan YouTube/Vimeo menjadi URL embed. Mengembalikan null untuk
     * tautan yang bukan platform tersebut, sehingga ditangani sebagai <video>.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        $url = $this->video_url;

        if (blank($url)) {
            return null;
        }

        // youtu.be/<id>
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // youtube.com/watch?v=<id>, /embed/<id>, /shorts/<id>, /live/<id>
        if (preg_match('~youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // vimeo.com/<id>
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        return null;
    }

    /**
     * Sumber untuk tag <video>: berkas unggahan, atau URL langsung
     * (mis. .mp4) yang bukan YouTube/Vimeo.
     */
    public function getVideoFileUrlAttribute(): ?string
    {
        if (filled($this->video_path)) {
            return $this->uploaded_video_url;
        }

        return $this->embed_url === null ? $this->video_url : null;
    }

    /**
     * ID video YouTube dari video_url (null bila bukan YouTube).
     */
    public function getYoutubeIdAttribute(): ?string
    {
        $embed = $this->embed_url;

        if ($embed && str_starts_with($embed, 'https://www.youtube.com/embed/')) {
            return Str::afterLast($embed, '/');
        }

        return null;
    }

    /* ---------------------------------------------------------------------
     | Modul PDF
     * ------------------------------------------------------------------- */

    public function hasPdf(): bool
    {
        return filled($this->pdf_path);
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->pdf_path);
    }

    public function getPdfDisplayNameAttribute(): string
    {
        return $this->pdf_name ?: Str::afterLast((string) $this->pdf_path, '/');
    }
}
