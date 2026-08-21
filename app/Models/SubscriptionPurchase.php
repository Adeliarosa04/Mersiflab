<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SubscriptionPurchase extends Model
{
    use HasFactory;

    /** Menunggu pembayaran / konfirmasi admin. */
    public const STATUS_PENDING = 'pending';

    /** Sudah dibayar dan aktif. */
    public const STATUS_SUCCESS = 'success';

    /** Masa aktif berakhir. */
    public const STATUS_EXPIRED = 'expired';

    /** Dibatalkan (oleh pengguna maupun ditolak admin). */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Digantikan paket yang lebih tinggi lewat alur upgrade.
     * Berbeda dari 'cancelled': pengguna tidak kehilangan layanan, paketnya
     * naik. Dipakai untuk jejak audit dan riwayat pembelian.
     */
    public const STATUS_UPGRADED = 'upgraded';

    protected $fillable = [
        'purchase_code',
        'user_id',
        'plan',
        'amount',
        'discount_amount',
        'final_amount',
        'status',
        'payment_method',
        'payment_provider',
        'paid_at',
        'started_at',
        'expires_at',
        'cancelled_at',
        'replaced_by_id',
        'is_upgrade',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_upgrade' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Create notification for admin when new subscription purchase is made
        static::created(function ($purchase) {
            if (Schema::hasTable('notifications')) {
                // Get all admin users
                $adminUsers = User::where('role', 'admin')->get();
                
                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'new_subscription_purchase',
                        'title' => 'Pembelian Subscription Baru',
                        'message' => "Siswa {$purchase->user->name} telah membeli paket {$purchase->plan}",
                        'notifiable_type' => SubscriptionPurchase::class,
                        'notifiable_id' => $purchase->id,
                        'is_read' => false,
                    ]);
                }
            }

            // JANGAN auto-create invoice di sini
            // Invoice hanya akan dibuat saat user klik "Bayar Sekarang" di halaman checkout
            // Ini memastikan invoice hanya dikirim jika user benar-benar ingin membayar
            // Jika user kembali tanpa klik "Bayar Sekarang", purchase tetap pending tanpa invoice
            $skipAutoInvoice = session('skip_auto_invoice', true); // Default true untuk mencegah auto-create
            
            // Hanya create invoice jika status 'success' (langsung dibayar tanpa checkout)
            // atau jika explicitly di-set untuk create invoice (bukan dari checkout flow)
            if ($purchase->status === 'success' && !$skipAutoInvoice) {
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

                Invoice::create([
                    'user_id' => $purchase->user_id,
                    'type' => 'subscription',
                    'invoiceable_id' => $purchase->id,
                    'invoiceable_type' => SubscriptionPurchase::class,
                    'amount' => $purchase->amount,
                    'tax_amount' => 0,
                    'discount_amount' => $purchase->discount_amount,
                    'total_amount' => $purchase->final_amount,
                    'currency' => 'IDR',
                    'status' => 'paid', // Auto-paid karena purchase sudah success
                    'payment_method' => $purchase->payment_method ?? 'bank_transfer',
                    'payment_provider' => $purchase->payment_provider ?? 'manual',
                    'paid_at' => now(),
                    'metadata' => [
                        'subscription_plan' => $purchase->plan,
                        'plan_features' => $features[$purchase->plan] ?? [],
                        'purchase_code' => $purchase->purchase_code,
                    ],
                ]);
            }
        });
    }

    /**
     * Generate unique purchase code for subscription
     */
    public static function generatePurchaseCode()
    {
        do {
            $code = 'SUB-' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('purchase_code', $code)->exists());
        
        return $code;
    }

    /**
     * Get the user that owns the subscription purchase
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'success' => 'success',
            'pending' => 'warning',
            'expired' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Activate subscription (mark as paid and update user)
     */
    public function activateSubscription()
    {
        $now = now();

        // Paket lama yang masih aktif (kalau ada) digantikan oleh paket ini.
        // Dijalankan SEBELUM update user, supaya data lama masih bisa dibaca.
        $replaced = $this->replaceActiveSubscriptions();

        $this->update([
            'status' => self::STATUS_SUCCESS,
            'paid_at' => $this->paid_at ?? $now,
            'started_at' => $now,
            'payment_provider' => $this->payment_provider ?? 'whatsapp',
            'expires_at' => $now->copy()->addMonth(),
            'is_upgrade' => $this->is_upgrade || $replaced->isNotEmpty(),
            'notes' => ($this->notes ?? '') . ' - Activated by admin via WhatsApp confirmation',
        ]);

        // Update user subscription
        $this->user->update([
            'is_subscriber' => true,
            'subscription_plan' => $this->plan,
            'subscription_started_at' => $now,
            'subscription_expires_at' => $this->expires_at,
            'subscription_cancelled_at' => null,
        ]);

        $isUpgrade = $replaced->isNotEmpty();

        // Create notification for student
        if (Schema::hasTable('notifications')) {
            $message = $isUpgrade
                ? "Upgrade ke paket {$this->plan} berhasil! Paket lama Anda sudah digantikan dan semua materi sesuai paket baru langsung bisa diakses."
                : "Paket {$this->plan} Anda sudah aktif! Anda sekarang dapat mengakses semua course sesuai paket Anda. Selamat belajar!";

            Notification::create([
                'user_id' => $this->user_id,
                'type' => $isUpgrade ? 'subscription_upgraded' : 'subscription_activated',
                'title' => $isUpgrade ? 'Upgrade Paket Berhasil!' : 'Subscription Aktif!',
                'message' => $message,
                'notifiable_type' => SubscriptionPurchase::class,
                'notifiable_id' => $this->id,
                'is_read' => false,
            ]);
        }

        // Log activity if method exists
        if (method_exists($this->user, 'logActivity')) {
            $this->user->logActivity(
                $isUpgrade ? 'subscription_upgraded' : 'subscription_activated',
                ($isUpgrade ? 'Subscription upgraded to ' : 'Subscription activated for ')
                    . ucfirst($this->plan) . ' plan - expires: ' . $this->expires_at->format('Y-m-d')
            );
        }
    }

    /**
     * Tandai semua langganan sukses milik user yang masih aktif sebagai
     * 'upgraded' karena digantikan purchase ini.
     *
     * Tidak melempar exception: kegagalan pencatatan riwayat tidak boleh
     * membatalkan aktivasi paket yang sudah dibayar pengguna.
     *
     * @return \Illuminate\Support\Collection<int, SubscriptionPurchase>
     */
    protected function replaceActiveSubscriptions()
    {
        try {
            $previous = static::where('user_id', $this->user_id)
                ->where('id', '!=', $this->id)
                ->where('status', self::STATUS_SUCCESS)
                ->get();

            foreach ($previous as $old) {
                $old->update([
                    'status' => self::STATUS_UPGRADED,
                    'replaced_by_id' => $this->id,
                    'notes' => ($old->notes ?? '') . ' - Replaced by ' . $this->purchase_code
                        . ' (upgrade to ' . $this->plan . ') on ' . now()->format('Y-m-d H:i:s'),
                ]);
            }

            return $previous;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Subscription: gagal menandai paket lama sebagai upgraded', [
                'purchase_id' => $this->id,
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Purchase pengganti (diisi saat paket ini di-upgrade).
     */
    public function replacedBy()
    {
        return $this->belongsTo(self::class, 'replaced_by_id');
    }

    /**
     * Batalkan langganan aktif ini atas permintaan pengguna.
     *
     * Aturan minimal 1 bulan TIDAK diperiksa di sini - itu tanggung jawab
     * SubscriptionPlanService yang dipanggil controller, supaya aturan bisnis
     * hanya ditulis di satu tempat.
     */
    public function cancelByUser(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'notes' => ($this->notes ?? '') . ' - Cancelled by user on ' . now()->format('Y-m-d H:i:s')
                . ($reason ? ' (' . $reason . ')' : ''),
        ]);
    }

    /**
     * Get formatted plan name
     */
    public function getFormattedPlanAttribute()
    {
        return ucfirst($this->plan);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get formatted final amount
     */
    public function getFormattedFinalAmountAttribute()
    {
        return 'Rp' . number_format($this->final_amount, 0, ',', '.');
    }
}
