/* ==========================================================================
   Modal pembayaran QRIS

   Mengisi resources/views/partials/payment-modal.blade.php dari payload
   `payment` yang dikirim server, lalu menampilkannya. Dipakai baik oleh
   checkout langganan maupun checkout pembelian course, sehingga keduanya
   memberi pengalaman yang sama.

   QRIS-nya adalah QR merchant statis milik situs — nominal tidak tertanam
   di dalam kode, jadi pembayar mengisi jumlahnya sendiri dan admin yang
   memverifikasi.
   ========================================================================== */
(function (window, document) {
    'use strict';

    var payment = null;

    function byId(id) {
        return document.getElementById(id);
    }

    function setText(id, value) {
        var node = byId(id);
        if (node) node.textContent = (value === null || value === undefined || value === '') ? '-' : value;
    }

    function toggle(id, visible) {
        var node = byId(id);
        if (node) node.hidden = !visible;
    }

    /** Baris item: nama paket atau judul course beserta harganya. */
    function renderItems(items) {
        var holder = byId('payItems');
        if (!holder) return;

        holder.innerHTML = '';

        (items || []).forEach(function (item) {
            var row = document.createElement('div');
            row.className = 'subs-pay-row subs-pay-item';

            var name = document.createElement('span');
            name.className = 'subs-pay-label';
            name.textContent = item.name || 'Item';

            var amount = document.createElement('span');
            amount.className = 'subs-pay-value';
            amount.textContent = item.amount || '';

            row.appendChild(name);
            row.appendChild(amount);
            holder.appendChild(row);
        });
    }

    /**
     * Isi modal lalu tampilkan.
     * @param {object} data payload `payment` dari response server
     */
    function openPaymentModalQris(data) {
        payment = data || {};

        setText('payInvoiceNumber', payment.invoice_number);
        setText('payPurchaseCode', payment.reference);
        setText('payCreatedAt', payment.created_at);
        setText('paySubtotal', payment.subtotal);
        setText('payTotal', payment.total);
        setText('payQrisAmount', payment.total);

        renderItems(payment.items);

        if (payment.due_date) {
            setText('payDueDate', payment.due_date);
            toggle('payDueRow', true);
        } else {
            toggle('payDueRow', false);
        }

        if (payment.has_discount) {
            setText('payDiscount', '- ' + payment.discount);
            toggle('payDiscountRow', true);
        } else {
            toggle('payDiscountRow', false);
        }

        var qrisImage = byId('payQrisImage');
        if (payment.qris_url && qrisImage) {
            qrisImage.src = payment.qris_url;
            toggle('payQrisImage', true);
            toggle('payQrisMissing', false);
        } else {
            toggle('payQrisImage', false);
            toggle('payQrisMissing', true);
        }

        var waLink = byId('payWhatsappLink');
        if (waLink) {
            if (payment.whatsapp_url) {
                waLink.href = payment.whatsapp_url;
                waLink.hidden = false;
            } else {
                waLink.hidden = true;
            }
        }

        var statusBtn = byId('payCheckStatusBtn');
        if (statusBtn && payment.invoice_url) {
            statusBtn.href = payment.invoice_url;
        }

        var modal = byId('paymentConfirmationModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Tutup modal. Halaman pemanggil menentukan tujuan berikutnya lewat
     * window.onPaymentModalDone(payment).
     */
    function closePaymentModalQris() {
        var modal = byId('paymentConfirmationModal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        if (typeof window.onPaymentModalDone === 'function') {
            window.onPaymentModalDone(payment);
        }
    }

    window.openPaymentModalQris = openPaymentModalQris;
    window.closePaymentModalQris = closePaymentModalQris;
})(window, document);
