<?php

namespace App\Http\Controllers;

use App\Support\AuthRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    /**
     * Batas percobaan login admin sebelum dikunci sementara.
     */
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 60;

    /**
     * Tampilkan halaman login
     */
    public function showLoginForm()
    {
        // Jika sudah login, tidak perlu melihat form login lagi.
        // Admin -> panel admin; user biasa -> halaman sesuai role-nya.
        if (auth()->check()) {
            return redirect()->to(AuthRedirect::homeFor(auth()->user()));
        }

        $response = response()->view('admin.auth.login');
        
        // Add cache control headers
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
        
        return $response;
    }

    /**
     * Proses login (POST)
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username harus diisi',
            'password.required' => 'Password harus diisi',
        ]);

        // Kunci percobaan berdasarkan username + IP untuk mencegah brute force.
        $throttleKey = 'admin|' . Str::lower((string) $credentials['username']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            Log::warning('Admin login throttled', ['ip' => $request->ip()]);

            return redirect()->back()
                ->withInput($request->only('username'))
                ->with('error', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.");
        }

        // Attempt login menggunakan email atau username
        // Check remember me checkbox (Laravel expects boolean)
        $remember = $request->boolean('remember');

        // Jika menggunakan email sebagai username
        if (Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $user = Auth::user();

            // Cek apakah user adalah admin
            if (!$user->isAdmin()) {
                Auth::logout();
                RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

                return redirect()->back()
                    ->withInput($request->only('username'))
                    ->with('error', 'Anda bukan administrator. Silakan login di halaman yang sesuai.');
            }

            // Akun admin yang dinonaktifkan/di-ban tidak boleh masuk.
            // Tanpa pemeriksaan ini login "berhasil" dulu, lalu baru ditendang
            // middleware pada request berikutnya tanpa penjelasan yang jelas.
            if ($user->isBanned() || $user->is_active === false) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->withInput($request->only('username'))
                    ->with('error', 'Akun admin ini dinonaktifkan. Hubungi super admin untuk bantuan.');
            }

            RateLimiter::clear($throttleKey);

            // Update last login
            $user->updateLastLogin();
            
            // Log login activity
            $user->logActivity('admin_login', 'Admin logged in to the system');
            
            // Regenerate session ID untuk keamanan
            $request->session()->regenerate();

            // Redirect ke admin dashboard
            return redirect()->route('admin.dashboard')->with('success', 'Login berhasil!');
        }

        // Jika login gagal — catat percobaan untuk keperluan throttle.
        RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

        return redirect()->back()
            ->withInput($request->only('username'))
            ->with('error', 'Email atau Password salah');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout activity before logging out
        if ($user && $user->isAdmin()) {
            $user->logActivity('admin_logout', 'Admin logged out from the system');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke admin login dengan cache control headers untuk prevent back button
        return redirect()->route('admin.login')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
            ])
            ->with('success', 'Anda telah logout');
    }
}
