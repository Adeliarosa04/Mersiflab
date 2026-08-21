<?php

namespace Tests\Feature;

use App\Models\FreeClass;
use App\Models\FreeClassLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FreeClassTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Tester',
            'email' => 'freeclass-admin@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function student(): User
    {
        return User::create([
            'name' => 'Student Tester',
            'email' => 'freeclass-student@example.com',
            'password' => Hash::make('SecretPass123'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * PNG 1x1 asli.
     *
     * UploadedFile::fake()->image() butuh ekstensi GD yang tidak selalu
     * tersedia, jadi berkas dibuat dari byte PNG yang sudah pasti valid.
     */
    private function fakePng(string $name = 'thumb.png'): UploadedFile
    {
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    /**
     * PPTX asli (arsip ZIP berisi [Content_Types].xml).
     *
     * Penting untuk pengujian: finfo pada banyak sistem melaporkan PPTX
     * sebagai application/zip, sehingga aturan `mimes:pptx` saja akan menolak
     * berkas yang sah. Berkas ini memastikan validasi menerima PPTX nyata.
     */
    private function fakePptx(string $name = 'slide.pptx'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pptx');

        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types/>');
        $zip->addFromString('ppt/presentation.xml', '<?xml version="1.0"?><p:presentation/>');
        $zip->close();

        return UploadedFile::fake()->createWithContent($name, file_get_contents($path));
    }

    /**
     * Payload level minimal yang valid.
     */
    private function levelPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Level 1',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ], $overrides);
    }

    private function makeFreeClass(array $overrides = [], array $levels = []): FreeClass
    {
        $freeClass = FreeClass::create(array_merge([
            'title' => 'Pengenalan Augmented Reality',
            'description' => 'Kelas gratis dasar-dasar AR.',
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides));

        if ($levels === []) {
            $levels = [[
                'name' => 'Level 1',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'sort_order' => 0,
            ]];
        }

        foreach ($levels as $i => $level) {
            $freeClass->levels()->create(array_merge(['sort_order' => $i], $level));
        }

        return $freeClass->fresh('levels');
    }

    /* =================================================================
     | Admin CRUD
     * ============================================================== */

    public function test_admin_can_open_the_management_pages()
    {
        $this->actingAs($this->admin());

        // Penamaan UI kini "Free Course" (rute & tabel tetap free-classes).
        $this->get(route('admin.free-classes.index'))->assertOk()->assertSee('Free Course Management');
        $this->get(route('admin.free-classes.create'))->assertOk()
            ->assertSee('Judul Kursus Gratis')
            ->assertSee('Add Level')
            ->assertSee('Level Materi');
    }

    public function test_admin_can_create_a_free_class_with_a_single_level()
    {
        Storage::fake('public');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.free-classes.store'), [
            'title' => 'Pengenalan Augmented Reality',
            'description' => 'Kelas gratis dasar-dasar AR.',
            'is_active' => '1',
            'sort_order' => 0,
            'levels' => [
                $this->levelPayload([
                    'pdf_file' => UploadedFile::fake()->create('modul.pdf', 120, 'application/pdf'),
                ]),
            ],
        ]);

        $response->assertRedirect(route('admin.free-classes.index'));
        $response->assertSessionHas('success');

        $freeClass = FreeClass::first();
        $this->assertSame($admin->id, $freeClass->created_by);
        $this->assertCount(1, $freeClass->levels);

        $level = $freeClass->levels->first();
        $this->assertSame('Level 1', $level->name);
        $this->assertSame('modul.pdf', $level->pdf_name);
        Storage::disk('public')->assertExists($level->pdf_path);
    }

    public function test_admin_can_create_a_free_class_with_multiple_levels()
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'AR Bertingkat',
            'description' => 'Dari dasar sampai praktik.',
            'is_active' => '1',
            'levels' => [
                $this->levelPayload(['name' => 'Level 1 - Pengenalan']),
                [
                    'name' => 'Level 2 - Praktik',
                    'video_file' => UploadedFile::fake()->create('klip.mp4', 400, 'video/mp4'),
                    'ppt_file' => $this->fakePptx(),
                ],
                $this->levelPayload(['name' => 'Level 3 - Studi Kasus', 'video_url' => 'https://vimeo.com/76979871']),
            ],
        ])->assertRedirect(route('admin.free-classes.index'));

        $levels = FreeClass::first()->levels;
        $this->assertCount(3, $levels);

        // Urutan mengikuti urutan pengiriman form.
        $this->assertSame(
            ['Level 1 - Pengenalan', 'Level 2 - Praktik', 'Level 3 - Studi Kasus'],
            $levels->pluck('name')->all()
        );
        $this->assertSame([0, 1, 2], $levels->pluck('sort_order')->all());

        // Level 2: video unggahan + slide PPT.
        $this->assertNotNull($levels[1]->video_path);
        $this->assertSame('slide.pptx', $levels[1]->ppt_name);
        Storage::disk('public')->assertExists($levels[1]->video_path);
        Storage::disk('public')->assertExists($levels[1]->ppt_path);

        // Level 3: Vimeo diubah menjadi URL embed.
        $this->assertSame('https://player.vimeo.com/video/76979871', $levels[2]->embed_url);
    }

    public function test_updating_syncs_levels_adds_updates_and_removes()
    {
        Storage::fake('public');
        $admin = $this->admin();

        $freeClass = $this->makeFreeClass([], [
            ['name' => 'Level 1', 'video_url' => 'https://youtu.be/aaaaaa'],
            ['name' => 'Level 2', 'video_path' => 'free-classes/videos/lama.mp4'],
        ]);
        Storage::disk('public')->put('free-classes/videos/lama.mp4', 'x');

        [$first, $second] = $freeClass->levels->all();

        $this->actingAs($admin)->put(route('admin.free-classes.update', $freeClass), [
            'title' => $freeClass->title,
            'description' => $freeClass->description,
            'is_active' => '1',
            'levels' => [
                // Level 1 dipertahankan dengan nama baru.
                ['id' => $first->id, 'name' => 'Level 1 (revisi)', 'video_url' => 'https://youtu.be/bbbbbb'],
                // Level 2 dihilangkan dari form -> harus terhapus.
                // Level baru ditambahkan.
                $this->levelPayload(['name' => 'Level Baru']),
            ],
        ])->assertRedirect(route('admin.free-classes.index'));

        $levels = $freeClass->fresh('levels')->levels;

        $this->assertSame(['Level 1 (revisi)', 'Level Baru'], $levels->pluck('name')->all());
        $this->assertDatabaseMissing('free_class_levels', ['id' => $second->id]);

        // Berkas milik level yang dihapus ikut dibersihkan.
        Storage::disk('public')->assertMissing('free-classes/videos/lama.mp4');
    }

    public function test_levels_of_another_free_class_cannot_be_hijacked_by_id()
    {
        $admin = $this->admin();

        $other = $this->makeFreeClass(['title' => 'Kelas Lain'], [
            ['name' => 'Milik Kelas Lain', 'video_url' => 'https://youtu.be/aaaaaa'],
        ]);
        $victimLevel = $other->levels->first();

        $target = $this->makeFreeClass(['title' => 'Kelas Target']);

        $this->actingAs($admin)->put(route('admin.free-classes.update', $target), [
            'title' => $target->title,
            'description' => $target->description,
            'is_active' => '1',
            'levels' => [
                ['id' => $victimLevel->id, 'name' => 'Dibajak', 'video_url' => 'https://youtu.be/cccccc'],
            ],
        ]);

        // Level milik kelas lain tidak boleh berubah.
        $this->assertSame('Milik Kelas Lain', $victimLevel->fresh()->name);
        $this->assertSame($other->id, $victimLevel->fresh()->free_class_id);
    }

    public function test_deleting_a_free_class_removes_its_levels_and_files()
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.free-classes.store'), [
            'title' => 'Dengan Berkas',
            'description' => 'Punya video, modul, dan slide.',
            'is_active' => '1',
            'levels' => [[
                'name' => 'Level 1',
                'video_file' => UploadedFile::fake()->create('klip.mp4', 300, 'video/mp4'),
                'pdf_file' => UploadedFile::fake()->create('modul.pdf', 100, 'application/pdf'),
                'ppt_file' => $this->fakePptx(),
            ]],
        ]);

        $freeClass = FreeClass::first();
        $level = $freeClass->levels->first();
        $paths = $level->filePaths();
        $this->assertCount(3, $paths);

        $this->actingAs($admin)->delete(route('admin.free-classes.destroy', $freeClass));

        $this->assertDatabaseMissing('free_classes', ['id' => $freeClass->id]);
        $this->assertDatabaseMissing('free_class_levels', ['id' => $level->id]);

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_admin_can_toggle_visibility()
    {
        $freeClass = $this->makeFreeClass();

        $this->actingAs($this->admin())
            ->post(route('admin.free-classes.toggleActive', $freeClass))
            ->assertRedirect(route('admin.free-classes.index'));

        $this->assertFalse($freeClass->refresh()->is_active);
    }

    /* =================================================================
     | Validasi
     * ============================================================== */

    public function test_at_least_one_level_is_required()
    {
        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'Tanpa Level',
            'description' => 'Uji validasi.',
        ])->assertSessionHasErrors('levels');

        $this->assertDatabaseCount('free_classes', 0);
    }

    public function test_each_level_requires_a_name_and_a_video()
    {
        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'Level Tidak Lengkap',
            'description' => 'Uji validasi.',
            'levels' => [
                ['name' => ''],
            ],
        ])->assertSessionHasErrors(['levels.0.name', 'levels.0.video_url']);

        $this->assertDatabaseCount('free_classes', 0);
    }

    public function test_existing_video_satisfies_the_requirement_on_update()
    {
        $freeClass = $this->makeFreeClass();
        $level = $freeClass->levels->first();

        // Tidak mengirim video baru: video lama pada level tetap dipakai.
        $this->actingAs($this->admin())->put(route('admin.free-classes.update', $freeClass), [
            'title' => $freeClass->title,
            'description' => $freeClass->description,
            'is_active' => '1',
            'levels' => [
                ['id' => $level->id, 'name' => 'Level 1 (judul baru)'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Level 1 (judul baru)', $level->fresh()->name);
        $this->assertNotNull($level->fresh()->video_url);
    }

    public function test_module_must_be_a_pdf()
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'PDF Palsu',
            'description' => 'Uji validasi.',
            'levels' => [
                $this->levelPayload([
                    'pdf_file' => UploadedFile::fake()->create('palsu.txt', 10, 'text/plain'),
                ]),
            ],
        ])->assertSessionHasErrors('levels.0.pdf_file');
    }

    public function test_slide_must_be_a_powerpoint_file()
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'PPT Palsu',
            'description' => 'Uji validasi.',
            'levels' => [
                $this->levelPayload([
                    'ppt_file' => UploadedFile::fake()->create('palsu.txt', 10, 'text/plain'),
                ]),
            ],
        ])->assertSessionHasErrors('levels.0.ppt_file');
    }

    public function test_a_real_pptx_is_accepted_even_though_it_is_detected_as_zip()
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'Dengan Slide',
            'description' => 'Uji PPTX.',
            'is_active' => '1',
            'levels' => [
                $this->levelPayload(['ppt_file' => $this->fakePptx()]),
            ],
        ])->assertSessionHasNoErrors();

        $level = FreeClass::first()->levels->first();
        $this->assertSame('slide.pptx', $level->ppt_name);
        $this->assertTrue($level->hasPpt());
        Storage::disk('public')->assertExists($level->ppt_path);
    }

    /**
     * Berkas harus tersimpan dengan ekstensi aslinya.
     *
     * Regresi: PPTX secara teknis adalah ZIP, sehingga penamaan bawaan Laravel
     * (berbasis tebakan MIME) menyimpannya sebagai ".zip". Ekstensi keliru itu
     * membuat Google Docs Viewer menolak merender slide pada tombol "Lihat".
     */
    public function test_uploaded_files_keep_their_real_extension()
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'Ekstensi Berkas',
            'description' => 'Uji penamaan berkas.',
            'is_active' => '1',
            'levels' => [[
                'name' => 'Level 1',
                'video_url' => 'https://youtu.be/aaaaaa',
                'pdf_file' => UploadedFile::fake()->create('modul.pdf', 50, 'application/pdf'),
                'ppt_file' => $this->fakePptx(),
            ]],
        ])->assertSessionHasNoErrors();

        $level = FreeClass::first()->levels->first();

        $this->assertStringEndsWith('.pdf', $level->pdf_path);
        $this->assertStringEndsWith('.pptx', $level->ppt_path);
    }

    public function test_thumbnail_must_be_an_image()
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'Thumb Palsu',
            'description' => 'Uji validasi.',
            'thumbnail_file' => UploadedFile::fake()->create('palsu.txt', 10, 'text/plain'),
            'levels' => [$this->levelPayload()],
        ])->assertSessionHasErrors('thumbnail_file');
    }

    public function test_admin_can_upload_a_thumbnail()
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.free-classes.store'), [
            'title' => 'Dengan Thumbnail',
            'description' => 'Uji thumbnail.',
            'thumbnail_file' => $this->fakePng(),
            'is_active' => '1',
            'levels' => [$this->levelPayload()],
        ])->assertRedirect(route('admin.free-classes.index'));

        $freeClass = FreeClass::first();
        $this->assertNotNull($freeClass->thumbnail_path);
        Storage::disk('public')->assertExists($freeClass->thumbnail_path);
    }

    /* =================================================================
     | Kontrol akses
     * ============================================================== */

    public function test_guests_are_redirected_to_admin_login()
    {
        $this->get(route('admin.free-classes.index'))->assertRedirect(route('admin.login'));
    }

    public function test_students_cannot_manage_free_classes()
    {
        $this->actingAs($this->student())
            ->get(route('admin.free-classes.index'))
            ->assertForbidden();
    }

    /* =================================================================
     | Halaman Courses — kartu TIDAK berubah
     * ============================================================== */

    public function test_courses_page_shows_active_free_classes()
    {
        $freeClass = $this->makeFreeClass();

        $this->get(route('courses'))
            ->assertOk()
            ->assertSee('id="free-class"', false)
            ->assertSee($freeClass->title)
            ->assertSee(route('free-classes.show', $freeClass), false);
    }

    /**
     * Kartu di halaman Courses tetap minimalis: thumbnail + judul saja,
     * satu kartu per Free Class walaupun kelasnya punya banyak level.
     */
    public function test_course_cards_stay_minimal_even_for_multi_level_classes()
    {
        $freeClass = $this->makeFreeClass(
            ['description' => 'Deskripsi panjang yang tidak boleh tampil di kartu.'],
            [
                ['name' => 'Level 1', 'video_url' => 'https://youtu.be/aaaaaa'],
                ['name' => 'Level 2', 'video_url' => 'https://youtu.be/bbbbbb'],
                ['name' => 'Level 3', 'video_url' => 'https://youtu.be/cccccc'],
            ]
        );

        $html = $this->get(route('courses'))->assertOk()->getContent();

        $start = strpos($html, 'id="free-class"');
        $section = substr($html, $start, strpos($html, '</section>', $start) - $start);

        $this->assertSame(1, substr_count($section, 'free-class-card'));
        $this->assertStringNotContainsString('<video', $section);
        $this->assertStringNotContainsString('<iframe', $section);
        $this->assertStringNotContainsString($freeClass->description, $section);
        $this->assertStringNotContainsString('Level 2', $section);
    }

    public function test_courses_page_hides_inactive_free_classes()
    {
        $this->makeFreeClass(['is_active' => false, 'title' => 'Kelas Tersembunyi']);

        $this->get(route('courses'))
            ->assertOk()
            ->assertDontSee('Kelas Tersembunyi')
            ->assertDontSee('id="free-class"', false);
    }

    public function test_free_class_section_appears_above_the_main_course_list()
    {
        $this->makeFreeClass();

        $html = $this->get(route('courses'))->getContent();

        $this->assertLessThan(
            strpos($html, 'popular-section'),
            strpos($html, 'id="free-class"'),
            'Seksi Free Class harus berada di atas daftar kursus utama.'
        );
    }

    public function test_courses_page_still_works_without_any_free_class()
    {
        $this->get(route('courses'))
            ->assertOk()
            ->assertSee('Explore Courses')
            ->assertDontSee('id="free-class"', false);
    }

    public function test_homepage_links_to_the_free_class_section_only_when_data_exists()
    {
        $this->get(route('home'))->assertOk()->assertDontSee('courses#free-class', false);

        $this->makeFreeClass();

        $this->get(route('home'))->assertOk()->assertSee('#free-class', false);
    }

    /* =================================================================
     | Halaman detail — multi-level
     * ============================================================== */

    public function test_detail_page_renders_a_tab_and_panel_for_every_level()
    {
        $freeClass = $this->makeFreeClass([], [
            ['name' => 'Level 1 - Pengenalan', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
             'pdf_path' => 'free-classes/modules/modul.pdf', 'pdf_name' => 'modul.pdf'],
            ['name' => 'Level 2 - Praktik', 'video_url' => 'https://vimeo.com/76979871',
             'ppt_path' => 'free-classes/slides/slide.pptx', 'ppt_name' => 'slide.pptx'],
        ]);

        $response = $this->get(route('free-classes.show', $freeClass))->assertOk();
        $html = $response->getContent();

        // Satu tab dan satu panel per level.
        $this->assertSame(2, substr_count($html, 'free-class-level-tab '));
        $this->assertSame(2, substr_count($html, 'id="level-panel-'));

        $response->assertSee('Level 1 - Pengenalan')
            ->assertSee('Level 2 - Praktik')
            ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('https://player.vimeo.com/video/76979871', false)
            ->assertSee('Unduh PDF')
            ->assertSee('Unduh PPT')
            ->assertSee('modul.pdf')
            ->assertSee('slide.pptx');
    }

    /**
     * Kartu PDF dan PPT harus punya struktur & aksi yang sama:
     * masing-masing memiliki tombol "Lihat" dan tombol unduh.
     */
    public function test_both_material_cards_offer_view_and_download()
    {
        $freeClass = $this->makeFreeClass([], [[
            'name' => 'Level 1',
            'video_url' => 'https://youtu.be/aaaaaa',
            'pdf_path' => 'free-classes/modules/modul.pdf',
            'pdf_name' => 'modul.pdf',
            'ppt_path' => 'free-classes/slides/slide.pptx',
            'ppt_name' => 'slide.pptx',
        ]]);

        $html = $this->get(route('free-classes.show', $freeClass))->assertOk()->getContent();

        // Dua kartu materi, masing-masing satu tombol "Lihat".
        $this->assertSame(2, substr_count($html, 'class="free-class-material"'));
        $this->assertSame(1, substr_count($html, 'data-preview-kind="pdf"'));
        $this->assertSame(1, substr_count($html, 'data-preview-kind="ppt"'));

        // Modal pratinjau tersedia satu kali untuk dipakai bersama.
        $this->assertSame(1, substr_count($html, 'id="materialPreviewModal"'));
        $this->assertStringContainsString('Unduh PDF', $html);
        $this->assertStringContainsString('Unduh PPT', $html);
    }

    /**
     * Slide dirender di sisi klien oleh PPTXjs yang di-host sendiri, jadi
     * halaman tidak boleh lagi bergantung pada layanan luar.
     */
    public function test_pptx_viewer_assets_are_served_locally()
    {
        $freeClass = $this->makeFreeClass([], [[
            'name' => 'Level 1',
            'video_url' => 'https://youtu.be/aaaaaa',
            'ppt_path' => 'free-classes/slides/slide.pptx',
            'ppt_name' => 'slide.pptx',
        ]]);

        $html = $this->get(route('free-classes.show', $freeClass))->assertOk()->getContent();

        // Tidak ada lagi viewer pihak ketiga.
        $this->assertStringNotContainsString('docs.google.com', $html);
        $this->assertStringNotContainsString('view.officeapps.live.com', $html);

        // Skrip penampil & basis berkas library berasal dari domain sendiri.
        $this->assertStringContainsString('assets/js/free-class-preview.js', $html);
        $this->assertStringContainsString('data-vendor-base="' . e(asset('assets/vendor/pptx')) . '"', $html);

        foreach (['jszip.min.js', 'filereader.js', 'dingbat.js', 'pptxjs.js'] as $file) {
            $this->assertFileExists(public_path('assets/vendor/pptx/' . $file), $file . ' harus tersedia lokal');
        }
    }

    public function test_preview_modal_has_slide_controls()
    {
        $freeClass = $this->makeFreeClass([], [[
            'name' => 'Level 1',
            'video_url' => 'https://youtu.be/aaaaaa',
            'ppt_path' => 'free-classes/slides/slide.pptx',
            'ppt_name' => 'slide.pptx',
        ]]);

        $this->get(route('free-classes.show', $freeClass))
            ->assertOk()
            ->assertSee('id="pptxViewer"', false)      // kontainer render slide
            ->assertSee('id="pptxPrev"', false)        // navigasi mundur
            ->assertSee('id="pptxNext"', false)        // navigasi maju
            ->assertSee('id="pptxCounter"', false)     // indikator "Slide x dari y"
            ->assertSee('id="pptxZoomIn"', false)      // zoom
            ->assertSee('id="pptxZoomOut"', false)
            ->assertSee('id="pptxZoomFit"', false)
            ->assertSee('id="pptxLoading"', false)     // spinner
            ->assertSee('id="materialPreviewDownload"', false); // unduh sebagai cadangan
    }

    /**
     * Footer modal hanya menyisakan tombol Unduh.
     *
     * "Buka di tab baru" dihapus beserta handler-nya. Tombol cadangan Office
     * Web Viewer di panel galat TIDAK ikut dihapus - itu kontrol berbeda yang
     * hanya muncul saat render lokal gagal.
     */
    public function test_preview_modal_no_longer_offers_open_in_new_tab()
    {
        $freeClass = $this->makeFreeClass([], [[
            'name' => 'Level 1',
            'video_url' => 'https://youtu.be/aaaaaa',
            'ppt_path' => 'free-classes/slides/slide.pptx',
            'ppt_name' => 'slide.pptx',
        ]]);

        $response = $this->get(route('free-classes.show', $freeClass))->assertOk();

        $response->assertDontSee('Buka di tab baru')
            ->assertDontSee('id="materialPreviewOpen"', false)
            // Unduh tetap ada.
            ->assertSee('id="materialPreviewDownload"', false)
            // Cadangan Office Viewer tetap ada.
            ->assertSee('id="pptxOfficeFallback"', false);

        // Handler dan konstanta yang hanya dipakai tombol itu ikut hilang.
        $js = file_get_contents(public_path('assets/js/free-class-preview.js'));

        $this->assertStringNotContainsString('materialPreviewOpen', $js);
        $this->assertStringNotContainsString('op/view.aspx', $js);

        // embed.aspx dipertahankan: dipakai tombol cadangan di panel galat.
        $this->assertStringContainsString('op/embed.aspx', $js);
    }

    /**
     * Kotak pratinjau mengikuti rasio slide, bukan aspect-ratio tetap.
     *
     * Nilai batas di JS dan CSS harus sama persis; kalau berbeda, skala fit
     * meleset dan scrollbar vertikal muncul lagi.
     */
    public function test_preview_box_follows_slide_ratio_without_scrollbars()
    {
        $js = file_get_contents(public_path('assets/js/free-class-preview.js'));
        $css = file_get_contents(public_path('assets/css/free-class.css'));

        // Rasio dibaca dari ukuran inline yang ditulis PPTXjs, tidak dipatok.
        $this->assertStringContainsString('parseFloat(slide.style.width)', $js);
        $this->assertStringContainsString('function layoutStage', $js);

        // Skala fit dari min(lebar, tinggi) - bukan lebar saja.
        $this->assertStringContainsString('Math.min(byWidth, byHeight)', $js);

        // Kontrak nilai JS <-> CSS.
        $this->assertStringContainsString('MAX_VIEWPORT_H = 0.85', $js);
        $this->assertStringContainsString('MAX_VIEWPORT_W = 0.90', $js);
        $this->assertStringContainsString('STAGE_PADDING = 24', $js);
        $this->assertStringContainsString('max-height: 85vh', $css);
        $this->assertStringContainsString('max-width: 90vw', $css);

        // Kotak pratinjau memakai ukuran hasil hitungan, bukan aspect-ratio
        // tetap. (16/9 di tempat lain pada berkas ini - kartu video, thumbnail -
        // tidak ada hubungannya, jadi pemeriksaan dibatasi ke aturan ini saja.)
        preg_match(
            '/#materialPreviewModal\.is-ppt \.free-class-preview-frame \{(.*?)\}/s',
            $css,
            $frameRule
        );

        $this->assertNotEmpty($frameRule, 'Aturan kotak pratinjau PPT tidak ditemukan.');
        $this->assertStringContainsString('var(--preview-w', $frameRule[1]);
        $this->assertStringContainsString('var(--preview-h', $frameRule[1]);
        $this->assertStringNotContainsString('16 / 9', $frameRule[1]);

        // Panggung tidak menggulir saat fit; hanya saat zoom melebihi fit.
        $this->assertStringContainsString('.free-class-pptx-stage.is-zoomed', $css);
        $this->assertStringContainsString("classList.toggle('is-zoomed'", $js);
    }

    public function test_preview_button_carries_the_file_url_for_the_viewer()
    {
        $freeClass = $this->makeFreeClass([], [[
            'name' => 'Level 1',
            'video_url' => 'https://youtu.be/aaaaaa',
            'ppt_path' => 'free-classes/slides/slide.pptx',
            'ppt_name' => 'slide.pptx',
        ]]);

        $level = $freeClass->levels->first();

        $this->get(route('free-classes.show', $freeClass))
            ->assertOk()
            // URL berkas dipakai JS untuk membangun URL Google Docs Viewer.
            ->assertSee('data-preview-url="' . e($level->ppt_url) . '"', false)
            ->assertSee('data-preview-kind="ppt"', false);
    }

    public function test_only_the_first_level_panel_is_visible_initially()
    {
        $freeClass = $this->makeFreeClass([], [
            ['name' => 'Level 1', 'video_url' => 'https://youtu.be/aaaaaa'],
            ['name' => 'Level 2', 'video_url' => 'https://youtu.be/bbbbbb'],
        ]);

        $html = $this->get(route('free-classes.show', $freeClass))->assertOk()->getContent();

        [$first, $second] = $freeClass->levels->all();

        $this->assertStringContainsString('id="level-panel-' . $first->id . '"', $html);
        $this->assertSame(1, substr_count($html, 'free-class-level-panel is-active'));

        // Panel kedua dirender tetapi disembunyikan.
        $secondPanel = substr($html, strpos($html, 'id="level-panel-' . $second->id . '"'), 200);
        $this->assertStringContainsString('hidden', $secondPanel);
    }

    /**
     * Kelas dengan satu level harus tetap berjalan normal (tidak error,
     * tab tetap dirender walau hanya satu).
     */
    public function test_single_level_class_still_works()
    {
        $freeClass = $this->makeFreeClass();

        $this->get(route('free-classes.show', $freeClass))
            ->assertOk()
            ->assertSee('Level 1')
            ->assertSee($freeClass->title);

        $this->assertCount(1, $freeClass->levels);
    }

    public function test_detail_page_handles_a_class_without_any_level()
    {
        $freeClass = FreeClass::create([
            'title' => 'Belum Ada Materi',
            'description' => 'Kelas tanpa level.',
            'is_active' => true,
        ]);

        $this->get(route('free-classes.show', $freeClass))
            ->assertOk()
            ->assertSee('Materi untuk kelas ini belum tersedia.');
    }

    public function test_detail_page_of_an_inactive_free_class_is_not_found()
    {
        $freeClass = $this->makeFreeClass(['is_active' => false]);

        $this->get(route('free-classes.show', $freeClass))->assertNotFound();
    }

    public function test_detail_page_escapes_user_supplied_content()
    {
        $freeClass = $this->makeFreeClass(
            ['title' => 'XSS Probe', 'description' => '<img src=x onerror=alert(2)>'],
            [['name' => '<script>alert(1)</script>', 'video_url' => 'https://youtu.be/aaaaaa']]
        );

        $html = $this->get(route('free-classes.show', $freeClass))->assertOk()->getContent();

        $this->assertStringNotContainsString('<img src=x onerror', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(2)&gt;', $html);
    }

    public function test_level_without_downloads_shows_a_clear_message()
    {
        $freeClass = $this->makeFreeClass();

        $this->get(route('free-classes.show', $freeClass))
            ->assertOk()
            ->assertSee('Belum ada modul PDF maupun slide PPT untuk level ini.')
            ->assertDontSee('Unduh PDF');
    }

    /* =================================================================
     | Model
     * ============================================================== */

    public function test_video_url_variants_are_converted_to_embed_urls()
    {
        $cases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://vimeo.com/76979871' => 'https://player.vimeo.com/video/76979871',
        ];

        foreach ($cases as $input => $expected) {
            $level = new FreeClassLevel(['video_url' => $input]);
            $this->assertSame($expected, $level->embed_url, $input);
        }

        // URL langsung ke berkas video tetap diputar lewat <video>.
        $direct = new FreeClassLevel(['video_url' => 'https://cdn.example.com/klip.mp4']);
        $this->assertNull($direct->embed_url);
        $this->assertSame('https://cdn.example.com/klip.mp4', $direct->video_file_url);
    }

    /**
     * Regresi bug 404: URL berkas harus mengikuti host request, bukan APP_URL.
     */
    public function test_file_urls_follow_the_request_host_not_app_url()
    {
        config(['app.url' => 'http://salah-host.test']);

        $freeClass = $this->makeFreeClass(
            ['thumbnail_path' => 'free-classes/thumbnails/thumb.png'],
            [[
                'name' => 'Level 1',
                'video_path' => 'free-classes/videos/klip.mp4',
                'pdf_path' => 'free-classes/modules/modul.pdf',
                'pdf_name' => 'modul.pdf',
                'ppt_path' => 'free-classes/slides/slide.pptx',
                'ppt_name' => 'slide.pptx',
            ]]
        );

        $html = $this->get(route('free-classes.show', $freeClass))->assertOk()->getContent();

        $this->assertStringNotContainsString('salah-host.test', $html);
        $this->assertStringContainsString('/storage/free-classes/videos/klip.mp4', $html);
        $this->assertStringContainsString('/storage/free-classes/modules/modul.pdf', $html);
        $this->assertStringContainsString('/storage/free-classes/slides/slide.pptx', $html);
    }

    public function test_thumbnail_falls_back_to_the_first_level_youtube_cover()
    {
        // 1. Berkas unggahan menang.
        $uploaded = $this->makeFreeClass(['thumbnail_path' => 'free-classes/thumbnails/thumb.png']);
        $this->assertStringContainsString('/storage/free-classes/thumbnails/thumb.png', $uploaded->thumbnail_url);

        // 2. Tanpa thumbnail, pakai cover YouTube dari level pertama.
        $youtube = $this->makeFreeClass(['title' => 'Dari YouTube']);
        $this->assertSame('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $youtube->thumbnail_url);

        // 3. Video unggahan tanpa thumbnail -> null, view memakai placeholder.
        $upload = $this->makeFreeClass(['title' => 'Video Unggahan'], [
            ['name' => 'Level 1', 'video_path' => 'free-classes/videos/klip.mp4'],
        ]);
        $this->assertNull($upload->thumbnail_url);
    }

    public function test_levels_are_ordered_by_sort_order()
    {
        $freeClass = $this->makeFreeClass([], [
            ['name' => 'Ketiga', 'video_url' => 'https://youtu.be/cccccc', 'sort_order' => 5],
            ['name' => 'Pertama', 'video_url' => 'https://youtu.be/aaaaaa', 'sort_order' => 1],
            ['name' => 'Kedua', 'video_url' => 'https://youtu.be/bbbbbb', 'sort_order' => 3],
        ]);

        $this->assertSame(
            ['Pertama', 'Kedua', 'Ketiga'],
            $freeClass->fresh('levels')->levels->pluck('name')->all()
        );
    }

    public function test_ordering_uses_sort_order_then_newest()
    {
        $this->makeFreeClass(['title' => 'Kedua', 'sort_order' => 5]);
        $this->makeFreeClass(['title' => 'Pertama', 'sort_order' => 1]);

        $this->assertSame(
            ['Pertama', 'Kedua'],
            FreeClass::ordered()->pluck('title')->all()
        );
    }
}
