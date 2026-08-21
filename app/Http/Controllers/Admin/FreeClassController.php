<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FreeClass;
use App\Models\FreeClassLevel;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pengelolaan "Free Class" oleh admin (CRUD sederhana).
 *
 * Sejak dukungan multi-level, materi tidak lagi disimpan langsung pada
 * Free Class melainkan pada relasi one-to-many FreeClassLevel. Form admin
 * mengirim array `levels[]` yang disinkronkan di sini: level baru dibuat,
 * level lama diperbarui, level yang dihapus dari form ikut dihapus beserta
 * berkasnya.
 */
class FreeClassController extends Controller
{
    /**
     * Maksimal ukuran unggahan (KB). Dibatasi mengikuti
     * upload_max_filesize / post_max_size pada konfigurasi PHP.
     */
    private const MAX_VIDEO_KB = 40960;
    private const MAX_PDF_KB = 20480;
    private const MAX_PPT_KB = 20480;
    private const MAX_THUMBNAIL_KB = 4096;

    /** Berkas yang baru tersimpan pada request ini — dibersihkan bila gagal. */
    private array $storedPaths = [];

    public function index()
    {
        $freeClasses = FreeClass::withCount('levels')->ordered()->get();

        // Tanpa symlink public/storage, video & modul yang diunggah akan 404
        // di frontend. Beri tahu admin di tempat yang akan dia lihat.
        $storageLinkMissing = ! FreeClass::storageLinkExists();

        return view('admin.free-classes.index', compact('freeClasses', 'storageLinkMissing'));
    }

    public function create()
    {
        return view('admin.free-classes.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        try {
            $freeClass = DB::transaction(function () use ($request, $validated) {
                $data = [
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'is_active' => $request->boolean('is_active'),
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'created_by' => auth()->id(),
                ];

                if ($request->hasFile('thumbnail_file')) {
                    $data['thumbnail_path'] = $this->storeFile($request->file('thumbnail_file'), 'thumbnails');
                }

                $freeClass = FreeClass::create($data);

                $this->syncLevels($freeClass, $request->input('levels', []), $request);

                return $freeClass;
            });
        } catch (\Throwable $e) {
            $this->discardStoredFiles();
            Log::error('Free Class store failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Free Class gagal disimpan. Silakan coba lagi.');
        }

        auth()->user()->logActivity('free_class_created', "Menambahkan free class: {$freeClass->title}");

        return redirect()->route('admin.free-classes.index')
            ->with('success', 'Free Class berhasil ditambahkan.');
    }

    public function edit(FreeClass $freeClass)
    {
        $freeClass->load('levels');

        return view('admin.free-classes.edit', compact('freeClass'));
    }

    public function update(Request $request, FreeClass $freeClass)
    {
        $validated = $this->validateRequest($request, $freeClass);

        try {
            DB::transaction(function () use ($request, $validated, $freeClass) {
                $data = [
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'is_active' => $request->boolean('is_active'),
                    'sort_order' => $validated['sort_order'] ?? 0,
                ];

                if ($request->hasFile('thumbnail_file')) {
                    $this->deleteFile($freeClass->thumbnail_path);
                    $data['thumbnail_path'] = $this->storeFile($request->file('thumbnail_file'), 'thumbnails');
                } elseif ($request->boolean('remove_thumbnail')) {
                    $this->deleteFile($freeClass->thumbnail_path);
                    $data['thumbnail_path'] = null;
                }

                $freeClass->update($data);

                $this->syncLevels($freeClass, $request->input('levels', []), $request);
            });
        } catch (\Throwable $e) {
            $this->discardStoredFiles();
            Log::error('Free Class update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Free Class gagal diperbarui. Silakan coba lagi.');
        }

        auth()->user()->logActivity('free_class_updated', "Mengubah free class: {$freeClass->title}");

        return redirect()->route('admin.free-classes.index')
            ->with('success', 'Free Class berhasil diperbarui.');
    }

    public function destroy(FreeClass $freeClass)
    {
        $title = $freeClass->title;

        // Berkas dihapus manual; cascade database hanya menghapus barisnya.
        foreach ($freeClass->levels as $level) {
            foreach ($level->filePaths() as $path) {
                $this->deleteFile($path);
            }
        }

        $this->deleteFile($freeClass->thumbnail_path);
        $this->deleteFile($freeClass->video_path);
        $this->deleteFile($freeClass->pdf_path);

        $freeClass->delete();

        auth()->user()->logActivity('free_class_deleted', "Menghapus free class: {$title}");

        return redirect()->route('admin.free-classes.index')
            ->with('success', 'Free Class berhasil dihapus.');
    }

    /**
     * Aktif/nonaktifkan tanpa membuka form edit.
     */
    public function toggleActive(FreeClass $freeClass)
    {
        $freeClass->update(['is_active' => ! $freeClass->is_active]);

        $state = $freeClass->is_active ? 'mengaktifkan' : 'menonaktifkan';
        auth()->user()->logActivity('free_class_updated', "Admin {$state} free class: {$freeClass->title}");

        return redirect()->route('admin.free-classes.index')
            ->with('success', 'Status Free Class berhasil diperbarui.');
    }

    /* =====================================================================
     | Sinkronisasi level
     * ================================================================== */

    /**
     * Samakan level di database dengan yang dikirim form.
     *
     * @param  array<int, array<string, mixed>>  $levels
     */
    private function syncLevels(FreeClass $freeClass, array $levels, Request $request): void
    {
        $keptIds = [];
        $position = 0;

        foreach (array_keys($levels) as $index) {
            $input = $levels[$index];

            // Level lama hanya boleh dipakai ulang bila memang milik Free Class
            // ini — mencegah id dari request menimpa level milik kelas lain.
            $existing = null;
            if (! empty($input['id'])) {
                $existing = $freeClass->levels()->whereKey($input['id'])->first();
            }

            $data = [
                'name' => $input['name'],
                'sort_order' => $position++,
            ];

            $videoFile = $request->file("levels.{$index}.video_file");
            $pdfFile = $request->file("levels.{$index}.pdf_file");
            $pptFile = $request->file("levels.{$index}.ppt_file");

            // --- Video: berkas unggahan menang atas tautan ---
            if ($videoFile) {
                if ($existing) {
                    $this->deleteFile($existing->video_path);
                }
                $data['video_path'] = $this->storeFile($videoFile, 'videos');
                $data['video_url'] = null;
            } elseif (filled($input['video_url'] ?? null)) {
                if ($existing) {
                    $this->deleteFile($existing->video_path);
                }
                $data['video_path'] = null;
                $data['video_url'] = $input['video_url'];
            }

            // --- Modul PDF ---
            if ($pdfFile) {
                if ($existing) {
                    $this->deleteFile($existing->pdf_path);
                }
                $data['pdf_path'] = $this->storeFile($pdfFile, 'modules');
                $data['pdf_name'] = $pdfFile->getClientOriginalName();
            } elseif ($existing && ! empty($input['remove_pdf'])) {
                $this->deleteFile($existing->pdf_path);
                $data['pdf_path'] = null;
                $data['pdf_name'] = null;
            }

            // --- Slide PPT ---
            if ($pptFile) {
                if ($existing) {
                    $this->deleteFile($existing->ppt_path);
                }
                $data['ppt_path'] = $this->storeFile($pptFile, 'slides');
                $data['ppt_name'] = $pptFile->getClientOriginalName();
            } elseif ($existing && ! empty($input['remove_ppt'])) {
                $this->deleteFile($existing->ppt_path);
                $data['ppt_path'] = null;
                $data['ppt_name'] = null;
            }

            if ($existing) {
                $existing->update($data);
                $keptIds[] = $existing->id;
            } else {
                $keptIds[] = $freeClass->levels()->create($data)->id;
            }
        }

        // Level yang tidak lagi dikirim form berarti dihapus admin.
        $removed = $freeClass->levels()->whereKeyNot($keptIds)->get();

        foreach ($removed as $level) {
            foreach ($level->filePaths() as $path) {
                $this->deleteFile($path);
            }
            $level->delete();
        }

        $freeClass->unsetRelation('levels');
    }

    /* =====================================================================
     | Validasi
     * ================================================================== */

    /**
     * Aturan validasi bersama untuk store & update.
     */
    private function validateRequest(Request $request, ?FreeClass $freeClass = null): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:' . self::MAX_THUMBNAIL_KB,
            'sort_order' => 'nullable|integer|min:0|max:9999',

            'levels' => 'required|array|min:1',
            'levels.*.id' => 'nullable|integer',
            'levels.*.name' => 'required|string|max:255',
            'levels.*.video_url' => 'nullable|url|max:2048',
            'levels.*.video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:' . self::MAX_VIDEO_KB,
            'levels.*.pdf_file' => 'nullable|file|mimes:pdf|max:' . self::MAX_PDF_KB,
            // PPTX secara teknis adalah arsip ZIP, sehingga finfo pada banyak
            // sistem melaporkannya sebagai application/zip. Karena itu aturan
            // `mimes` saja akan menolak berkas PPTX yang sah. Kombinasi yang
            // dipakai: `extensions` mengunci ekstensi berkas, `mimetypes`
            // menyaring tipe yang mungkin dilaporkan untuk PPT/PPTX.
            'levels.*.ppt_file' => [
                'nullable',
                'file',
                'extensions:ppt,pptx',
                'mimetypes:application/vnd.ms-powerpoint,'
                    . 'application/vnd.openxmlformats-officedocument.presentationml.presentation,'
                    . 'application/zip,application/x-zip-compressed,application/octet-stream',
                'max:' . self::MAX_PPT_KB,
            ],
        ];

        $messages = [
            'levels.required' => 'Tambahkan minimal satu level materi.',
            'levels.min' => 'Tambahkan minimal satu level materi.',
            'levels.*.name.required' => 'Nama level wajib diisi.',
            'levels.*.video_url.url' => 'Tautan video tidak valid.',
            'levels.*.video_file.mimetypes' => 'Video harus berformat MP4, WebM, atau OGG.',
            'levels.*.video_file.max' => 'Ukuran video maksimal 40 MB. Untuk video besar, gunakan tautan YouTube/Vimeo.',
            'levels.*.pdf_file.mimes' => 'Modul harus berupa berkas PDF.',
            'levels.*.pdf_file.max' => 'Ukuran modul PDF maksimal 20 MB.',
            'levels.*.ppt_file.extensions' => 'Slide harus berupa berkas PPT atau PPTX.',
            'levels.*.ppt_file.mimetypes' => 'Slide harus berupa berkas PPT atau PPTX.',
            'levels.*.ppt_file.max' => 'Ukuran slide PPT maksimal 20 MB.',
            'thumbnail_file.image' => 'Thumbnail harus berupa gambar (JPG, PNG, atau WebP).',
            'thumbnail_file.mimes' => 'Thumbnail harus berformat JPG, PNG, atau WebP.',
            'thumbnail_file.max' => 'Ukuran thumbnail maksimal 4 MB.',
        ];

        $validator = validator($request->all(), $rules, $messages);

        // Setiap level wajib punya video: berkas baru, tautan, atau video
        // yang sudah tersimpan sebelumnya pada level tersebut.
        $validator->after(function ($validator) use ($request, $freeClass) {
            foreach ((array) $request->input('levels', []) as $index => $input) {
                $hasNewVideo = $request->hasFile("levels.{$index}.video_file")
                    || filled($input['video_url'] ?? null);

                if ($hasNewVideo) {
                    continue;
                }

                $hasExistingVideo = false;
                if ($freeClass && ! empty($input['id'])) {
                    $hasExistingVideo = (bool) $freeClass->levels()
                        ->whereKey($input['id'])
                        ->where(fn ($q) => $q->whereNotNull('video_path')->orWhereNotNull('video_url'))
                        ->exists();
                }

                if (! $hasExistingVideo) {
                    $validator->errors()->add(
                        "levels.{$index}.video_url",
                        'Isi tautan video atau unggah berkas video untuk level ini.'
                    );
                }
            }
        });

        return $validator->validate();
    }

    /* =====================================================================
     | Berkas
     * ================================================================== */

    /**
     * Simpan berkas dan catat path-nya, agar bisa dibersihkan bila
     * transaksi gagal di tengah jalan.
     *
     * Nama berkas dibuat sendiri (acak + ekstensi asli) dan TIDAK memakai
     * $file->store() bawaan. store() menamai berkas dari ekstensi hasil
     * tebakan MIME, sedangkan PPTX secara teknis adalah arsip ZIP sehingga
     * tersimpan sebagai ".zip". Ekstensi yang salah membuat Google Docs
     * Viewer menolak merender slide, dan berkas terunduh dengan tipe keliru.
     */
    private function storeFile(UploadedFile $file, string $folder): string
    {
        $path = $file->storeAs(
            "free-classes/{$folder}",
            Str::random(40) . '.' . $this->safeExtension($file),
            'public'
        );

        $this->storedPaths[] = $path;

        return $path;
    }

    /**
     * Ekstensi berkas yang aman dipakai sebagai nama di disk.
     *
     * Diambil dari nama asli (agar .pptx tetap .pptx), dibersihkan dari
     * karakter selain huruf/angka, dan ditolak bila termasuk ekstensi yang
     * dapat dieksekusi. Tipe berkas itu sendiri sudah disaring lebih dulu
     * oleh aturan validasi.
     */
    private function safeExtension(UploadedFile $file): string
    {
        $blocked = ['php', 'phtml', 'phar', 'phps', 'html', 'htm', 'svg', 'js'];

        $extension = preg_replace('/[^a-z0-9]/', '', strtolower((string) $file->getClientOriginalExtension()));

        if ($extension === '' || in_array($extension, $blocked, true)) {
            $extension = preg_replace('/[^a-z0-9]/', '', strtolower((string) $file->guessExtension()));
        }

        return $extension !== '' && ! in_array($extension, $blocked, true) ? $extension : 'bin';
    }

    private function deleteFile(?string $path): void
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Buang berkas yang sempat tersimpan ketika penyimpanan gagal.
     */
    private function discardStoredFiles(): void
    {
        foreach ($this->storedPaths as $path) {
            $this->deleteFile($path);
        }

        $this->storedPaths = [];
    }
}
