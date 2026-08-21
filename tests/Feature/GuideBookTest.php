<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\ClassModel;
use App\Models\FreeClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Guide Book LMS — panduan penyusunan materi.
 *
 * Fitur pelengkap: tombol pemicu + modal berisi standar video, modul PDF,
 * dan slide PPT. Pengujian menekankan dua hal: panduan benar-benar muncul di
 * halaman unggah materi, dan kehadirannya tidak mengubah form yang sudah ada.
 */
class GuideBookTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Guide Admin',
            'email' => 'guidebook-admin@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function teacher(): User
    {
        return User::create([
            'name' => 'Guide Teacher',
            'email' => 'guidebook-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function chapterFor(User $teacher): Chapter
    {
        $class = ClassModel::create([
            'teacher_id' => $teacher->id,
            'name' => 'Kelas Uji Panduan',
            'description' => 'Kelas untuk menguji panduan materi.',
            'status' => 'active',
        ]);

        return Chapter::create([
            'class_id' => $class->id,
            'title' => 'Chapter Uji Panduan',
            'description' => 'Chapter untuk menguji panduan materi.',
            'order' => 1,
        ]);
    }

    private function freeClass(): FreeClass
    {
        return FreeClass::create([
            'title' => 'Kelas Gratis Uji Panduan',
            'description' => 'Deskripsi kelas gratis untuk pengujian panduan.',
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    /* ==============================================================
     | Tombol pemicu
     ============================================================== */

    public function test_admin_free_class_create_page_shows_guide_button(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.free-classes.create'));

        $response->assertStatus(200);
        $response->assertSee('Panduan Penyusunan Materi');
        $response->assertSee('data-bs-target="#guideBookModal"', false);

        // Wajib type="button": pemicu berada di dalam <form> unggah, sehingga
        // tombol tanpa type akan men-submit form dan merusak alur yang ada.
        $this->assertMatchesRegularExpression(
            '/<button\s+type="button"\s+class="guide-book-trigger"/',
            $response->getContent()
        );
    }

    public function test_admin_free_class_edit_page_shows_guide_button(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.free-classes.edit', $this->freeClass()));

        $response->assertStatus(200);
        $response->assertSee('Panduan Penyusunan Materi');
        $response->assertSee('guideBookModal', false);
    }

    public function test_teacher_document_upload_form_shows_guide_button(): void
    {
        $teacher = $this->teacher();

        $response = $this->actingAs($teacher)
            ->get(route('teacher.modules.create.document', $this->chapterFor($teacher)));

        $response->assertStatus(200);
        $response->assertSee('Panduan Penyusunan Materi');
        $response->assertSee('guideBookModal', false);
    }

    public function test_teacher_video_upload_form_shows_guide_button(): void
    {
        $teacher = $this->teacher();

        $response = $this->actingAs($teacher)
            ->get(route('teacher.modules.create.video', $this->chapterFor($teacher)));

        $response->assertStatus(200);
        $response->assertSee('Panduan Penyusunan Materi');
    }

    public function test_teacher_module_type_page_shows_guide_button(): void
    {
        $teacher = $this->teacher();

        $response = $this->actingAs($teacher)
            ->get(route('teacher.modules.create', $this->chapterFor($teacher)));

        $response->assertStatus(200);
        $response->assertSee('Panduan Penyusunan Materi');
    }

    public function test_teacher_manage_content_page_shows_guide_button(): void
    {
        $response = $this->actingAs($this->teacher())->get(route('teacher.manage.content'));

        $response->assertStatus(200);
        $response->assertSee('Panduan Penyusunan Materi');
    }

    /* ==============================================================
     | Isi panduan
     ============================================================== */

    public function test_guide_modal_contains_every_material_standard(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('admin.free-classes.create'))
            ->getContent();

        $expected = [
            // Judul kini berbahasa Indonesia penuh.
            'PANDUAN PENYUSUNAN MATERI LMS MERSIF LAB',

            // Video
            '1. Ketentuan Video Pembelajaran',
            '5 – 10 Menit (Micro-learning)',
            'MP4 (H.264), maks. 100 MB',
            'Animasi, Screencast/Tutorial, Explainer, Demonstrasi, atau Talking Head',

            // Modul PDF
            '2. Ketentuan Modul Pembelajaran (PDF)',
            '15 – 80 Halaman',
            'PDF, maks. 20 MB',
            'Materi utama, Penugasan/Latihan, Studi Kasus',
            '1 Topik boleh lebih dari 1 modul',

            // Slide PPT
            '3. Ketentuan Slide Presentasi (PPT)',
            'Maks. 30 Slide',
            '.ppt / .pptx, maks. 25 MB',
            'Ringkasan visual',

            // Tips penyusunan course (seluruhnya Bahasa Indonesia)
            'Tips Membuat Course yang Berkualitas',
            'Berikan nama course yang jelas dan deskriptif',
            'Tulis deskripsi rinci untuk membantu siswa memahami isi materi',
            'Gunakan gambar sampul berkualitas tinggi yang relevan dengan course',
            'Semua course awal berstatus Draft dan memerlukan persetujuan Admin',
            'Tambahkan bab (chapter) dan modul sebelum mengajukan persetujuan',
            'Anda dapat terus mengedit course hingga disetujui oleh Admin',

            // Footer
            'Panduan ini dapat dibuka kembali kapan saja',
            // Tombol konfirmasi: kelas kompak + tetap menutup modal.
            'guide-book-confirm" data-bs-dismiss="modal"',
            'Mengerti',
        ];

        foreach ($expected as $needle) {
            $this->assertStringContainsString($needle, $html, "Panduan kehilangan: {$needle}");
        }
    }

    public function test_guide_modal_is_scrollable_and_has_close_control(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('admin.free-classes.create'))
            ->getContent();

        // Isi panduan harus bisa digulir di layar laptop/tablet kecil.
        $this->assertStringContainsString('modal-dialog-scrollable', $html);

        // Tombol X di pojok kanan atas.
        $this->assertStringContainsString('aria-label="Tutup panduan"', $html);
        $this->assertStringContainsString('class="guide-book-close"', $html);
    }

    /* ==============================================================
     | Tidak mengganggu yang sudah ada
     ============================================================== */

    public function test_guide_modal_is_rendered_once_per_page(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('admin.free-classes.create'))
            ->getContent();

        // @once menjaga markup modal, CSS, dan JS tetap tunggal walaupun
        // partial di-include berkali-kali — id ganda akan mematahkan Bootstrap.
        $this->assertSame(1, substr_count($html, 'id="guideBookModal"'));
        $this->assertSame(1, substr_count($html, 'assets/css/guide-book.css'));
        $this->assertSame(1, substr_count($html, 'assets/js/guide-book.js'));
    }

    public function test_upload_form_fields_remain_intact(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('admin.free-classes.create'))
            ->getContent();

        // Panduan hanya pelengkap: form unggah harus tetap utuh.
        $this->assertStringContainsString('action="' . route('admin.free-classes.store') . '"', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('name="description"', $html);
        $this->assertStringContainsString('name="thumbnail_file"', $html);
        $this->assertStringContainsString('id="addLevelBtn"', $html);
    }

    public function test_guide_assets_exist_on_disk(): void
    {
        $this->assertFileExists(public_path('assets/css/guide-book.css'));
        $this->assertFileExists(public_path('assets/js/guide-book.js'));
    }
}
