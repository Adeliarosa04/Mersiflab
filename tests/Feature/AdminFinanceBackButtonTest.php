<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Halaman Detail Statistik Pengajar (Financial Management) harus punya tombol
 * kembali ke halaman utama Financial Management.
 */
class AdminFinanceBackButtonTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Finance Admin',
            'email' => 'finance-admin@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

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

    public function test_halaman_detail_pengajar_punya_tombol_kembali_ke_financial_management(): void
    {
        $teacher = $this->teacher();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.finance.teacher', $teacher->id));

        $response->assertOk()
            ->assertSee('Kembali ke Financial Management')
            // Tautan mengarah ke halaman utama Financial Management via GET.
            ->assertSee(route('admin.finance.dashboard'))
            // Judul halaman yang sudah ada tidak boleh hilang.
            ->assertSee('Financial Management - ' . $teacher->name);
    }

    public function test_halaman_utama_financial_management_tetap_bisa_dibuka(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.finance.dashboard'))
            ->assertOk()
            ->assertSee('Financial Dashboard')
            ->assertSee('Teacher Statistics');
    }
}
