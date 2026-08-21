<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\GuestChatService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Memindahkan riwayat chat tamu ke akun begitu pengguna berhasil
 * login atau menyelesaikan registrasi.
 *
 * Dipasang sebagai listener event bawaan Laravel (bukan mengubah
 * AuthController/GoogleAuthController) supaya alur autentikasi yang sudah
 * berjalan tidak tersentuh sama sekali. Listener ini juga dipanggil pada saat
 * yang tepat: event Login dikirim SEBELUM session()->regenerate(), sehingga
 * session id tamu masih bisa dipakai sebagai penanda cadangan selain cookie.
 */
class MigrateGuestChatHistory
{
    public function __construct(
        private GuestChatService $guestChats,
        private Request $request,
    ) {
    }

    /**
     * @param  Login|Registered  $event
     */
    public function handle(object $event): void
    {
        try {
            $user = $event->user ?? null;

            if (!$user instanceof User) {
                return;
            }

            $token = $this->guestChats->readToken($this->request);
            $sessionId = $this->guestChats->currentSessionId();

            $this->guestChats->migrateToUser($user, $token, $sessionId);
        } catch (\Throwable $e) {
            // Migrasi riwayat chat bersifat pelengkap - kegagalannya tidak boleh
            // membatalkan atau mengganggu proses login/registrasi pengguna.
            Log::error('MigrateGuestChatHistory: gagal memproses event autentikasi', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
