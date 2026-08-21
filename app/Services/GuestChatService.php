<?php

namespace App\Services;

use App\Models\AiChat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Mengelola identitas & kuota chat pengunjung yang belum login, sekaligus
 * memigrasikan riwayat chat tamu ke akun terdaftar saat login/register.
 *
 * Kenapa pakai cookie token, bukan session id saja?
 * Laravel me-regenerate session id setiap kali user berhasil login
 * (session()->regenerate()), sehingga riwayat tamu yang hanya ditandai
 * session_id akan "hilang". Cookie token bertahan melewati proses itu,
 * jadi riwayat tamu tetap bisa ditemukan dan dipindahkan ke user_id.
 */
class GuestChatService
{
    /** Nama atribut request tempat token disimpan selama satu siklus request. */
    private const REQUEST_ATTRIBUTE = 'mersy_guest_token';

    /**
     * Ambil token tamu dari cookie. Bila belum ada, buat token baru dan
     * antrikan cookie-nya supaya terkirim bersama response.
     */
    public function resolveToken(Request $request): string
    {
        // Sudah pernah di-resolve pada request ini.
        if ($request->attributes->has(self::REQUEST_ATTRIBUTE)) {
            return (string) $request->attributes->get(self::REQUEST_ATTRIBUTE);
        }

        $token = $this->readToken($request);

        if ($token === null) {
            $token = (string) Str::uuid();
            $this->queueCookie($token);
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $token);

        return $token;
    }

    /**
     * Baca token tamu yang sudah ada tanpa membuat yang baru.
     */
    public function readToken(Request $request): ?string
    {
        $name = $this->cookieName();

        $token = $request->cookie($name);

        // Cookie yang baru di-queue pada request yang sama belum terbaca dari
        // $request, jadi cek juga antrean cookie.
        if (!is_string($token) || $token === '') {
            $queued = Cookie::queued($name);
            $token = $queued ? $queued->getValue() : null;
        }

        if (!is_string($token) || trim($token) === '') {
            return null;
        }

        // Batasi panjang agar tidak dipakai untuk menyuntik nilai aneh ke DB.
        return mb_substr(trim($token), 0, 64);
    }

    /**
     * Jumlah pesan yang sudah dipakai tamu ini (berdasarkan token cookie
     * ATAU session id, mana saja yang cocok).
     */
    public function usedQuota(?string $token, ?string $sessionId = null): int
    {
        try {
            return AiChat::query()
                ->whereNull('user_id')
                ->where(function ($query) use ($token, $sessionId) {
                    $matched = false;

                    if (filled($token)) {
                        $query->orWhere('guest_token', $token);
                        $matched = true;
                    }

                    if (filled($sessionId)) {
                        $query->orWhere('session_id', $sessionId);
                        $matched = true;
                    }

                    if (!$matched) {
                        // Tanpa identitas apa pun, jangan hitung apa-apa.
                        $query->whereRaw('1 = 0');
                    }
                })
                ->count();
        } catch (\Throwable $e) {
            Log::error('GuestChat: gagal menghitung kuota tamu', ['error' => $e->getMessage()]);

            // Gagal baca DB tidak boleh memblokir user; anggap belum terpakai.
            return 0;
        }
    }

    /**
     * Batas pesan gratis untuk tamu.
     */
    public function quotaLimit(): int
    {
        return max(0, (int) config('llm.quota.guest', 3));
    }

    /**
     * Sisa pesan gratis tamu.
     */
    public function remainingQuota(?string $token, ?string $sessionId = null): int
    {
        return max(0, $this->quotaLimit() - $this->usedQuota($token, $sessionId));
    }

    /**
     * Pindahkan riwayat chat tamu ke akun user yang baru login/register.
     *
     * Dipanggil dari listener event Login/Registered, jadi tidak ada controller
     * autentikasi yang perlu diubah. Selalu aman: kegagalan hanya dicatat di
     * log dan tidak pernah menggagalkan proses login.
     *
     * @return int Jumlah baris riwayat yang berhasil dipindahkan.
     */
    public function migrateToUser(User $user, ?string $token, ?string $sessionId = null): int
    {
        if (blank($token) && blank($sessionId)) {
            return 0;
        }

        try {
            $migrated = AiChat::query()
                ->whereNull('user_id')
                ->where(function ($query) use ($token, $sessionId) {
                    if (filled($token)) {
                        $query->orWhere('guest_token', $token);
                    }

                    if (filled($sessionId)) {
                        $query->orWhere('session_id', $sessionId);
                    }
                })
                ->update([
                    'user_id' => $user->id,
                    // guest_token sengaja dipertahankan sebagai jejak asal-usul,
                    // session_id dikosongkan karena sudah tidak relevan.
                    'session_id' => null,
                    'migrated_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($migrated > 0) {
                Log::info('GuestChat: riwayat chat tamu dipindahkan ke akun', [
                    'user_id' => $user->id,
                    'rows' => $migrated,
                ]);
            }

            return $migrated;
        } catch (\Throwable $e) {
            Log::error('GuestChat: gagal memindahkan riwayat chat tamu', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Antrikan cookie token tamu (httpOnly, umur panjang).
     */
    private function queueCookie(string $token): void
    {
        try {
            Cookie::queue(Cookie::make(
                name: $this->cookieName(),
                value: $token,
                minutes: (int) config('llm.guest_cookie.lifetime', 43200),
                path: '/',
                domain: null,
                secure: null,
                httpOnly: true,
                raw: false,
                sameSite: 'lax',
            ));
        } catch (\Throwable $e) {
            Log::warning('GuestChat: gagal mengantrikan cookie token tamu', ['error' => $e->getMessage()]);
        }
    }

    private function cookieName(): string
    {
        return (string) config('llm.guest_cookie.name', 'mersy_guest_token');
    }

    /**
     * Session id saat ini, atau null bila session belum aktif.
     */
    public function currentSessionId(): ?string
    {
        try {
            return Session::isStarted() ? Session::getId() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
