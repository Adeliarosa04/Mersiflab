@extends('layouts.app')

@section('title', 'Checkout')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cart-checkout.css') }}">
{{-- Modal pembayaran QRIS, dipakai bersama dengan checkout subscription. --}}
<link rel="stylesheet" href="{{ asset('assets/css/payment-modal.css') }}">
@endsection

@section('content')
<div class="checkout-page">
    <div class="container">
        <div class="checkout-wrapper">
            <div class="checkout-left">
                <h5 class="summary-title">Ringkasan Product</h5>
            </div>
            <div class="checkout-content-wrapper">
                <div class="checkout-left-content">
                    <div class="product-list">
                            @php
                                $subtotal = 0;
                                $items = [];
                            @endphp

                            @if(isset($purchases) && $purchases->count() > 0)
                                @foreach($purchases as $p)
                                    @php
                                        $price = $p->amount;
                                        $subtotal += $price;
                                        $course = $p->course;
                                        $items[] = [
                                            'course' => $course,
                                            'name' => $course->name ?? 'Course',
                                            'price' => $price
                                        ];
                                    @endphp
                                @endforeach
                            @elseif(isset($purchase))
                                @php
                                    $subtotal = $purchase->amount;
                                    $course = $purchase->course;
                                    $items[] = [
                                        'course' => $course,
                                        'name' => $course->name ?? 'Course',
                                        'price' => $purchase->amount
                                    ];
                                @endphp
                            @endif

                            @foreach($items as $it)
                                @php
                                    $course = $it['course'] ?? null;
                                @endphp
                                <div class="product-card">
                                    @if($course && $course->image)
                                        <div class="product-image">
                                            <img src="{{ asset('storage/' . $course->image) }}" alt="{{ $course->name }}">
                                        </div>
                                    @else
                                        <div class="product-image-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    @endif
                                    <div class="product-details">
                                        <h6 class="product-title">{{ $it['name'] }}</h6>
                                        
                                        @if($course && $course->description)
                                            <p class="product-description">{{ $course->description }}</p>
                                        @endif

                                        <div class="product-meta">
                                            @if($course && $course->teacher)
                                                <div class="product-meta-item">
                                                    <i class="fas fa-user-tie"></i>
                                                    <span>{{ $course->teacher->name }}</span>
                                                </div>
                                            @endif
                                            @if($course && $course->category)
                                                <div class="product-meta-item">
                                                    <i class="fas fa-tag"></i>
                                                    <span>{{ $course->category_name ?? $course->category }}</span>
                                                </div>
                                            @endif
                                            @if($course && $course->formatted_total_duration)
                                                <div class="product-meta-item">
                                                    <i class="fas fa-clock"></i>
                                                    <span>{{ $course->formatted_total_duration }}</span>
                                                </div>
                                            @endif
                                            @if($course)
                                                @if($course->chapters_count > 0)
                                                    <div class="product-meta-item">
                                                        <i class="fas fa-list"></i>
                                                        <span>{{ $course->chapters_count }} Chapter</span>
                                                    </div>
                                                @endif
                                                @if($course->modules_count > 0)
                                                    <div class="product-meta-item">
                                                        <i class="fas fa-book-open"></i>
                                                        <span>{{ $course->modules_count }} Modul</span>
                                                    </div>
                                                @endif
                                                @if($course->average_rating > 0)
                                                    <div class="product-meta-item">
                                                        <i class="fas fa-star"></i>
                                                        <span>{{ number_format($course->average_rating, 1) }} ({{ $course->reviews_count ?? 0 }} review)</span>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                        @if($course && $course->formatted_includes && count($course->formatted_includes) > 0)
                                            <div class="product-section">
                                                <h6 class="product-section-title">
                                                    <i class="fas fa-gift"></i>
                                                    Yang Termasuk
                                                </h6>
                                                <div class="product-includes">
                                                    @foreach($course->formatted_includes as $include)
                                                        <div class="product-include-item">
                                                            <i class="{{ $include['icon'] }} {{ $include['color'] }}"></i>
                                                            <span>{{ $include['text'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div class="product-price-section">
                                            @if($course && $course->has_discount && $course->discounted_price && $course->discount_percentage > 0)
                                                <div class="product-price-old">Rp{{ number_format($course->price, 0, ',', '.') }}</div>
                                                <div class="product-price-discount">Diskon {{ $course->discount_percentage }}%</div>
                                            @endif
                                            <div class="product-price">Rp{{ number_format($it['price'], 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                    </div>
                </div>

                <div class="checkout-right">
                    <div class="payment-card">
                    <!-- Back Button -->
                    <button class="btn btn-outline-secondary mb-3" onclick="showCancelConfirmation()" style="width: 100%;">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    
                    <a href="#" class="btn btn-teal choose-payment" onclick="openPaymentModal()">Pilih Metode Pembayaran  &nbsp; ›</a>
                    
                    <!-- Selected Payment Method Display -->
                    <div id="selectedPaymentMethod" style="display: none; margin-bottom: 12px;">
                        <div class="selected-method-box">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <img id="selectedPaymentIcon" src="" alt="" style="width: 24px; height: 24px;">
                                    <span id="selectedPaymentName" style="font-weight: 500;"></span>
                                </div>
                                <button type="button" onclick="openPaymentModal()" class="btn-change-method">Ubah</button>
                            </div>
                        </div>
                    </div>

                    @php
                        $total = isset($subtotal) ? $subtotal : 0;
                    @endphp
                    <div class="price-list">
                        <div class="price-row">
                            <span>Subtotal</span>
                            <span id="subtotalCheckout">Rp{{ number_format($subtotal ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="price-row">
                            <span>Biaya Transaksi</span>
                            <span>-</span>
                        </div>
                        <div class="price-row total">
                            <strong>Total</strong>
                            <strong id="totalCheckout">Rp{{ number_format($total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    
                    <!-- Bayar Sekarang Button -->
                    <button id="bayarSekarangBtn" class="btn-bayar-sekarang" onclick="showPaymentConfirmation()" disabled>
                        Bayar Sekarang
                    </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Payment Method Modal -->
<div id="paymentModal" class="payment-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Pilih Metode Pembayaran</h3>
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

{{-- Modal pembayaran QRIS (bersama dengan checkout subscription) --}}
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
            <h3>Batalkan Pembelian?</h3>
            <p>Apakah Anda yakin ingin membatalkan pembelian ini?</p>
            <p>Course akan tetap tersedia untuk pembelian kembali.</p>
        </div>
        <div class="confirmation-actions">
            <button class="btn btn-secondary" onclick="closeCancelConfirmation()">Tidak</button>
            <button class="btn btn-danger" onclick="confirmCancelPurchase()">Ya, Batalkan</button>
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
        confirmBtn.textContent = 'Membatalkan...';

        // Get course IDs from session data
        const courseIds = @json(session('checkout_course_ids', []));
        
        if (courseIds.length === 0) {
            // If no course IDs in session, try to get from purchases
            const purchases = @json(isset($purchases) ? $purchases->pluck('class_id') : (isset($purchase) ? [$purchase->class_id] : []));
            
            if (purchases.length === 0) {
                // Fallback: just go back
                window.history.back();
                return;
            }
            
            cancelPurchaseAndRedirect(purchases);
        } else {
            cancelPurchaseAndRedirect(courseIds);
        }
    }

    function cancelPurchaseAndRedirect(courseIds) {
        // Cancel each course purchase
        let cancelPromises = courseIds.map(courseId => {
            return fetch('/cart/cancel-pending', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    course_id: courseId
                })
            });
        });

        Promise.all(cancelPromises)
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(results => {
                // Check if all were successful
                const allSuccessful = results.every(result => result.success);
                
                if (allSuccessful) {
                    // Redirect to previous page or courses page
                    const referrer = document.referrer;
                    if (referrer && referrer.includes(window.location.hostname)) {
                        window.location.href = referrer;
                    } else {
                        window.location.href = '{{ route('courses') }}';
                    }
                } else {
                    alert('Beberapa pembelian gagal dibatalkan. Silakan coba lagi.');
                    // Re-enable button
                    const confirmBtn = document.querySelector('#cancelPurchaseModal .btn-danger');
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Ya, Batalkan';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                // Re-enable button
                const confirmBtn = document.querySelector('#cancelPurchaseModal .btn-danger');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Ya, Batalkan';
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
        
        // Hide the choose payment button
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
        
        // Enable Bayar Sekarang button
        document.getElementById('bayarSekarangBtn').disabled = false;
        
        // Close modal
        closePaymentModal();
        
        // Add hidden input for form submission
        let hiddenInput = document.getElementById('selected_payment_method');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'selected_payment_method';
            hiddenInput.name = 'payment_method';
            document.querySelector('.payment-card').appendChild(hiddenInput);
        }
        hiddenInput.value = code;
    }

    // Payment Confirmation Functions
    function showPaymentConfirmation() {
        // Check if payment method is selected
        if (!selectedPaymentMethod) {
            alert('Silakan pilih metode pembayaran terlebih dahulu.');
            return;
        }
        
        // Disable button to prevent double submission
        const btn = document.getElementById('bayarSekarangBtn');
        btn.disabled = true;
        btn.textContent = 'Memproses...';
        
        // Get payment method from hidden input or selectedPaymentMethod
        const paymentMethodInput = document.getElementById('selected_payment_method');
        const paymentMethod = paymentMethodInput ? paymentMethodInput.value : selectedPaymentMethod.code;
        const paymentProvider = selectedPaymentMethod.code;
        
        // Send payment to server
        fetch('{{ route('cart.processPayment') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                payment_method: selectedPaymentMethod.name,
                payment_provider: paymentProvider
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openPaymentModalQris(data.payment);
            } else {
                alert(data.message || 'Terjadi kesalahan saat memproses pembayaran.');
                btn.disabled = false;
                btn.textContent = 'Bayar Sekarang';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
            btn.disabled = false;
            btn.textContent = 'Bayar Sekarang';
        });
    }

    /**
     * Tujuan setelah modal pembayaran ditutup. openPaymentModalQris/
     * closePaymentModalQris berada di assets/js/payment-modal.js.
     */
    window.onPaymentModalDone = function (payment) {
        window.location.href = (payment && payment.invoice_url)
            ? payment.invoice_url
            : '{{ route('courses') }}';
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

{{-- Pengisi & kontrol modal pembayaran QRIS (bersama dengan checkout subscription) --}}
<script src="{{ asset('assets/js/payment-modal.js') }}"></script>
@endsection
