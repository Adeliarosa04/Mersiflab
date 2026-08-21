<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\SubscriptionPurchase;
use App\Models\User;
use App\Services\SubscriptionPlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionUpgradeAndCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Invoice mengirim email otomatis lewat boot model - dipalsukan agar
        // test tidak bergantung pada SMTP.
        Mail::fake();
    }

    private function student(string $email = 'subs-user@example.com'): User
    {
        return User::create([
            'name' => 'Subscription Tester',
            'email' => $email,
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Buat user dengan langganan yang MASIH aktif beserta purchase sukses-nya.
     *
     * Catatan: expires_at sengaja tidak dihitung sebagai started_at + 1 bulan.
     * Paket 1 bulan hanya berumur 28-31 hari, sehingga langganan berusia >30
     * hari otomatis sudah kedaluwarsa dan tidak lagi bisa dibatalkan. Untuk
     * menguji aturan pembatalan, expires_at dibuat pasti berada di masa depan
     * (mewakili langganan yang diperpanjang / berdurasi lebih panjang).
     */
    private function subscriberWithPlan(string $plan, int $daysAgo = 0, string $email = 'subs-user@example.com'): User
    {
        $user = $this->student($email);
        $startedAt = now()->subDays($daysAgo);
        $expiresAt = now()->addDays(7);

        SubscriptionPurchase::create([
            'purchase_code' => SubscriptionPurchase::generatePurchaseCode(),
            'user_id' => $user->id,
            'plan' => $plan,
            'amount' => SubscriptionPlanService::PLAN_PRICES[$plan],
            'discount_amount' => 0,
            'final_amount' => SubscriptionPlanService::PLAN_PRICES[$plan],
            'status' => SubscriptionPurchase::STATUS_SUCCESS,
            'payment_method' => 'qris',
            'payment_provider' => 'manual',
            'paid_at' => $startedAt,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
        ]);

        $user->update([
            'is_subscriber' => true,
            'subscription_plan' => $plan,
            'subscription_started_at' => $startedAt,
            'subscription_expires_at' => $expiresAt,
        ]);

        return $user->fresh();
    }

    // ================= UPGRADE =================

    public function test_pengguna_standard_bisa_membuka_halaman_checkout_premium(): void
    {
        $user = $this->subscriberWithPlan('standard');

        // Sebelum perbaikan: dialihkan ke halaman subscription dengan pesan
        // "Anda sudah memiliki subscription aktif".
        $this->actingAs($user)
            ->get('/subscription/payment/premium')
            ->assertOk()
            ->assertSee('Premium Plan Subscription')
            // Tombol bayar tidak boleh terkunci lagi untuk alur upgrade.
            ->assertSee('Bayar Upgrade')
            ->assertDontSee('Active Subscription - Cannot Purchase');
    }

    public function test_pengguna_standard_bisa_memproses_pembayaran_upgrade_premium(): void
    {
        $user = $this->subscriberWithPlan('standard');

        $response = $this->actingAs($user)->postJson(route('subscription.process.payment'), [
            'plan' => 'premium',
            'payment_method' => 'qris',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        // Invoice + QRIS baru ikut dikirim ke frontend.
        $this->assertNotEmpty($response->json('invoice_number'));
        $this->assertNotEmpty($response->json('payment.reference'));
        $this->assertSame('Rp150.000', $response->json('payment.total'));

        $upgrade = SubscriptionPurchase::where('user_id', $user->id)
            ->where('plan', 'premium')
            ->first();

        $this->assertNotNull($upgrade, 'Purchase premium harus dibuat.');
        $this->assertSame(SubscriptionPurchase::STATUS_PENDING, $upgrade->status);
        $this->assertTrue($upgrade->is_upgrade);
        $this->assertSame('150000.00', (string) $upgrade->final_amount);

        // Paket Standard tetap aktif selama upgrade belum disetujui admin.
        $this->assertTrue($user->fresh()->hasActiveSubscription());
        $this->assertSame('standard', $user->fresh()->subscription_plan);

        $invoice = Invoice::where('invoiceable_id', $upgrade->id)
            ->where('invoiceable_type', SubscriptionPurchase::class)
            ->first();

        $this->assertNotNull($invoice);
        $this->assertTrue($invoice->metadata['is_upgrade']);
        $this->assertSame('standard', $invoice->metadata['upgraded_from']);
    }

    public function test_saat_upgrade_disetujui_paket_standard_lama_ditandai_upgraded(): void
    {
        $user = $this->subscriberWithPlan('standard');
        $old = SubscriptionPurchase::where('user_id', $user->id)->first();

        $upgrade = SubscriptionPurchase::create([
            'purchase_code' => SubscriptionPurchase::generatePurchaseCode(),
            'user_id' => $user->id,
            'plan' => 'premium',
            'amount' => 150000,
            'discount_amount' => 0,
            'final_amount' => 150000,
            'status' => SubscriptionPurchase::STATUS_PENDING,
            'is_upgrade' => true,
        ]);

        $upgrade->activateSubscription();

        $this->assertSame(SubscriptionPurchase::STATUS_UPGRADED, $old->fresh()->status);
        $this->assertSame($upgrade->id, $old->fresh()->replaced_by_id);

        $user = $user->fresh();
        $this->assertSame('premium', $user->subscription_plan);
        $this->assertTrue($user->hasActiveSubscription());
        $this->assertNotNull($user->subscription_started_at);
    }

    public function test_downgrade_premium_ke_standard_tetap_ditolak(): void
    {
        $user = $this->subscriberWithPlan('premium');

        $this->actingAs($user)
            ->postJson(route('subscription.process.payment'), [
                'plan' => 'standard',
                'payment_method' => 'qris',
            ])
            ->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertSame(0, SubscriptionPurchase::where('plan', 'standard')
            ->where('status', SubscriptionPurchase::STATUS_PENDING)->count());
    }

    public function test_membeli_paket_yang_sama_tetap_ditolak(): void
    {
        $user = $this->subscriberWithPlan('standard');

        $this->actingAs($user)
            ->postJson(route('subscription.process.payment'), [
                'plan' => 'standard',
                'payment_method' => 'qris',
            ])
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_pembelian_normal_tanpa_langganan_tidak_berubah(): void
    {
        $user = $this->student('fresh-buyer@example.com');

        $response = $this->actingAs($user)->postJson(route('subscription.process.payment'), [
            'plan' => 'standard',
            'payment_method' => 'qris',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $purchase = SubscriptionPurchase::where('user_id', $user->id)->first();

        $this->assertSame('standard', $purchase->plan);
        $this->assertFalse((bool) $purchase->is_upgrade);
        $this->assertSame('50000.00', (string) $purchase->final_amount);
    }

    public function test_purchase_pending_standard_tidak_dipakai_ulang_untuk_checkout_premium(): void
    {
        $user = $this->subscriberWithPlan('standard');

        $pendingStandard = SubscriptionPurchase::create([
            'purchase_code' => SubscriptionPurchase::generatePurchaseCode(),
            'user_id' => $user->id,
            'plan' => 'standard',
            'amount' => 50000,
            'discount_amount' => 0,
            'final_amount' => 50000,
            'status' => SubscriptionPurchase::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->withSession(['latest_subscription_purchase_id' => $pendingStandard->id])
            ->postJson(route('subscription.process.payment'), [
                'plan' => 'premium',
                'payment_method' => 'qris',
            ])
            ->assertOk();

        // Purchase Standard yang pending tidak boleh berubah jadi tagihan Premium.
        $this->assertSame('standard', $pendingStandard->fresh()->plan);
        $this->assertSame('50000.00', (string) $pendingStandard->fresh()->final_amount);

        $premium = SubscriptionPurchase::where('user_id', $user->id)->where('plan', 'premium')->first();
        $this->assertNotNull($premium);
        $this->assertNotSame($pendingStandard->id, $premium->id);
    }

    // ================= PEMBATALAN (ATURAN 1 BULAN) =================

    public function test_pembatalan_diblokir_sebelum_satu_bulan(): void
    {
        $user = $this->subscriberWithPlan('standard', daysAgo: 10);

        $response = $this->actingAs($user)->postJson(route('subscription.cancel'));

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonPath('cancellation.allowed', false);

        $this->assertStringContainsString(
            'Langganan tidak dapat dibatalkan sebelum masa aktif mencapai 1 bulan.',
            $response->json('message')
        );

        // Langganan harus tetap aktif.
        $user = $user->fresh();
        $this->assertTrue($user->hasActiveSubscription());
        $this->assertSame('standard', $user->subscription_plan);
    }

    public function test_pembatalan_tepat_di_hari_ke_29_masih_diblokir(): void
    {
        $user = $this->subscriberWithPlan('standard', daysAgo: 29);

        $this->actingAs($user)
            ->postJson(route('subscription.cancel'))
            ->assertStatus(422);

        $this->assertTrue($user->fresh()->hasActiveSubscription());
    }

    public function test_pembatalan_diizinkan_setelah_tiga_puluh_hari(): void
    {
        $user = $this->subscriberWithPlan('premium', daysAgo: 31);

        $this->actingAs($user)
            ->postJson(route('subscription.cancel'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $user = $user->fresh();
        $this->assertFalse($user->hasActiveSubscription());
        $this->assertNull($user->subscription_plan);
        $this->assertNotNull($user->subscription_cancelled_at);

        $purchase = SubscriptionPurchase::where('user_id', $user->id)->first();
        $this->assertSame(SubscriptionPurchase::STATUS_CANCELLED, $purchase->status);
        $this->assertNotNull($purchase->cancelled_at);
    }

    public function test_pembatalan_tanpa_langganan_aktif_ditolak_dengan_sopan(): void
    {
        $user = $this->student('no-sub@example.com');

        $this->actingAs($user)
            ->postJson(route('subscription.cancel'))
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_halaman_subscription_menampilkan_tombol_upgrade_untuk_pengguna_standard(): void
    {
        $user = $this->subscriberWithPlan('standard', daysAgo: 5);

        $response = $this->actingAs($user)->get(route('subscription.page'));

        $response->assertOk()
            ->assertSee('Upgrade ke Premium')
            ->assertDontSee('Already Have Active Subscription')
            // Indikator aturan 1 bulan tampil karena baru 5 hari.
            ->assertSee('Batalkan Langganan');
    }

    /**
     * Ambang aturan bisa diatur lewat config/subscription.php tanpa ubah kode.
     * Ini penting karena paket 1 bulan hanya berumur 28-31 hari, sehingga
     * ambang 30 hari menyisakan jendela pembatalan yang sangat sempit.
     */
    public function test_ambang_pembatalan_bisa_diatur_lewat_config(): void
    {
        config(['subscription.min_active_days_before_cancel' => 7]);

        $user = $this->subscriberWithPlan('standard', daysAgo: 10);

        $this->actingAs($user)
            ->postJson(route('subscription.cancel'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertFalse($user->fresh()->hasActiveSubscription());
    }

    /**
     * Halaman paket harus tetap normal untuk tamu dan pengguna tanpa langganan
     * (tidak ada tombol batalkan, tombol beli biasa tetap tampil).
     */
    public function test_halaman_subscription_tetap_normal_untuk_tamu_dan_non_pelanggan(): void
    {
        $this->get(route('subscription.page'))
            ->assertOk()
            ->assertSee('Login to Subscribe')
            ->assertDontSee('Batalkan Langganan');

        $user = $this->student('non-subscriber@example.com');

        $this->actingAs($user)
            ->get(route('subscription.page'))
            ->assertOk()
            ->assertSee('Subscribe Standard')
            ->assertSee('Subscribe Premium')
            ->assertDontSee('Batalkan Langganan');
    }

    public function test_service_menghitung_status_pembatalan_dengan_benar(): void
    {
        $service = app(SubscriptionPlanService::class);

        $baru = $this->subscriberWithPlan('standard', daysAgo: 3, email: 'baru@example.com');
        $status = $service->cancellationStatus($baru);

        $this->assertTrue($status['has_subscription']);
        $this->assertFalse($status['allowed']);
        $this->assertSame(3, $status['days_active']);
        $this->assertSame(30, $status['minimum_days']);
        $this->assertNotNull($status['eligible_at']);

        $lama = $this->subscriberWithPlan('standard', daysAgo: 45, email: 'lama@example.com');
        $this->assertTrue($service->cancellationStatus($lama)['allowed']);
    }
}
