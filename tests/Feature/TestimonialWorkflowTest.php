<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Purchase;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Alur testimoni setelah refaktorisasi:
 * siswa mengirim (pending) -> admin menyetujui/menolak -> hanya yang
 * approved tampil di halaman publik.
 */
class TestimonialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $email = 'testi-student@example.com'): User
    {
        return User::create([
            'name' => 'Testi Student',
            'email' => $email,
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Testi Admin',
            'email' => 'testi-admin@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function teacher(): User
    {
        return User::create([
            'name' => 'Testi Teacher',
            'email' => 'testi-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function course(): ClassModel
    {
        return ClassModel::create([
            'teacher_id' => $this->teacher()->id,
            'name' => 'Kursus IoT Dasar',
            'description' => 'Belajar IoT dari nol.',
            'category' => 'IoT',
            'price' => 100000,
            'is_published' => true,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'rating' => 5,
            'content' => 'Materi IoT-nya runtut dan mudah diikuti, mentornya juga responsif sekali.',
        ], $overrides);
    }

    // ==================== SISI SISWA ====================

    public function test_siswa_bisa_membuka_halaman_form_testimoni(): void
    {
        $this->actingAs($this->student())
            ->get(route('my-testimonials'))
            ->assertOk()
            ->assertSee('Tulis Testimoni')
            ->assertSee('Kirim Testimoni');
    }

    public function test_siswa_mengirim_testimoni_dan_statusnya_pending(): void
    {
        $student = $this->student();

        $response = $this->actingAs($student)
            ->post(route('my-testimonials.store'), $this->payload());

        $response->assertRedirect(route('my-testimonials'))
            ->assertSessionHas('success', 'Testimoni berhasil dikirim dan menunggu peninjauan admin.');

        $testimonial = Testimonial::first();

        $this->assertNotNull($testimonial);
        $this->assertSame(Testimonial::STATUS_PENDING, $testimonial->status);
        $this->assertFalse($testimonial->is_published);
        $this->assertSame($student->id, $testimonial->user_id);
        $this->assertSame(5, $testimonial->rating);
        $this->assertSame($student->name, $testimonial->name);
    }

    public function test_siswa_bisa_memilih_kursus_yang_diikutinya(): void
    {
        $student = $this->student();
        $course = $this->course();

        Purchase::create([
            'user_id' => $student->id,
            'class_id' => $course->id,
            'status' => 'success',
            'amount' => 100000,
            'final_amount' => 100000,
        ]);

        $this->actingAs($student)
            ->post(route('my-testimonials.store'), $this->payload(['course_id' => $course->id]))
            ->assertSessionHas('success');

        $this->assertSame($course->id, Testimonial::first()->course_id);
    }

    public function test_siswa_tidak_bisa_memilih_kursus_yang_tidak_diikuti(): void
    {
        $course = $this->course();

        $this->actingAs($this->student())
            ->post(route('my-testimonials.store'), $this->payload(['course_id' => $course->id]))
            ->assertSessionHas('error');

        $this->assertSame(0, Testimonial::count());
    }

    public function test_validasi_rating_dan_isi_testimoni(): void
    {
        $student = $this->student();

        // Tanpa rating.
        $this->actingAs($student)
            ->post(route('my-testimonials.store'), ['content' => str_repeat('a', 30)])
            ->assertSessionHasErrors('rating');

        // Rating di luar 1-5.
        $this->actingAs($student)
            ->post(route('my-testimonials.store'), $this->payload(['rating' => 9]))
            ->assertSessionHasErrors('rating');

        // Isi terlalu pendek.
        $this->actingAs($student)
            ->post(route('my-testimonials.store'), $this->payload(['content' => 'Bagus']))
            ->assertSessionHasErrors('content');

        $this->assertSame(0, Testimonial::count());
    }

    public function test_siswa_dibatasi_tiga_testimoni_pending(): void
    {
        $student = $this->student();

        for ($i = 1; $i <= 3; $i++) {
            $this->actingAs($student)
                ->post(route('my-testimonials.store'), $this->payload(['content' => "Testimoni nomor {$i} yang cukup panjang untuk lolos validasi."]))
                ->assertSessionHas('success');
        }

        $this->actingAs($student)
            ->post(route('my-testimonials.store'), $this->payload(['content' => 'Testimoni keempat yang seharusnya ditolak sistem.']))
            ->assertSessionHas('error');

        $this->assertSame(3, Testimonial::count());
    }

    public function test_siswa_bisa_menghapus_testimoni_pending_miliknya(): void
    {
        $student = $this->student();

        $this->actingAs($student)->post(route('my-testimonials.store'), $this->payload());
        $testimonial = Testimonial::first();

        $this->actingAs($student)
            ->delete(route('my-testimonials.destroy', $testimonial->id))
            ->assertSessionHas('success');

        $this->assertSame(0, Testimonial::count());
    }

    public function test_siswa_tidak_bisa_menghapus_testimoni_milik_orang_lain(): void
    {
        $owner = $this->student('owner@example.com');
        $other = $this->student('other@example.com');

        $this->actingAs($owner)->post(route('my-testimonials.store'), $this->payload());
        $testimonial = Testimonial::first();

        $this->actingAs($other)
            ->delete(route('my-testimonials.destroy', $testimonial->id))
            ->assertForbidden();

        $this->assertSame(1, Testimonial::count());
    }

    // ==================== SISI ADMIN ====================

    public function test_admin_melihat_dashboard_moderasi_tanpa_tombol_tambah(): void
    {
        $this->actingAs($this->student())->post(route('my-testimonials.store'), $this->payload());

        $response = $this->actingAs($this->admin())->get(route('admin.testimonials.index'));

        $response->assertOk()
            ->assertSee('Testimonials Moderation')
            // Tab filter status tersedia.
            ->assertSee('Pending')
            ->assertSee('Approved')
            ->assertSee('Rejected')
            // Tombol aksi moderasi tersedia.
            ->assertSee('Approve')
            ->assertSee('Reject')
            // Form pembuatan testimoni oleh admin sudah dihapus.
            ->assertDontSee('Add Testimonial');
    }

    public function test_form_pembuatan_testimoni_admin_dialihkan_ke_moderasi(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.testimonials.create'))
            ->assertRedirect(route('admin.testimonials.index'))
            ->assertSessionHas('error');
    }

    public function test_admin_menyetujui_testimoni(): void
    {
        $this->actingAs($this->student())->post(route('my-testimonials.store'), $this->payload());
        $testimonial = Testimonial::first();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.testimonials.approve', $testimonial->id))
            ->assertSessionHas('success');

        $testimonial->refresh();

        $this->assertSame(Testimonial::STATUS_APPROVED, $testimonial->status);
        $this->assertTrue($testimonial->is_published);
        $this->assertSame($admin->id, $testimonial->reviewed_by);
        $this->assertNotNull($testimonial->reviewed_at);
    }

    public function test_admin_menolak_testimoni_beserta_alasannya(): void
    {
        $this->actingAs($this->student())->post(route('my-testimonials.store'), $this->payload());
        $testimonial = Testimonial::first();

        $this->actingAs($this->admin())
            ->post(route('admin.testimonials.reject', $testimonial->id), [
                'rejection_reason' => 'Isi kurang relevan.',
            ])
            ->assertSessionHas('success');

        $testimonial->refresh();

        $this->assertSame(Testimonial::STATUS_REJECTED, $testimonial->status);
        $this->assertFalse($testimonial->is_published);
        $this->assertSame('Isi kurang relevan.', $testimonial->rejection_reason);
    }

    public function test_filter_tab_status_menyaring_daftar(): void
    {
        $student = $this->student();

        $this->actingAs($student)->post(route('my-testimonials.store'), $this->payload(['content' => 'Testimoni yang akan disetujui admin nanti.']));
        $approved = Testimonial::first();
        $approved->approve($this->admin()->id);

        $this->actingAs($student)->post(route('my-testimonials.store'), $this->payload(['content' => 'Testimoni yang masih menunggu peninjauan admin.']));

        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)
            ->get(route('admin.testimonials.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('menunggu peninjauan admin')
            ->assertDontSee('yang akan disetujui admin nanti');

        $this->actingAs($admin)
            ->get(route('admin.testimonials.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('yang akan disetujui admin nanti')
            ->assertDontSee('masih menunggu peninjauan admin');
    }

    // ==================== HALAMAN PUBLIK ====================

    public function test_halaman_publik_hanya_menampilkan_testimoni_approved(): void
    {
        $student = $this->student();

        $this->actingAs($student)->post(route('my-testimonials.store'), $this->payload(['content' => 'Testimoni PENDING yang belum boleh tampil publik.']));
        $this->actingAs($student)->post(route('my-testimonials.store'), $this->payload(['content' => 'Testimoni DITOLAK yang juga tidak boleh tampil.']));
        $this->actingAs($student)->post(route('my-testimonials.store'), $this->payload(['content' => 'Testimoni DISETUJUI yang tampil di landing page.']));

        $all = Testimonial::orderBy('id')->get();
        $admin = $this->admin();

        $all[1]->reject($admin->id, 'Tidak relevan.');
        $all[2]->approve($admin->id);

        // Keluar dari sesi login supaya benar-benar menguji tampilan publik.
        $this->post(route('logout'));

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Testimoni DISETUJUI yang tampil di landing page.')
            ->assertDontSee('Testimoni PENDING yang belum boleh tampil publik.')
            ->assertDontSee('Testimoni DITOLAK yang juga tidak boleh tampil.');
    }

    public function test_scope_approved_hanya_mengembalikan_status_approved(): void
    {
        $student = $this->student();
        $admin = $this->admin();

        $this->actingAs($student)->post(route('my-testimonials.store'), $this->payload());
        Testimonial::first()->approve($admin->id);

        $this->actingAs($student)->post(route('my-testimonials.store'), $this->payload(['content' => 'Testimoni kedua yang masih pending peninjauan.']));

        $this->assertSame(1, Testimonial::approved()->count());
        $this->assertSame(1, Testimonial::pending()->count());
    }
}
