<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Saat siswa kembali dari detail course, halaman My Course harus mendarat
 * tepat di kartu course yang tadi dibuka (bukan kembali ke paling atas).
 */
class MyCoursePositionTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::create([
            'name' => 'Pos Teacher',
            'email' => 'pos-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function student(): User
    {
        return User::create([
            'name' => 'Pos Student',
            'email' => 'pos-student@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    private function courseOwnedBy(User $teacher, string $name): ClassModel
    {
        return ClassModel::create([
            'teacher_id' => $teacher->id,
            'name' => $name,
            'description' => 'Deskripsi ' . $name,
            'category' => 'Web',
            'price' => 100000,
            'is_published' => true,
        ]);
    }

    private function studentWithCourses(int $count = 3): array
    {
        $student = $this->student();
        $teacher = $this->teacher();
        $courses = [];

        for ($i = 1; $i <= $count; $i++) {
            $course = $this->courseOwnedBy($teacher, "Kursus Nomor {$i}");

            Purchase::create([
                'purchase_code' => Purchase::generatePurchaseCode(),
                'user_id' => $student->id,
                'class_id' => $course->id,
                'status' => 'success',
                'amount' => 100000,
                'final_amount' => 100000,
            ]);

            $courses[] = $course;
        }

        return [$student, $courses];
    }

    /**
     * Tombol kembali di halaman detail kini mengarah ke katalog Courses, bukan
     * ke My Course. Pemulihan posisi karena itu bersandar pada penanda yang
     * ditulis kartu saat diklik (js-open-course -> sessionStorage), bukan lagi
     * pada parameter ?course= yang dibawa tombol kembali.
     */
    public function test_tombol_kembali_detail_tidak_lagi_menuju_my_course(): void
    {
        [$student, $courses] = $this->studentWithCourses();
        $target = $courses[1];

        $this->actingAs($student)
            ->get(route('course.detail', $target->id))
            ->assertOk()
            ->assertSee('Kembali ke Courses')
            ->assertDontSee('href="' . route('my-courses', ['course' => $target->id]) . '"', false);
    }

    public function test_setiap_kartu_course_punya_penanda_posisi(): void
    {
        [$student, $courses] = $this->studentWithCourses();

        $response = $this->actingAs($student)->get(route('my-courses'));

        $response->assertOk();

        foreach ($courses as $course) {
            $response->assertSee('id="course-card-' . $course->id . '"', false);
            $response->assertSee('data-course-id="' . $course->id . '"', false);
        }
    }

    public function test_halaman_my_course_memuat_skrip_pemulih_posisi(): void
    {
        [$student] = $this->studentWithCourses();

        $this->actingAs($student)
            ->get(route('my-courses'))
            ->assertOk()
            ->assertSee('mersif.myCourses.lastPosition', false)
            ->assertSee('js-open-course', false)
            ->assertSee('course-card-restored', false);
    }

    /**
     * Membuka My Course dengan ?course=ID tidak boleh merusak halaman -
     * daftar course tetap tampil seperti biasa.
     */
    public function test_parameter_course_tidak_merusak_daftar(): void
    {
        [$student, $courses] = $this->studentWithCourses();

        $response = $this->actingAs($student)
            ->get(route('my-courses', ['course' => $courses[2]->id]));

        $response->assertOk()
            ->assertSee('My Courses')
            ->assertSee('Kursus Nomor 1')
            ->assertSee('Kursus Nomor 2')
            ->assertSee('Kursus Nomor 3');
    }

    /**
     * Parameter course yang tidak dikenal tidak boleh menimbulkan error.
     */
    public function test_parameter_course_asing_diabaikan_dengan_aman(): void
    {
        [$student] = $this->studentWithCourses();

        $this->actingAs($student)
            ->get(route('my-courses', ['course' => 999999]))
            ->assertOk()
            ->assertSee('My Courses');
    }
}
