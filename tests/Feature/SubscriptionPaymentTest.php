<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Purchase;
use App\Models\SubscriptionPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Alur pembayaran langganan siswa.
 *
 * Bug utama yang ditutup di sini: setelah membayar, siswa dilempar ke
 * /invoice/{id} yang merender profile.invoice — view khusus pembelian course.
 * Invoice subscription tidak punya invoice_items sehingga view itu membaca
 * properti pada null dan halaman gagal dirender; QR Code dan invoice tidak
 * pernah tampil.
 */
class SubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $email = 'siswa-subs@example.com'): User
    {
        return User::create([
            'name' => 'Siswa Uji',
            'email' => $email,
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    /** Bayar satu paket dan kembalikan response JSON-nya. */
    private function pay(User $user, string $plan, array $overrides = [])
    {
        return $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->post('/subscription/process-payment', array_merge([
                'plan' => $plan,
                'payment_method' => 'qris',
                'discount_amount' => 0,
                'final_amount' => $plan === 'standard' ? 50000 : 150000,
            ], $overrides));
    }

    /* ==============================================================
     | Halaman checkout
     ============================================================== */

    public function test_checkout_page_opens_for_both_plans(): void
    {
        $user = $this->student();

        foreach (['standard', 'premium'] as $plan) {
            $response = $this->actingAs($user)->get('/subscription/payment/' . $plan);

            $response->assertStatus(200);
            $response->assertSee(ucfirst($plan) . ' Plan Subscription');
        }
    }

    public function test_checkout_page_only_offers_one_choose_payment_button(): void
    {
        $html = $this->actingAs($this->student())
            ->get('/subscription/payment/standard')
            ->getContent();

        // Sebelumnya tombol ini ditulis dua kali (Indonesia + Inggris), dan JS
        // hanya menyembunyikan yang pertama sehingga satu tombol tertinggal
        // di layar setelah metode dipilih.
        $this->assertSame(1, substr_count($html, 'class="btn btn-teal choose-payment"'));
        $this->assertStringContainsString('querySelectorAll(\'.choose-payment\')', $html);
    }

    /* ==============================================================
     | Proses pembayaran
     ============================================================== */

    public function test_payment_creates_pending_purchase_and_invoice(): void
    {
        $user = $this->student();

        $response = $this->pay($user, 'standard');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $purchase = SubscriptionPurchase::where('user_id', $user->id)->first();
        $this->assertNotNull($purchase, 'Record pembelian langganan tidak dibuat.');
        $this->assertSame('pending', $purchase->status);
        $this->assertSame('standard', $purchase->plan);
        $this->assertEquals(50000, (float) $purchase->final_amount);

        $invoice = Invoice::where('invoiceable_id', $purchase->id)
            ->where('invoiceable_type', SubscriptionPurchase::class)
            ->first();

        $this->assertNotNull($invoice, 'Invoice tidak dibuat.');
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('subscription', $invoice->type);
        $this->assertNotEmpty($invoice->invoice_number);
    }

    public function test_payment_response_carries_invoice_details_and_qris(): void
    {
        $user = $this->student();

        $payment = $this->pay($user, 'premium')->json('payment');

        $this->assertIsArray($payment, 'Response tidak membawa payload pembayaran.');

        foreach (['invoice_id', 'invoice_number', 'invoice_url', 'reference',
                  'items', 'created_at', 'subtotal', 'total', 'qris_url',
                  'whatsapp_url'] as $key) {
            $this->assertArrayHasKey($key, $payment, "Payload kehilangan: {$key}");
        }

        $this->assertStringContainsString('Paket Premium', $payment['items'][0]['name']);
        $this->assertSame('Rp150.000', $payment['total']);
        $this->assertStringContainsString('qris', strtolower((string) $payment['qris_url']));
    }

    public function test_amount_is_computed_on_the_server(): void
    {
        $user = $this->student();

        // final_amount & discount dari klien tidak boleh dipercaya.
        $this->pay($user, 'premium', ['final_amount' => 1, 'discount_amount' => 149999]);

        $purchase = SubscriptionPurchase::where('user_id', $user->id)->first();

        $this->assertEquals(150000, (float) $purchase->amount);
        $this->assertEquals(150000, (float) $purchase->final_amount);
        $this->assertEquals(0, (float) $purchase->discount_amount);
    }

    public function test_invalid_plan_is_rejected(): void
    {
        $response = $this->pay($this->student(), 'diamond');

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
        $this->assertSame(0, SubscriptionPurchase::count());
    }

    /* ==============================================================
     | Halaman invoice + QR Code
     ============================================================== */

    public function test_subscription_invoice_page_renders_with_qr_code(): void
    {
        $user = $this->student();
        $payment = $this->pay($user, 'standard')->json('payment');

        $response = $this->actingAs($user)->get('/invoice/' . $payment['invoice_id']);

        $response->assertStatus(200);
        $response->assertViewIs('profile.invoice-subscription');

        $html = $response->getContent();
        $this->assertStringContainsString('QRIS Payment', $html);
        $this->assertStringContainsString('images/qris-payment.jpeg', $html);
        $this->assertStringContainsString($payment['reference'], $html);
        $this->assertStringContainsString('Subscription Package Standard', $html);
    }

    public function test_premium_invoice_page_renders(): void
    {
        $user = $this->student();
        $payment = $this->pay($user, 'premium')->json('payment');

        $response = $this->actingAs($user)->get('/invoice/' . $payment['invoice_id']);

        $response->assertStatus(200);
        $response->assertSee('Subscription Package Premium');
        $response->assertSee('Rp150.000');
    }

    public function test_invoice_url_in_payload_points_to_a_working_page(): void
    {
        $user = $this->student();
        $payment = $this->pay($user, 'standard')->json('payment');

        // Tombol "Check Payment Status" memakai URL ini.
        $this->actingAs($user)->get($payment['invoice_url'])->assertStatus(200);
    }

    public function test_another_student_cannot_open_the_invoice(): void
    {
        $owner = $this->student('pemilik@example.com');
        $payment = $this->pay($owner, 'standard')->json('payment');

        $intruder = $this->student('penyusup@example.com');

        $this->actingAs($intruder)
            ->get('/invoice/' . $payment['invoice_id'])
            ->assertStatus(403);
    }

    /* ==============================================================
     | Tidak merusak invoice course
     ============================================================== */

    public function test_course_invoice_still_uses_its_own_view(): void
    {
        $teacher = User::create([
            'name' => 'Pengajar', 'email' => 'pengajar-inv@example.com',
            'password' => Hash::make('SecretPass123'), 'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        $student = $this->student();

        $class = ClassModel::create([
            'teacher_id' => $teacher->id,
            'name' => 'Kelas Berbayar',
            'description' => 'Kelas uji invoice course.',
            'price' => 200000,
            'status' => 'active',
        ]);

        $purchase = Purchase::create([
            'purchase_code' => 'ML-TEST01',
            'user_id' => $student->id,
            'class_id' => $class->id,
            'amount' => 200000,
            'status' => 'pending',
            'payment_method' => 'qris',
        ]);

        $invoice = Invoice::create([
            'user_id' => $student->id,
            'type' => 'course',
            'invoiceable_id' => $purchase->id,
            'invoiceable_type' => Purchase::class,
            'amount' => 200000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 200000,
            'currency' => 'IDR',
            'status' => 'pending',
            'payment_method' => 'qris',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'purchase_id' => $purchase->id,
            'item_name' => 'Kelas Berbayar',
            'amount' => 200000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 200000,
            'currency' => 'IDR',
        ]);

        $response = $this->actingAs($student)->get('/invoice/' . $invoice->id);

        $response->assertStatus(200);
        $response->assertViewIs('profile.invoice');
    }

    public function test_subscription_download_is_not_confused_with_a_course_purchase(): void
    {
        $user = $this->student();
        $this->pay($user, 'standard');
        $purchase = SubscriptionPurchase::where('user_id', $user->id)->first();

        // Tombol unduh pada halaman invoice mengirim ?type=subscription. Tanpa
        // parameter itu, id yang bertabrakan dengan id sebuah Purchase akan
        // menghasilkan dokumen course yang keliru.
        $response = $this->actingAs($user)
            ->get('/invoice/' . $purchase->id . '/download?type=subscription');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_purchase_history_link_opens_the_subscription_invoice(): void
    {
        $user = $this->student();
        $this->pay($user, 'standard');
        $purchase = SubscriptionPurchase::where('user_id', $user->id)->first();

        // Bentuk tautan pada halaman Purchase History.
        $response = $this->actingAs($user)
            ->get('/invoice/' . $purchase->id . '?type=subscription');

        $response->assertStatus(200);
        $response->assertViewIs('profile.invoice-subscription');
        $response->assertSee($purchase->purchase_code);
        $response->assertSee('QRIS Payment');
    }

    public function test_subscription_link_is_not_answered_with_a_course_invoice(): void
    {
        $owner = $this->student('pemilik2@example.com');
        $this->pay($owner, 'standard');
        $purchase = SubscriptionPurchase::where('user_id', $owner->id)->first();

        // Invoice course milik siswa lain dengan id yang sama tidak boleh
        // ikut terambil oleh tautan ?type=subscription.
        $other = $this->student('lain@example.com');

        $this->actingAs($other)
            ->get('/invoice/' . $purchase->id . '?type=subscription')
            ->assertStatus(403);
    }

    public function test_qris_asset_exists(): void
    {
        $this->assertFileExists(public_path(config('app.payment.qris_image_path')));
    }
}
