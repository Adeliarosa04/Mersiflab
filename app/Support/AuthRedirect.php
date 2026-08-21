<?php

namespace App\Support;

use App\Models\User;

/**
 * Menentukan tujuan redirect setelah login / saat halaman auth diakses oleh
 * user yang sudah login.
 *
 * Dikumpulkan di satu tempat supaya AuthController, AdminAuthController, dan
 * route /dashboard tidak punya aturan sendiri-sendiri yang bisa saling
 * bertentangan (sebelumnya admin yang login lewat /login mendarat di home dan
 * /dashboard juga melemparnya kembali ke home, sehingga admin tidak punya
 * jalan menuju panelnya).
 */
class AuthRedirect
{
    /**
     * Halaman tujuan setelah login, dan tujuan redirect bila halaman auth
     * (login/register) dibuka oleh user yang sudah login.
     *
     * Hanya admin yang dialihkan ke area khusus: panel admin terpisah dari
     * situs publik dan tidak punya link masuk dari navbar, sehingga tanpa ini
     * admin tidak punya jalan menuju panelnya. Teacher dan student tetap
     * mendarat di beranda seperti perilaku semula.
     */
    public static function homeFor(?User $user): string
    {
        if ($user && $user->isAdmin()) {
            return route('admin.dashboard');
        }

        return route('home');
    }
}
