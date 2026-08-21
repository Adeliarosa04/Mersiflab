<?php

namespace Tests\Feature;

use App\Models\AiChat;
use App\Models\User;
use App\Services\KnowledgeBaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'llm.api_key' => 'test-key',
            'llm.quota.guest' => 3,
            'llm.quota.free_user' => 5,
        ]);
    }

    /** Balasan sukses palsu dari penyedia LLM. */
    private function fakeLlmSuccess(string $text = 'Halo dari Mersy.'): void
    {
        Http::fake([
            '*generativelanguage*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => $text]]]],
                ],
            ], 200),
        ]);
    }

    private function student(string $email = 'ai-student@example.com'): User
    {
        return User::create([
            'name' => 'AI Student',
            'email' => $email,
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    public function test_tamu_bisa_chat_dan_riwayatnya_tersimpan(): void
    {
        $this->fakeLlmSuccess('Ini jawaban Mersy.');

        $response = $this->postJson('/ai-assistant/chat', ['message' => 'Apa itu MersifLab?']);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('remaining_questions', 2);

        $this->assertSame(1, AiChat::whereNull('user_id')->count());
        $this->assertNotNull(AiChat::first()->guest_token);
    }

    public function test_kuota_tamu_berhenti_di_tiga_pesan_dan_mengembalikan_cta(): void
    {
        $this->fakeLlmSuccess();

        for ($i = 1; $i <= 3; $i++) {
            $this->postJson('/ai-assistant/chat', ['message' => "Pertanyaan {$i}"])->assertOk();
        }

        $response = $this->postJson('/ai-assistant/chat', ['message' => 'Pertanyaan keempat']);

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'require_login' => true])
            ->assertJsonPath('cta.title', 'Daftar / Login untuk Lanjut Chatting');

        // Pesan keempat tidak boleh ikut tersimpan.
        $this->assertSame(3, AiChat::count());
    }

    public function test_riwayat_chat_tamu_pindah_ke_akun_setelah_login(): void
    {
        $this->fakeLlmSuccess();

        $user = $this->student();

        $this->postJson('/ai-assistant/chat', ['message' => 'Pertanyaan sebagai tamu'])->assertOk();
        $this->assertSame(1, AiChat::whereNull('user_id')->count());

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'SecretPass123',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, AiChat::whereNull('user_id')->count());
        $this->assertSame(1, AiChat::where('user_id', $user->id)->count());
        $this->assertNotNull(AiChat::first()->migrated_at);
    }

    /**
     * Kuota tamu harus tetap terhitung lewat cookie token, walaupun session
     * id-nya berganti (mis. browser mulai session baru). Ini yang membuat
     * riwayat tamu selamat saat Laravel me-regenerate session id waktu login.
     */
    public function test_kuota_tamu_terhitung_lewat_cookie_meski_session_berganti(): void
    {
        $this->fakeLlmSuccess();

        $cookieName = config('llm.guest_cookie.name');

        $first = $this->postJson('/ai-assistant/chat', ['message' => 'Pertanyaan pertama']);
        $first->assertOk()->assertJsonPath('remaining_questions', 2);

        $token = $first->getCookie($cookieName, false)?->getValue();
        $this->assertNotEmpty($token, 'Cookie token tamu harus dikirim ke browser.');

        // Baris riwayat ditandai dengan token yang sama seperti isi cookie.
        // (Nilai cookie terenkripsi masih membawa prefix HMAC dari Laravel,
        // yang otomatis dilepas oleh middleware EncryptCookies saat dibaca.)
        $this->assertStringContainsString(
            AiChat::first()->guest_token,
            decrypt($token, false),
            'guest_token pada riwayat harus sama dengan nilai cookie.'
        );

        // Session baru (session id berbeda), tapi cookie token dibawa serta.
        $this->flushSession();

        $second = $this->withUnencryptedCookie($cookieName, AiChat::first()->guest_token)
            ->postJson('/ai-assistant/chat', ['message' => 'Pertanyaan kedua']);

        $second->assertOk()->assertJsonPath('remaining_questions', 1);
    }

    public function test_kegagalan_llm_dibalas_pesan_ramah_bukan_error_lima_ratus(): void
    {
        Http::fake([
            '*generativelanguage*' => Http::response(['error' => ['message' => 'quota exceeded']], 500),
        ]);

        $response = $this->postJson('/ai-assistant/chat', ['message' => 'Halo']);

        $response->assertOk()
            ->assertJson(['success' => true, 'service_unavailable' => true]);

        // Pesan mentah dari penyedia tidak boleh bocor ke pengguna.
        $response->assertJsonMissing(['error' => ['message' => 'quota exceeded']]);
        $this->assertStringNotContainsString('quota exceeded', $response->json('answer'));

        // Kegagalan layanan tidak memotong kuota tamu.
        $this->assertSame(0, AiChat::count());
    }

    public function test_api_key_kosong_tidak_membuat_error_lima_ratus(): void
    {
        config(['llm.api_key' => null]);
        Http::fake();

        $response = $this->postJson('/ai-assistant/chat', ['message' => 'Halo']);

        $response->assertOk()->assertJson(['service_unavailable' => true]);
        Http::assertNothingSent();
    }

    public function test_check_limit_mengembalikan_kuota_tamu(): void
    {
        $this->getJson('/ai-assistant/check-limit')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'is_authenticated' => false,
                'remaining_questions' => 3,
                'daily_limit' => 3,
            ]);
    }

    public function test_prompt_menyisipkan_konteks_knowledge_base(): void
    {
        $this->fakeLlmSuccess();

        $this->postJson('/ai-assistant/chat', ['message' => 'Berapa harga paket premium?'])->assertOk();

        Http::assertSent(function ($request) {
            $prompt = data_get($request->data(), 'contents.0.parts.0.text', '');

            return str_contains($prompt, 'Asisten AI Resmi MersifLab')
                && str_contains($prompt, 'DATA INTERNAL MERSIFLAB')
                && str_contains($prompt, 'Rp 150.000');
        });
    }

    public function test_retriever_memilih_dokumen_yang_relevan(): void
    {
        $retriever = app(KnowledgeBaseService::class);

        $ids = array_column($retriever->retrieve('Bagaimana cara menjadi guru di MersifLab?'), 'id');

        $this->assertContains('faq-menjadi-guru', $ids);
    }

    public function test_user_login_punya_kuota_harian_sendiri(): void
    {
        $this->fakeLlmSuccess();

        $user = $this->student('ai-quota@example.com');
        $this->actingAs($user);

        $this->getJson('/ai-assistant/check-limit')
            ->assertOk()
            ->assertJson([
                'is_authenticated' => true,
                'daily_limit' => 5,
                'remaining_questions' => 5,
            ]);

        $this->postJson('/ai-assistant/chat', ['message' => 'Halo'])
            ->assertOk()
            ->assertJsonPath('remaining_questions', 4);
    }
}
