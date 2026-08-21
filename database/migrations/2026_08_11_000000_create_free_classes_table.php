<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk fitur "Free Class" — kelas gratis yang dikelola admin dan
 * ditampilkan di bagian atas halaman Courses.
 *
 * Tabel baru, berdiri sendiri, tidak menyentuh tabel/relasi yang sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_classes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');

            // Video bisa berupa tautan (YouTube/Vimeo/URL langsung) ATAU berkas
            // yang diunggah admin. Keduanya nullable — minimal salah satu diisi,
            // divalidasi di controller.
            $table->string('video_url')->nullable();
            $table->string('video_path')->nullable();

            // Modul PDF (opsional).
            $table->string('pdf_path')->nullable();
            $table->string('pdf_name')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_classes');
    }
};
