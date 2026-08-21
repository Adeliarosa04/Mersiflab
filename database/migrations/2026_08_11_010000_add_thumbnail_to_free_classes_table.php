<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thumbnail untuk kartu Free Class pada halaman Courses.
 *
 * Kolom tambahan bersifat nullable: entri lama tetap valid, dan bila kosong
 * tampilan jatuh ke cover video (YouTube) atau placeholder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('free_classes', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('free_classes', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
        });
    }
};
