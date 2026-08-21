<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Halaman Detail Course punya satu tombol keluar berlabel "Kembali ke Courses".
 *
 * Tujuannya adalah halaman katalog Courses yang terakhir dibuka — lengkap
 * dengan filter/kategorinya — dengan fallback ke route('courses') bila halaman
 * sebelumnya tidak dapat dipercaya (tautan luar, refresh, atau halaman non
 * katalog). Fallback itulah yang mencegah putaran tak berujung.
 */
class CourseDetailBackButtonTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::create([
            'name' => 'Back Teacher',
            'email' => 'back-teacher@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
    }

    private function student(): User
    {
        return User::create([
            'name' => 'Back Student',
            'email' => 'back-student@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    private function course(?User $teacher = null): ClassModel
    {
        return ClassModel::create([
            'teacher_id' => ($teacher ?? $this->teacher())->id,
            'name' => 'Kursus Back Button',
            'description' => 'Kursus untuk menguji tombol kembali.',
            'category' => 'Web',
            'price' => 100000,
            'is_published' => true,
        ]);
    }

    /** Membuka detail course dengan header Referer tertentu. */
    private function openDetail(ClassModel $course, ?string $referer = null)
    {
        return $this->get(
            route('course.detail', $course->id),
            $referer === null ? [] : ['referer' => $referer]
        );
    }

    /** href tombol kembali yang benar-benar dirender. */
    private function backHref(string $html): string
    {
        preg_match('/href="([^"]*)"\s+class="btn-course-back"/', $html, $m);

        return html_entity_decode($m[1] ?? '');
    }

    /* ==============================================================
     | Satu kontrol, satu label
     ============================================================== */

    /**
     * Hanya SATU kontrol keluar yang ditampilkan: tombol "Kembali" berlabel.
     * Tombol silang (X) sengaja dihilangkan agar tidak ada dua tombol dengan
     * fungsi yang sama.
     */
    public function test_hanya_ada_satu_tombol_kembali_tanpa_tombol_silang(): void
    {
        $response = $this->openDetail($this->course());

        $response->assertOk()
            ->assertSee('btn-course-back', false)
            ->assertDontSee('btn-course-close', false);

        // Dihitung dari atribut class-nya, bukan sekadar kemunculan teks:
        // nama kelas yang sama juga dipakai selector di skrip peningkatan.
        $this->assertSame(
            1,
            preg_match_all('/<a[^>]*class="btn-course-back"/', $response->getContent()),
            'Tombol kembali harus muncul tepat satu kali.'
        );
    }

    /** Labelnya seragam untuk semua peran: tamu, siswa, dan guru pemilik. */
    public function test_label_selalu_kembali_ke_courses(): void
    {
        $teacher = $this->teacher();
        $course = $this->course($teacher);

        $this->openDetail($course)->assertOk()->assertSee('Kembali ke Courses');

        $this->actingAs($this->student())
            ->get(route('course.detail', $course->id))
            ->assertOk()
            ->assertSee('Kembali ke Courses');

        $this->actingAs($teacher)
            ->get(route('course.detail', $course->id))
            ->assertOk()
            ->assertSee('Kembali ke Courses')
            ->assertDontSee('Kembali ke My Courses');
    }

    /* ==============================================================
     | Kembali ke katalog yang terakhir dibuka, filter ikut terbawa
     ============================================================== */

    public function test_filter_katalog_ikut_terbawa(): void
    {
        $referer = route('courses') . '?category=Web&sort=newest';

        $html = $this->openDetail($this->course(), $referer)->assertOk()->getContent();

        $this->assertSame($referer, $this->backHref($html));
    }

    public function test_kembali_ke_hasil_pencarian(): void
    {
        $referer = route('search') . '?q=laravel';

        $html = $this->openDetail($this->course(), $referer)->assertOk()->getContent();

        $this->assertSame($referer, $this->backHref($html));
    }

    public function test_siswa_login_juga_kembali_ke_katalog_berfilter(): void
    {
        $referer = route('courses') . '?category=Web';

        $html = $this->actingAs($this->student())
            ->get(route('course.detail', $this->course()->id), ['referer' => $referer])
            ->assertOk()
            ->getContent();

        $this->assertSame($referer, $this->backHref($html));
    }

    /* ==============================================================
     | Fallback aman — inti jaminan "tidak pernah buntu / berputar"
     ============================================================== */

    /**
     * Saat halaman di-refresh, browser mengirim Referer berisi URL halaman ini
     * sendiri. Tanpa penjagaan, tombol akan menunjuk dirinya sendiri dan
     * pengguna terjebak di halaman yang sama.
     */
    public function test_refresh_tidak_membuat_tombol_menunjuk_dirinya_sendiri(): void
    {
        $course = $this->course();
        $self = route('course.detail', $course->id);

        $href = $this->backHref($this->openDetail($course, $self)->assertOk()->getContent());

        $this->assertSame(route('courses'), $href);
        $this->assertNotSame($self, $href);
    }

    public function test_tanpa_referer_memakai_fallback_katalog(): void
    {
        $html = $this->openDetail($this->course())->assertOk()->getContent();

        $this->assertSame(route('courses'), $this->backHref($html));
    }

    public function test_referer_situs_luar_diabaikan(): void
    {
        $html = $this->openDetail($this->course(), 'https://example.com/promo')
            ->assertOk()
            ->getContent();

        $this->assertSame(route('courses'), $this->backHref($html));
    }

    public function test_halaman_non_katalog_memakai_fallback(): void
    {
        $course = $this->course();

        foreach ([url('/'), route('my-courses'), route('course.detail', 999999)] as $referer) {
            $html = $this->openDetail($course, $referer)->assertOk()->getContent();

            $this->assertSame(
                route('courses'),
                $this->backHref($html),
                "Referer {$referer} seharusnya memakai fallback katalog."
            );
        }
    }

    /* ==============================================================
     | Tidak merusak yang sudah ada
     ============================================================== */

    /** Breadcrumb "Courses > {nama}" di bawah tombol harus tetap utuh. */
    public function test_breadcrumb_di_bawah_tombol_tetap_utuh(): void
    {
        $course = $this->course();

        $this->openDetail($course)
            ->assertOk()
            ->assertSee('breadcrumb-nav', false)
            ->assertSee('href="' . route('courses') . '"', false)
            ->assertSee($course->name);
    }

    /* ==============================================================
     | Lapis kedua: history.back() demi posisi gulir & filter katalog
     ============================================================== */

    /**
     * Datang dari katalog: tombol ditandai agar klik-nya ditukar menjadi
     * history.back(), satu-satunya cara memulihkan posisi gulir, filter, dan
     * nomor halaman katalog persis seperti yang ditinggalkan.
     */
    public function test_dari_katalog_tombol_ditandai_untuk_history_back(): void
    {
        $html = $this->openDetail($this->course(), route('courses') . '?category=Web&page=3')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<a[^>]*class="btn-course-back"[^>]*data-history-back="1"/',
            $html,
            'Tombol seharusnya ditandai untuk memakai riwayat browser.'
        );

        $this->assertStringContainsString('window.history.back()', $html);
    }

    /**
     * Bukan dari katalog (tautan langsung, refresh, situs luar): penanda tidak
     * dipasang, sehingga klik memakai href biasa dan tidak melompat ke tempat
     * acak dalam riwayat pengguna.
     */
    public function test_bukan_dari_katalog_tidak_memakai_history_back(): void
    {
        $course = $this->course();

        $sumber = [
            null,
            route('course.detail', $course->id),
            'https://example.com/promo',
            url('/'),
        ];

        foreach ($sumber as $referer) {
            $html = $this->openDetail($course, $referer)->assertOk()->getContent();

            $this->assertDoesNotMatchRegularExpression(
                '/<a[^>]*class="btn-course-back"[^>]*data-history-back="1"/',
                $html,
                'Penanda riwayat tidak boleh dipasang untuk referer: ' . ($referer ?? '(kosong)')
            );
        }
    }

    /**
     * Penurunan aman: href tetap ada dan tetap benar walau JavaScript mati,
     * tautan dibuka di tab baru, atau riwayat browser kosong.
     */
    public function test_href_tetap_menjadi_cadangan_tanpa_javascript(): void
    {
        $referer = route('courses') . '?category=Web&page=3';

        $html = $this->openDetail($this->course(), $referer)->assertOk()->getContent();

        // Bukan <button onclick>: href harus tetap dapat dipakai.
        $this->assertSame($referer, $this->backHref($html));

        // Penjaga untuk klik tab-baru dan riwayat kosong.
        $this->assertStringContainsString('window.history.length <= 1', $html);
        $this->assertStringContainsString('event.metaKey', $html);
    }

    /** Skrip peningkatan dicetak sekali saja walau partial di-include ulang. */
    public function test_skrip_riwayat_hanya_sekali(): void
    {
        $html = $this->openDetail($this->course())->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'window.history.back()'));
    }
}
