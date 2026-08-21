<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SubscriptionPurchase;
use App\Services\SubscriptionPlanService;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Harga resmi tiap paket, dalam Rupiah.
     *
     * Dipakai sebagai satu-satunya sumber kebenaran harga. Sebelumnya
     * final_amount diambil mentah dari request, sehingga nominal invoice bisa
     * dikirim sembarang dari sisi klien.
     *
     * Nilainya kini berasal dari SubscriptionPlanService agar harga, peringkat
     * paket, dan aturan pembatalan tinggal di satu tempat.
     */
    private const PLAN_PRICES = SubscriptionPlanService::PLAN_PRICES;

    public function __construct(private SubscriptionPlanService $plans)
    {
    }

    /**
     * Show subscription plans page
     */
    public function show()
    {
        $user = auth()->user();

        // Dikirim ke view supaya tombol paket & tombol pembatalan tahu
        // konteksnya (upgrade / sudah aktif / belum boleh dibatalkan).
        return view('subscription.index', [
            'currentPlan' => $this->plans->activePlan($user),
            'cancellation' => $this->plans->cancellationStatus($user),
        ]);
    }

    /**
     * Show subscription payment page
     * Just shows the payment page, no pending purchase created yet
     */
    public function showPayment($plan)
    {
        if (!in_array($plan, ['standard', 'premium'])) {
            return redirect()->route('subscription.page')->with('error', 'Invalid subscription plan.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Langganan aktif tidak lagi memblokir semua pembelian secara buta.
        // Upgrade ke paket yang lebih tinggi (Standard -> Premium) diizinkan;
        // paket yang sama atau lebih rendah tetap ditolak seperti sebelumnya.
        $context = $this->plans->purchaseContext($user, $plan);

        if (!$context['allowed']) {
            return redirect()->route('subscription.page')->with('error', $context['message']);
        }

        // Check if there's a recent pending subscription purchase for this user (within 24 hours)
        // Dibatasi pada paket yang sama: purchase pending Standard tidak boleh
        // dipakai ulang untuk checkout Premium, karena plan-nya akan salah.
        $hasRecentPendingSubscription = SubscriptionPurchase::where('user_id', $user->id)
            ->where('status', SubscriptionPurchase::STATUS_PENDING)
            ->where('plan', $plan)
            ->where('created_at', '>=', now()->subHours(24))
            ->first();

        if ($hasRecentPendingSubscription) {
            // Check if invoice already exists
            $hasInvoice = \App\Models\Invoice::where('invoiceable_id', $hasRecentPendingSubscription->id)
                ->where('invoiceable_type', SubscriptionPurchase::class)
                ->exists();

            if ($hasInvoice) {
                return redirect()->route('subscription.page')->with('error', 'Anda sudah memiliki pembelian langganan yang pending. Silakan tunggu persetujuan admin terlebih dahulu.');
            }

            // Use existing pending purchase
            \Illuminate\Support\Facades\Session::put('latest_subscription_purchase_id', $hasRecentPendingSubscription->id);
        }

        // Don't create any purchase record here - only create when user clicks "Bayar Sekarang"
        return view('checkout.subscription', compact('plan'));
    }

    /**
     * Process subscription payment - called when user clicks "Bayar Sekarang"
     * Creates invoice and sends email to user and admin
     */
    public function processPayment(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $plan = $request->input('plan');
        $paymentMethod = $request->input('payment_method');
        $paymentProvider = $request->input('payment_provider', 'manual');

        if (!in_array($plan, ['standard', 'premium'])) {
            $errorMessage = 'Invalid subscription plan.';
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 400);
            }
            return redirect()->route('subscription.page')->with('error', $errorMessage);
        }

        // Validasi paket dilakukan SETELAH plan diketahui, karena keputusannya
        // bergantung pada paket tujuan: upgrade ke paket lebih tinggi boleh,
        // paket sama / lebih rendah tetap ditolak.
        $context = $this->plans->purchaseContext($user, $plan);

        if (!$context['allowed']) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $context['message']], 400);
            }
            return redirect()->route('subscription.page')->with('error', $context['message']);
        }

        $isUpgrade = $context['is_upgrade'];

        // Harga dihitung di server; nilai dari form hanya untuk tampilan.
        // Belum ada fitur diskon/kupon pada alur ini — form selalu mengirim 0 —
        // jadi discount_amount dari klien sengaja diabaikan. Kalau nanti diskon
        // dibutuhkan, nilainya harus berasal dari sumber sisi server.
        $baseAmount = $this->plans->priceFor($plan);
        $discountAmount = 0;
        $finalAmount = $baseAmount - $discountAmount;

        // Create or get subscription purchase (only create when user actually clicks "Bayar Sekarang")
        $subscriptionPurchaseId = session('latest_subscription_purchase_id');

        $subscriptionPurchase = null;

        if ($subscriptionPurchaseId) {
            // Filter plan wajib ada: tanpa ini, purchase pending Standard bisa
            // dipakai ulang untuk checkout Premium sehingga invoice tercatat
            // Premium tapi purchase-nya tetap Standard.
            $subscriptionPurchase = SubscriptionPurchase::where('id', $subscriptionPurchaseId)
                ->where('user_id', $user->id)
                ->where('plan', $plan)
                ->where('status', SubscriptionPurchase::STATUS_PENDING)
                ->first();

            // Ensure expires_at exists for display (1 month duration)
            if ($subscriptionPurchase && !$subscriptionPurchase->expires_at) {
                try {
                    $subscriptionPurchase->update(['expires_at' => now()->addMonth()]);
                } catch (\Exception $e) {
                    // ignore update failures
                }
            }
        }

        if (!$subscriptionPurchase) {
            // Create new subscription purchase record
            $subscriptionPurchase = SubscriptionPurchase::create([
                'purchase_code' => SubscriptionPurchase::generatePurchaseCode(),
                'user_id' => $user->id,
                'plan' => $plan,
                'amount' => $baseAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'status' => SubscriptionPurchase::STATUS_PENDING,
                'payment_method' => $paymentMethod,
                'payment_provider' => $paymentProvider,
                'is_upgrade' => $isUpgrade,
                'expires_at' => now()->addMonth(), // set 1 month duration for subscription
            ]);
        }

        if (!$subscriptionPurchase) {
            $errorMessage = 'Gagal membuat pembelian subscription. Silakan coba lagi.';
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 400);
            }
            return redirect()->route('subscription.payment', $plan)->with('error', $errorMessage);
        }

        // Check if invoice already exists
        $existingInvoice = \App\Models\Invoice::where('invoiceable_id', $subscriptionPurchase->id)
            ->where('invoiceable_type', SubscriptionPurchase::class)
            ->exists();

        if ($existingInvoice) {
            $errorMessage = 'Invoice untuk pembelian ini sudah dibuat. Silakan cek email Anda.';
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 400);
            }
            return redirect()->route('subscription.page')->with('error', $errorMessage);
        }

        // Set flag to skip auto-invoice creation (kita akan buat manual)
        \Illuminate\Support\Facades\Session::put('skip_auto_invoice', true);

        // Update payment method di subscription purchase
        $subscriptionPurchase->update([
            'payment_method' => $paymentMethod,
            'payment_provider' => $paymentProvider,
            'is_upgrade' => $isUpgrade,
        ]);

        // Create invoice - invoice akan otomatis mengirim email via Invoice model boot method
        $features = [
            'standard' => [
                'Access all standard courses',
                'Basic certificate',
                'Email support',
                '1 month validity'
            ],
            'premium' => [
                'Access all courses (standard + premium)',
                'Premium certificate',
                'Priority support',
                'Download materials',
                '1 month validity'
            ]
        ];

        $invoice = \App\Models\Invoice::create([
            'user_id' => $user->id,
            'type' => 'subscription',
            'invoiceable_id' => $subscriptionPurchase->id,
            'invoiceable_type' => SubscriptionPurchase::class,
            'amount' => $subscriptionPurchase->amount,
            'tax_amount' => 0,
            'discount_amount' => $subscriptionPurchase->discount_amount,
            'total_amount' => $subscriptionPurchase->final_amount,
            'currency' => 'IDR',
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'payment_provider' => $paymentProvider,
            'metadata' => [
                'subscription_plan' => $plan,
                'plan_features' => $features[$plan] ?? [],
                'purchase_code' => $subscriptionPurchase->purchase_code,
                // Jejak upgrade agar invoice & email jelas menyebut asal paket.
                'is_upgrade' => $isUpgrade,
                'upgraded_from' => $isUpgrade ? $context['current_plan'] : null,
            ],
        ]);

        // Remove skip flag
        \Illuminate\Support\Facades\Session::forget('skip_auto_invoice');
        
        // Clear subscription purchase session
        \Illuminate\Support\Facades\Session::forget('latest_subscription_purchase_id');

        // Log activity if method exists
        if (method_exists($user, 'logActivity')) {
            $user->logActivity(
                $isUpgrade ? 'subscription_upgrade_requested' : 'subscription_purchase',
                ($isUpgrade
                    ? 'User requested upgrade from ' . ucfirst((string) $context['current_plan']) . ' to ' . ucfirst($plan)
                    : 'User purchased ' . ucfirst($plan) . ' subscription')
                . ' - Purchase Code: ' . $subscriptionPurchase->purchase_code
            );
        }

        // Check if request is AJAX/JSON
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice pembayaran telah dikirim ke email Anda.',
                'invoice_number' => $invoice->invoice_number,

                // Data tambahan agar modal pembayaran bisa langsung menampilkan
                // rincian invoice + QRIS tanpa permintaan susulan.
                'payment' => $this->paymentPayload($invoice, $subscriptionPurchase),
            ]);
        }

        $successMessage = $isUpgrade
            ? 'Permintaan upgrade ke paket ' . ucfirst($plan) . ' diterima. Invoice pembayaran telah dikirim ke email Anda. Paket ' . ucfirst((string) $context['current_plan']) . ' tetap aktif sampai pembayaran upgrade disetujui admin.'
            : 'Invoice pembayaran telah dikirim ke email Anda. Silakan cek email untuk melakukan pembayaran dan konfirmasi. Tunggu notifikasi bahwa pembayaran telah disetujui oleh admin.';

        return redirect()->route('subscription.page')->with('success', $successMessage);
    }

    /**
     * Batalkan langganan aktif atas permintaan pengguna.
     *
     * Aturan bisnis: langganan tidak bisa dibatalkan sebelum masa aktifnya
     * mencapai 1 bulan (30 hari). Pengecekannya memakai SubscriptionPlanService
     * supaya konsisten dengan indikator yang ditampilkan di frontend.
     *
     * Catatan: ini pembatalan LANGGANAN AKTIF, berbeda dengan
     * cancelPendingPurchase() yang hanya membatalkan pembelian yang belum
     * dibayar. Kedua alur sengaja dipisah dan tidak saling mengganggu.
     */
    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            return redirect()->route('login');
        }

        $status = $this->plans->cancellationStatus($user);

        // Tidak punya langganan aktif.
        if (!$status['has_subscription']) {
            return $this->cancelResponse($request, false, $status['message'] ?? 'Anda belum memiliki langganan aktif.', $status, 400);
        }

        // Aturan 1 bulan: blokir pembatalan.
        if (!$status['allowed']) {
            $detail = $this->plans->cancelBlockedMessage()
                . ' Langganan Anda baru berjalan ' . $status['days_active'] . ' hari.'
                . ($status['eligible_at']
                    ? ' Pembatalan bisa dilakukan mulai ' . $status['eligible_at']->format('d M Y') . '.'
                    : '');

            return $this->cancelResponse($request, false, $detail, $status, 422);
        }

        try {
            $reason = trim((string) $request->input('reason', '')) ?: null;

            // Tandai purchase aktif terakhir sebagai dibatalkan (kalau ada).
            $activePurchase = SubscriptionPurchase::where('user_id', $user->id)
                ->where('status', SubscriptionPurchase::STATUS_SUCCESS)
                ->orderByDesc('id')
                ->first();

            if ($activePurchase) {
                $activePurchase->cancelByUser($reason);
            }

            $cancelledPlan = ucfirst((string) ($user->subscription_plan ?? 'langganan'));

            // Cabut akses langganan pada akun pengguna.
            $user->update([
                'is_subscriber' => false,
                'subscription_plan' => null,
                'subscription_expires_at' => null,
                'subscription_started_at' => null,
                'subscription_cancelled_at' => now(),
            ]);

            if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => 'subscription_cancelled',
                    'title' => 'Langganan Dibatalkan',
                    'message' => "Paket {$cancelledPlan} Anda telah dibatalkan. Anda bisa berlangganan lagi kapan saja melalui halaman Subscription.",
                    'notifiable_type' => SubscriptionPurchase::class,
                    'notifiable_id' => $activePurchase?->id,
                    'is_read' => false,
                ]);
            }

            if (method_exists($user, 'logActivity')) {
                $user->logActivity('subscription_cancelled', 'User cancelled ' . $cancelledPlan . ' subscription'
                    . ($activePurchase ? ' - Purchase Code: ' . $activePurchase->purchase_code : ''));
            }

            $status = $this->plans->cancellationStatus($user);

            return $this->cancelResponse(
                $request,
                true,
                'Langganan ' . $cancelledPlan . ' Anda berhasil dibatalkan. Anda dapat berlangganan kembali kapan saja.',
                $status
            );
        } catch (\Throwable $e) {
            Log::error('Subscription: gagal membatalkan langganan', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->cancelResponse($request, false, 'Terjadi kendala saat membatalkan langganan. Silakan coba lagi beberapa saat lagi.', $status, 500);
        }
    }

    /**
     * Bentuk respons pembatalan yang seragam untuk AJAX maupun form biasa.
     */
    private function cancelResponse(Request $request, bool $success, string $message, array $status, int $errorCode = 200)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'cancellation' => [
                    'allowed' => $status['allowed'] ?? false,
                    'days_active' => $status['days_active'] ?? 0,
                    'days_remaining' => $status['days_remaining'] ?? 0,
                    'minimum_days' => $status['minimum_days'] ?? SubscriptionPlanService::MIN_ACTIVE_DAYS_BEFORE_CANCEL,
                    'eligible_at' => isset($status['eligible_at']) && $status['eligible_at']
                        ? $status['eligible_at']->format('d M Y')
                        : null,
                ],
            ], $success ? 200 : $errorCode);
        }

        return redirect()->route('subscription.page')
            ->with($success ? 'success' : 'error', $message);
    }
    
    /**
     * Rincian yang dibutuhkan modal pembayaran QRIS.
     *
     * QRIS di sini adalah QR merchant statis yang sama dengan yang dipakai
     * email invoice dan PDF — bukan QR dinamis dari payment gateway, sehingga
     * nominal tetap harus diisi manual oleh pembayar dan dikonfirmasi admin.
     */
    private function paymentPayload(\App\Models\Invoice $invoice, SubscriptionPurchase $purchase): array
    {
        $qrisPath = config('app.payment.qris_image_path');
        $total = (float) ($purchase->final_amount ?? $purchase->amount);

        $confirmText = 'Halo MersifLab, saya ingin konfirmasi pembayaran untuk subscription '
            . $purchase->purchase_code . ' sebesar Rp' . number_format($total, 0, ',', '.');

        return [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_url' => route('invoice', $invoice->id),
            'reference' => $purchase->purchase_code,
            'items' => [[
                'name' => 'Paket ' . ucfirst($purchase->plan) . ' — 1 bulan',
                'amount' => 'Rp' . number_format($total, 0, ',', '.'),
            ]],
            'created_at' => $invoice->created_at->format('d M Y, H:i') . ' WIB',
            'due_date' => $invoice->due_date ? $invoice->due_date->format('d M Y, H:i') . ' WIB' : null,
            'subtotal' => 'Rp' . number_format((float) $purchase->amount, 0, ',', '.'),
            'discount' => 'Rp' . number_format((float) $purchase->discount_amount, 0, ',', '.'),
            'has_discount' => (float) $purchase->discount_amount > 0,
            'total' => 'Rp' . number_format($total, 0, ',', '.'),
            'qris_url' => $qrisPath && file_exists(public_path($qrisPath)) ? asset($qrisPath) : null,
            'whatsapp_url' => 'https://wa.me/' . config('app.payment.whatsapp_number')
                . '?text=' . rawurlencode($confirmText),
        ];
    }

    /**
     * Subscribe user to a plan (redirects to payment checkout)
     * This ensures all subscriptions go through payment verification
     */
    public function subscribe(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $plan = $request->input('plan');
        if (!in_array($plan, ['standard', 'premium'])) {
            return redirect()->route('subscription.page')->with('error', 'Invalid subscription plan.');
        }

        // Redirect to payment checkout instead of directly activating
        return redirect()->route('subscription.payment', $plan);
    }

    /**
     * Cancel pending subscription purchase
     */
    public function cancelPendingPurchase(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isStudent()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $user = auth()->user();

        // Find recent pending subscription purchase without invoice
        $pendingPurchase = SubscriptionPurchase::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24)) // Only recent purchases
            ->first();

        // Check if there's an invoice for this purchase
        if ($pendingPurchase) {
            $hasInvoice = \App\Models\Invoice::where('invoiceable_id', $pendingPurchase->id)
                ->where('invoiceable_type', SubscriptionPurchase::class)
                ->exists();
            
            if ($hasInvoice) {
                return response()->json(['success' => false, 'message' => 'No cancellable pending purchase found.']);
            }
        }

        if (!$pendingPurchase) {
            return response()->json(['success' => false, 'message' => 'No cancellable pending purchase found.']);
        }

        // Delete the pending purchase
        $pendingPurchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pending subscription purchase cancelled successfully.'
        ]);
    }
}
