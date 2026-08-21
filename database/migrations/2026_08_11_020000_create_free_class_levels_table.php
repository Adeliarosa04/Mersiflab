<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Level materi untuk Free Class (relasi one-to-many).
 *
 * Satu Free Class dapat memiliki banyak level; setiap level menyimpan
 * videonya sendiri, modul PDF, dan slide PPT.
 *
 * Kolom media lama pada tabel `free_classes` SENGAJA TIDAK DIHAPUS — datanya
 * disalin ke sini sebagai "Level 1" supaya materi yang sudah terlanjur
 * diunggah admin tidak hilang, dan rollback tetap memungkinkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_class_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('free_class_id')->constrained('free_classes')->cascadeOnDelete();

            $table->string('name');

            // Video: tautan (YouTube/Vimeo/URL langsung) ATAU berkas unggahan.
            $table->string('video_url')->nullable();
            $table->string('video_path')->nullable();

            // Modul PDF (opsional).
            $table->string('pdf_path')->nullable();
            $table->string('pdf_name')->nullable();

            // Slide presentasi PPT/PPTX (opsional).
            $table->string('ppt_path')->nullable();
            $table->string('ppt_name')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['free_class_id', 'sort_order']);
        });

        $this->backfillExistingMaterialsAsLevelOne();
    }

    public function down(): void
    {
        Schema::dropIfExists('free_class_levels');
    }

    /**
     * Pindahkan materi yang sudah ada menjadi "Level 1" pada tiap Free Class.
     * Kolom asal tetap dibiarkan utuh sebagai cadangan.
     */
    private function backfillExistingMaterialsAsLevelOne(): void
    {
        $existing = DB::table('free_classes')
            ->select('id', 'video_url', 'video_path', 'pdf_path', 'pdf_name', 'created_at')
            ->get();

        $now = now();
        $rows = [];

        foreach ($existing as $freeClass) {
            $hasMaterial = filled($freeClass->video_url)
                || filled($freeClass->video_path)
                || filled($freeClass->pdf_path);

            if (! $hasMaterial) {
                continue;
            }

            $rows[] = [
                'free_class_id' => $freeClass->id,
                'name' => 'Level 1',
                'video_url' => $freeClass->video_url,
                'video_path' => $freeClass->video_path,
                'pdf_path' => $freeClass->pdf_path,
                'pdf_name' => $freeClass->pdf_name,
                'ppt_path' => null,
                'ppt_name' => null,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('free_class_levels')->insert($rows);
        }
    }
};
