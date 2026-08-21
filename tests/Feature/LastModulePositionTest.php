<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\ClassModel;
use App\Models\Module;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Saat siswa menekan "Kembali ke Course" dari halaman modul, halaman detail
 * course harus menyorot chapter tempat modul terakhir itu berada.
 */
class LastModulePositionTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::create([
            'name' => 'Modul Teacher',
            'email' => 'modul-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function student(): User
    {
        return User::create([
            'name' => 'Modul Student',
            'email' => 'modul-student@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @return array{0: User, 1: ClassModel, 2: array<int, Chapter>, 3: array<int, Module>}
     */
    private function courseWithChapters(): array
    {
        $student = $this->student();
        $teacher = $this->teacher();

        $course = ClassModel::create([
            'teacher_id' => $teacher->id,
            'name' => 'Kursus Dengan Chapter',
            'description' => 'Kursus untuk menguji posisi modul terakhir.',
            'category' => 'Web',
            'price' => 100000,
            'is_published' => true,
        ]);

        Purchase::create([
            'purchase_code' => Purchase::generatePurchaseCode(),
            'user_id' => $student->id,
            'class_id' => $course->id,
            'status' => 'success',
            'amount' => 100000,
            'final_amount' => 100000,
        ]);

        // ModuleViewController memeriksa enrollment, bukan hanya purchase.
        \Illuminate\Support\Facades\DB::table('class_student')->insert([
            'class_id' => $course->id,
            'user_id' => $student->id,
            'progress' => 0,
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $chapters = [];
        $modules = [];

        for ($c = 1; $c <= 2; $c++) {
            // Chapter & modul hanya tampil untuk siswa bila is_published true
            // dan approval_status-nya approved (lihat CourseController@detail).
            $chapter = Chapter::create([
                'class_id' => $course->id,
                'title' => "Chapter {$c}",
                'order' => $c,
                'is_published' => true,
            ]);

            for ($m = 1; $m <= 2; $m++) {
                $module = Module::create([
                    'chapter_id' => $chapter->id,
                    'title' => "Modul {$c}.{$m}",
                    'type' => 'text',
                    'order' => $m,
                    'is_published' => true,
                ]);

                // approval_status tidak termasuk $fillable pada model Module,
                // jadi harus di-set terpisah agar modul dianggap tayang.
                $module->approval_status = 'approved';
                $module->save();

                $modules[] = $module;
            }

            $chapters[] = $chapter;
        }

        return [$student, $course, $chapters, $modules];
    }

    public function test_setiap_chapter_membawa_daftar_id_modulnya(): void
    {
        [$student, $course, $chapters, $modules] = $this->courseWithChapters();

        $response = $this->actingAs($student)->get(route('course.detail', $course->id));

        $response->assertOk();

        foreach ($chapters as $chapter) {
            $response->assertSee('data-chapter-id="' . $chapter->id . '"', false);
            $response->assertSee('id="chapter-card-' . $chapter->id . '"', false);
        }

        // Chapter pertama memuat id kedua modulnya.
        $response->assertSee(
            'data-module-ids="' . $modules[0]->id . ',' . $modules[1]->id . '"',
            false
        );
    }

    public function test_tombol_kembali_ke_course_membawa_parameter_modul(): void
    {
        [$student, $course, $chapters, $modules] = $this->courseWithChapters();
        $target = $modules[2]; // modul pertama di chapter kedua

        $response = $this->actingAs($student)
            ->get(route('module.show', [$course->id, $chapters[1]->id, $target->id]));

        $response->assertOk()
            ->assertSee('Kembali ke Course')
            ->assertSee(route('course.detail', $course->id) . '?module=' . $target->id, false)
            ->assertSee('data-last-module-id="' . $target->id . '"', false);
    }

    /**
     * Halaman modul TIDAK boleh lagi menyimpan penanda modul terakhir.
     *
     * Penanda sessionStorage itu bertahan 12 jam dan dibaca halaman detail
     * course sebagai cadangan pemulihan posisi, sehingga kunjungan baru dari
     * katalog ikut tergulir otomatis ke tengah halaman. Pemulihan posisi kini
     * hanya lewat parameter ?module= yang eksplisit.
     */
    public function test_halaman_modul_tidak_menyimpan_penanda_sesi(): void
    {
        [$student, $course, $chapters, $modules] = $this->courseWithChapters();

        $this->actingAs($student)
            ->get(route('module.show', [$course->id, $chapters[0]->id, $modules[0]->id]))
            ->assertOk()
            ->assertDontSee('mersif.lastModule.course.' . $course->id, false)
            ->assertDontSee("sessionStorage.setItem('mersif.lastModule", false);
    }

    /**
     * Halaman detail harus terbuka dari paling atas.
     *
     * scrollRestoration dimatikan sinkron di awal konten (bukan menunggu
     * DOMContentLoaded) supaya browser tidak sempat memulihkan posisi gulir
     * lama - kalau terlambat, pengguna melihat halaman "melompat".
     */
    public function test_detail_course_memaksa_posisi_paling_atas(): void
    {
        [$student, $course] = $this->courseWithChapters();

        $html = $this->actingAs($student)
            ->get(route('course.detail', $course->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString("history.scrollRestoration = 'manual'", $html);
        $this->assertStringContainsString('window.scrollTo(0, 0)', $html);

        // Penjaga anti-regresi: skrip pemfokus tidak boleh lagi punya sumber
        // modul implisit. Satu-satunya pemicu adalah params.get('module').
        $this->assertStringNotContainsString('storedModuleId', $html);
        $this->assertStringContainsString("var moduleId = params.get('module');", $html);

        // scrollRestoration harus berada SEBELUM badan halaman.
        $this->assertLessThan(
            strpos($html, 'course-detail-page'),
            strpos($html, 'scrollRestoration'),
            'Skrip scrollRestoration harus dijalankan sebelum konten dirender.'
        );
    }

    public function test_detail_course_memuat_skrip_pemfokus_chapter(): void
    {
        [$student, $course] = $this->courseWithChapters();

        $this->actingAs($student)
            ->get(route('course.detail', $course->id))
            ->assertOk()
            ->assertSee('chapter-card-resumed', false)
            ->assertSee('findChapterCardByModule', false);
    }

    public function test_parameter_modul_tidak_merusak_halaman_detail(): void
    {
        [$student, $course, $chapters, $modules] = $this->courseWithChapters();

        $this->actingAs($student)
            ->get(route('course.detail', $course->id) . '?module=' . $modules[3]->id)
            ->assertOk()
            ->assertSee('Chapter 1')
            ->assertSee('Chapter 2');
    }

    public function test_parameter_modul_asing_diabaikan_dengan_aman(): void
    {
        [$student, $course] = $this->courseWithChapters();

        $this->actingAs($student)
            ->get(route('course.detail', $course->id) . '?module=999999')
            ->assertOk()
            ->assertSee('Chapter 1');
    }
}
