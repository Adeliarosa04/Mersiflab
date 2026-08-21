<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Models\User;
use App\Services\GuestChatService;
use App\Services\LlmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint Mersy AI Assistant.
 *
 * Prinsip penanganan error di controller ini: tidak ada detail teknis yang
 * bocor ke browser. Semua exception dicatat lewat Log::error, sementara
 * pengguna hanya menerima pesan yang ramah, dan widget chat tetap tampil
 * normal.
 */
class AiAssistantController extends Controller
{
    public function __construct(
        private LlmService $llm,
        private GuestChatService $guestChats,
    ) {
    }

    /**
     * Kirim pertanyaan ke AI Assistant.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $quota = $this->resolveQuota($request, $user);

        // --- Batas kuota tamu (3 pesan) ---------------------------------
        if (!$user && $quota['remaining'] !== null && $quota['remaining'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Kuota {$quota['limit']} pesan gratis Anda sudah habis. Daftar atau login untuk melanjutkan mengobrol dengan Mersy.",
                'require_login' => true,
                'remaining_questions' => 0,
                'daily_limit' => $quota['limit'],
                'cta' => $this->loginCta(),
            ], 403);
        }

        // --- Batas kuota harian pengguna terdaftar -----------------------
        if ($user && $quota['limit'] !== null && $quota['remaining'] !== null && $quota['remaining'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Anda sudah mencapai batas {$quota['limit']} pertanyaan hari ini. Silakan lanjutkan besok, atau berlangganan untuk akses tanpa batas.",
                'daily_limit_reached' => true,
                'remaining_questions' => 0,
                'daily_limit' => $quota['limit'],
                'cta' => $this->subscriptionCta(),
            ], 429);
        }

        // --- Validasi lampiran file (khusus subscriber Premium) ----------
        if (($request->hasFile('files') || $request->hasFile('file')) && !$quota['allow_files']) {
            return response()->json([
                'success' => false,
                'message' => 'Lampiran file hanya tersedia untuk pengguna subscription Premium.',
            ], 403);
        }

        if ($quota['allow_files']) {
            $request->validate([
                'files.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
                'file' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
            ]);
        }

        try {
            [$savedFiles, $fileContext] = $quota['allow_files']
                ? $this->processAttachments($request)
                : [[], ''];

            $result = $this->llm->ask(
                question: $request->input('message'),
                extraParts: $this->inlineImageParts($savedFiles),
                fileContext: $fileContext,
            );

            // LLM tidak tersedia: balas 200 dengan pesan ramah supaya widget
            // menampilkannya sebagai bubble biasa, bukan layar error.
            // Pesan gagal juga TIDAK memotong kuota pengguna.
            if (!$result['success']) {
                return response()->json([
                    'success' => true,
                    'answer' => $result['answer'],
                    'service_unavailable' => true,
                    'remaining_questions' => $quota['remaining'],
                    'daily_limit' => $quota['limit'],
                    'is_unlimited' => $quota['limit'] === null,
                    'allow_file_upload' => $quota['allow_files'],
                    'daily_used' => $quota['used'],
                ]);
            }

            $answer = $result['answer'];

            if (!empty($savedFiles)) {
                $answer .= "\n\n---\n\n📎 File terlampir: "
                    . implode(', ', array_column($savedFiles, 'name'));
            }

            $this->storeChat($request, $user, $request->input('message'), $answer);

            // Hitung ulang kuota setelah pesan tersimpan.
            $quota = $this->resolveQuota($request, $user);
            $answer .= $this->quotaNotice($user, $quota);

            return response()->json([
                'success' => true,
                'answer' => $answer,
                'remaining_questions' => $quota['remaining'],
                'daily_limit' => $quota['limit'],
                'daily_used' => $quota['used'],
                'is_unlimited' => $quota['limit'] === null,
                'allow_file_upload' => $quota['allow_files'],
                'require_login' => !$user && $quota['remaining'] === 0,
                'cta' => (!$user && $quota['remaining'] === 0) ? $this->loginCta() : null,
                'files_attached' => !empty($savedFiles)
                    ? array_map(fn ($f) => ['name' => $f['name'], 'size' => $f['size']], $savedFiles)
                    : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('AiAssistant: gagal memproses pesan chat', [
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);

            return response()->json([
                'success' => true,
                'answer' => 'Maaf, ada kendala teknis di sisi kami saat memproses pesan Anda. Silakan coba kirim ulang beberapa saat lagi ya.',
                'service_unavailable' => true,
            ]);
        }
    }

    /**
     * Riwayat percakapan (20 terakhir) untuk user login maupun tamu.
     */
    public function getHistory(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();

            $query = AiChat::query()->orderBy('created_at', 'asc')->limit(20);

            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $token = $this->guestChats->resolveToken($request);
                $sessionId = $this->guestChats->currentSessionId();

                $query->whereNull('user_id')
                    ->where(function ($q) use ($token, $sessionId) {
                        $q->where('guest_token', $token);

                        if (filled($sessionId)) {
                            $q->orWhere('session_id', $sessionId);
                        }
                    });
            }

            return response()->json([
                'success' => true,
                'chats' => $query->get(['id', 'question', 'answer', 'created_at']),
            ]);
        } catch (\Throwable $e) {
            Log::error('AiAssistant: gagal memuat riwayat chat', ['error' => $e->getMessage()]);

            // Riwayat kosong lebih baik daripada widget error.
            return response()->json(['success' => true, 'chats' => []]);
        }
    }

    /**
     * Status kuota untuk ditampilkan di widget.
     */
    public function checkLimit(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $quota = $this->resolveQuota($request, $user);

            return response()->json([
                'success' => true,
                'is_authenticated' => (bool) $user,
                'remaining_questions' => $quota['remaining'],
                'daily_limit' => $quota['limit'],
                'daily_used' => $quota['used'],
                'is_unlimited' => $quota['limit'] === null,
                'allow_file_upload' => $quota['allow_files'],
                'subscription_plan' => $user?->subscription_plan,
                'service_available' => $this->llm->isConfigured(),
                'cta' => (!$user && $quota['remaining'] === 0) ? $this->loginCta() : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('AiAssistant: gagal memeriksa kuota', ['error' => $e->getMessage()]);

            // Fallback aman: widget tetap bisa dipakai dengan kuota tamu default.
            return response()->json([
                'success' => true,
                'is_authenticated' => Auth::check(),
                'remaining_questions' => Auth::check() ? null : $this->guestChats->quotaLimit(),
                'daily_limit' => Auth::check() ? null : $this->guestChats->quotaLimit(),
                'daily_used' => 0,
                'is_unlimited' => false,
                'allow_file_upload' => false,
                'service_available' => $this->llm->isConfigured(),
            ]);
        }
    }

    /**
     * Hitung kuota berdasarkan status pengguna.
     *
     * @return array{limit:?int, used:int, remaining:?int, allow_files:bool}
     */
    private function resolveQuota(Request $request, ?User $user): array
    {
        // --- Tamu: kuota berbasis token cookie + session id ---------------
        if (!$user) {
            $token = $this->guestChats->resolveToken($request);
            $sessionId = $this->guestChats->currentSessionId();

            $limit = $this->guestChats->quotaLimit();
            $used = $this->guestChats->usedQuota($token, $sessionId);

            return [
                'limit' => $limit,
                'used' => $used,
                'remaining' => max(0, $limit - $used),
                'allow_files' => false,
            ];
        }

        // --- Pengguna terdaftar ------------------------------------------
        $hasSubscription = false;
        $hasCourse = false;

        try {
            $hasSubscription = $user->hasActiveSubscription();
            $hasCourse = $user->enrolledClasses()->exists() || $user->classes()->exists();
        } catch (\Throwable $e) {
            Log::warning('AiAssistant: gagal membaca status langganan/kursus user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $plan = strtolower((string) ($user->subscription_plan ?? ''));

        if ($hasSubscription) {
            return [
                'limit' => null, // tanpa batas
                'used' => $this->userUsedToday($user),
                'remaining' => null,
                'allow_files' => $plan === 'premium',
            ];
        }

        $limit = $hasCourse
            ? (int) config('llm.quota.student', 15)
            : (int) config('llm.quota.free_user', 5);

        $used = $this->userUsedToday($user);

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'allow_files' => false,
        ];
    }

    /**
     * Jumlah pertanyaan user hari ini. Kegagalan DB dianggap 0 agar chat
     * tetap bisa dipakai.
     */
    private function userUsedToday(User $user): int
    {
        try {
            return AiChat::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count();
        } catch (\Throwable $e) {
            Log::error('AiAssistant: gagal menghitung pemakaian harian', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Simpan percakapan. Untuk tamu, guest_token ikut disimpan agar riwayatnya
     * bisa dimigrasikan ke akun saat pengguna login/register.
     */
    private function storeChat(Request $request, ?User $user, string $question, string $answer): void
    {
        try {
            AiChat::create([
                'user_id' => $user?->id,
                'session_id' => $user ? null : $this->guestChats->currentSessionId(),
                'guest_token' => $user ? null : $this->guestChats->resolveToken($request),
                'question' => $question,
                'answer' => $answer,
            ]);
        } catch (\Throwable $e) {
            // Jawaban sudah didapat - gagal menyimpan riwayat tidak boleh
            // membatalkan jawaban yang akan ditampilkan ke pengguna.
            Log::error('AiAssistant: gagal menyimpan riwayat chat', [
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Catatan sisa kuota yang ditempel di akhir jawaban.
     */
    private function quotaNotice(?User $user, array $quota): string
    {
        if ($quota['limit'] === null) {
            return '';
        }

        $remaining = $quota['remaining'];

        if (!$user) {
            if ($remaining === 1) {
                return "\n\n---\n\n⚠️ Ini pesan gratis terakhir Anda. Daftar atau login untuk lanjut mengobrol dengan Mersy.";
            }

            if ($remaining === 0) {
                return "\n\n---\n\n🔒 Kuota pesan gratis Anda sudah habis. Daftar atau login untuk melanjutkan — riwayat obrolan ini akan otomatis ikut pindah ke akun Anda.";
            }

            return '';
        }

        if ($remaining === 0) {
            return "\n\n---\n\n🔒 Anda sudah mencapai batas harian. Silakan lanjut besok, atau berlangganan untuk akses tanpa batas.";
        }

        if ($remaining !== null && $remaining <= 5) {
            return "\n\n---\n\n⚠️ Sisa {$remaining} pertanyaan untuk hari ini.";
        }

        return '';
    }

    /**
     * CTA "Daftar / Login untuk Lanjut Chatting" untuk widget.
     */
    private function loginCta(): array
    {
        return [
            'title' => 'Daftar / Login untuk Lanjut Chatting',
            'actions' => [
                ['label' => 'Login', 'url' => route('login')],
                ['label' => 'Daftar Gratis', 'url' => route('register')],
            ],
        ];
    }

    /**
     * CTA berlangganan untuk user terdaftar yang kuota hariannya habis.
     */
    private function subscriptionCta(): array
    {
        return [
            'title' => 'Berlangganan untuk chat tanpa batas',
            'actions' => [
                ['label' => 'Lihat Paket', 'url' => route('subscription.page')],
            ],
        ];
    }

    /**
     * Simpan lampiran & ekstrak teksnya untuk konteks tambahan.
     *
     * @return array{0: array<int, array{path:string, name:string, size:int}>, 1: string}
     */
    private function processAttachments(Request $request): array
    {
        $uploaded = $request->file('files') ?? $request->file('file');

        if (!$uploaded) {
            return [[], ''];
        }

        if (!is_array($uploaded)) {
            $uploaded = [$uploaded];
        }

        $savedFiles = [];
        $contextParts = [];

        foreach ($uploaded as $file) {
            if (!$file) {
                continue;
            }

            try {
                $path = $file->store('ai_attachments', 'public');

                $savedFiles[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];

                $fullPath = storage_path('app/public/' . $path);
                $extracted = $this->extractTextFromFile(
                    $fullPath,
                    strtolower(pathinfo($path, PATHINFO_EXTENSION))
                );

                if ($extracted) {
                    $contextParts[] = "File: {$file->getClientOriginalName()}\n" . mb_substr($extracted, 0, 3500);
                }
            } catch (\Throwable $e) {
                Log::error('AiAssistant: gagal memproses lampiran', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [$savedFiles, implode("\n\n", $contextParts)];
    }

    /**
     * Ubah lampiran gambar menjadi bagian inline_data untuk request LLM.
     */
    private function inlineImageParts(array $savedFiles): array
    {
        $parts = [];

        foreach ($savedFiles as $fileInfo) {
            try {
                $fullPath = storage_path('app/public/' . $fileInfo['path']);
                $mimeType = $this->getFileMimeType($fullPath);

                if (!$this->isSupportedImageType($mimeType)) {
                    continue;
                }

                $binary = @file_get_contents($fullPath);

                if ($binary === false) {
                    continue;
                }

                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $mimeType,
                        'data' => base64_encode($binary),
                    ],
                ];
            } catch (\Throwable $e) {
                Log::warning('AiAssistant: gagal menyiapkan gambar untuk LLM', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $parts;
    }

    private function getFileMimeType(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (isset($mimeTypes[$ext])) {
            return $mimeTypes[$ext];
        }

        return file_exists($filePath) ? (mime_content_type($filePath) ?: 'application/octet-stream') : 'application/octet-stream';
    }

    private function isSupportedImageType(string $mimeType): bool
    {
        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
    }

    /**
     * Ekstraksi teks sederhana dari lampiran txt/docx/pdf.
     */
    private function extractTextFromFile(string $fullPath, string $ext): ?string
    {
        try {
            if (!file_exists($fullPath)) {
                return null;
            }

            if ($ext === 'txt') {
                return file_get_contents($fullPath) ?: null;
            }

            if ($ext === 'docx') {
                $zip = new \ZipArchive();

                if ($zip->open($fullPath) === true) {
                    $index = $zip->locateName('word/document.xml');

                    if ($index !== false) {
                        $data = $zip->getFromIndex($index);
                        $zip->close();

                        return strip_tags($data) ?: null;
                    }

                    $zip->close();
                }

                return null;
            }

            if ($ext === 'pdf' && function_exists('shell_exec')) {
                $out = @shell_exec('pdftotext ' . escapeshellarg($fullPath) . ' - 2>&1');

                if ($out && !str_contains(strtolower($out), 'not found')) {
                    return $out;
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('AiAssistant: gagal mengekstrak teks lampiran', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
