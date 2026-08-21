<?php

namespace Tests\Feature;

use App\Models\TeacherWithdrawal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Label status penarikan harus mencerminkan keadaan sebenarnya.
 *
 * Status 'approved' adalah keadaan AKHIR: bukti transfer sudah diunggah, saldo
 * guru sudah dipotong, dan processed_at terisi. Sebelumnya label untuk status
 * itu ditulis 'Diproses', sehingga guru maupun admin mengira dananya masih
 * berjalan padahal sudah cair.
 */
class WithdrawalStatusLabelTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::create([
            'name' => 'Label Teacher',
            'email' => 'label-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Label Admin',
            'email' => 'label-admin@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function withdrawal(User $teacher, string $status): TeacherWithdrawal
    {
        $withdrawal = TeacherWithdrawal::create([
            'teacher_id' => $teacher->id,
            'amount' => 500000,
            'bank_name' => 'BCA',
            'bank_account_name' => 'Label Teacher',
            'bank_account_number' => '1234567890',
        ]);

        $withdrawal->forceFill(['status' => $status])->save();

        return $withdrawal->refresh();
    }

    /* ==============================================================
     | Pemetaan label
     ============================================================== */

    public function test_setiap_status_punya_label_yang_benar(): void
    {
        $teacher = $this->teacher();

        $harapan = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'processed' => 'Diproses',
            'rejected' => 'Ditolak',
        ];

        foreach ($harapan as $status => $label) {
            $this->assertSame(
                $label,
                $this->withdrawal($teacher, $status)->status_label,
                "Label untuk status '{$status}' tidak sesuai."
            );
        }
    }

    /** Penarikan yang sudah cair tidak boleh lagi berbunyi "Diproses". */
    public function test_status_disetujui_bukan_diproses(): void
    {
        $withdrawal = $this->withdrawal($this->teacher(), 'approved');

        $this->assertSame('Disetujui', $withdrawal->status_label);
        $this->assertNotSame('Diproses', $withdrawal->status_label);
    }

    /**
     * Guardrail 2: warna badge harus sama bahasa visualnya di kedua peran.
     * Sisi guru memakai bg-{status_badge}, jadi 'approved' wajib hijau.
     */
    public function test_warna_badge_status_disetujui_hijau(): void
    {
        $teacher = $this->teacher();

        $this->assertSame('success', $this->withdrawal($teacher, 'approved')->status_badge);
        $this->assertSame('warning', $this->withdrawal($teacher, 'pending')->status_badge);
        $this->assertSame('danger', $this->withdrawal($teacher, 'rejected')->status_badge);
        $this->assertSame('info', $this->withdrawal($teacher, 'processed')->status_badge);
    }

    /* ==============================================================
     | Tampilan halaman
     ============================================================== */

    public function test_tabel_admin_menampilkan_disetujui_dengan_badge_hijau(): void
    {
        $teacher = $this->teacher();
        $this->withdrawal($teacher, 'approved');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.finance.teacher', ['teacherId' => $teacher->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Disetujui', $html);
        $this->assertStringNotContainsString('Diproses', $html);

        // Palet pastel hijau dipertahankan seperti sebelumnya.
        $this->assertStringContainsString('background: #d4edda; color: #155724', $html);
    }

    /** Status ditolak tetap merah, status menunggu tetap kuning. */
    public function test_warna_lain_pada_tabel_admin_tidak_berubah(): void
    {
        $teacher = $this->teacher();
        $this->withdrawal($teacher, 'pending');
        $this->withdrawal($teacher, 'rejected');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.finance.teacher', ['teacherId' => $teacher->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('background: #fff3cd; color: #856404', $html);
        $this->assertStringContainsString('background: #f8d7da; color: #721c24', $html);
    }

    /** Guardrail 1: kolom tabel Withdrawal History tidak berubah. */
    public function test_kolom_tabel_withdrawal_history_tetap_utuh(): void
    {
        $teacher = $this->teacher();
        $this->withdrawal($teacher, 'approved');

        $this->actingAs($this->admin())
            ->get(route('admin.finance.teacher', ['teacherId' => $teacher->id]))
            ->assertOk()
            ->assertSee('Withdrawal Code')
            ->assertSee('Amount')
            ->assertSee('Bank')
            ->assertSee('Status')
            ->assertSee('Action');
    }

    /** Guardrail 2: guru melihat label yang sama dengan admin. */
    public function test_label_konsisten_di_sisi_guru(): void
    {
        $teacher = $this->teacher();
        $this->withdrawal($teacher, 'approved');

        $this->actingAs($teacher)
            ->get(route('teacher.finance.management'))
            ->assertOk()
            ->assertSee('Disetujui')
            ->assertSee('bg-success', false);
    }
}
