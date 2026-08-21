<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Panel samping guru harus punya gulir sendiri.
 *
 * .profile-sidebar sudah sticky sejak awal, tetapi tanpa batas tinggi. Isinya
 * lebih tinggi daripada area pandang di laptop biasa, dan elemen sticky yang
 * lebih tinggi dari scrollport tidak punya tempat untuk dipaku - ia ikut
 * tergulir. Aturan .teacher-sidebar membatasi tingginya setinggi layar dan
 * memberinya scrollbar sendiri.
 */
class TeacherSidebarScrollTest extends TestCase
{
    use RefreshDatabase;

    /** Semua halaman panel guru yang memasang sidebar. */
    private const HALAMAN = [
        'teacher.profile',
        'teacher.courses',
        'teacher.manage.content',
        'teacher.statistics',
        'teacher.finance.management',
        'teacher.notification-preferences',
        'teacher.notifications',
    ];

    private function teacher(): User
    {
        return User::create([
            'name' => 'Sidebar Teacher',
            'email' => 'sidebar-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    /** Kelas penanda terpasang di seluruh halaman panel guru. */
    public function test_setiap_halaman_guru_memakai_teacher_sidebar(): void
    {
        $teacher = $this->teacher();

        foreach (self::HALAMAN as $rute) {
            $this->actingAs($teacher)
                ->get(route($rute))
                ->assertOk()
                ->assertSee('profile-sidebar teacher-sidebar', false);
        }
    }

    /** Aturan gulir mandiri benar-benar dikirim ke browser. */
    public function test_sidebar_punya_batas_tinggi_dan_gulir_sendiri(): void
    {
        $html = $this->actingAs($this->teacher())
            ->get(route('teacher.profile'))
            ->assertOk()
            ->getContent();

        foreach ([
            '.teacher-sidebar',
            'position: sticky',
            'max-height: calc(100vh - 120px)',
            'overflow-y: auto',
            // Gulir sidebar tidak boleh merembet menggulirkan halaman.
            'overscroll-behavior: contain',
        ] as $aturan) {
            $this->assertStringContainsString($aturan, $html, "Aturan hilang: {$aturan}");
        }
    }

    /** Scrollbar tipis seperti panel Admin, untuk Firefox maupun Chromium. */
    public function test_scrollbar_tipis_diterapkan(): void
    {
        $html = $this->actingAs($this->teacher())
            ->get(route('teacher.profile'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('scrollbar-width: thin', $html);
        $this->assertStringContainsString('.teacher-sidebar::-webkit-scrollbar', $html);
    }

    /* ==============================================================
     | Guardrails
     ============================================================== */

    /**
     * Di layar sempit sidebar menumpuk di atas konten. Menempel dan membatasi
     * tinggi di sana akan memotong isinya, jadi harus dikembalikan ke aliran
     * normal lewat media query.
     */
    public function test_tampilan_sempit_dikembalikan_ke_aliran_normal(): void
    {
        $html = $this->actingAs($this->teacher())
            ->get(route('teacher.profile'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('@media (max-width: 991.98px)', $html);

        // Blok sempit harus melepas sticky dan batas tingginya.
        preg_match('/@media \(max-width: 991\.98px\) \{\s*\.teacher-sidebar \{(.*?)\}/s', $html, $m);

        $this->assertNotEmpty($m, 'Blok media query untuk layar sempit tidak ditemukan.');
        $this->assertStringContainsString('position: static', $m[1]);
        $this->assertStringContainsString('max-height: none', $m[1]);
    }

    /** Menu sidebar dan penanda halaman aktif tidak boleh ikut berubah. */
    public function test_menu_sidebar_tetap_utuh(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('teacher.statistics'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('My Courses')
            ->assertSee('Manage Content')
            ->assertSee('Statistics')
            ->assertSee('Finance Management')
            ->assertSee('Notification Preferences')
            ->assertSee('Logout Account')
            ->assertSee('profile-nav-item active', false);
    }

    /**
     * Halaman profil siswa memakai .profile-sidebar yang sama. Aturan gulir
     * baru harus tetap khusus guru, jadi penanda itu tidak boleh muncul.
     */
    public function test_sidebar_siswa_tidak_ikut_berubah(): void
    {
        $student = User::create([
            'name' => 'Sidebar Student',
            'email' => 'sidebar-student@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('profile-sidebar', false)
            ->assertDontSee('teacher-sidebar', false);
    }
}
