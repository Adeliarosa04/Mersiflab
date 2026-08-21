<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat penyelesaian level Free Course per pengguna.
 *
 * Strukturnya sengaja meniru module_completions (course berbayar) supaya cara
 * menghitung progres kedua jenis kursus konsisten:
 *   progress = jumlah level selesai / jumlah seluruh level * 100
 *
 * Tanpa tabel ini, progres Free Course tidak punya sumber data per pengguna
 * dan hanya bisa ditebak dari kelengkapan materi - itulah sebabnya progres
 * selalu tampil 100%.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('free_class_level_completions')) {
            return;
        }

        Schema::create('free_class_level_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('free_class_id');
            $table->unsignedBigInteger('free_class_level_id');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Satu level hanya boleh tercatat sekali per pengguna.
            $table->unique(['user_id', 'free_class_level_id'], 'fclc_user_level_unique');
            $table->index(['user_id', 'free_class_id'], 'fclc_user_class_index');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('free_class_id')->references('id')->on('free_classes')->cascadeOnDelete();
            $table->foreign('free_class_level_id')->references('id')->on('free_class_levels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_class_level_completions');
    }
};
