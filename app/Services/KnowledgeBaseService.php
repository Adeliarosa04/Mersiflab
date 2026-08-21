<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\FreeClass;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Context Retriever untuk arsitektur RAG (Retrieval-Augmented Generation).
 *
 * Tugasnya: memilih potongan knowledge base internal MersifLab yang paling
 * relevan dengan pertanyaan user, lalu merangkainya menjadi blok konteks yang
 * disisipkan ke system prompt SEBELUM pesan dikirim ke LLM.
 *
 * Sumber pengetahuan:
 *   1. Dokumen statis dari config/mersif_knowledge.php (FAQ, harga, silabus, fitur).
 *   2. Data live dari database (katalog kursus & kelas gratis) supaya jawaban
 *      soal "kursus apa saja yang ada" selalu mengikuti isi LMS terkini.
 *
 * Kegagalan pengambilan data live TIDAK boleh menggagalkan chat - service ini
 * selalu mengembalikan konteks statis sebagai minimum.
 */
class KnowledgeBaseService
{
    /** Cache key & TTL untuk katalog kursus dari database. */
    private const CACHE_KEY_COURSES = 'mersy.kb.courses';
    private const CACHE_KEY_FREE_CLASSES = 'mersy.kb.free_classes';
    private const CACHE_TTL = 600; // 10 menit

    /**
     * Kata umum bahasa Indonesia/Inggris yang tidak berguna untuk retrieval.
     */
    private const STOP_WORDS = [
        'yang', 'untuk', 'dari', 'dengan', 'pada', 'apa', 'apakah', 'bagaimana',
        'saya', 'aku', 'kamu', 'ada', 'itu', 'ini', 'dan', 'atau', 'bisa',
        'tolong', 'mau', 'ingin', 'gimana', 'kalau', 'jika', 'the', 'for',
        'what', 'how', 'can', 'you', 'are', 'about', 'aja', 'saja', 'dong',
    ];

    /**
     * Bangun blok konteks internal untuk sebuah pertanyaan.
     *
     * @return string Blok teks siap sisip ke system prompt (bisa kosong bila
     *                konfigurasi knowledge base tidak tersedia).
     */
    public function buildContext(string $question): string
    {
        $documents = $this->retrieve($question);

        if (empty($documents)) {
            return '';
        }

        $maxChars = (int) config('mersif_knowledge.max_context_chars', 9000);
        $blocks = [];
        $used = 0;

        foreach ($documents as $doc) {
            $block = '[' . strtoupper($doc['category'] ?? 'info') . '] ' . ($doc['title'] ?? '') . "\n"
                . trim($doc['content'] ?? '');

            if ($used + mb_strlen($block) > $maxChars) {
                break;
            }

            $blocks[] = $block;
            $used += mb_strlen($block);
        }

        return implode("\n\n", $blocks);
    }

    /**
     * Ambil dokumen paling relevan (statis + live) untuk sebuah pertanyaan.
     *
     * @return array<int, array{id:string, category:string, title:string, content:string}>
     */
    public function retrieve(string $question): array
    {
        $documents = $this->allDocuments();

        if (empty($documents)) {
            return [];
        }

        $tokens = $this->tokenize($question);
        $alwaysInclude = (array) config('mersif_knowledge.always_include', []);
        $limit = (int) config('mersif_knowledge.max_documents', 6);

        $scored = [];

        foreach ($documents as $index => $doc) {
            $score = $this->score($doc, $tokens);

            if (in_array($doc['id'] ?? '', $alwaysInclude, true)) {
                $score += 1000; // pastikan selalu ikut, tapi tetap di urutan atas
            }

            if ($score > 0) {
                // $index dipakai sebagai tie-breaker agar urutan stabil.
                $scored[] = ['score' => $score, 'order' => $index, 'doc' => $doc];
            }
        }

        // Tidak ada yang cocok sama sekali: kirim dokumen inti supaya LLM tetap
        // punya identitas platform dan bisa menjawab "belum punya informasi".
        if (empty($scored)) {
            return $this->fallbackDocuments($documents);
        }

        usort($scored, function ($a, $b) {
            return $b['score'] <=> $a['score'] ?: $a['order'] <=> $b['order'];
        });

        return array_map(
            fn ($item) => $item['doc'],
            array_slice($scored, 0, max(1, $limit))
        );
    }

    /**
     * Gabungan dokumen statis (config) dan dokumen dinamis (database).
     */
    private function allDocuments(): array
    {
        $static = (array) config('mersif_knowledge.documents', []);

        return array_merge($static, $this->dynamicDocuments());
    }

    /**
     * Dokumen yang dirakit dari data LMS yang sedang berjalan.
     *
     * Dibungkus try-catch: kalau tabel belum ada atau DB bermasalah, chatbot
     * tetap jalan dengan pengetahuan statis saja.
     */
    private function dynamicDocuments(): array
    {
        $docs = [];

        $courseSummary = $this->courseCatalogSummary();
        if ($courseSummary !== null) {
            $docs[] = [
                'id' => 'katalog-kursus-live',
                'category' => 'silabus',
                'title' => 'Daftar Kursus yang Sedang Tayang di LMS',
                'keywords' => ['kursus', 'course', 'kelas', 'materi', 'belajar', 'katalog', 'daftar kursus', 'harga kursus', 'tersedia'],
                'content' => $courseSummary,
            ];
        }

        $freeClassSummary = $this->freeClassSummary();
        if ($freeClassSummary !== null) {
            $docs[] = [
                'id' => 'katalog-kelas-gratis-live',
                'category' => 'silabus',
                'title' => 'Daftar Kelas Gratis yang Sedang Tayang',
                'keywords' => ['gratis', 'free class', 'kelas gratis', 'free', 'coba'],
                'content' => $freeClassSummary,
            ];
        }

        return $docs;
    }

    /**
     * Ringkasan katalog kursus terbit, di-cache agar tidak query tiap pesan.
     */
    private function courseCatalogSummary(): ?string
    {
        try {
            return Cache::remember(self::CACHE_KEY_COURSES, self::CACHE_TTL, function () {
                $courses = ClassModel::query()
                    ->where('is_published', true)
                    ->orderByDesc('is_featured')
                    ->orderByDesc('total_sales')
                    ->limit(25)
                    ->get(['name', 'category', 'price', 'total_duration']);

                if ($courses->isEmpty()) {
                    return null;
                }

                $lines = ['Kursus berbayar yang sedang tersedia di LMS MersifLab saat ini:'];

                foreach ($courses as $course) {
                    $parts = ['- ' . $course->name];

                    if (!empty($course->category)) {
                        $parts[] = 'kategori ' . $course->category;
                    }

                    $price = (float) ($course->price ?? 0);
                    $parts[] = $price > 0
                        ? 'harga Rp ' . number_format($price, 0, ',', '.')
                        : 'gratis';

                    if (!empty($course->total_duration)) {
                        $parts[] = 'durasi ' . $course->total_duration;
                    }

                    $lines[] = implode(', ', $parts) . '.';
                }

                $lines[] = 'Daftar lengkap dan terbaru selalu ada di halaman Courses.';

                return implode("\n", $lines);
            });
        } catch (\Throwable $e) {
            Log::warning('KnowledgeBase: gagal memuat katalog kursus', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Ringkasan kelas gratis aktif, di-cache seperti katalog kursus.
     */
    private function freeClassSummary(): ?string
    {
        try {
            return Cache::remember(self::CACHE_KEY_FREE_CLASSES, self::CACHE_TTL, function () {
                $classes = FreeClass::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->limit(15)
                    ->get(['title']);

                if ($classes->isEmpty()) {
                    return null;
                }

                $lines = ['Kelas gratis (Free Class) yang sedang tayang:'];

                foreach ($classes as $class) {
                    $lines[] = '- ' . $class->title;
                }

                $lines[] = 'Kelas gratis bisa diakses tanpa biaya melalui menu Free Class.';

                return implode("\n", $lines);
            });
        } catch (\Throwable $e) {
            Log::warning('KnowledgeBase: gagal memuat kelas gratis', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Skor relevansi sederhana: kecocokan keyword bernilai lebih tinggi
     * daripada kemunculan token di judul atau isi dokumen.
     */
    private function score(array $doc, array $tokens): int
    {
        if (empty($tokens)) {
            return 0;
        }

        $keywords = array_map('mb_strtolower', (array) ($doc['keywords'] ?? []));
        $title = mb_strtolower((string) ($doc['title'] ?? ''));
        $content = mb_strtolower((string) ($doc['content'] ?? ''));

        $score = 0;

        foreach ($tokens as $token) {
            foreach ($keywords as $keyword) {
                if ($keyword === $token) {
                    $score += 12;
                } elseif (str_contains($keyword, $token) || str_contains($token, $keyword)) {
                    $score += 6;
                }
            }

            if (str_contains($title, $token)) {
                $score += 4;
            }

            if (str_contains($content, $token)) {
                $score += 1;
            }
        }

        return $score;
    }

    /**
     * Dokumen inti yang dipakai saat tidak ada satu pun kecocokan keyword.
     */
    private function fallbackDocuments(array $documents): array
    {
        $wanted = array_merge(
            (array) config('mersif_knowledge.always_include', []),
            ['fitur-lms', 'harga-subscription']
        );

        $fallback = array_values(array_filter(
            $documents,
            fn ($doc) => in_array($doc['id'] ?? '', $wanted, true)
        ));

        return $fallback ?: array_slice($documents, 0, 2);
    }

    /**
     * Pecah pertanyaan menjadi token bermakna (>2 huruf, bukan stop word).
     *
     * @return array<int, string>
     */
    private function tokenize(string $question): array
    {
        $normalized = mb_strtolower(trim($question));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $normalized) ?? '';

        $tokens = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = array_filter(
            $tokens,
            fn ($token) => mb_strlen($token) > 2 && !in_array($token, self::STOP_WORDS, true)
        );

        return array_values(array_unique($tokens));
    }

    /**
     * Bersihkan cache katalog agar knowledge base ikut ter-update setelah
     * ada perubahan kursus/kelas gratis. Aman dipanggil kapan saja.
     */
    public function flushCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY_COURSES);
            Cache::forget(self::CACHE_KEY_FREE_CLASSES);
        } catch (\Throwable $e) {
            Log::warning('KnowledgeBase: gagal membersihkan cache', ['error' => $e->getMessage()]);
        }
    }
}
