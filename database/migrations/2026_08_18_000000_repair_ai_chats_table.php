<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaikan tabel ai_chats.
 *
 * Latar belakang: pada beberapa environment, migrasi create_ai_chats_table
 * sudah tercatat "Ran" di tabel migrations padahal tabel ai_chats-nya tidak
 * pernah benar-benar terbentuk (mis. database di-restore dari dump lama).
 * Akibatnya setiap request ke AI Assistant melempar QueryException
 * "Base table or view not found" dan berujung 500 Internal Server Error.
 *
 * Migrasi ini sengaja ditulis idempoten: membuat tabel hanya bila belum ada,
 * dan menambah kolom hanya bila kolomnya belum ada. Aman dijalankan pada
 * database yang sudah sehat maupun yang tabelnya hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_chats')) {
            Schema::create('ai_chats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('session_id')->nullable();
                $table->text('question');
                $table->longText('answer');
                $table->timestamps();
            });
        }

        Schema::table('ai_chats', function (Blueprint $table) {
            // Token tamu berbasis cookie: bertahan melewati session regenerate
            // saat login, sehingga riwayat chat tamu bisa dimigrasikan ke akun.
            if (!Schema::hasColumn('ai_chats', 'guest_token')) {
                $table->string('guest_token', 64)->nullable()->after('session_id');
            }

            // Penanda kapan sebuah baris riwayat tamu dipindahkan ke akun.
            if (!Schema::hasColumn('ai_chats', 'migrated_at')) {
                $table->timestamp('migrated_at')->nullable()->after('answer');
            }
        });

        // Index untuk query kuota & riwayat yang dipanggil pada tiap pesan.
        $this->addIndexIfMissing('ai_chats', 'ai_chats_guest_token_index', ['guest_token']);
        $this->addIndexIfMissing('ai_chats', 'ai_chats_session_id_index', ['session_id']);
        $this->addIndexIfMissing('ai_chats', 'ai_chats_user_id_created_at_index', ['user_id', 'created_at']);
    }

    public function down(): void
    {
        // Tabel ai_chats TIDAK di-drop di sini: pembuatannya adalah milik
        // migrasi create_ai_chats_table. Rollback hanya mencabut tambahan
        // dari migrasi ini supaya data riwayat chat tidak ikut terhapus.
        if (!Schema::hasTable('ai_chats')) {
            return;
        }

        $this->dropIndexIfExists('ai_chats', 'ai_chats_guest_token_index');
        $this->dropIndexIfExists('ai_chats', 'ai_chats_user_id_created_at_index');

        Schema::table('ai_chats', function (Blueprint $table) {
            foreach (['guest_token', 'migrated_at'] as $column) {
                if (Schema::hasColumn('ai_chats', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        try {
            if ($this->indexExists($table, $indexName)) {
                return;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->index($columns, $indexName);
            });
        } catch (\Throwable $e) {
            // Index hanya optimasi - kegagalan tidak boleh menggagalkan migrasi.
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            if (!$this->indexExists($table, $indexName)) {
                return;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->dropIndex($indexName);
            });
        } catch (\Throwable $e) {
            // Diabaikan dengan alasan yang sama seperti addIndexIfMissing().
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }
};
