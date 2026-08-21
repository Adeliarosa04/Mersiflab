/* ==========================================================================
   Guide Book LMS

   Modal panduan ini disisipkan di dekat form unggah, yang sering berada di
   dalam <form>, kartu ber-overflow, atau elemen ber-transform. Ketiganya
   membuat modal terpotong atau tertimpa walaupun z-index sudah tinggi, karena
   ancestor tersebut membentuk containing block baru.

   Karena itu elemen modal dipindahkan ke <body> begitu halaman siap. Tombol
   pemicu tetap bekerja: Bootstrap mencari target lewat selector, bukan lewat
   posisi elemen di DOM.
   ========================================================================== */
(function () {
    'use strict';

    function detachModal() {
        var modal = document.getElementById('guideBookModal');

        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    }

    /**
     * Menandai <body> selama modal panduan terbuka.
     *
     * Backdrop Bootstrap (.modal-backdrop) disisipkan sebagai anak <body>,
     * bukan anak modal, sehingga tidak bisa dijangkau selector turunan.
     * Penanda ini membuat efek blur hanya berlaku untuk modal panduan dan
     * tidak mengubah tampilan modal lain (pembayaran, konfirmasi, dsb).
     */
    function markBackdrop() {
        var modal = document.getElementById('guideBookModal');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', function () {
            document.body.classList.add('guide-book-open');
        });

        // hidden.bs.modal: dipanggil setelah animasi tutup selesai, jadi blur
        // ikut memudar bersama backdrop-nya.
        modal.addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('guide-book-open');
        });
    }

    function init() {
        detachModal();
        markBackdrop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
