@extends('layouts.app')

@section('title', 'Subscription Payment')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cart-checkout.css') }}">
{{-- Modal pembayaran QRIS, dipakai bersama dengan checkout course. --}}
<link rel="stylesheet" href="{{ asset('assets/css/payment-modal.css') }}">
@endsection

@section('content')
<div class="checkout-page">
    <div class="container">
        <div class="checkout-wrapper">
            @php
                // Konteks paket dihitung dengan aturan yang sama seperti backend
                // (SubscriptionPlanService), supaya tampilan checkout tidak lagi
                // mengunci pengguna Standard yang sedang meng-upgrade ke Premium.
                $checkoutUser = auth()->user();
                $planService = app(\App\Services\SubscriptionPlanService::class);
                $isUpgradeCheckout = $planService->isUpgrade($checkoutUser, $plan);
                $currentActivePlan = $planService->activePlan($checkoutUser);
                $blockedByActivePlan = $currentActivePlan !== null && !$isUpgradeCheckout;
            @endphp

            <!-- Upgrade Notice -->
            @if($isUpgradeCheckout)
            <div class="alert alert-info alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fas fa-arrow-up" style="color: #1A76D1; font-size: 20px; margin-top: 2px;"></i>
                    <div>
                            <strong>Upgrade ke {{ ucfirst($plan) }}</strong><br>
                            Anda sedang meng-upgrade dari paket {{ ucfirst($currentActivePlan) }}. Paket {{ ucfirst($currentActivePlan) }} tetap aktif sampai pembayaran upgrade disetujui admin, lalu otomatis digantikan paket {{ ucfirst($plan) }}.<br>
                            <small>Paket saat ini berakhir: {{ $checkoutUser->subscription_expires_at ? $checkoutUser->subscription_expires_at->format('d M Y H:i') : 'Unknown' }} WIB</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @elseif($blockedByActivePlan)
            <!-- Active Subscription Warning -->
            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fas fa-exclamation-triangle" style="color: #f57c00; font-size: 20px; margin-top: 2px;"></i>
                    <div>
                            <strong>Notice</strong><br>
                            You already have an active subscription. A new subscription will activate after the current one ends.<br>
                            <small>Subscription expires: {{ $checkoutUser->subscription_expires_at ? $checkoutUser->subscription_expires_at->format('d M Y H:i') : 'Unknown' }} WIB</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            <div class="checkout-left">
                 <h5 class="summary-title">Subscription Summary</h5>
            </div>
            <div class="checkout-content-wrapper">
                <div class="checkout-left-content">
                    <div class="product-list">
                        @php
                            $planName = ucfirst($plan);
                            $planPrice = $plan === 'standard' ? 50000 : 150000;
                            $planDescription = $plan === 'standard' 
                                ? 'Get access to all standard classes' 
                                : 'Get access to all standard to premium classes';
                        @endphp
                        <div class="product-card">
                            <div class="product-image-placeholder" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-crown" style="font-size: 2rem; color: #FFD700;"></i>
                            </div>
                            <div class="product-details">
                                <h6 class="product-title">{{ $planName }} Plan Subscription</h6>
                                <p class="product-description">{{ $planDescription }}</p>

                                <div class="product-meta">
                                    <div class="product-meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>1 Month</span>
                                    </div>
                                    <div class="product-meta-item">
                                        <i class="fas fa-infinity"></i>
                                        <span>Unlimited Access</span>
                                    </div>
                                    @if($plan === 'premium')
                                        <div class="product-meta-item">
                                            <i class="fas fa-star"></i>
                                            <span>Premium Certificate</span>
                                        </div>
                                        <div class="product-meta-item">
                                            <i class="fas fa-download"></i>
                                            <span>Download Materials</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="product-section">
                                    <h6 class="product-section-title">
                                        <i class="fas fa-gift"></i>
                                        Yang Termasuk
                                    </h6>
                                    <div class="product-includes">
                                        @if($plan === 'standard')
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Access to all standard courses</span>
                                            </div>
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Basic certificate</span>
                                            </div>
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Email support</span>
                                            </div>
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Validity: 1 month</span>
                                            </div>
                                        @else
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Access to all courses (standard + premium)</span>
                                            </div>
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Premium certificate</span>
                                            </div>
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Priority support</span>
                                            </div>
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Download materials</span>
                                            </div>
                                            <div class="product-include-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                    <span>Validity: 1 month</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="product-price-section">
                                    <div class="product-price">Rp{{ number_format($planPrice, 0, ',', '.') }}<span style="font-size: 0.8em; color: #666; margin-left: 4px;">/month</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="checkout-right">
                <div class="payment-card">
                    <!-- Back Button -->
                    <button class="btn btn-outline-secondary mb-3" onclick="showCancelConfirmation()" style="width: 100%;">
                            <i class="fas fa-arrow-left"></i> Back
                    </button>
                    
                    <a href="#" class="btn btn-teal choose-payment" onclick="openPaymentModal()">Choose Payment Method  &nbsp; ›</a>

                    <!-- Selected Payment Method Display -->
                    <div id="selectedPaymentMethod" style="display: none; margin-bottom: 12px;">
                        <div class="selected-method-box">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <img id="selectedPaymentIcon" src="" alt="" style="width: 24px; height: 24px;">
                                    <span id="selectedPaymentName" style="font-weight: 500;"></span>
                                </div>
                                <button type="button" onclick="openPaymentModal()" class="btn-change-method">Change</button>
                            </div>
                        </div>
                    </div>

                    @php
                        $total = $plan === 'standard' ? 50000 : 150000;
                    @endphp
                    <div class="price-list">
                        <div class="price-row">
                            <span>Subtotal</span>
                            <span id="subtotalCheckout">Rp{{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="price-row">
                               <span>Transaction Fee</span>
                            <span>-</span>
                        </div>
                        <div class="price-row total">
                            <strong>Total</strong>
                            <strong id="totalCheckout">Rp{{ number_format($total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    
                    <!-- Bayar Sekarang Button -->
                    {{-- Hanya paket yang sama / lebih rendah yang dikunci.
                         Upgrade ke paket lebih tinggi tetap bisa dibayar. --}}
                    @if($blockedByActivePlan)
                    <button id="bayarSekarangBtn" class="btn-bayar-sekarang" disabled style="opacity: 0.6; cursor: not-allowed;">
                            <i class="fas fa-lock me-2"></i>Active Subscription - Cannot Purchase
                    </button>
                    @else
                    <button id="bayarSekarangBtn" class="btn-bayar-sekarang" onclick="showPaymentConfirmation()" disabled>
                            {{ $isUpgradeCheckout ? 'Bayar Upgrade' : 'Pay Now' }}
                    </button>
                    @endif
                    
                    <!-- Hidden form for submission -->
                    <form action="{{ route('subscription.process.payment') }}" method="POST" id="paymentForm" style="display: none;">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan }}">
                        <input type="hidden" name="payment_method" id="selected_payment_method">
                        <input type="hidden" name="discount_amount" value="0">
                        <input type="hidden" name="final_amount" value="{{ $total }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Modal -->
<div id="paymentModal" class="payment-modal">
    <div class="modal-content">
        <div class="modal-header">
              <h3>Choose Payment Method</h3>
            <button class="close-btn" onclick="closePaymentModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="payment-methods-grid">
                <!-- QRIS -->
                <div class="payment-category">
                    <h4>QRIS</h4>
                    <div class="payment-options">
                        <div class="payment-option payment-option-active" onclick="selectPayment('QRIS', 'qris')">
                            <img src="{{ asset('images/payment/qris.png') }}" alt="QRIS" onerror="this.src='https://via.placeholder.com/40x40/1A76D1/FFFFFF?text=QRIS'">
                            <span>QRIS</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Modal pembayaran QRIS (bersama dengan checkout course) --}}
@include("partials.payment-modal")

<!-- Cancel Purchase Confirmation Modal -->
<div id="cancelPurchaseModal" class="payment-confirmation-modal">
    <div class="confirmation-modal-content">
        <div class="confirmation-icon" style="background: #dc3545;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="confirmation-content">
              <h3>Cancel Purchase?</h3>
              <p>Are you sure you want to cancel this subscription purchase?</p>
              <p>The subscription will remain available for purchase again.</p>
        </div>
        <div class="confirmation-actions">
              <button class="btn btn-secondary" onclick="closeCancelConfirmation()">No</button>
              <button class="btn btn-danger" onclick="confirmCancelPurchase()">Yes, Cancel</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let selectedPaymentMethod = null;

    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Cancel Purchase Confirmation Functions
    function showCancelConfirmation() {
        document.getElementById('cancelPurchaseModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeCancelConfirmation() {
        document.getElementById('cancelPurchaseModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function confirmCancelPurchase() {
        // Disable buttons to prevent double submission
        const confirmBtn = event.target;
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Cancelling...';

        // Cancel any pending subscription purchase
        fetch('/subscription/cancel-pending', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect back to subscription page
                window.location.href = '{{ route("subscription.page") }}';
            } else {
                // Even if cancel fails, still redirect (user wants to go back)
                window.location.href = '{{ route("subscription.page") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Even if there's an error, still redirect
            window.location.href = '{{ route("subscription.page") }}';
        });
    }

    // Payment Modal Functions
    function openPaymentModal() {
        document.getElementById('paymentModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function selectPayment(name, code) {
        selectedPaymentMethod = { name, code };
        
        // Update UI
        const selectedDiv = document.getElementById('selectedPaymentMethod');
        const nameSpan = document.getElementById('selectedPaymentName');
        const iconImg = document.getElementById('selectedPaymentIcon');
        
        // Hide the choose payment button(s)
        document.querySelectorAll('.choose-payment').forEach(function (el) {
            el.style.display = 'none';
        });
        
        // Show selected payment method
        selectedDiv.style.display = 'block';
        nameSpan.textContent = name;
        
        // Set icon based on payment method
        const iconMap = {
            'qris': '{{ asset("images/payment/qris.png") }}',
        };
        
        iconImg.onerror = function() {
            this.src = 'https://via.placeholder.com/24x24/1A76D1/FFFFFF?text=QRIS';
        };
        
        iconImg.src = iconMap[code] || 'https://via.placeholder.com/24x24/CCCCCC/FFFFFF?text=?';
        iconImg.alt = name;
        
        // Update hidden input
        document.getElementById('selected_payment_method').value = code;
        
        // Enable Bayar Sekarang button
        document.getElementById('bayarSekarangBtn').disabled = false;
        
        // Close modal
        closePaymentModal();
    }

    // Payment Confirmation Functions
    function showPaymentConfirmation() {
        // Check if payment method is selected
        if (!selectedPaymentMethod) {
            alert('Please choose a payment method first.');
            return;
        }
        
        // Disable button to prevent double submission
        const btn = document.getElementById('bayarSekarangBtn');
        btn.disabled = true;
        btn.textContent = 'Processing...';
        
        // Get payment method from hidden input or selectedPaymentMethod
        const paymentMethodInput = document.getElementById('selected_payment_method');
        const paymentMethod = paymentMethodInput ? paymentMethodInput.value : selectedPaymentMethod.code;
        const paymentProvider = selectedPaymentMethod.code;
        
        // Submit form
        const form = document.getElementById('paymentForm');
        const formData = new FormData(form);
        
        // Send payment to server
        fetch('{{ route('subscription.process.payment') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If redirect response, parse as text and show success
                return response.text().then(() => ({ success: true }));
            }
        })
        .then(data => {
            if (data && data.success) {
                openPaymentModalQris(data.payment);
            } else {
                alert(data?.message || 'An error occurred while processing the payment.');
                btn.disabled = false;
                btn.textContent = 'Pay Now';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the payment. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Pay Now';
        });
    }

    /**
     * Tujuan setelah modal pembayaran ditutup. openPaymentModalQris/
     * closePaymentModalQris berada di assets/js/payment-modal.js.
     */
    window.onPaymentModalDone = function (payment) {
        window.location.href = (payment && payment.invoice_url)
            ? payment.invoice_url
            : '{{ route("subscription.page") }}';
    };

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('paymentModal');
        if (event.target === modal) {
            closePaymentModal();
        }
    }

    // Escape key to close modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePaymentModal();
        }
    });

</script>

{{-- Pengisi & kontrol modal pembayaran QRIS (bersama dengan checkout course) --}}
<script src="{{ asset('assets/js/payment-modal.js') }}"></script>
@endsection
