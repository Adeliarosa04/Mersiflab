<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Notification;
use App\Models\Purchase;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Testimoni dari sisi SISWA.
 *
 * Siswa menulis testimoni di sini; testimoni tersimpan dengan status
 * 'pending' dan baru tampil di halaman publik setelah disetujui admin
 * lewat Admin\TestimonialController.
 *
 * Catatan: fitur ini BERBEDA dari rating per-kursus (ClassReview) yang
 * dikirim lewat CourseController@submitRating. Testimoni di sini adalah
 * ulasan tentang platform MersifLab yang tampil di landing page.
 */
class TestimonialController extends Controller
{
    /** Batas testimoni pending per siswa, supaya antrean moderasi tidak dibanjiri. */
    private const MAX_PENDING_PER_USER = 3;

    /**
     * Halaman "My Testimonials": form pengisian + daftar testimoni milik siswa.
     */
    public function index()
    {
        $user = Auth::user();

        // Guru punya halaman kelolanya sendiri; arahkan seperti halaman profil lain.
        if ($user->isTeacher()) {
            return redirect()->route('teacher.profile');
        }

        return view('profile.my-testimonials', [
            'testimonials' => $this->userTestimonials($user),
            'courses' => $this->availableCourses($user),
            'pendingCount' => Testimonial::where('user_id', $user->id)->pending()->count(),
            'maxPending' => self::MAX_PENDING_PER_USER,
        ]);
    }

    /**
     * Simpan testimoni baru dengan status 'pending'.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isStudent()) {
            return redirect()->route('profile')
                ->with('error', 'Hanya siswa yang dapat mengirim testimoni.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:20|max:2000',
            'course_id' => 'nullable|integer|exists:classes,id',
            'position' => 'nullable|string|max:255',
        ], [
            'rating.required' => 'Silakan pilih rating bintang terlebih dahulu.',
            'content.required' => 'Isi testimoni tidak boleh kosong.',
            'content.min' => 'Testimoni minimal 20 karakter agar cukup informatif.',
        ]);

        // Batasi jumlah testimoni yang masih menunggu peninjauan.
        $pendingCount = Testimonial::where('user_id', $user->id)->pending()->count();

        if ($pendingCount >= self::MAX_PENDING_PER_USER) {
            return redirect()->route('my-testimonials')
                ->with('error', 'Anda masih punya ' . $pendingCount . ' testimoni yang menunggu peninjauan admin. Tunggu hingga ditinjau sebelum mengirim lagi.');
        }

        // Kursus yang dipilih harus benar-benar milik siswa tersebut.
        $courseId = $validated['course_id'] ?? null;

        if ($courseId && !$this->availableCourses($user)->contains('id', $courseId)) {
            return back()->withInput()
                ->with('error', 'Anda hanya bisa menulis testimoni untuk kursus yang Anda ikuti.');
        }

        try {
            $testimonial = Testimonial::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                // name & position di-snapshot agar tampilan publik tetap utuh
                // walaupun nanti siswa mengganti nama profilnya.
                'name' => $user->name,
                'position' => $validated['position'] ?? $this->defaultPosition($user, $courseId),
                'content' => $validated['content'],
                'rating' => $validated['rating'],
                'status' => Testimonial::STATUS_PENDING,
                // Belum boleh tampil di publik sebelum disetujui admin.
                'is_published' => false,
            ]);

            $this->notifyAdmins($testimonial, $user);

            if (method_exists($user, 'logActivity')) {
                $user->logActivity('testimonial_submitted', 'Student submitted a testimonial (ID: ' . $testimonial->id . ')');
            }
        } catch (\Throwable $e) {
            Log::error('Testimonial: gagal menyimpan testimoni siswa', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()
                ->with('error', 'Terjadi kendala saat mengirim testimoni. Silakan coba lagi beberapa saat lagi.');
        }

        return redirect()->route('my-testimonials')
            ->with('success', 'Testimoni berhasil dikirim dan menunggu peninjauan admin.');
    }

    /**
     * Siswa boleh menghapus testimoni miliknya yang belum disetujui.
     */
    public function destroy(Testimonial $testimonial)
    {
        $user = Auth::user();

        if ($testimonial->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke testimoni ini.');
        }

        if ($testimonial->isApproved()) {
            return redirect()->route('my-testimonials')
                ->with('error', 'Testimoni yang sudah dipublikasikan hanya bisa dihapus oleh admin.');
        }

        $testimonial->delete();

        return redirect()->route('my-testimonials')
            ->with('success', 'Testimoni berhasil dihapus.');
    }

    /**
     * Daftar testimoni milik siswa, terbaru lebih dulu.
     */
    private function userTestimonials(User $user)
    {
        return Testimonial::where('user_id', $user->id)
            ->with('course')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Kursus yang boleh dipilih siswa: yang sudah dibeli atau sudah di-enroll.
     *
     * Memakai sumber data yang sama dengan halaman My Courses supaya
     * daftarnya konsisten.
     */
    private function availableCourses(User $user)
    {
        try {
            $purchasedIds = Purchase::where('user_id', $user->id)
                ->where('status', 'success')
                ->pluck('class_id');

            $enrolledIds = collect();

            if (Schema::hasTable('class_student')) {
                $enrolledIds = DB::table('class_student')
                    ->where('user_id', $user->id)
                    ->pluck('class_id');
            }

            $ids = $purchasedIds->merge($enrolledIds)->unique()->filter()->values();

            if ($ids->isEmpty()) {
                return collect();
            }

            return ClassModel::whereIn('id', $ids)
                ->orderBy('name')
                ->get(['id', 'name']);
        } catch (\Throwable $e) {
            Log::warning('Testimonial: gagal memuat daftar kursus siswa', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // Daftar kosong lebih baik daripada halaman error; kolom kursus
            // memang opsional.
            return collect();
        }
    }

    /**
     * Posisi/keterangan bawaan yang tampil di bawah nama pada landing page.
     */
    private function defaultPosition(User $user, $courseId): string
    {
        if ($courseId) {
            $course = ClassModel::find($courseId);

            if ($course) {
                return 'Student - ' . $course->name;
            }
        }

        return 'Student di MersifLab';
    }

    /**
     * Beri tahu admin bahwa ada testimoni baru yang perlu ditinjau.
     */
    private function notifyAdmins(Testimonial $testimonial, User $author): void
    {
        try {
            if (!Schema::hasTable('notifications')) {
                return;
            }

            foreach (User::where('role', 'admin')->get() as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'testimonial_submitted',
                    'title' => 'Testimoni Baru Menunggu Peninjauan',
                    'message' => "{$author->name} mengirim testimoni baru dan menunggu persetujuan Anda.",
                    'notifiable_type' => Testimonial::class,
                    'notifiable_id' => $testimonial->id,
                    'is_read' => false,
                ]);
            }
        } catch (\Throwable $e) {
            // Notifikasi bersifat pelengkap - kegagalannya tidak boleh
            // membatalkan testimoni yang sudah tersimpan.
            Log::warning('Testimonial: gagal mengirim notifikasi ke admin', [
                'testimonial_id' => $testimonial->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
