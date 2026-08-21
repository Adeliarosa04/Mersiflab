<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tabel "Penjualan Terbaru" di halaman Finance Management guru hanya boleh
 * menampilkan transaksi yang benar-benar sudah dibayar.
 *
 * Kolom `status` pada tabel purchases berupa enum
 * ['pending', 'success', 'expired', 'cancelled'] dan hanya 'success' yang
 * berarti lunas - status itu pula yang menambah saldo guru.
 */
class TeacherFinanceRecentSalesTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::create([
            'name' => 'Finance Teacher',
            'email' => 'finance-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function student(string $nama, string $email): User
    {
        return User::create([
            'name' => $nama,
            'email' => $email,
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    private function courseFor(User $teacher, string $nama): ClassModel
    {
        return ClassModel::create([
            'teacher_id' => $teacher->id,
            'name' => $nama,
            'description' => 'Kursus untuk menguji tabel penjualan.',
            'category' => 'Web',
            'price' => 100000,
            'is_published' => true,
        ]);
    }

    private function purchase(User $pembeli, ClassModel $course, string $status, int $amount): Purchase
    {
        return Purchase::create([
            'purchase_code' => Purchase::generatePurchaseCode(),
            'user_id' => $pembeli->id,
            'class_id' => $course->id,
            'status' => $status,
            'amount' => $amount,
        ]);
    }

    /**
     * Menyiapkan satu guru dengan empat transaksi - satu per status.
     *
     * @return array{0: User, 1: array<string, User>}
     */
    private function skenario(): array
    {
        $teacher = $this->teacher();
        $course = $this->courseFor($teacher, 'Kursus Keuangan');

        $pembeli = [
            'success' => $this->student('Siswa Lunas', 'lunas@example.com'),
            'pending' => $this->student('Siswa Tertunda', 'tertunda@example.com'),
            'expired' => $this->student('Siswa Kedaluwarsa', 'kedaluwarsa@example.com'),
            'cancelled' => $this->student('Siswa Batal', 'batal@example.com'),
        ];

        $this->purchase($pembeli['success'], $course, 'success', 150000);
        $this->purchase($pembeli['pending'], $course, 'pending', 20000000);
        $this->purchase($pembeli['expired'], $course, 'expired', 30000);
        $this->purchase($pembeli['cancelled'], $course, 'cancelled', 40000);

        return [$teacher, $pembeli];
    }

    /* ==============================================================
     | Inti perbaikan
     ============================================================== */

    public function test_hanya_transaksi_sukses_yang_tampil(): void
    {
        [$teacher, $pembeli] = $this->skenario();

        $response = $this->actingAs($teacher)
            ->get(route('teacher.finance.management'))
            ->assertOk();

        $response->assertSee($pembeli['success']->name);

        foreach (['pending', 'expired', 'cancelled'] as $status) {
            $response->assertDontSee($pembeli[$status]->name);
        }

        // Nominal transaksi tertunda Rp20.000.000 tidak boleh bocor ke halaman.
        $response->assertDontSee('20.000.000');
    }

    public function test_controller_menyaring_di_level_query(): void
    {
        [$teacher] = $this->skenario();

        $recentPurchases = $this->actingAs($teacher)
            ->get(route('teacher.finance.management'))
            ->assertOk()
            ->viewData('recentPurchases');

        $this->assertCount(1, $recentPurchases);
        $this->assertSame(['success'], $recentPurchases->pluck('status')->unique()->values()->all());
    }

    /** Transaksi milik guru lain tetap tidak boleh ikut terbawa. */
    public function test_transaksi_guru_lain_tidak_ikut(): void
    {
        [$teacher] = $this->skenario();

        $guruLain = User::create([
            'name' => 'Guru Lain',
            'email' => 'guru-lain@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        $pembeliLain = $this->student('Siswa Guru Lain', 'siswa-lain@example.com');
        $this->purchase($pembeliLain, $this->courseFor($guruLain, 'Kursus Guru Lain'), 'success', 90000);

        $this->actingAs($teacher)
            ->get(route('teacher.finance.management'))
            ->assertOk()
            ->assertDontSee('Siswa Guru Lain');
    }

    /* ==============================================================
     | Guardrails
     ============================================================== */

    /** Kolom tabel tidak boleh berubah. */
    public function test_kolom_tabel_tetap_utuh(): void
    {
        [$teacher] = $this->skenario();

        $this->actingAs($teacher)
            ->get(route('teacher.finance.management'))
            ->assertOk()
            ->assertSee('Penjualan Terbaru')
            ->assertSee('Pelanggan')
            ->assertSee('Kursus')
            ->assertSee('Jumlah')
            ->assertSee('Tanggal');
    }

    /**
     * Halaman ini hanya membaca. Transaksi berstatus apa pun harus tetap utuh
     * di database supaya alur pembelian siswa tidak terpengaruh.
     */
    public function test_transaksi_siswa_tidak_diubah(): void
    {
        [$teacher] = $this->skenario();

        $this->actingAs($teacher)->get(route('teacher.finance.management'))->assertOk();

        foreach (['success', 'pending', 'expired', 'cancelled'] as $status) {
            $this->assertDatabaseHas('purchases', ['status' => $status]);
        }

        $this->assertSame(4, Purchase::count());
    }

    /**
     * Kartu ringkasan memakai saldo guru, yang hanya bertambah dari transaksi
     * 'success'. Jadi angka di kartu dan isi tabel bersumber dari data yang
     * sama - tidak ada lagi dua versi angka pendapatan.
     */
    public function test_ringkasan_saldo_hanya_dari_transaksi_sukses(): void
    {
        [$teacher] = $this->skenario();

        $balance = $this->actingAs($teacher)
            ->get(route('teacher.finance.management'))
            ->assertOk()
            ->viewData('balance');

        // Rp20.000.000 dari transaksi tertunda tidak boleh ikut terhitung.
        $this->assertLessThan(20000000, (float) $balance->total_earnings);
    }
}
