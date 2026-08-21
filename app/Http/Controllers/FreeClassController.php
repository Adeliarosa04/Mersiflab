<?php

namespace App\Http\Controllers;

use App\Models\FreeClass;
use App\Models\FreeClassLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Halaman publik untuk satu Free Course.
 *
 * Kartu di halaman Courses hanya menampilkan thumbnail + judul; video player,
 * deskripsi lengkap, modul PDF, dan slide PPT ada di halaman detail ini.
 */
class FreeClassController extends Controller
{
    public function show(FreeClass $freeClass)
    {
        // Entri nonaktif tidak boleh diakses lewat URL langsung.
        abort_unless($freeClass->is_active, 404);

        // Level materi, sudah terurut. Kelas dengan satu level tetap valid —
        // tab tetap dirender, hanya berisi satu entri.
        $levels = $freeClass->levels()->get();

        // Progres dihitung per pengguna, memakai cara yang sama dengan course
        // berbayar (module_completions): jumlah level selesai / total level.
        $progress = $this->progressFor($freeClass, $levels);

        // Kelas gratis lain untuk navigasi lanjutan di bawah halaman.
        $otherFreeClasses = FreeClass::active()
            ->whereKeyNot($freeClass->getKey())
            ->ordered()
            ->take(3)
            ->get();

        return view('free-classes.show', [
            'freeClass' => $freeClass,
            'levels' => $levels,
            'otherFreeClasses' => $otherFreeClasses,
            'totalLevels' => $progress['total_levels'],
            'completedLevels' => $progress['completed_levels'],
            'progressPercentage' => $progress['progress_percentage'],
            'completedLevelIds' => $progress['completed_ids'],
        ]);
    }

    /**
     * Tandai satu level sebagai selesai untuk pengguna yang sedang login.
     */
    public function completeLevel(Request $request, FreeClass $freeClass, FreeClassLevel $level)
    {
        abort_unless($freeClass->is_active, 404);

        // Level harus benar-benar milik kelas ini.
        abort_unless((int) $level->free_class_id === (int) $freeClass->id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan masuk terlebih dahulu untuk menyimpan progres belajar Anda.');
        }

        try {
            // updateOrInsert: aman diklik berulang, tidak menggandakan baris.
            DB::table('free_class_level_completions')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'free_class_level_id' => $level->id,
                ],
                [
                    'free_class_id' => $freeClass->id,
                    'completed_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('FreeCourse: gagal menyimpan progres level', [
                'user_id' => $user->id,
                'level_id' => $level->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Progres belum tersimpan. Silakan coba lagi.');
        }

        return back()->with('success', 'Level "' . $level->name . '" ditandai selesai.');
    }

    /**
     * Batalkan tanda selesai pada satu level.
     */
    public function uncompleteLevel(Request $request, FreeClass $freeClass, FreeClassLevel $level)
    {
        abort_unless($freeClass->is_active, 404);
        abort_unless((int) $level->free_class_id === (int) $freeClass->id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            DB::table('free_class_level_completions')
                ->where('user_id', $user->id)
                ->where('free_class_level_id', $level->id)
                ->delete();
        } catch (\Throwable $e) {
            Log::error('FreeCourse: gagal membatalkan progres level', [
                'user_id' => $user->id,
                'level_id' => $level->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Tanda selesai pada level "' . $level->name . '" dibatalkan.');
    }

    /**
     * Hitung progres pengguna pada sebuah Free Course.
     *
     * Tamu (belum login) selalu 0% — progres tidak bisa dilacak tanpa akun.
     *
     * @return array{total_levels:int, completed_levels:int, progress_percentage:int, completed_ids:array<int,int>}
     */
    private function progressFor(FreeClass $freeClass, $levels): array
    {
        $totalLevels = $levels->count();

        $empty = [
            'total_levels' => $totalLevels,
            'completed_levels' => 0,
            'progress_percentage' => 0,
            'completed_ids' => [],
        ];

        $user = Auth::user();

        if (!$user || $totalLevels === 0) {
            return $empty;
        }

        try {
            $completedIds = DB::table('free_class_level_completions')
                ->where('user_id', $user->id)
                ->where('free_class_id', $freeClass->id)
                // Hanya hitung level yang MASIH ada, supaya level yang sudah
                // dihapus admin tidak membuat progres melebihi 100%.
                ->whereIn('free_class_level_id', $levels->pluck('id'))
                ->pluck('free_class_level_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        } catch (\Throwable $e) {
            Log::error('FreeCourse: gagal membaca progres level', [
                'user_id' => $user->id,
                'free_class_id' => $freeClass->id,
                'error' => $e->getMessage(),
            ]);

            return $empty;
        }

        $completed = count($completedIds);

        return [
            'total_levels' => $totalLevels,
            'completed_levels' => $completed,
            'progress_percentage' => (int) round(($completed / $totalLevels) * 100),
            'completed_ids' => $completedIds,
        ];
    }
}
