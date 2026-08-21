<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function unverifiedUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Verify Tester',
            'email' => 'verify@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verification_token' => Str::random(60),
            'email_verification_sent_at' => now(),
        ], $overrides));
    }

    /**
     * TEST A — signup mengarahkan ke halaman login dengan notifikasi berhasil,
     * bukan ke halaman verifikasi dan bukan pula langsung masuk ke situs.
     */
    public function test_registration_redirects_to_login_with_success_message()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // User harus login sendiri dulu.
        $this->assertGuest();

        $user = User::where('email', 'newstudent@example.com')->first();
        $this->assertNotNull($user);

        // Infrastruktur verifikasi tetap hidup: token dibuat & email dikirim,
        // hanya saja tidak menghalangi user memakai akunnya.
        $this->assertNull($user->email_verified_at);
        $this->assertSame(60, strlen($user->email_verification_token));
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_registration_never_redirects_to_the_verification_page()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'No Pending Page',
            'email' => 'nopending@example.com',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * TEST B — setelah signup user bisa login, logout, lalu login lagi,
     * tanpa pernah diminta verifikasi.
     */
    public function test_user_can_login_logout_and_login_again_without_verification()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Returning User',
            'email' => 'returning@example.com',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
        ]);
        $this->assertGuest();

        // Login pertama.
        $this->post('/login', ['email' => 'returning@example.com', 'password' => 'SecretPass123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        // Login kedua.
        $this->post('/login', ['email' => 'returning@example.com', 'password' => 'SecretPass123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->assertNull(User::where('email', 'returning@example.com')->value('email_verified_at'));
    }

    /**
     * Login tidak boleh menerbitkan token verifikasi baru.
     */
    public function test_login_does_not_issue_a_new_verification_token()
    {
        $user = $this->unverifiedUser();
        $tokenBefore = $user->email_verification_token;
        $sentAtBefore = (string) $user->email_verification_sent_at;

        $this->post('/login', ['email' => $user->email, 'password' => 'SecretPass123'])
            ->assertRedirect(route('home'));

        $user->refresh();
        $this->assertSame($tokenBefore, $user->email_verification_token);
        $this->assertSame($sentAtBefore, (string) $user->email_verification_sent_at);
    }

    public function test_valid_token_verifies_the_email()
    {
        $user = $this->unverifiedUser();

        $response = $this->get(route('email.verify', [
            'token' => $user->email_verification_token,
            'email' => $user->email,
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->email_verification_token);
    }

    public function test_expired_token_is_rejected_and_user_can_request_a_new_link()
    {
        $user = $this->unverifiedUser([
            'email_verification_sent_at' => now()->subHours(25),
        ]);

        $response = $this->get(route('email.verify', [
            'token' => $user->email_verification_token,
            'email' => $user->email,
        ]));

        $response->assertRedirect(route('email.verification.pending'));
        $response->assertSessionHasErrors('error');
        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_invalid_token_is_rejected()
    {
        $user = $this->unverifiedUser();

        $response = $this->get(route('email.verify', [
            'token' => 'not-the-right-token',
            'email' => $user->email,
        ]));

        $response->assertRedirect(route('email.verification.pending'));
        $response->assertSessionHasErrors('error');
        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_already_used_token_reports_email_as_verified_not_invalid()
    {
        $user = $this->unverifiedUser();
        $token = $user->email_verification_token;

        // Klik pertama: berhasil.
        $this->get(route('email.verify', ['token' => $token, 'email' => $user->email]))
            ->assertRedirect(route('login'));

        // Klik kedua dengan token yang sama: user tidak boleh melihat "tidak valid".
        $response = $this->get(route('email.verify', ['token' => $token, 'email' => $user->email]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $response->assertSessionHasNoErrors();
    }

    public function test_unknown_email_is_rejected_without_leaking_details()
    {
        $response = $this->get(route('email.verify', [
            'token' => Str::random(60),
            'email' => 'nobody@example.com',
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('error');
    }

    public function test_login_is_allowed_even_when_email_is_not_verified()
    {
        $user = $this->unverifiedUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SecretPass123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * TEST F — akun banned / nonaktif tetap tidak boleh masuk.
     */
    public function test_banned_user_cannot_login()
    {
        $user = $this->unverifiedUser(['is_banned' => true]);

        $this->post('/login', ['email' => $user->email, 'password' => 'SecretPass123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login()
    {
        $user = $this->unverifiedUser(['is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'SecretPass123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * Infrastruktur verifikasi harus tetap utuh untuk dipakai nanti.
     */
    public function test_verification_infrastructure_is_still_available()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('email.verify'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('verify.resend'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('email.verification.pending'));

        // Tabel reset password (dipakai fitur forgot password) tetap ada.
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('password_reset_tokens'));

        // Kolom penyimpan status verifikasi tetap ada.
        foreach (['email_verified_at', 'email_verification_token', 'email_verification_sent_at'] as $column) {
            $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('users', $column), $column);
        }
    }

    public function test_login_succeeds_after_verification()
    {
        $user = $this->unverifiedUser(['email_verified_at' => now()]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'SecretPass123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_resend_issues_a_new_token_and_sends_email()
    {
        Notification::fake();

        $user = $this->unverifiedUser(['email_verification_sent_at' => now()->subMinutes(5)]);
        $oldToken = $user->email_verification_token;

        $response = $this->from(route('email.verification.pending'))
            ->post(route('verify.resend'), ['email' => $user->email]);

        $response->assertRedirect(route('email.verification.pending'));
        $response->assertSessionHas('success');

        $this->assertNotSame($oldToken, $user->refresh()->email_verification_token);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_is_throttled_to_prevent_spam()
    {
        Notification::fake();

        // Token baru saja dikirim (< 60 detik).
        $user = $this->unverifiedUser(['email_verification_sent_at' => now()]);

        $response = $this->from(route('email.verification.pending'))
            ->post(route('verify.resend'), ['email' => $user->email]);

        $response->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }

    public function test_resend_falls_back_to_session_email_when_form_field_is_empty()
    {
        Notification::fake();

        $user = $this->unverifiedUser(['email_verification_sent_at' => now()->subMinutes(5)]);

        $response = $this->withSession(['pending_verification_email' => $user->email])
            ->from(route('email.verification.pending'))
            ->post(route('verify.resend'), ['email' => '']);

        $response->assertSessionHas('success');
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_for_already_verified_email_shows_clear_message()
    {
        Notification::fake();

        $user = $this->unverifiedUser(['email_verified_at' => now()]);

        $response = $this->from(route('email.verification.pending'))
            ->post(route('verify.resend'), ['email' => $user->email]);

        $response->assertSessionHasErrors('email');
        Notification::assertNothingSent();
    }
}
