@extends('layouts.app')

@section('title', 'Subscription')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/subs.css') }}">
@endsection

@section('content')
<section class="subscription-section">
    <div class="container">
        <!-- Header -->
        <div class="subscription-header">
            <h2>Subscription Plans</h2>
            <p>Choose a subscription plan that suits your needs. Currently, subscription features are available directly without payment (simulation).</p>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Error Alert (mis. aturan pembatalan 1 bulan atau paket tidak valid) -->
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Active Subscription Alert -->
        @auth
            @php
                $user = auth()->user();
                if ($user && $user->hasActiveSubscription()) {
                    $activePlan = ucfirst($user->subscription_plan);
                    $expiryDate = $user->subscription_expires_at ? $user->subscription_expires_at->format('d M Y H:i') : 'Tidak diketahui';
                }
            @endphp
            @if(isset($activePlan))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fas fa-info-circle" style="color: #1976d2; font-size: 20px; margin-top: 2px;"></i>
                    <div>
                        <strong>Subscription Aktif</strong><br>
                        Anda saat ini memiliki subscription <strong>{{ $activePlan }}</strong>.<br>
                        <small>Berakhir: {{ $expiryDate }} WIB</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
        @endauth

        <!-- Plans Container -->
        <div class="plans-container">
            <!-- Standard Plan -->
            <div class="plan-card">
                <div class="plan-header">
                    <h5 class="plan-name">Standard</h5>
                    <div class="plan-price">
                        <span class="price-amount">Rp 50.000</span>
                        <span class="price-period">/month</span>
                    </div>
                </div>

                <ul class="plan-features">
                    <li>Get access to all standard courses</li>
                    <li>Get unlimited AI Assistant</li>
                </ul>

                <div class="plan-action">
                    @auth
                        @php
                            $user = auth()->user();
                            $isSubscribedStandard = $user->is_subscriber && $user->subscription_plan === 'standard' && $user->subscription_expires_at && $user->subscription_expires_at > now();
                            $isPremium = $user->is_subscriber && $user->subscription_plan === 'premium' && $user->subscription_expires_at && $user->subscription_expires_at > now();
                            
                            // Check for pending subscription purchases
                            $pendingStandardPurchase = \App\Models\SubscriptionPurchase::where('user_id', $user->id)
                                ->where('plan', 'standard')
                                ->where('status', 'pending')
                                ->first();
                            
                            $pendingPremiumPurchase = \App\Models\SubscriptionPurchase::where('user_id', $user->id)
                                ->where('plan', 'premium')
                                ->where('status', 'pending')
                                ->first();
                        @endphp
                        
                        @if($isSubscribedStandard)
                            <button class="btn-status btn-active" disabled>
                                <i class="fas fa-check-circle"></i> Active Plan
                            </button>
                            <div class="plan-info">
                                <p class="expiry-date">Expires: {{ $user->subscription_expires_at->format('d M Y') }}</p>
                            </div>
                            @include('subscription.partials.cancel-action')
                        @elseif($isPremium)
                            <button class="btn-status btn-downgrade" disabled>
                                <i class="fas fa-arrow-down"></i> Downgrade from Premium
                            </button>
                            <div class="plan-info">
                                <p class="expiry-date">Expires: {{ $user->subscription_expires_at->format('d M Y') }}</p>
                            </div>
                        @elseif($user->hasActiveSubscription())
                            <button class="btn-status btn-disabled" disabled>
                                <i class="fas fa-lock"></i> Already Have Active Subscription
                            </button>
                            <div class="plan-info">
                                <p class="expiry-date">Current plan expires: {{ $user->subscription_expires_at->format('d M Y') }}</p>
                            </div>
                        @elseif($pendingStandardPurchase)
                            <button class="btn-status btn-pending" disabled>
                                <i class="fas fa-clock"></i> Pending Payment
                            </button>
                            <div class="plan-info">
                                <p class="purchase-code">{{ $pendingStandardPurchase->purchase_code }}</p>
                                <p class="pending-note">Menunggu konfirmasi pembayaran</p>
                            </div>
                        @elseif($pendingPremiumPurchase)
                            <button class="btn-status btn-disabled" disabled>
                                <i class="fas fa-lock"></i> Premium Subscription Pending
                            </button>
                            <div class="plan-info">
                                <p class="pending-note">You have pending Premium subscription</p>
                            </div>
                        @else
                            <a href="{{ route('subscription.payment', 'standard') }}" class="btn-subscribe btn-subscribe-primary">
                                Subscribe Standard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-login">
                            Login to Subscribe
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Premium Plan -->
            <div class="plan-card premium">
                <div class="plan-header">
                    <h5 class="plan-name">Premium</h5>
                    <div class="plan-price">
                        <span class="price-amount">Rp 150.000</span>
                        <span class="price-period">/month</span>
                    </div>
                </div>

                <ul class="plan-features">
                    <li>Get access to all standard to premium courses</li>
                    <li>Get unlimited smarter AI assistant (can upload files to ask questions)</li>
                </ul>

                <div class="plan-action">
                    @auth
                        @php
                            $user = auth()->user();
                            $isSubscribedPremium = $user->is_subscriber && $user->subscription_plan === 'premium' && $user->subscription_expires_at && $user->subscription_expires_at > now();
                            
                            $pendingPremiumPurchase = \App\Models\SubscriptionPurchase::where('user_id', $user->id)
                                ->where('plan', 'premium')
                                ->where('status', 'pending')
                                ->first();
                            
                            $pendingStandardPurchase = \App\Models\SubscriptionPurchase::where('user_id', $user->id)
                                ->where('plan', 'standard')
                                ->where('status', 'pending')
                                ->first();
                        @endphp
                        
                        @if($isSubscribedPremium)
                            <button class="btn-status btn-active" disabled>
                                <i class="fas fa-check-circle"></i> Active Plan
                            </button>
                            <div class="plan-info">
                                <p class="expiry-date">Expires: {{ $user->subscription_expires_at->format('d M Y') }}</p>
                            </div>
                            @include('subscription.partials.cancel-action')
                        @elseif($pendingPremiumPurchase)
                            <button class="btn-status btn-pending" disabled>
                                <i class="fas fa-clock"></i> Pending Payment
                            </button>
                            <div class="plan-info">
                                <p class="purchase-code">{{ $pendingPremiumPurchase->purchase_code }}</p>
                                <p class="pending-note">Menunggu konfirmasi pembayaran</p>
                            </div>
                        @elseif(($currentPlan ?? null) === 'standard')
                            {{-- Pengguna paket Standard aktif: tawarkan upgrade, bukan
                                 tombol terkunci "Already Have Active Subscription". --}}
                            <a href="{{ route('subscription.payment', 'premium') }}" class="btn-subscribe btn-subscribe-primary">
                                <i class="fas fa-arrow-up"></i> Upgrade ke Premium
                            </a>
                            <div class="plan-info">
                                <p class="pending-note">Paket Standard Anda akan digantikan setelah pembayaran upgrade disetujui.</p>
                            </div>
                        @elseif($user->hasActiveSubscription())
                            <button class="btn-status btn-disabled" disabled>
                                <i class="fas fa-lock"></i> Already Have Active Subscription
                            </button>
                            <div class="plan-info">
                                <p class="expiry-date">Current plan expires: {{ $user->subscription_expires_at->format('d M Y') }}</p>
                            </div>
                        @elseif($pendingStandardPurchase)
                            <button class="btn-status btn-disabled" disabled>
                                <i class="fas fa-lock"></i> Standard Subscription Pending
                            </button>
                            <div class="plan-info">
                                <p class="pending-note">You have pending Standard subscription</p>
                            </div>
                        @else
                            <a href="{{ route('subscription.payment', 'premium') }}" class="btn-subscribe btn-subscribe-primary">
                                Subscribe Premium
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-login">
                            Login to Subscribe
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>

@auth
    @php
        $cancelInfo = $cancellation ?? null;
        $cancelAllowed = (bool) ($cancelInfo['allowed'] ?? false);
        $cancelHasSub = (bool) ($cancelInfo['has_subscription'] ?? false);
    @endphp

    @if($cancelHasSub)
        {{-- Modal penjelasan aturan pembatalan 1 bulan (saat belum memenuhi syarat) --}}
        <div id="cancelRuleModal" class="subs-modal" role="dialog" aria-modal="true" aria-labelledby="cancelRuleTitle">
            <div class="subs-modal-content">
                <div class="subs-modal-icon subs-modal-icon-info">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3 id="cancelRuleTitle">Belum Bisa Dibatalkan</h3>
                <p class="subs-modal-lead">Langganan tidak dapat dibatalkan sebelum masa aktif mencapai 1 bulan.</p>
                <ul class="subs-modal-list">
                    <li>Masa aktif berjalan: <strong>{{ (int) ($cancelInfo['days_active'] ?? 0) }} hari</strong></li>
                    <li>Minimal sebelum bisa dibatalkan: <strong>{{ (int) ($cancelInfo['minimum_days'] ?? 30) }} hari</strong></li>
                    @if(!empty($cancelInfo['eligible_at']))
                        <li>Bisa dibatalkan mulai: <strong>{{ $cancelInfo['eligible_at']->format('d M Y') }}</strong></li>
                    @endif
                </ul>
                <p class="subs-modal-note">Selama menunggu, langganan Anda tetap aktif dan seluruh materi sesuai paket bisa diakses seperti biasa.</p>
                <div class="subs-modal-actions">
                    <button type="button" class="btn-subscribe btn-subscribe-primary" onclick="closeSubsModal('cancelRuleModal')">Mengerti</button>
                </div>
            </div>
        </div>

        {{-- Modal konfirmasi pembatalan (saat sudah memenuhi syarat 1 bulan) --}}
        <div id="cancelSubscriptionModal" class="subs-modal" role="dialog" aria-modal="true" aria-labelledby="cancelConfirmTitle">
            <div class="subs-modal-content">
                <div class="subs-modal-icon subs-modal-icon-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 id="cancelConfirmTitle">Batalkan Langganan?</h3>
                <p class="subs-modal-lead">Akses paket berlangganan Anda akan dihentikan setelah pembatalan diproses.</p>
                <p class="subs-modal-note">Anda tetap bisa berlangganan lagi kapan saja dari halaman ini.</p>
                <div id="cancelSubscriptionError" class="subs-modal-error" style="display: none;"></div>
                <div class="subs-modal-actions">
                    <button type="button" class="btn-subscribe btn-subscribe-outline" onclick="closeSubsModal('cancelSubscriptionModal')">Tidak, Kembali</button>
                    <button type="button" id="confirmCancelSubscriptionBtn" class="btn-cancel-subscription" onclick="submitCancelSubscription()">
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>
    @endif
@endauth
@endsection

@section('scripts')
<script>
    // Modal aturan & konfirmasi pembatalan langganan.
    // Ditulis dengan vanilla JS agar tidak bergantung pada library tambahan.
    function openSubsModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeSubsModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Dipanggil tombol terkunci: jelaskan aturan 1 bulan.
    function openCancelRuleModal() {
        openSubsModal('cancelRuleModal');
    }

    // Dipanggil tombol aktif: minta konfirmasi sebelum membatalkan.
    function openCancelSubscriptionModal() {
        const errorBox = document.getElementById('cancelSubscriptionError');
        if (errorBox) {
            errorBox.style.display = 'none';
            errorBox.textContent = '';
        }
        openSubsModal('cancelSubscriptionModal');
    }

    async function submitCancelSubscription() {
        const button = document.getElementById('confirmCancelSubscriptionBtn');
        const errorBox = document.getElementById('cancelSubscriptionError');
        const originalText = button ? button.innerHTML : '';

        if (button) {
            button.disabled = true;
            button.innerHTML = 'Memproses...';
        }

        try {
            const response = await fetch('{{ route('subscription.cancel') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            });

            let data = null;
            try {
                data = await response.json();
            } catch (parseError) {
                data = null;
            }

            if (data && data.success) {
                window.location.reload();
                return;
            }

            // Gagal (termasuk aturan 1 bulan): tampilkan alasannya di modal,
            // jangan menampilkan error mentah ke pengguna.
            if (errorBox) {
                errorBox.textContent = (data && data.message)
                    ? data.message
                    : 'Pembatalan tidak dapat diproses saat ini. Silakan coba lagi nanti.';
                errorBox.style.display = 'block';
            }
        } catch (error) {
            console.error('Cancel subscription error:', error);
            if (errorBox) {
                errorBox.textContent = 'Koneksi bermasalah. Silakan coba lagi beberapa saat lagi.';
                errorBox.style.display = 'block';
            }
        } finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }
    }

    // Tutup modal saat mengklik area gelap di luar kotak.
    document.addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('subs-modal')) {
            closeSubsModal(event.target.id);
        }
    });

    // Tutup modal dengan tombol Escape.
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        ['cancelRuleModal', 'cancelSubscriptionModal'].forEach(closeSubsModal);
    });
</script>
@endsection