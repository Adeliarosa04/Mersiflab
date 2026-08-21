<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Pesan generik untuk user. Detail teknis hanya masuk ke log server.
     */
    private const GENERIC_ERROR = 'Login dengan Google gagal. Silakan coba lagi.';

    /**
     * Pastikan kredensial OAuth tersedia sebelum mengirim user ke Google.
     * Tanpa client_id/redirect Google akan membalas "Error 400: invalid_request",
     * yang tidak bisa dipahami user.
     */
    private function googleConfigError(): ?string
    {
        $config = config('services.google');

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return 'GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET belum diset pada environment.';
        }

        if (empty($config['redirect'])) {
            return 'GOOGLE_REDIRECT_URI belum diset pada environment.';
        }

        return null;
    }

    /**
     * Redirect to Google for authentication
     */
    public function redirect($role = null)
    {
        if ($configError = $this->googleConfigError()) {
            Log::error('Google Auth misconfiguration: ' . $configError);

            return redirect()->route('login')->with('error', self::GENERIC_ERROR);
        }

        // Always set role as student
        Session::put('google_role', 'student');
        Session::save();

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            Log::error('Google Auth Redirect Error: ' . $e->getMessage());

            return redirect()->route('login')->with('error', self::GENERIC_ERROR);
        }
    }

    /**
     * Handle Google callback
     */
    public function callback(\Illuminate\Http\Request $request)
    {
        if ($configError = $this->googleConfigError()) {
            Log::error('Google Auth misconfiguration on callback: ' . $configError);

            return redirect()->route('login')->with('error', self::GENERIC_ERROR);
        }

        // Google mengembalikan ?error=access_denied ketika user membatalkan consent.
        if ($request->query('error')) {
            Log::warning('Google Auth denied by user: ' . $request->query('error'));

            return redirect()->route('login')->with('error', 'Login dengan Google dibatalkan.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            // Common intermittent issue: session/state not available on callback.
            // Retry with a stateless request as a defensive fallback and log a warning.
            Log::warning('Google Auth InvalidState — retrying stateless fallback: ' . $e->getMessage());
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
            } catch (\Exception $e2) {
                Log::error('Google Auth Stateless fallback failed: ' . $e2->getMessage());
                return redirect()->route('login')->with('error', self::GENERIC_ERROR);
            }
        } catch (\Exception $e) {
            // Jangan tampilkan pesan mentah (bisa memuat detail request/credential).
            Log::error('Google Auth Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', self::GENERIC_ERROR);
        }

        if (empty($googleUser->email)) {
            Log::error('Google Auth Error: akun Google tidak mengembalikan alamat email.');

            return redirect()->route('login')->with('error', self::GENERIC_ERROR);
        }

        // Get role from session (always student)
        $requestedRole = 'student';

        // Check if user exists by email OR google_id
        // ACCOUNT LINKING
        //
        // Identitas yang dipakai HANYA yang berasal dari Google setelah
        // authentication berhasil ($googleUser), bukan input dari frontend.
        //
        // Urutan pencarian: google_id dulu, baru email.
        //   1. google_id  — identitas paling stabil; tidak berubah walau user
        //                   mengganti alamat Gmail-nya.
        //   2. email      — Google sudah membuktikan kepemilikan alamat ini,
        //                   jadi menautkannya ke akun email/password yang sudah
        //                   ada itu aman dan mencegah akun duplikat.
        //
        // Karena kolom google_id UNIQUE dan langkah 1 sudah menghabiskan semua
        // kemungkinan google_id yang terpakai, langkah 2 tidak akan pernah
        // menabrak google_id milik user lain.
        $existingUser = User::where('google_id', $googleUser->id)->first()
            ?? User::where('email', $googleUser->email)->first();

        // If user exists, login directly
        if ($existingUser) {
            try {
                $updateData = [];

                // Tautkan akun email/password yang sudah ada ke Google.
                if (! $existingUser->google_id) {
                    $updateData['google_id'] = $googleUser->id;
                }

                // Google sudah memverifikasi kepemilikan email, jadi status
                // verifikasi akun ikut ditandai (dipakai untuk keamanan akun
                // di kemudian hari, bukan sebagai syarat login).
                if (! $existingUser->email_verified_at) {
                    $updateData['email_verified_at'] = now();
                    $updateData['email_verification_token'] = null;
                }

                // User mengganti alamat Gmail-nya: ikut perbarui, kecuali
                // alamat baru itu sudah dipakai akun lain.
                if ($existingUser->email !== $googleUser->email) {
                    $emailTaken = User::where('email', $googleUser->email)
                        ->where('id', '!=', $existingUser->id)
                        ->exists();

                    if ($emailTaken) {
                        Log::warning('Google Auth: email bentrok saat linking', [
                            'user_id' => $existingUser->id,
                        ]);

                        return redirect()->route('login')->with('error',
                            'Email Google ini sudah digunakan oleh akun lain. Silakan login memakai akun tersebut.'
                        );
                    }

                    $updateData['email'] = $googleUser->email;
                }

                if (!empty($updateData)) {
                    $existingUser->update($updateData);
                }
            } catch (\Exception $e) {
                Log::error('Google Auth Update Error: ' . $e->getMessage());
                // Continue dengan login meskipun update gagal
            }

            $user = $existingUser;
        } else {
            // Belum ada akun yang cocok — buat akun baru dengan role student.
            try {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => null,
                    'role' => 'student',
                    'is_subscriber' => false,
                    // Email sudah terverifikasi oleh Google.
                    'email_verified_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Google Auth Create User Error: ' . $e->getMessage());
                return redirect()->route('login')->with('error',
                    'Gagal membuat akun baru. Silakan coba lagi atau hubungi admin.'
                );
            }
        }

        // Import Google avatar if user has none and Google provides one
        try {
            if ((empty($user->avatar) || !$user->avatar) && !empty($googleUser->avatar)) {
                // Try to fetch avatar
                $avatarUrl = $googleUser->avatar;
                // Prefer a larger size if Google provides sz param
                if (strpos($avatarUrl, 'sz=') === false) {
                    $avatarUrl = $avatarUrl . (strpos($avatarUrl, '?') === false ? '?' : '&') . 'sz=512';
                }

                $response = \Illuminate\Support\Facades\Http::get($avatarUrl);

                if ($response->successful()) {
                    $content = $response->body();
                    // Limit to 2MB like regular uploads
                    if (strlen($content) <= 2 * 1024 * 1024) {
                        $contentType = $response->header('Content-Type', 'image/jpeg');
                        $ext = 'jpg';
                        if (strpos($contentType, 'png') !== false) $ext = 'png';
                        if (strpos($contentType, 'gif') !== false) $ext = 'gif';
                        if (strpos($contentType, 'webp') !== false) $ext = 'webp';

                        $filename = 'avatars/google_' . $user->id . '_' . time() . '.' . $ext;

                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $content);

                        // Save avatar path on user
                        $user->avatar = $filename;
                        $user->save();
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to import Google avatar for user ' . ($user->id ?? 'unknown') . ': ' . $e->getMessage());
        }

        // Akun yang di-ban atau dinonaktifkan tetap tidak boleh masuk,
        // termasuk lewat Google.
        if ($user->isBanned() || $user->is_active === false) {
            Log::warning('Google Auth: login ditolak, akun nonaktif/banned', ['user_id' => $user->id]);

            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Hubungi admin untuk bantuan.');
        }

        // Bersihkan sisa state OAuth + flash error lama SEBELUM login, supaya
        // tidak ada popup error yang muncul setelah login berhasil.
        // Catatan: pembersihan harus dilakukan sebelum Auth::login(), karena
        // menghapus seluruh isi session SETELAH login akan ikut membuang
        // identitas user yang baru saja di-set (user tampak logout lagi).
        Session::forget(['google_role', 'error', 'errors', 'success']);

        // Login user + regenerate session ID (mitigasi session fixation,
        // konsisten dengan login email/password).
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        // Update last login untuk tracking (sama seperti login biasa)
        $user->updateLastLogin();

        // Log login activity (sama seperti login biasa)
        $user->logActivity('google_login', 'User logged in to the system via Google');

        // Redirect based on user role - always send to home to avoid unintended redirection back to login
        return redirect()->route('home')->with('success', 'Login berhasil!');
    }
}
