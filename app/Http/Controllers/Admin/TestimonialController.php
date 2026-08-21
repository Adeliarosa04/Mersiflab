<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Moderasi testimoni dari sisi ADMIN.
 *
 * Setelah refaktorisasi, testimoni ditulis oleh SISWA (lihat
 * App\Http\Controllers\TestimonialController). Admin tidak lagi membuat
 * testimoni sendiri - perannya hanya menyetujui, menolak, atau menghapus.
 */
class TestimonialController extends Controller
{
    /**
     * Dashboard moderasi dengan filter tab status.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', Testimonial::STATUS_PENDING);

        // Nilai di luar daftar status dianggap "semua".
        if (!in_array($status, Testimonial::statuses(), true)) {
            $status = 'all';
        }

        $query = Testimonial::with(['user', 'course', 'reviewer'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $testimonials = $query->paginate(25)->withQueryString();

        return view('admin.testimonials.index', [
            'testimonials' => $testimonials,
            'activeStatus' => $status,
            'counts' => $this->statusCounts(),
        ]);
    }

    /**
     * Detail satu testimoni untuk ditinjau.
     */
    public function show(Testimonial $testimonial)
    {
        $testimonial->load(['user', 'course', 'reviewer']);

        return view('admin.testimonials.show', compact('testimonial'));
    }

    /**
     * Setujui / publikasikan testimoni.
     */
    public function approve(Testimonial $testimonial)
    {
        if ($testimonial->isApproved()) {
            return redirect()->back()->with('error', 'Testimoni ini sudah dipublikasikan.');
        }

        $testimonial->approve(auth()->id());

        $this->notifyAuthor(
            $testimonial,
            'testimonial_approved',
            'Testimoni Anda Dipublikasikan',
            'Terima kasih! Testimoni Anda telah disetujui admin dan kini tampil di halaman utama MersifLab.'
        );

        $this->logAdminActivity('testimonial_approved', 'Approved testimonial ID: ' . $testimonial->id);

        return redirect()->back()->with('success', 'Testimoni berhasil disetujui dan dipublikasikan.');
    }

    /**
     * Tolak testimoni beserta alasannya (opsional).
     */
    public function reject(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $reason = $validated['rejection_reason'] ?? null;

        $testimonial->reject(auth()->id(), $reason);

        $this->notifyAuthor(
            $testimonial,
            'testimonial_rejected',
            'Testimoni Belum Dapat Dipublikasikan',
            'Testimoni Anda belum dapat kami publikasikan.'
                . ($reason ? ' Catatan admin: ' . $reason : '')
                . ' Anda dapat mengirim testimoni baru kapan saja.'
        );

        $this->logAdminActivity('testimonial_rejected', 'Rejected testimonial ID: ' . $testimonial->id);

        return redirect()->back()->with('success', 'Testimoni telah ditolak.');
    }

    /**
     * Kembalikan testimoni ke antrean peninjauan.
     */
    public function unpublish(Testimonial $testimonial)
    {
        $testimonial->markPending(auth()->id());

        $this->logAdminActivity('testimonial_unpublished', 'Moved testimonial back to pending, ID: ' . $testimonial->id);

        return redirect()->back()->with('success', 'Testimoni dikembalikan ke status pending.');
    }

    /**
     * Alias lama dari tombol publish/unpublish.
     *
     * Route admin.testimonials.togglePublish sudah dipakai sebelum
     * refaktorisasi, jadi tetap dipertahankan agar tidak ada tautan/bookmark
     * yang rusak. Perilakunya kini mengikuti status baru.
     */
    public function togglePublish(Testimonial $testimonial)
    {
        return $testimonial->isApproved()
            ? $this->unpublish($testimonial)
            : $this->approve($testimonial);
    }

    /**
     * Pembuatan testimoni oleh admin sudah DIHAPUS.
     *
     * Route resource tetap terdaftar supaya tidak ada rute yang hilang, tetapi
     * diarahkan kembali ke dashboard moderasi dengan penjelasan.
     */
    public function create()
    {
        return $this->redirectToModeration();
    }

    public function store(Request $request)
    {
        return $this->redirectToModeration();
    }

    /**
     * Admin masih boleh merapikan isi testimoni (mis. typo) sebelum terbit.
     * Fitur ini sudah ada sebelumnya dan sengaja dipertahankan.
     */
    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'content' => 'required|string|max:2000',
            'rating' => 'nullable|integer|min:1|max:5',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            // remove old avatar file if exists
            if ($testimonial->avatar && Storage::disk('public')->exists($testimonial->avatar)) {
                Storage::disk('public')->delete($testimonial->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('testimonials', 'public');
        }

        // Status TIDAK diubah di sini - perubahan status hanya lewat tombol
        // Approve/Reject supaya jejak moderasinya jelas.
        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial)
    {
        // delete avatar file if exists
        if ($testimonial->avatar && Storage::disk('public')->exists($testimonial->avatar)) {
            Storage::disk('public')->delete($testimonial->avatar);
        }

        $testimonial->delete();

        $this->logAdminActivity('testimonial_deleted', 'Deleted testimonial ID: ' . $testimonial->id);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    /**
     * Jumlah testimoni per status untuk badge pada tab filter.
     *
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        try {
            $counts = Testimonial::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
        } catch (\Throwable $e) {
            Log::error('Testimonial: gagal menghitung status', ['error' => $e->getMessage()]);
            $counts = [];
        }

        return [
            Testimonial::STATUS_PENDING => (int) ($counts[Testimonial::STATUS_PENDING] ?? 0),
            Testimonial::STATUS_APPROVED => (int) ($counts[Testimonial::STATUS_APPROVED] ?? 0),
            Testimonial::STATUS_REJECTED => (int) ($counts[Testimonial::STATUS_REJECTED] ?? 0),
            'all' => array_sum($counts),
        ];
    }

    /**
     * Kabari siswa penulis tentang hasil moderasi.
     */
    private function notifyAuthor(Testimonial $testimonial, string $type, string $title, string $message): void
    {
        try {
            if (!$testimonial->user_id || !Schema::hasTable('notifications')) {
                return;
            }

            Notification::create([
                'user_id' => $testimonial->user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'notifiable_type' => Testimonial::class,
                'notifiable_id' => $testimonial->id,
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Testimonial: gagal mengirim notifikasi ke penulis', [
                'testimonial_id' => $testimonial->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function logAdminActivity(string $type, string $description): void
    {
        try {
            $admin = auth()->user();

            if ($admin && method_exists($admin, 'logActivity')) {
                $admin->logActivity($type, $description);
            }
        } catch (\Throwable $e) {
            // Pencatatan aktivitas tidak boleh menggagalkan aksi moderasi.
        }
    }

    private function redirectToModeration()
    {
        return redirect()->route('admin.testimonials.index')
            ->with('error', 'Testimoni kini ditulis oleh siswa melalui halaman profil mereka. Admin hanya bertugas menyetujui atau menolak testimoni yang masuk.');
    }
}
