<?php

namespace App\Models;

use App\Models\Concerns\HasClassMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Satu level materi di dalam sebuah Free Class.
 *
 * Setiap level punya video, modul PDF, dan slide PPT sendiri.
 */
class FreeClassLevel extends Model
{
    use HasClassMedia;

    protected $fillable = [
        'free_class_id',
        'name',
        'video_url',
        'video_path',
        'pdf_path',
        'pdf_name',
        'ppt_path',
        'ppt_name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function freeClass()
    {
        return $this->belongsTo(FreeClass::class);
    }

    /**
     * Scope: urutan tampil level.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /* ---------------------------------------------------------------------
     | Slide PPT
     * ------------------------------------------------------------------- */

    public function hasPpt(): bool
    {
        return filled($this->ppt_path);
    }

    public function getPptUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->ppt_path);
    }

    public function getPptDisplayNameAttribute(): string
    {
        return $this->ppt_name ?: Str::afterLast((string) $this->ppt_path, '/');
    }

    /**
     * Apakah level ini punya materi unduhan sama sekali.
     */
    public function hasDownloads(): bool
    {
        return $this->hasPdf() || $this->hasPpt();
    }

    /**
     * Semua berkas milik level ini — dipakai saat menghapus agar tidak
     * meninggalkan sampah di storage.
     */
    public function filePaths(): array
    {
        return array_filter([$this->video_path, $this->pdf_path, $this->ppt_path]);
    }
}
