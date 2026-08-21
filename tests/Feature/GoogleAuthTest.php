<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\InvalidStateException;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Controller menolak request lebih awal bila kredensial OAuth kosong,
        // jadi test perlu konfigurasi dummy (bukan kredensial asli).
        config([
            'services.google.client_id' => 'test-client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockSocialUser(array $overrides = []): object
    {
        return (object) array_merge([
            'id' => 'google-id-123',
            'name' => 'Social Tester',
            'email' => 'social@example.com',
            'avatar' => null,
        ], $overrides);
    }

    public function test_invalid_state_exception_falls_back_to_stateless_and_logs_in()
    {
        $socialUser = $this->mockSocialUser();

        // Mock the provider: first ->user() throws InvalidStateException, then stateless()->user() returns the user
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andThrow(new InvalidStateException())->once();
        $provider->shouldReceive('stateless')->andReturnSelf()->once();
        $provider->shouldReceive('user')->andReturn($socialUser)->once();

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        // Hit the callback route (Google would normally redirect here with code/state)
        $response = $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'social@example.com']);
    }

    /**
     * Regresi: sebelumnya callback memanggil Session::flush() SETELAH
     * Auth::login(), sehingga identitas user ikut terhapus dan user tampak
     * logout lagi pada request berikutnya.
     */
    public function test_session_still_authenticated_on_the_next_request()
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($this->mockSocialUser())->once();

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']));
        $response->assertRedirect(route('home'));

        // Identitas user harus benar-benar tersimpan di dalam session.
        // Catatan: assertAuthenticated() saja tidak cukup — guard menyimpan user
        // di memori selama satu siklus aplikasi, sehingga session yang sudah
        // di-flush pun masih terlihat "authenticated".
        $sessionKey = $this->app['auth']->guard('web')->getName();
        $userId = User::where('email', 'social@example.com')->value('id');

        $this->assertSame($userId, $response->getSession()->get($sessionKey));

        // Request berikutnya (menggunakan session yang sama) harus tetap login.
        $this->get('/dashboard')->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_google_user_is_marked_as_email_verified()
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($this->mockSocialUser())->once();

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']));

        $this->assertNotNull(User::where('email', 'social@example.com')->first()->email_verified_at);
    }

    /**
     * TEST C — akun Google baru dibuat lalu user langsung masuk.
     */
    public function test_new_google_user_gets_an_account_and_is_signed_in()
    {
        $this->assertDatabaseMissing('users', ['email' => 'social@example.com']);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($this->mockSocialUser())->once();
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']))
            ->assertRedirect(route('home'));

        $user = User::where('email', 'social@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-id-123', $user->google_id);
        $this->assertSame('student', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * TEST D — email yang sudah punya akun email/password ditautkan,
     * bukan dibuatkan akun kedua.
     */
    public function test_existing_email_account_is_linked_instead_of_duplicated()
    {
        $existing = User::create([
            'name' => 'Existing User',
            'email' => 'social@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('SecretPass123'),
            'role' => 'student',
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($this->mockSocialUser())->once();
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']))
            ->assertRedirect(route('home'));

        // Tidak ada akun duplikat.
        $this->assertSame(1, User::where('email', 'social@example.com')->count());

        $existing->refresh();
        $this->assertSame('google-id-123', $existing->google_id);
        $this->assertNotNull($existing->password, 'password lama tidak boleh hilang');
        $this->assertAuthenticatedAs($existing);
    }

    /**
     * Akun dicari lewat google_id lebih dulu, sehingga user yang mengganti
     * alamat Gmail tetap masuk ke akun yang sama (bukan akun baru).
     */
    public function test_account_is_matched_by_google_id_when_email_changed()
    {
        $existing = User::create([
            'name' => 'Existing User',
            'email' => 'old-address@example.com',
            'password' => null,
            'role' => 'student',
            'google_id' => 'google-id-123',
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($this->mockSocialUser(['email' => 'new-address@example.com']))->once();
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']))
            ->assertRedirect(route('home'));

        $this->assertSame(1, User::count());
        $this->assertSame('new-address@example.com', $existing->refresh()->email);
        $this->assertAuthenticatedAs($existing);
    }

    /**
     * TEST F — banned / nonaktif tetap ditolak walaupun lewat Google.
     */
    public function test_banned_user_cannot_sign_in_with_google()
    {
        User::create([
            'name' => 'Banned User',
            'email' => 'social@example.com',
            'password' => null,
            'role' => 'student',
            'is_banned' => true,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($this->mockSocialUser())->once();
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_sign_in_with_google()
    {
        User::create([
            'name' => 'Inactive User',
            'email' => 'social@example.com',
            'password' => null,
            'role' => 'student',
            'is_active' => false,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($this->mockSocialUser())->once();
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_generic_exception_returns_friendly_error_message()
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andThrow(new \Exception('provider down: client_secret=SENSITIVE'))->once();

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('auth.google.callback', ['code' => 'x', 'state' => 'y']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');

        // Pesan untuk user harus generik — detail teknis hanya di log server.
        $this->assertSame('Login dengan Google gagal. Silakan coba lagi.', session('error'));
        $this->assertStringNotContainsString('SENSITIVE', session('error'));
        $this->assertGuest();
    }

    public function test_missing_oauth_credentials_show_friendly_error_instead_of_google_400()
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect(route('login'));
        $this->assertSame('Login dengan Google gagal. Silakan coba lagi.', session('error'));
        $this->assertGuest();
    }

    public function test_user_cancelling_consent_is_handled()
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Login dengan Google dibatalkan.');
        $this->assertGuest();
    }
}
