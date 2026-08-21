<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refaktorisasi testimoni: dari "dibuat admin" menjadi "dikirim siswa,
 * dimoderasi admin".
 *
 * Kolom lama (name, position, content, avatar, is_published, admin_id) SENGAJA
 * dipertahankan supaya data dan kode yang sudah ada tidak rusak:
 *  - name/position tetap diisi (di-snapshot dari data siswa saat submit),
 *    sehingga tampilan landing page tidak perlu diubah strukturnya.
 *  - is_published tetap ada dan otomatis disinkronkan dengan status baru
 *    (approved = true, pending/rejected = false), jadi kode lama yang masih
 *    membaca is_published tetap memberi hasil yang benar.
 *
 * Kolom baru:
 *  - user_id       : siswa penulis testimoni.
 *  - course_id     : kursus yang diulas (opsional).
 *  - rating        : 1-5 bintang.
 *  - status        : pending | approved | rejected.
 *  - reviewed_by   : admin yang memoderasi.
 *  - reviewed_at   : waktu moderasi.
 *  - rejection_reason : catatan admin saat menolak.
 *
 * Ditulis idempoten agar aman dijalankan ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('testimonials')) {
            return;
        }

        Schema::table('testimonials', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonials', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')
                    ->comment('Siswa penulis testimoni');
            }

            if (!Schema::hasColumn('testimonials', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->after('user_id')
                    ->comment('Kursus yang diulas (opsional)');
            }

            if (!Schema::hasColumn('testimonials', 'rating')) {
                $table->unsignedTinyInteger('rating')->nullable()->after('content')
                    ->comment('Rating bintang 1-5');
            }

            if (!Schema::hasColumn('testimonials', 'status')) {
                $table->string('status', 20)->default('pending')->after('is_published')
                    ->comment('pending | approved | rejected');
            }

            if (!Schema::hasColumn('testimonials', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('admin_id')
                    ->comment('Admin yang menyetujui/menolak');
            }

            if (!Schema::hasColumn('testimonials', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (!Schema::hasColumn('testimonials', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('reviewed_at');
            }
        });

        $this->addForeignKeys();
        $this->addIndexes();
        $this->backfillStatus();
    }

    public function down(): void
    {
        if (!Schema::hasTable('testimonials')) {
            return;
        }

        // Lepas foreign key dulu supaya kolomnya bisa dihapus.
        foreach (['testimonials_user_id_foreign', 'testimonials_course_id_foreign', 'testimonials_reviewed_by_foreign'] as $fk) {
            try {
                Schema::table('testimonials', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk);
                });
            } catch (\Throwable $e) {
                // Sudah tidak ada - abaikan.
            }
        }

        Schema::table('testimonials', function (Blueprint $table) {
            foreach (['user_id', 'course_id', 'rating', 'status', 'reviewed_by', 'reviewed_at', 'rejection_reason'] as $column) {
                if (Schema::hasColumn('testimonials', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Foreign key dipasang terpisah dan defensif: pada database yang datanya
     * sudah tidak konsisten, kegagalan FK tidak boleh menggagalkan migrasi.
     */
    private function addForeignKeys(): void
    {
        $targets = [
            'user_id' => 'users',
            'reviewed_by' => 'users',
            'course_id' => 'classes',
        ];

        foreach ($targets as $column => $referenced) {
            try {
                if (!Schema::hasTable($referenced) || $this->foreignKeyExists('testimonials', "testimonials_{$column}_foreign")) {
                    continue;
                }

                Schema::table('testimonials', function (Blueprint $table) use ($column, $referenced) {
                    $table->foreign($column)->references('id')->on($referenced)->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Relasi tetap berjalan lewat Eloquent walau FK gagal dipasang.
            }
        }
    }

    private function addIndexes(): void
    {
        foreach ([
            'testimonials_status_index' => ['status'],
            'testimonials_user_id_index' => ['user_id'],
        ] as $name => $columns) {
            try {
                if ($this->indexExists('testimonials', $name)) {
                    continue;
                }

                Schema::table('testimonials', function (Blueprint $table) use ($columns, $name) {
                    $table->index($columns, $name);
                });
            } catch (\Throwable $e) {
                // Index hanya optimasi.
            }
        }
    }

    /**
     * Testimoni lama belum punya status. Yang sudah terbit dianggap approved,
     * sisanya menunggu moderasi - supaya landing page tidak tiba-tiba kosong.
     */
    private function backfillStatus(): void
    {
        try {
            DB::table('testimonials')->where('is_published', true)
                ->whereIn('status', ['pending', ''])
                ->update(['status' => 'approved']);

            DB::table('testimonials')->whereNull('status')->update(['status' => 'pending']);
        } catch (\Throwable $e) {
            // Backfill bersifat pelengkap.
        }
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        foreach (Schema::getForeignKeys($table) as $key) {
            if (($key['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }

    private function indexExists(string $table, string $name): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }
};
