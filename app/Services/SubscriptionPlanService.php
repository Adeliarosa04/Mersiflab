<?php

namespace App\Services;

use App\Models\SubscriptionPurchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Aturan bisnis paket langganan MersifLab.
 *
 * Dipisah dari controller supaya satu aturan hanya ditulis di satu tempat dan
 * bisa dipakai ulang oleh view, controller, maupun perintah admin:
 *  - Peringkat paket (standard < premium) untuk membedakan upgrade vs downgrade.
 *  - Aturan pembatalan minimal 1 bulan (30 hari) sejak langganan mulai aktif.
 */
class SubscriptionPlanService
{
    /** Harga resmi tiap paket, dalam Rupiah. Sumber kebenaran tunggal. */
    public const PLAN_PRICES = [
        'standard' => 50000,
        'premium' => 150000,
    ];

    /**
     * Peringkat paket. Angka lebih besar = paket lebih tinggi.
     * Dipakai untuk menentukan apakah sebuah transisi termasuk upgrade.
     */
    private const PLAN_RANK = [
        'standard' => 1,
        'premium' => 2,
    ];

    /**
     * Minimal usia langganan sebelum boleh dibatalkan (hari).
     * Dipakai sebagai default bila config/subscription.php tidak tersedia.
     */
    public const MIN_ACTIVE_DAYS_BEFORE_CANCEL = 30;

    /** Pesan baku aturan pembatalan, dipakai backend maupun frontend. */
    public const CANCEL_BLOCKED_MESSAGE = 'Langganan tidak dapat dibatalkan sebelum masa aktif mencapai 1 bulan.';

    /**
     * Ambang minimal hari yang berlaku saat ini (bisa diatur lewat config).
     */
    public function minimumActiveDays(): int
    {
        $days = (int) config('subscription.min_active_days_before_cancel', self::MIN_ACTIVE_DAYS_BEFORE_CANCEL);

        return max(0, $days);
    }

    /**
     * Pesan aturan pembatalan yang berlaku saat ini.
     */
    public function cancelBlockedMessage(): string
    {
        return (string) config('subscription.cancel_blocked_message', self::CANCEL_BLOCKED_MESSAGE);
    }

    /**
     * Daftar paket yang dikenal sistem.
     *
     * @return array<int, string>
     */
    public function availablePlans(): array
    {
        return array_keys(self::PLAN_PRICES);
    }

    public function isValidPlan(?string $plan): bool
    {
        return $plan !== null && array_key_exists(strtolower($plan), self::PLAN_PRICES);
    }

    public function priceFor(string $plan): int
    {
        $plan = strtolower($plan);

        return (int) (config('subscription.prices.' . $plan) ?? self::PLAN_PRICES[$plan] ?? 0);
    }

    /**
     * Paket aktif pengguna saat ini, atau null bila tidak berlangganan.
     */
    public function activePlan(?User $user): ?string
    {
        if (!$user || !$user->hasActiveSubscription()) {
            return null;
        }

        $plan = strtolower((string) ($user->subscription_plan ?? ''));

        return $this->isValidPlan($plan) ? $plan : null;
    }

    /**
     * Apakah $targetPlan lebih tinggi dari paket aktif pengguna?
     *
     * Pengguna tanpa langganan aktif bukan kasus upgrade - itu pembelian biasa.
     */
    public function isUpgrade(?User $user, string $targetPlan): bool
    {
        $current = $this->activePlan($user);

        if ($current === null || !$this->isValidPlan($targetPlan)) {
            return false;
        }

        return (self::PLAN_RANK[strtolower($targetPlan)] ?? 0) > (self::PLAN_RANK[$current] ?? 0);
    }

    /**
     * Apakah $targetPlan lebih rendah atau sama dengan paket aktif?
     */
    public function isDowngradeOrSame(?User $user, string $targetPlan): bool
    {
        return $this->activePlan($user) !== null && !$this->isUpgrade($user, $targetPlan);
    }

    /**
     * Ringkasan konteks pembelian sebuah paket untuk pengguna tertentu.
     *
     * @return array{
     *     is_upgrade: bool,
     *     current_plan: ?string,
     *     target_plan: string,
     *     allowed: bool,
     *     message: ?string
     * }
     */
    public function purchaseContext(?User $user, string $targetPlan): array
    {
        $targetPlan = strtolower($targetPlan);
        $current = $this->activePlan($user);

        // Belum berlangganan: alur pembelian normal, tidak berubah sama sekali.
        if ($current === null) {
            return [
                'is_upgrade' => false,
                'current_plan' => null,
                'target_plan' => $targetPlan,
                'allowed' => true,
                'message' => null,
            ];
        }

        if ($this->isUpgrade($user, $targetPlan)) {
            return [
                'is_upgrade' => true,
                'current_plan' => $current,
                'target_plan' => $targetPlan,
                'allowed' => true,
                'message' => null,
            ];
        }

        // Paket sama = perpanjangan/duplikat, paket lebih rendah = downgrade.
        // Keduanya tetap diblokir seperti perilaku lama.
        $message = $current === $targetPlan
            ? 'Anda sudah berlangganan paket ' . ucfirst($current) . '. Tunggu hingga masa aktif berakhir sebelum membeli paket yang sama.'
            : 'Paket ' . ucfirst($current) . ' Anda masih aktif. Downgrade ke paket ' . ucfirst($targetPlan) . ' baru bisa dilakukan setelah masa aktif berakhir.';

        return [
            'is_upgrade' => false,
            'current_plan' => $current,
            'target_plan' => $targetPlan,
            'allowed' => false,
            'message' => $message,
        ];
    }

    /**
     * Tanggal mulai langganan aktif pengguna.
     *
     * Urutan sumber data (paling akurat lebih dulu):
     *   1. users.subscription_started_at
     *   2. started_at / paid_at purchase sukses terakhir
     *   3. created_at purchase sukses terakhir
     */
    public function subscriptionStartedAt(?User $user): ?Carbon
    {
        if (!$user) {
            return null;
        }

        if (!empty($user->subscription_started_at)) {
            return Carbon::parse($user->subscription_started_at);
        }

        try {
            $purchase = SubscriptionPurchase::where('user_id', $user->id)
                ->where('status', SubscriptionPurchase::STATUS_SUCCESS)
                ->orderByDesc('id')
                ->first();
        } catch (\Throwable $e) {
            Log::error('Subscription: gagal membaca tanggal mulai langganan', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (!$purchase) {
            return null;
        }

        $start = $purchase->started_at ?? $purchase->paid_at ?? $purchase->created_at;

        return $start ? Carbon::parse($start) : null;
    }

    /**
     * Status aturan pembatalan untuk pengguna.
     *
     * Selalu mengembalikan struktur yang sama supaya bisa dipakai langsung
     * oleh controller (validasi) maupun blade (tooltip/modal).
     *
     * @return array{
     *     has_subscription: bool,
     *     allowed: bool,
     *     started_at: ?Carbon,
     *     days_active: int,
     *     days_remaining: int,
     *     eligible_at: ?Carbon,
     *     minimum_days: int,
     *     message: ?string
     * }
     */
    public function cancellationStatus(?User $user): array
    {
        $minimumDays = $this->minimumActiveDays();

        $base = [
            'has_subscription' => false,
            'allowed' => false,
            'started_at' => null,
            'days_active' => 0,
            'days_remaining' => $minimumDays,
            'eligible_at' => null,
            'minimum_days' => $minimumDays,
            'message' => 'Anda belum memiliki langganan aktif.',
        ];

        if (!$user || !$user->hasActiveSubscription()) {
            return $base;
        }

        $startedAt = $this->subscriptionStartedAt($user);

        // Tanggal mulai tidak diketahui: jangan mengunci pengguna selamanya.
        // Perlakukan sebagai memenuhi syarat, dan catat supaya bisa ditelusuri.
        if ($startedAt === null) {
            Log::warning('Subscription: tanggal mulai langganan tidak diketahui, aturan 1 bulan dilewati', [
                'user_id' => $user->id,
            ]);

            return array_merge($base, [
                'has_subscription' => true,
                'allowed' => true,
                'days_remaining' => 0,
                'message' => null,
            ]);
        }

        $eligibleAt = $startedAt->copy()->addDays($minimumDays);
        $now = Carbon::now();

        // floor: hari ke-29 lewat 23 jam belum dihitung 30 hari.
        $daysActive = max(0, (int) floor($startedAt->diffInDays($now, false)));
        $daysRemaining = max(0, (int) ceil($now->diffInDays($eligibleAt, false)));

        $allowed = $now->greaterThanOrEqualTo($eligibleAt);

        return [
            'has_subscription' => true,
            'allowed' => $allowed,
            'started_at' => $startedAt,
            'days_active' => $daysActive,
            'days_remaining' => $allowed ? 0 : max(1, $daysRemaining),
            'eligible_at' => $eligibleAt,
            'minimum_days' => $minimumDays,
            'message' => $allowed ? null : $this->cancelBlockedMessage(),
        ];
    }
}
