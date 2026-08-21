<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\User;
use App\Support\BackUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Navigasi tombol "Kembali" / "Cancel".
 *
 * Fokus utama: tombol Cancel pada halaman Course Information (guru) harus
 * kembali ke halaman asal, bukan selalu melompat ke Manage Content.
 */
class BackNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::create([
            'name' => 'Nav Teacher',
            'email' => 'nav-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Nav Admin',
            'email' => 'nav-admin@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function course(User $teacher): ClassModel
    {
        return ClassModel::create([
            'teacher_id' => $teacher->id,
            'name' => 'Kursus Navigasi',
            'description' => 'Kursus untuk menguji tombol kembali.',
            'category' => 'Web',
            'price' => 100000,
            'is_published' => true,
        ]);
    }

    // ============ BUG UTAMA: Cancel di Course Information ============

    public function test_cancel_course_information_kembali_ke_halaman_asal(): void
    {
        $teacher = $this->teacher();
        $course = $this->course($teacher);

        $origin = route('teacher.chapters.index', $course->id);

        $response = $this->actingAs($teacher)
            ->get(route('teacher.classes.edit', $course->id), ['referer' => $origin]);

        $response->assertOk()
            // Kembali ke tempat asal...
            ->assertSee('href="' . $origin . '"', false)
            // ...bukan melompat ke Manage Content.
            ->assertDontSee('href="' . route('teacher.manage.content') . '" class="btn btn-outline-secondary"', false);
    }

    public function test_cancel_course_information_dari_my_courses_guru(): void
    {
        $teacher = $this->teacher();
        $course = $this->course($teacher);

        $this->actingAs($teacher)
            ->get(route('teacher.classes.edit', $course->id), ['referer' => route('teacher.courses')])
            ->assertOk()
            ->assertSee('href="' . route('teacher.courses') . '"', false);
    }

    public function test_cancel_course_information_fallback_aman_tanpa_referer(): void
    {
        $teacher = $this->teacher();
        $course = $this->course($teacher);

        // Tanpa halaman asal, jatuh ke daftar chapter course ini (bukan
        // langsung keluar ke Manage Content).
        $this->actingAs($teacher)
            ->get(route('teacher.classes.edit', $course->id))
            ->assertOk()
            ->assertSee('href="' . route('teacher.chapters.index', $course->id) . '"', false);
    }

    /**
     * Setelah validasi gagal, halaman form dirender ulang dan referer-nya
     * menjadi form itu sendiri. Tombol Cancel tidak boleh berputar ke
     * halaman yang sama.
     */
    public function test_cancel_tidak_berputar_ke_halaman_form_itu_sendiri(): void
    {
        $teacher = $this->teacher();
        $course = $this->course($teacher);

        $formUrl = route('teacher.classes.edit', $course->id);

        $this->actingAs($teacher)
            ->get($formUrl, ['referer' => $formUrl])
            ->assertOk()
            ->assertSee('href="' . route('teacher.chapters.index', $course->id) . '"', false);
    }

    // ============ PENYELARASAN GLOBAL ============

    public function test_tombol_kembali_admin_mengikuti_halaman_asal(): void
    {
        $admin = $this->admin();
        $target = User::create([
            'name' => 'Murid Nav',
            'email' => 'murid-nav@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        // Datang dari daftar siswa dengan query string: query harus ikut kembali.
        $origin = route('admin.students.index') . '?page=2';

        $this->actingAs($admin)
            ->get(route('admin.students.show', $target->id), ['referer' => $origin])
            ->assertOk()
            ->assertSee('href="' . $origin . '"', false);
    }

    public function test_tombol_kembali_menolak_referer_dari_situs_lain(): void
    {
        $admin = $this->admin();
        $target = User::create([
            'name' => 'Murid Nav 2',
            'email' => 'murid-nav2@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.show', $target->id), ['referer' => 'https://situs-lain.example.com/x'])
            ->assertOk()
            ->assertSee('href="' . route('admin.students.index') . '"', false)
            ->assertDontSee('situs-lain.example.com', false);
    }

    // ============ UNIT: aturan BackUrl ============

    public function test_backurl_menolak_halaman_form_sebagai_tujuan_kembali(): void
    {
        $fallback = route('teacher.manage.content');

        $this->get('/', ['referer' => url('/teacher/classes/create')]);

        // Referer berupa halaman form harus ditolak.
        $request = \Illuminate\Http\Request::create(url('/teacher/classes/9/edit'), 'GET');
        $request->headers->set('referer', url('/teacher/classes/create'));
        app()->instance('request', $request);
        url()->setRequest($request);

        $this->assertSame($fallback, BackUrl::to($fallback));
    }

    public function test_backurl_menerima_halaman_daftar_beserta_query_string(): void
    {
        $origin = url('/admin/testimonials?status=pending');

        $request = \Illuminate\Http\Request::create(url('/admin/testimonials/5'), 'GET');
        $request->headers->set('referer', $origin);
        app()->instance('request', $request);
        url()->setRequest($request);

        $this->assertSame($origin, BackUrl::to(url('/admin/testimonials')));
    }
}
