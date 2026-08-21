<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pembelian satu course secara langsung (tanpa paket langganan).
 *
 * Bug utama yang ditutup di sini: seluruh alur checkout course bergantung pada
 * session (latest_purchase_ids, checkout_items, checkout_total_amount),
 * sementara baris Purchase berstatus pending tersimpan permanen di database.
 * Begitu session habis, siswa terkunci — checkout menolak karena session
 * kosong, dan Buy Now ditolak karena sudah ada pending purchase.
 */
class CoursePurchaseTest extends TestCase
{
    use RefreshDatabase;

    private ?User $teacher = null;

    private function teacher(): User
    {
        return $this->teacher ??= User::create([
            'name' => 'Pengajar', 'email' => 'guru-course@example.com',
            'password' => Hash::make('SecretPass123'), 'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function student(string $email = 'siswa-course@example.com'): User
    {
        return User::create([
            'name' => 'Siswa Uji', 'email' => $email,
            'password' => Hash::make('SecretPass123'), 'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    private function course(string $name = 'Belajar Augmented Reality', int $price = 250000): ClassModel
    {
        return ClassModel::create([
            'teacher_id' => $this->teacher()->id,
            'name' => $name,
            'description' => 'Kelas uji pembelian satuan.',
            'price' => $price,
            'status' => 'active',
            'is_published' => true,
        ]);
    }

    private function pay(User $user)
    {
        return $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->post('/cart/process-payment', [
                'payment_method' => 'qris',
                'payment_provider' => 'qris',
            ]);
    }

    /* ==============================================================
     | Alur normal
     ============================================================== */

    public function test_buy_now_creates_pending_purchase_and_opens_checkout(): void
    {
        $student = $this->student();
        $course = $this->course();

        $response = $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);
        $response->assertRedirect(route('checkout'));

        $purchase = Purchase::where('user_id', $student->id)->first();
        $this->assertNotNull($purchase, 'Record pembelian tidak dibuat.');
        $this->assertSame('pending', $purchase->status);
        $this->assertEquals(250000, (float) $purchase->amount);
        $this->assertSame($course->id, $purchase->class_id);

        $this->actingAs($student)->get('/checkout')
            ->assertStatus(200)
            ->assertSee($course->name);
    }

    public function test_payment_creates_course_invoice_with_items(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);
        $response = $this->pay($student);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice, 'Invoice tidak dibuat.');
        $this->assertSame('course', $invoice->type);
        $this->assertSame('pending', $invoice->status);
        $this->assertEquals(250000, (float) $invoice->total_amount);
        $this->assertSame(1, $invoice->invoiceItems()->count());
    }

    public function test_payment_response_carries_invoice_details_and_qris(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);
        $payment = $this->pay($student)->json('payment');

        $this->assertIsArray($payment, 'Response tidak membawa payload pembayaran.');

        foreach (['invoice_id', 'invoice_number', 'invoice_url', 'reference',
                  'items', 'created_at', 'subtotal', 'total', 'qris_url',
                  'whatsapp_url'] as $key) {
            $this->assertArrayHasKey($key, $payment, "Payload kehilangan: {$key}");
        }

        // Judul course dan harganya harus tampil di modal.
        $this->assertSame($course->name, $payment['items'][0]['name']);
        $this->assertSame('Rp250.000', $payment['items'][0]['amount']);
        $this->assertSame('Rp250.000', $payment['total']);
        $this->assertStringContainsString('qris', strtolower((string) $payment['qris_url']));
    }

    public function test_course_invoice_page_shows_qr_code_and_course_title(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);
        $payment = $this->pay($student)->json('payment');

        $response = $this->actingAs($student)->get('/invoice/' . $payment['invoice_id']);

        $response->assertStatus(200);
        $response->assertViewIs('profile.invoice');

        $html = $response->getContent();
        $this->assertStringContainsString('QRIS Payment', $html);
        $this->assertStringContainsString('images/qris-payment.jpeg', $html);
        $this->assertStringContainsString($course->name, $html);
    }

    /* ==============================================================
     | Pemulihan setelah session checkout hilang
     ============================================================== */

    public function test_checkout_survives_a_lost_session(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);

        // Tutup browser / kembali esok hari: session checkout hilang.
        $this->flushSession();

        $this->actingAs($student)->get('/checkout')
            ->assertStatus(200)
            ->assertSee($course->name);
    }

    public function test_payment_survives_a_lost_session(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);
        $this->flushSession();

        $response = $this->pay($student);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertSame(1, Invoice::count());
    }

    public function test_buy_now_reuses_an_unpaid_pending_purchase(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);
        $this->flushSession();

        // Klik Buy Now lagi: dulu dipentalkan dengan "tunggu persetujuan admin".
        $this->actingAs($student)
            ->post('/cart/buy-now', ['course_id' => $course->id])
            ->assertRedirect(route('checkout'));

        // Tidak boleh menumpuk purchase baru untuk course yang sama.
        $this->assertSame(1, Purchase::where('user_id', $student->id)
            ->where('class_id', $course->id)
            ->where('status', 'pending')
            ->count());
    }

    public function test_buy_now_sends_student_to_the_existing_invoice(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);
        $payment = $this->pay($student)->json('payment');
        $this->flushSession();

        // Invoice sudah terbit: jangan buat nomor invoice baru yang membatalkan
        // tagihan yang mungkin sudah dibayar.
        $this->actingAs($student)
            ->post('/cart/buy-now', ['course_id' => $course->id])
            ->assertRedirect(route('invoice', $payment['invoice_id']));

        $this->assertSame(1, Invoice::count());
    }

    /* ==============================================================
     | Batasan yang harus tetap berlaku
     ============================================================== */

    public function test_student_with_lifetime_access_cannot_buy_again(): void
    {
        $student = $this->student();
        $course = $this->course();

        Purchase::create([
            'purchase_code' => 'ML-PAID01',
            'user_id' => $student->id,
            'class_id' => $course->id,
            'amount' => 250000,
            'status' => 'success',
        ]);

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);

        $this->assertSame(0, Purchase::where('status', 'pending')->count());
    }

    public function test_paid_purchase_is_not_pulled_back_into_checkout(): void
    {
        $student = $this->student();
        $course = $this->course();

        Purchase::create([
            'purchase_code' => 'ML-PAID02',
            'user_id' => $student->id,
            'class_id' => $course->id,
            'amount' => 250000,
            'status' => 'success',
        ]);

        $this->flushSession();

        // Tanpa pending purchase, checkout tidak boleh memulihkan apa pun.
        $this->actingAs($student)->get('/checkout')->assertRedirect(route('cart'));
    }

    public function test_recovery_only_touches_the_students_own_purchases(): void
    {
        $owner = $this->student('pemilik-course@example.com');
        $course = $this->course();

        $this->actingAs($owner)->post('/cart/buy-now', ['course_id' => $course->id]);

        $other = $this->student('siswa-lain@example.com');
        $this->flushSession();

        // Siswa lain tidak boleh mewarisi pembelian pending milik orang lain.
        $this->actingAs($other)->get('/checkout')->assertRedirect(route('cart'));
    }

    public function test_another_student_cannot_open_the_course_invoice(): void
    {
        $owner = $this->student('pemilik-inv@example.com');
        $course = $this->course();

        $this->actingAs($owner)->post('/cart/buy-now', ['course_id' => $course->id]);
        $payment = $this->pay($owner)->json('payment');

        $intruder = $this->student('penyusup-inv@example.com');

        $this->actingAs($intruder)
            ->get('/invoice/' . $payment['invoice_id'])
            ->assertStatus(403);
    }

    /* ==============================================================
     | Halaman checkout
     ============================================================== */

    public function test_cart_checkout_with_two_courses_lists_both_in_the_modal(): void
    {
        $student = $this->student();
        $first = $this->course('Kelas Pertama', 100000);
        $second = $this->course('Kelas Kedua', 150000);

        $this->actingAs($student)->post('/cart/prepare-checkout', [
            'course_ids' => [$first->id, $second->id],
        ])->assertRedirect(route('checkout'));

        $payment = $this->pay($student)->json('payment');

        $this->assertCount(2, $payment['items']);
        $this->assertSame('Kelas Pertama', $payment['items'][0]['name']);
        $this->assertSame('Kelas Kedua', $payment['items'][1]['name']);
        $this->assertSame('Rp250.000', $payment['total']);
    }

    public function test_checkout_page_loads_the_shared_payment_modal(): void
    {
        $student = $this->student();
        $course = $this->course();

        $this->actingAs($student)->post('/cart/buy-now', ['course_id' => $course->id]);
        $html = $this->actingAs($student)->get('/checkout')->getContent();

        $this->assertStringContainsString('id="paymentConfirmationModal"', $html);
        $this->assertStringContainsString('id="payQrisImage"', $html);
        $this->assertStringContainsString('assets/js/payment-modal.js', $html);
        $this->assertStringContainsString('assets/css/payment-modal.css', $html);
        $this->assertStringContainsString('Check Payment Status', $html);
    }
}
