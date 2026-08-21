<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\CommissionSetting;
use App\Models\Purchase;
use App\Models\TeacherWithdrawal;
use App\Models\User;
use App\Support\TeacherEarnings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pendapatan guru adalah nominal transaksi SETELAH dipotong komisi aplikasi.
 *
 * Sebelumnya Purchase::updateTeacherBalance() memakai
 *     $purchase->teacher_earning ?? $purchase->amount
 * sedangkan kolom teacher_earning tidak pernah diisi alur pembelian mana pun,
 * sehingga guru selalu dikreditkan 100% harga kursus.
 */
class TeacherNetEarningsTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(string $email = 'net-teacher@example.com'): User
    {
        return User::create([
            'name' => 'Net Teacher',
            'email' => $email,
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function student(string $email): User
    {
        return User::create([
            'name' => 'Net Student ' . $email,
            'email' => $email,
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    private function courseFor(User $teacher): ClassModel
    {
        return ClassModel::create([
            'teacher_id' => $teacher->id,
            'name' => 'Kursus Komisi',
            'description' => 'Kursus untuk menguji komisi.',
            'category' => 'Web',
            'price' => 1000000,
            'is_published' => true,
        ]);
    }

    private function purchase(ClassModel $course, string $status, int $amount, string $email): Purchase
    {
        return Purchase::create([
            'purchase_code' => Purchase::generatePurchaseCode(),
            'user_id' => $this->student($email)->id,
            'class_id' => $course->id,
            'status' => $status,
            'amount' => $amount,
        ]);
    }

    /**
     * Skenario yang meniru data asli: bruto Rp1.070.000 dari transaksi lunas,
     * satu transaksi tertunda yang harus diabaikan, dan penarikan yang sudah
     * disetujui sebesar Rp500.000.
     */
    private function skenario(): User
    {
        $teacher = $this->teacher();
        $course = $this->courseFor($teacher);

        $this->purchase($course, 'success', 1000000, 'a@example.com');
        $this->purchase($course, 'success', 50000, 'b@example.com');
        $this->purchase($course, 'success', 20000, 'c@example.com');
        $this->purchase($course, 'pending', 20000000, 'd@example.com');

        TeacherWithdrawal::create([
            'teacher_id' => $teacher->id,
            'amount' => 500000,
            'bank_name' => 'BCA',
            'bank_account_name' => 'Net Teacher',
            'bank_account_number' => '1234567890',
            'status' => 'approved',
        ]);

        return $teacher;
    }

    /* ==============================================================
     | Rumus inti
     ============================================================== */

    public function test_pendapatan_bersih_memakai_persentase_guru(): void
    {
        $ringkasan = TeacherEarnings::summaryFor($this->skenario()->id);

        // Bawaan: platform 20%, guru 80%.
        $this->assertEqualsWithDelta(1070000, $ringkasan['gross'], 0.01);
        $this->assertEqualsWithDelta(856000, $ringkasan['net'], 0.01);
        $this->assertEqualsWithDelta(214000, $ringkasan['platform_commission'], 0.01);
        $this->assertEqualsWithDelta(500000, $ringkasan['withdrawn'], 0.01);
        $this->assertEqualsWithDelta(356000, $ringkasan['available'], 0.01);
    }

    /** Transaksi tertunda tidak boleh ikut dihitung sebagai pendapatan. */
    public function test_transaksi_belum_lunas_diabaikan(): void
    {
        $ringkasan = TeacherEarnings::summaryFor($this->skenario()->id);

        $this->assertLessThan(20000000, $ringkasan['gross']);
    }

    /**
     * Guardrail 2: bila ada pengaturan komisi kustom, persentasenya yang dipakai.
     */
    public function test_pengaturan_komisi_kustom_dipakai(): void
    {
        $teacher = $this->teacher('kustom@example.com');
        $course = $this->courseFor($teacher);

        CommissionSetting::create([
            'teacher_id' => $teacher->id,
            'commission_type' => 'per_course',
            'platform_percentage' => 30,
            'teacher_percentage' => 70,
            'min_amount' => 50000,
            'is_active' => true,
        ]);

        $this->purchase($course, 'success', 1000000, 'kustom-beli@example.com');

        $ringkasan = TeacherEarnings::summaryFor($teacher->id);

        $this->assertEqualsWithDelta(700000, $ringkasan['net'], 0.01);
        $this->assertEqualsWithDelta(300000, $ringkasan['platform_commission'], 0.01);
        $this->assertSame(70.0, $ringkasan['teacher_percentage']);
    }

    /* ==============================================================
     | Tampilan kartu
     ============================================================== */

    public function test_kartu_menampilkan_angka_bersih(): void
    {
        $response = $this->actingAs($this->skenario())
            ->get(route('teacher.finance.management'))
            ->assertOk();

        $response->assertSee('856.000');   // Total Pendapatan (bersih)
        $response->assertSee('356.000');   // Saldo Saat Ini
        $response->assertSee('500.000');   // Sudah Ditarik

        // Angka bruto lama tidak boleh muncul lagi sebagai pendapatan/saldo.
        $response->assertDontSee('1.070.000');
        $response->assertDontSee('570.000');
    }

    public function test_saldo_tersedia_untuk_ditarik_ikut_menyesuaikan(): void
    {
        $html = $this->actingAs($this->skenario())
            ->get(route('teacher.finance.management'))
            ->assertOk()
            ->getContent();

        // Modal penarikan memakai $currentBalance yang sama, termasuk batas
        // maksimal di sisi JavaScript.
        $this->assertStringContainsString('Saldo yang tersedia untuk ditarik', $html);
        $this->assertStringContainsString("parseFloat('356000')", $html);
    }

    /* ==============================================================
     | Konsistensi dengan validasi penarikan
     ============================================================== */

    /**
     * Angka di layar dan angka yang divalidasi server harus sama. Kalau tidak,
     * guru bisa menarik lebih banyak daripada yang ditampilkan kepadanya.
     */
    public function test_penarikan_melebihi_saldo_bersih_ditolak(): void
    {
        $teacher = $this->skenario();

        // Rp400.000 masih di bawah saldo BRUTO lama (Rp570.000) tetapi di atas
        // saldo bersih yang benar (Rp356.000), jadi harus ditolak.
        $this->actingAs($teacher)
            ->post(route('teacher.withdrawal.request'), [
                'amount' => 400000,
                'bank_name' => 'BCA',
                'bank_account_name' => 'Net Teacher',
                'bank_account_number' => '1234567890',
            ])
            ->assertRedirect();

        $this->assertSame(1, TeacherWithdrawal::where('teacher_id', $teacher->id)->count());
    }

    public function test_penarikan_dalam_batas_saldo_bersih_diterima(): void
    {
        $teacher = $this->skenario();

        $this->actingAs($teacher)
            ->post(route('teacher.withdrawal.request'), [
                'amount' => 300000,
                'bank_name' => 'BCA',
                'bank_account_name' => 'Net Teacher',
                'bank_account_number' => '1234567890',
            ]);

        $this->assertDatabaseHas('teacher_withdrawals', [
            'teacher_id' => $teacher->id,
            'amount' => 300000,
            'status' => 'pending',
        ]);
    }

    /* ==============================================================
     | Guardrails
     ============================================================== */

    /** Guardrail 1: nominal penarikan yang sudah diajukan tidak boleh berubah. */
    public function test_histori_penarikan_tidak_diubah(): void
    {
        $teacher = $this->skenario();

        $this->actingAs($teacher)->get(route('teacher.finance.management'))->assertOk();

        $this->assertDatabaseHas('teacher_withdrawals', [
            'teacher_id' => $teacher->id,
            'amount' => 500000,
            'status' => 'approved',
        ]);
    }

    /**
     * Transaksi baru harus langsung mencatat rincian komisinya, supaya tarif
     * yang berlaku saat itu tetap melekat walau pengaturan berubah kemudian.
     */
    public function test_transaksi_baru_menyimpan_rincian_komisi(): void
    {
        $teacher = $this->teacher('rincian@example.com');
        $course = $this->courseFor($teacher);

        $purchase = $this->purchase($course, 'success', 1000000, 'rincian-beli@example.com');

        $purchase->refresh();

        $this->assertEqualsWithDelta(800000, (float) $purchase->teacher_earning, 0.01);
        $this->assertEqualsWithDelta(200000, (float) $purchase->platform_commission, 0.01);

        // Saldo tersimpan pun ikut bersih, bukan bruto.
        $this->assertEqualsWithDelta(800000, (float) $teacher->fresh()->teacherBalance->total_earnings, 0.01);
    }

    /**
     * Perintah penyelaras menurunkan saldo lama yang terlanjur bruto, tanpa
     * menyentuh total_withdrawn.
     */
    public function test_perintah_penyelaras_mengoreksi_saldo_bruto_lama(): void
    {
        $teacher = $this->skenario();

        // Meniru kondisi sebelum perbaikan: saldo tersimpan masih bruto.
        $balance = $teacher->teacherBalance;
        $balance->update([
            'total_earnings' => 1070000,
            'total_withdrawn' => 500000,
            'balance' => 570000,
        ]);

        $this->artisan('finance:update-balances')->assertExitCode(0);

        $balance->refresh();

        $this->assertEqualsWithDelta(856000, (float) $balance->total_earnings, 0.01);
        $this->assertEqualsWithDelta(356000, (float) $balance->balance, 0.01);

        // Histori penarikan tidak boleh disentuh.
        $this->assertEqualsWithDelta(500000, (float) $balance->total_withdrawn, 0.01);
    }
}
