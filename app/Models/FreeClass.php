<?php

namespace App\Models;

use App\Models\Concerns\HasClassMedia;
use Illuminate\Database\Eloquent\Model;

/**
 * Kelas gratis yang dikelola admin dan tampil di halaman Courses.
 *
 * Materi disimpan pada relasi one-to-many {@see FreeClassLevel}: satu Free
 * Class bisa punya banyak level, masing-masing dengan video, modul PDF, dan
 * slide PPT sendiri. Kelas dengan satu level tetap didukung penuh.
 *
 * Kolom media pada tabel ini (video_url, video_path, pdf_path, pdf_name)
 * adalah peninggalan struktur lama sebelum sistem berjenjang. Kolom tersebut
 * tidak lagi dipakai untuk menampilkan materi — datanya sudah dipindahkan ke
 * level pertama oleh migrasi — tetapi sengaja dipertahankan agar data lama
 * tidak hilang. Trait HasClassMedia tetap dipasang supaya accessor lama
 * (embed_url, video_file_url, dsb.) tidak mendadak hilang bila masih dipakai.
 */
class FreeClass extends Model
{
    use HasClassMedia;

    protected $fillable = [
        'title',
        'description',
        'thumbnail_path',
        'video_url',
        'video_path',
        'pdf_path',
        'pdf_name',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Level materi, sudah terurut.
     */
    public function levels()
    {
        return $this->hasMany(FreeClassLevel::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Admin yang membuat entri ini.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: hanya kelas gratis yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: urutan tampil — sort_order dulu, lalu yang terbaru.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    /**
     * Apakah symlink public/storage sudah dibuat.
     * Dipakai panel admin untuk memperingatkan admin sebelum berkas 404.
     */
    public static function storageLinkExists(): bool
    {
        return file_exists(public_path('storage'));
    }

    /* ---------------------------------------------------------------------
     | Level
     * ------------------------------------------------------------------- */

    /**
     * Level pertama — dipakai sebagai materi default saat halaman detail
     * dibuka, dan sebagai sumber cover video untuk thumbnail.
     */
    public function getFirstLevelAttribute(): ?FreeClassLevel
    {
        return $this->levels->first();
    }

    public function getLevelCountAttribute(): int
    {
        return $this->levels->count();
    }

    /* ---------------------------------------------------------------------
     | Thumbnail
     * ------------------------------------------------------------------- */

    /**
     * Thumbnail kartu: berkas unggahan admin, lalu cover video YouTube dari
     * level pertama, lalu null (view menampilkan placeholder).
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (filled($this->thumbnail_path)) {
            return $this->publicFileUrl($this->thumbnail_path);
        }

        $youtubeId = $this->first_level?->youtube_id ?? $this->youtube_id;

        if ($youtubeId) {
            return 'https://i.ytimg.com/vi/' . $youtubeId . '/hqdefault.jpg';
        }

        return null;
    }
}
