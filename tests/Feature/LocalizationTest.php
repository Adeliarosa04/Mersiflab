<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur multi-bahasa: pemilih bahasa, middleware SetLocale, dan berkas
 * terjemahan.
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bahasa_default_adalah_indonesia(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertSame('id', app()->getLocale());
        $response->assertSee('Beranda');
    }

    public function test_pengguna_bisa_beralih_ke_bahasa_inggris(): void
    {
        $this->get(route('language.switch', 'en'))
            ->assertRedirect();

        $this->assertSame('en', session('locale'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Subscription')
            ->assertDontSee('Beranda');
    }

    public function test_pengguna_bisa_kembali_ke_bahasa_indonesia(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get(route('language.switch', 'id'))
            ->assertRedirect();

        $this->assertSame('id', session('locale'));
    }

    public function test_bahasa_yang_tidak_didukung_menghasilkan_404(): void
    {
        $this->withSession(['locale' => 'id'])
            ->get('/language/fr')
            ->assertNotFound();

        // Pilihan lama tidak boleh ikut berubah.
        $this->assertSame('id', session('locale'));
    }

    public function test_session_rusak_jatuh_ke_bahasa_default(): void
    {
        $this->withSession(['locale' => 'klingon'])
            ->get('/')
            ->assertOk();

        $this->assertSame(SetLocale::DEFAULT, app()->getLocale());
    }

    public function test_berkas_terjemahan_punya_kunci_yang_sama(): void
    {
        $id = json_decode(file_get_contents(base_path('lang/id.json')), true);
        $en = json_decode(file_get_contents(base_path('lang/en.json')), true);

        $this->assertIsArray($id, 'lang/id.json harus JSON yang valid');
        $this->assertIsArray($en, 'lang/en.json harus JSON yang valid');
        $this->assertSame([], array_diff(array_keys($id), array_keys($en)), 'ada kunci di id.json yang tidak ada di en.json');
        $this->assertSame([], array_diff(array_keys($en), array_keys($id)), 'ada kunci di en.json yang tidak ada di id.json');
    }

    public function test_helper_menerjemahkan_sesuai_locale(): void
    {
        app()->setLocale('id');
        $this->assertSame('Beranda', __('Home'));
        $this->assertSame('Kursus', __('Courses'));

        app()->setLocale('en');
        $this->assertSame('Home', __('Home'));
        $this->assertSame('Courses', __('Courses'));
    }

    public function test_tombol_switcher_tampil_di_navbar(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('ml-lang-toggle', false)
            ->assertSee(route('language.switch', 'id'), false)
            ->assertSee(route('language.switch', 'en'), false);
    }

    /**
     * Guardrail: mengganti bahasa tidak boleh mengeluarkan pengguna dari sesi.
     */
    public function test_ganti_bahasa_tidak_mengeluarkan_pengguna(): void
    {
        $user = \App\Models\User::create([
            'name' => 'Lokal Tester',
            'email' => 'lokal@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('language.switch', 'en'))
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('en', session('locale'));
    }
}
