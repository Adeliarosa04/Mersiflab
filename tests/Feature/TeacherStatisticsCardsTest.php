<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Kartu statistik di halaman Statistics guru tampil tanpa ikon dekoratif.
 *
 * Ikon di sisi kanan tiap kartu dihapus demi tampilan yang lebih bersih.
 * Pengujian menjaga dua sisi sekaligus: ikon benar-benar hilang, dan label
 * maupun angkanya tetap utuh.
 */
class TeacherStatisticsCardsTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::create([
            'name' => 'Statistik Teacher',
            'email' => 'statistik-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function courseFor(User $teacher): ClassModel
    {
        return ClassModel::create([
            'teacher_id' => $teacher->id,
            'name' => 'Kursus Statistik',
            'description' => 'Kursus untuk menguji kartu statistik.',
            'category' => 'Web',
            'price' => 100000,
            'is_published' => true,
        ]);
    }

    /** Delapan kartu statistik tidak boleh menyisakan satu pun ikon. */
    public function test_kartu_statistik_tanpa_ikon(): void
    {
        $teacher = $this->teacher();
        $this->courseFor($teacher);

        $html = $this->actingAs($teacher)
            ->get(route('teacher.statistics'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('stat-icon', $html);

        // Tiap kartu harus polos: hanya label dan angka di dalamnya.
        preg_match_all('/<div class="stat-card">(.*?)<\/div>\s*<\/div>/s', $html, $cards);

        $this->assertCount(8, $cards[1], 'Seharusnya ada 8 kartu statistik.');

        foreach ($cards[1] as $isi) {
            $this->assertStringNotContainsString('<i ', $isi);
            $this->assertStringNotContainsString('<svg', $isi);
            $this->assertStringNotContainsString('<img', $isi);
        }
    }

    /** Guardrail: label dan angka besar wajib tetap ada. */
    public function test_label_dan_angka_tetap_utuh(): void
    {
        $teacher = $this->teacher();
        $this->courseFor($teacher);

        $response = $this->actingAs($teacher)->get(route('teacher.statistics'))->assertOk();

        foreach ([
            'Total Courses',
            'Total Chapters',
            'Total Modules',
            'Total Students',
            'Total Enrollments',
            'Avg. Completion Rate',
            'Published Courses',
            'Total Pendapatan',
        ] as $label) {
            $response->assertSee($label);
        }

        // Angka besar tetap dirender di kelas yang sama.
        $this->assertSame(
            8,
            preg_match_all('/class="stat-value"|class="stat-value" style=/', $response->getContent())
        );

        // Nilai yang dihitung controller benar-benar tercetak.
        $response->assertSee('0.0%')->assertSee('Rp');
    }

    /** Guardrail: sidebar dan judul halaman tidak ikut terhapus. */
    public function test_sidebar_dan_header_tetap_utuh(): void
    {
        $teacher = $this->teacher();

        $this->actingAs($teacher)
            ->get(route('teacher.statistics'))
            ->assertOk()
            ->assertSee('profile-sidebar', false)
            ->assertSee(route('teacher.profile'), false)
            ->assertSee(route('teacher.courses'), false)
            ->assertSee(route('teacher.manage.content'), false)
            ->assertSee('profile-nav-item active', false)
            // Ikon di luar kartu statistik (judul grafik, tabel) tetap dipakai.
            ->assertSee('fa-chart-line', false)
            ->assertSee('fa-graduation-cap', false);
    }
}
