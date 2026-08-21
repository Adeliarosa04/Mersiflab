<?php

namespace App\Console\Commands;

use App\Models\AiChat;
use App\Services\KnowledgeBaseService;
use App\Services\LlmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnosa cepat Mersy AI Assistant.
 *
 * Menjawab pertanyaan "kenapa chatbot error?" tanpa perlu membuka log:
 * mengecek tabel riwayat, kredensial LLM, knowledge base, dan (opsional)
 * melakukan satu panggilan nyata ke penyedia LLM.
 *
 * Contoh:
 *   php artisan mersy:check
 *   php artisan mersy:check --ask="Berapa harga paket premium?"
 */
class CheckAiAssistant extends Command
{
    protected $signature = 'mersy:check {--ask= : Kirim satu pertanyaan uji ke penyedia LLM}';

    protected $description = 'Cek kesiapan Mersy AI Assistant (tabel riwayat, API key, knowledge base, koneksi LLM)';

    public function handle(LlmService $llm, KnowledgeBaseService $knowledgeBase): int
    {
        $this->info('Pemeriksaan Mersy AI Assistant');
        $this->newLine();

        $ok = true;

        // 1. Tabel riwayat chat.
        if (Schema::hasTable('ai_chats')) {
            $missing = array_values(array_filter(
                ['guest_token', 'migrated_at'],
                fn ($column) => !Schema::hasColumn('ai_chats', $column)
            ));

            if ($missing) {
                $ok = false;
                $this->line('  <fg=red>[GAGAL]</> Tabel ai_chats kurang kolom: ' . implode(', ', $missing));
                $this->line('          Jalankan: php artisan migrate');
            } else {
                $this->line('  <fg=green>[OK]</>    Tabel ai_chats siap (' . AiChat::count() . ' baris riwayat).');
            }
        } else {
            $ok = false;
            $this->line('  <fg=red>[GAGAL]</> Tabel ai_chats tidak ditemukan - inilah penyebab umum error 500.');
            $this->line('          Jalankan: php artisan migrate');
        }

        // 2. Kredensial LLM.
        if ($llm->isConfigured()) {
            $this->line('  <fg=green>[OK]</>    API key LLM terbaca dari konfigurasi.');
        } else {
            $ok = false;
            $this->line('  <fg=red>[GAGAL]</> API key LLM kosong.');
            $this->line('          Isi LLM_API_KEY di file .env lalu jalankan: php artisan config:clear');
        }

        $this->line('  <fg=cyan>[INFO]</>  Model    : ' . config('llm.model'));
        $this->line('  <fg=cyan>[INFO]</>  Endpoint : ' . config('llm.endpoint'));
        $this->line('  <fg=cyan>[INFO]</>  Kuota tamu: ' . config('llm.quota.guest') . ' pesan');

        // 3. Knowledge base (RAG).
        $documents = (array) config('mersif_knowledge.documents', []);

        if (empty($documents)) {
            $ok = false;
            $this->line('  <fg=red>[GAGAL]</> Knowledge base kosong - cek config/mersif_knowledge.php');
        } else {
            $retrieved = $knowledgeBase->retrieve('Berapa harga paket subscription MersifLab?');
            $this->line('  <fg=green>[OK]</>    Knowledge base berisi ' . count($documents) . ' dokumen statis.');
            $this->line('  <fg=cyan>[INFO]</>  Contoh retrieval: ' . implode(', ', array_column($retrieved, 'id')));
        }

        // 4. Uji koneksi nyata (opsional).
        $question = $this->option('ask');

        if ($question) {
            $this->newLine();
            $this->info('Mengirim pertanyaan uji ke penyedia LLM...');

            $result = $llm->ask($question);

            if ($result['success']) {
                $this->line('  <fg=green>[OK]</>    Penyedia LLM membalas:');
                $this->newLine();
                $this->line(mb_substr($result['answer'], 0, 600));
            } else {
                $ok = false;
                $this->line('  <fg=red>[GAGAL]</> Penyedia LLM tidak membalas (alasan: ' . $result['reason'] . ').');
                $this->line('          Pesan untuk pengguna: ' . $result['answer']);
                $this->line('          Detail teknis ada di storage/logs/laravel.log');
            }
        } else {
            $this->newLine();
            $this->comment('Tips: tambahkan --ask="pertanyaan" untuk menguji koneksi nyata ke LLM.');
        }

        $this->newLine();

        if ($ok) {
            $this->info('Semua pemeriksaan lolos.');

            return self::SUCCESS;
        }

        $this->error('Ada pemeriksaan yang gagal - lihat keterangan di atas.');

        return self::FAILURE;
    }
}
