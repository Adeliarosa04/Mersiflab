{{--
    Modal pembayaran QRIS — dipakai bersama oleh checkout subscription dan
    checkout pembelian course.

    Isinya diisi dari JavaScript memakai payload `payment` yang dikirim server
    (lihat SubscriptionController::paymentPayload dan CartController::paymentPayload).

    Cara pakai pada halaman checkout:
        @include('partials.payment-modal')
    lalu panggil openPaymentModalQris(payload) setelah pembayaran diproses.

    Halaman pemanggil menyediakan window.onPaymentModalDone() untuk menentukan
    ke mana pengguna diarahkan setelah modal ditutup.
--}}

<div id="paymentConfirmationModal" class="subs-pay-modal">
    <div class="subs-pay-dialog" role="dialog" aria-modal="true" aria-labelledby="subsPayTitle">

        <div class="subs-pay-header">
            <div>
                <h3 class="subs-pay-title" id="subsPayTitle">Complete Your Payment</h3>
                <p class="subs-pay-subtitle">Scan the QRIS below to complete your order.</p>
            </div>
            <button type="button" class="subs-pay-close" onclick="closePaymentModalQris()" aria-label="Close">&times;</button>
        </div>

        <div class="subs-pay-body">

            {{-- Detail invoice --}}
            <div class="subs-pay-invoice">
                <div class="subs-pay-row">
                    <span class="subs-pay-label">Invoice Number</span>
                    <span class="subs-pay-value mono" id="payInvoiceNumber">-</span>
                </div>
                <div class="subs-pay-row">
                    <span class="subs-pay-label">Transaction ID</span>
                    <span class="subs-pay-value mono" id="payPurchaseCode">-</span>
                </div>
                <div class="subs-pay-row">
                    <span class="subs-pay-label">Date</span>
                    <span class="subs-pay-value" id="payCreatedAt">-</span>
                </div>
                <div class="subs-pay-row" id="payDueRow" hidden>
                    <span class="subs-pay-label">Pay Before</span>
                    <span class="subs-pay-value" id="payDueDate">-</span>
                </div>

                <div class="subs-pay-divider"></div>

                {{-- Baris item: paket langganan, atau judul course yang dibeli --}}
                <div id="payItems"></div>

                <div class="subs-pay-divider"></div>

                <div class="subs-pay-row">
                    <span class="subs-pay-label">Subtotal</span>
                    <span class="subs-pay-value" id="paySubtotal">-</span>
                </div>
                <div class="subs-pay-row" id="payDiscountRow" hidden>
                    <span class="subs-pay-label">Discount</span>
                    <span class="subs-pay-value" id="payDiscount">-</span>
                </div>
                <div class="subs-pay-row subs-pay-row-total">
                    <span class="subs-pay-label">Total</span>
                    <span class="subs-pay-value" id="payTotal">-</span>
                </div>
            </div>

            {{-- Area QRIS --}}
            <div class="subs-pay-qris">
                <span class="subs-pay-qris-badge"><i class="fas fa-qrcode"></i> QRIS</span>

                <div class="subs-pay-qris-frame">
                    <img id="payQrisImage" src="" alt="QRIS payment code" hidden>
                    <div class="subs-pay-qris-missing" id="payQrisMissing" hidden>
                        <i class="fas fa-qrcode"></i>
                        <p>QRIS code is not available. Please confirm with admin via WhatsApp.</p>
                    </div>
                </div>

                <p class="subs-pay-qris-amount" id="payQrisAmount">-</p>
                <p class="subs-pay-qris-hint">Scannable with any QRIS-supported app (GoPay, OVO, DANA, ShopeePay, m-banking).</p>
            </div>

            {{-- Panduan singkat --}}
            <ol class="subs-pay-steps">
                <li>Open your e-wallet or m-banking app, choose <strong>Scan QRIS</strong>.</li>
                <li>Scan the code above and enter the exact total amount.</li>
                <li>Confirm your payment via WhatsApp so admin can verify it faster.</li>
                <li>Your access is activated once admin approves the payment.</li>
            </ol>

            <a href="#" id="payWhatsappLink" class="subs-pay-wa" target="_blank" rel="noopener">
                <i class="fab fa-whatsapp"></i> Confirm Payment via WhatsApp
            </a>
        </div>

        <div class="subs-pay-footer">
            <a href="#" id="payCheckStatusBtn" class="btn subs-pay-btn subs-pay-btn-primary">
                Check Payment Status
            </a>
            <button type="button" class="btn subs-pay-btn subs-pay-btn-ghost" onclick="closePaymentModalQris()">
                Done
            </button>
        </div>

    </div>
</div>
