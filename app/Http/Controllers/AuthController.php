<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Support\AuthRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Default role untuk user baru
     * Ubah ke 'student' atau 'teacher' sesuai kebutuhan
     */
    private $defaultRole = 'student';

    /**
     * Batas percobaan login sebelum dikunci sementara.
     */
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 60;

    public function showLogin()
    {
        // User yang sudah login tidak perlu melihat form login lagi.
        if (Auth::check()) {
            return redirect()->to(AuthRedirect::homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    /**
     * Kunci percobaan login berdasarkan kombinasi email + IP, supaya satu
     * penyerang tidak bisa mencoba password tanpa batas, dan user lain yang
     * memakai email sama dari jaringan berbeda tidak ikut terkunci.
     */
    private function loginThrottleKey(Request $request): string
    {
        return Str::lower((string) $request->input('email')) . '|' . $request->ip();
    }

    /**
     * Login dengan validasi email verification
     */
    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to(AuthRedirect::homeFor(Auth::user()));
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = $this->loginThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            Log::warning('Login throttled', ['ip' => $request->ip()]);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Hormati checkbox "Remember me" pada form login.
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);

            $user = auth()->user();

            // CATATAN: verifikasi email BUKAN syarat login pada fase ini.
            // Kolom email_verified_at, token, notifikasi, dan route verifikasi
            // sengaja dipertahankan agar bisa diaktifkan kembali nanti —
            // lihat verifyEmail() dan resendVerificationEmail() di bawah.

            // Akun yang di-ban atau dinonaktifkan tetap tidak boleh masuk.
            if ($user->isBanned() || $user->is_active === false) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi admin untuk bantuan.',
                ])->onlyInput('email');
            }

            // Role sesuai, lanjutkan login
            $request->session()->regenerate();

            // Update last login untuk tracking
            $user->updateLastLogin();

            // Log login activity
            $user->logActivity('user_login', 'User logged in to the system');

            // Kembalikan user ke halaman yang tadi ingin dibuka sebelum
            // diminta login; kalau tidak ada, ke halaman sesuai role-nya.
            return redirect()->intended(AuthRedirect::homeFor($user));
        }

        // Login gagal — catat percobaan untuk keperluan throttle.
        RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        // User yang sudah login tidak perlu melihat form registrasi.
        if (Auth::check()) {
            return redirect()->to(AuthRedirect::homeFor(Auth::user()));
        }

        return view('auth.register');
    }

    /**
     * Register user.
     *
     * Email verifikasi tetap dibuat & dikirim (best effort) agar infrastruktur
     * verifikasi tetap hidup dan bisa diwajibkan kembali kapan saja, tetapi
     * TIDAK menghalangi user memakai akunnya.
     *
     * Setelah akun dibuat user diarahkan ke halaman login (bukan langsung
     * masuk ke situs, dan bukan pula ke halaman "Check Your Email").
     */
    public function register(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to(AuthRedirect::homeFor(Auth::user()));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Generate verification token
        $verificationToken = Str::random(60);

        // Create user with verification token
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'email_verification_token' => $verificationToken,
            'email_verification_sent_at' => now(),
        ]);

        // Send verification email
        $verificationUrl = route('email.verify', [
            'token' => $verificationToken,
            'email' => $user->email,
        ]);

        // Best effort — kegagalan kirim email tidak boleh menghalangi user
        // memakai akunnya, karena verifikasi bukan syarat login.
        $this->sendVerificationEmail($user, $verificationUrl);

        $user->logActivity('user_register', 'User registered an account');

        // User TIDAK di-login otomatis. Arahkan ke halaman login dengan
        // notifikasi berhasil, dan email-nya diisikan ke form supaya user
        // tinggal mengetik password.
        return redirect()->route('login')
            ->with('success', 'Akun berhasil dibuat! Silakan login untuk melanjutkan.')
            ->withInput(['email' => $user->email]);
    }

    /**
     * Kirim email verifikasi. Detail teknis (SMTP) hanya masuk ke log server.
     *
     * @return bool true jika email berhasil diserahkan ke mailer.
     */
    private function sendVerificationEmail(User $user, string $verificationUrl): bool
    {
        try {
            Log::info('Sending email verification', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            $user->notify(new VerifyEmailNotification($user, $verificationUrl));

            Log::info('Email verification sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send email verification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Show email verification pending page
     */
    public function showVerify(Request $request)
    {
        // Jaga agar alamat email tetap tersedia untuk form resend meskipun
        // flash session sudah habis (mis. setelah refresh halaman).
        $email = $request->session()->get('email')
            ?? $request->session()->get('pending_verification_email');

        return view('auth.email-verification-pending', ['pendingEmail' => $email]);
    }

    /**
     * Verify email dari link yang dikirim ke email
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        Log::info('Email verification attempt', [
            'token' => substr($token, 0, 10) . '***',
            'email' => $email,
        ]);

        if (!$token || !$email) {
            Log::warning('Email verification failed: Missing parameters', [
                'has_token' => !!$token,
                'has_email' => !!$email,
            ]);
            return redirect()->route('login')
                ->withErrors(['error' => 'Link verifikasi tidak valid.']);
        }

        $account = User::where('email', $email)->first();

        // Email sudah terverifikasi (mis. link diklik dua kali, atau user
        // sudah login lewat Google). Jangan tampilkan pesan "tidak valid".
        if ($account && $account->email_verified_at) {
            Log::info('Email verification skipped: already verified', [
                'user_id' => $account->id,
            ]);

            return redirect()->route('login')
                ->with('success', 'Email Anda sudah diverifikasi. Silakan login.');
        }

        // Find user by email and token
        $user = $account && $account->email_verification_token
            && hash_equals($account->email_verification_token, $token)
                ? $account
                : null;

        if (!$user) {
            Log::warning('Email verification failed: User or token not found', [
                'email' => $email,
                'token_hint' => substr($token, 0, 10) . '***',
            ]);

            // Token sudah dipakai/diganti oleh link yang lebih baru, atau memang
            // tidak valid. Arahkan ke halaman pending agar user bisa minta ulang.
            if ($account) {
                $request->session()->put('pending_verification_email', $account->email);

                return redirect()->route('email.verification.pending')
                    ->with('email', $account->email)
                    ->withErrors(['error' => 'Link verifikasi tidak valid atau sudah tidak berlaku. Silakan minta link verifikasi baru.']);
            }

            return redirect()->route('login')
                ->withErrors(['error' => 'Link verifikasi tidak valid atau email belum terdaftar.']);
        }

        // Check if token has expired (24 hours)
        if ($user->email_verification_sent_at && $user->email_verification_sent_at->addHours(24)->isPast()) {
            Log::warning('Email verification failed: Token expired', [
                'email' => $email,
                'sent_at' => $user->email_verification_sent_at,
            ]);
            $request->session()->put('pending_verification_email', $user->email);

            return redirect()->route('email.verification.pending')
                ->withErrors(['error' => 'Link verifikasi sudah kedaluwarsa. Silakan minta link verifikasi baru.'])
                ->with('email', $user->email);
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_sent_at' => null,
        ]);

        $request->session()->forget('pending_verification_email');

        Log::info('Email verified successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->route('login')
            ->with('success', 'Email berhasil diverifikasi! Silakan login dengan akun Anda.');
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        // Email bisa datang dari form; jika kosong (flash session habis),
        // pakai email yang tersimpan di session halaman pending.
        $email = $request->input('email') ?: $request->session()->get('pending_verification_email');

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['email' => $email],
            ['email' => 'required|email|exists:users'],
            ['email.required' => 'Alamat email tidak diketahui. Silakan login atau daftar ulang.',
             'email.exists' => 'Email tersebut belum terdaftar.']
        );

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = User::where('email', $email)->first();
        $request->session()->put('pending_verification_email', $user->email);

        Log::info('Resend verification email attempt', [
            'email' => $user->email,
            'already_verified' => !!$user->email_verified_at,
        ]);

        // Check if email already verified
        if ($user->email_verified_at) {
            return back()->withErrors(['email' => 'Email Anda sudah diverifikasi. Silakan login.']);
        }

        // Throttle: cegah pengiriman berulang (double submit / spam).
        if ($user->email_verification_sent_at && $user->email_verification_sent_at->diffInSeconds(now()) < 60) {
            return back()->withErrors([
                'email' => 'Email verifikasi baru saja dikirim. Silakan tunggu 1 menit sebelum mencoba lagi.',
            ]);
        }

        // Generate new verification token
        $verificationToken = Str::random(60);

        $user->update([
            'email_verification_token' => $verificationToken,
            'email_verification_sent_at' => now(),
        ]);

        // Send verification email
        $verificationUrl = route('email.verify', [
            'token' => $verificationToken,
            'email' => $user->email,
        ]);

        if (! $this->sendVerificationEmail($user, $verificationUrl)) {
            return back()->withErrors([
                'email' => 'Email verifikasi belum dapat dikirim. Silakan coba lagi beberapa saat lagi.',
            ]);
        }

        return back()
            ->with('email', $user->email)
            ->with('success', 'Email verifikasi telah dikirim ulang. Silakan periksa inbox Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
