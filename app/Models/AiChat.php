<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChat extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        // Token cookie tamu. Bertahan melewati session regenerate saat login,
        // dipakai untuk menghitung kuota tamu dan memigrasikan riwayat chat
        // ke akun pengguna terdaftar.
        'guest_token',
        'question',
        'answer',
        'migrated_at',
    ];

    protected $casts = [
        'migrated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Riwayat milik seorang tamu (belum login), dicari lewat token cookie
     * dan/atau session id.
     */
    public function scopeForGuest($query, ?string $token, ?string $sessionId = null)
    {
        return $query->whereNull('user_id')
            ->where(function ($q) use ($token, $sessionId) {
                $matched = false;

                if (filled($token)) {
                    $q->orWhere('guest_token', $token);
                    $matched = true;
                }

                if (filled($sessionId)) {
                    $q->orWhere('session_id', $sessionId);
                    $matched = true;
                }

                if (!$matched) {
                    $q->whereRaw('1 = 0');
                }
            });
    }
}
