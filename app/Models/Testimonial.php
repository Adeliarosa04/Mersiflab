<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class Testimonial extends Model
{
    use HasFactory;

    /** Menunggu peninjauan admin. Status bawaan saat siswa mengirim. */
    public const STATUS_PENDING = 'pending';

    /** Disetujui admin dan tampil di halaman publik. */
    public const STATUS_APPROVED = 'approved';

    /** Ditolak admin, tidak tampil di halaman publik. */
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'course_id',
        'name',
        'position',
        'content',
        'rating',
        'avatar',
        'is_published',
        'status',
        'admin_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'rating' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Daftar status yang valid.
     *
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];
    }

    // ==================== RELASI ====================

    /**
     * Siswa penulis testimoni.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Kursus yang diulas (opsional).
     */
    public function course()
    {
        return $this->belongsTo(ClassModel::class, 'course_id');
    }

    /**
     * Admin moderator yang menyetujui/menolak.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Admin pembuat testimoni (data lama, sebelum refaktorisasi).
     * Dipertahankan agar testimoni lama tetap tampil dengan benar.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ==================== SCOPE ====================

    /**
     * Hanya testimoni yang boleh tampil di halaman publik.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Filter berdasarkan status, mengabaikan nilai yang tidak dikenal.
     */
    public function scopeWithStatus($query, ?string $status)
    {
        if ($status === null || !in_array($status, self::statuses(), true)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    // ==================== MODERASI ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Setujui & publikasikan testimoni.
     *
     * is_published ikut disinkronkan supaya kode lama yang masih membaca
     * kolom tersebut tetap memberi hasil yang benar.
     */
    public function approve(?int $adminId = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'is_published' => true,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Tolak testimoni beserta alasannya (opsional).
     */
    public function reject(?int $adminId = null, ?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'is_published' => false,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Kembalikan ke antrean moderasi.
     */
    public function markPending(?int $adminId = null): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'is_published' => false,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Label status yang siap ditampilkan.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Pending',
        };
    }

    // ==================== TAMPILAN ====================

    public function avatarUrl()
    {
        // Prioritas: foto profil siswa penulis (alur baru), lalu admin
        // pembuat (data lama), lalu file avatar yang diunggah manual.
        if ($this->user && !empty($this->user->avatar)) {
            return Storage::disk('public')->url($this->user->avatar);
        }

        if ($this->admin && !empty($this->admin->avatar)) {
            return Storage::disk('public')->url($this->admin->avatar);
        }

        if ($this->avatar) {
            return Storage::disk('public')->url($this->avatar);
        }

        $name = urlencode($this->name ?? 'User');
        return "https://ui-avatars.com/api/?name={$name}&background=667eea&color=fff";
    }
}
