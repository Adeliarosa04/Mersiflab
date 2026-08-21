/* ==========================================================================
   Pratinjau materi Free Class (Modul PDF & Slide PPTX)

   PDF  : dirender browser di dalam <iframe>.
   PPTX : dirender di sisi klien oleh PPTXjs. Berkas diambil dari storage
          milik situs sendiri, jadi tidak ada layanan pihak ketiga dan tetap
          berjalan di localhost maupun jaringan tertutup.

   Library PPTXjs berukuran besar, karena itu dimuat secara lazy — baru saat
   tombol "Lihat" pada materi PPT ditekan pertama kali.
   ========================================================================== */
(function () {
    'use strict';

    var modalEl = document.getElementById('materialPreviewModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }

    var modal = new bootstrap.Modal(modalEl);

    var el = {
        title: document.getElementById('materialPreviewLabel'),
        frame: document.getElementById('materialPreviewFrame'),
        stage: document.getElementById('pptxStage'),
        viewer: document.getElementById('pptxViewer'),
        toolbar: document.getElementById('pptxToolbar'),
        loading: document.getElementById('pptxLoading'),
        error: document.getElementById('pptxError'),
        errorText: document.getElementById('pptxErrorText'),
        officeFallback: document.getElementById('pptxOfficeFallback'),
        counter: document.getElementById('pptxCounter'),
        prev: document.getElementById('pptxPrev'),
        next: document.getElementById('pptxNext'),
        zoomIn: document.getElementById('pptxZoomIn'),
        zoomOut: document.getElementById('pptxZoomOut'),
        zoomFit: document.getElementById('pptxZoomFit'),
        zoomLevel: document.getElementById('pptxZoomLevel'),
        downloadLink: document.getElementById('materialPreviewDownload')
    };

    // Office Web Viewer hanya dipakai bila berkas benar-benar dapat
    // diunduh dari internet (lihat perhitungan di preview-modal.blade.php).
    // Sekarang HANYA dipakai tombol cadangan di panel galat; tombol footer
    // "Buka di tab baru" beserta konstanta view.aspx-nya sudah dihapus.
    var OFFICE_VIEWER_USABLE = modalEl.dataset.officeViewer === '1';

    var MIN_ZOOM = 0.2;
    var MAX_ZOOM = 3;
    var RENDER_TIMEOUT = 45000;   // batas menunggu render selesai
    var SETTLE_DELAY = 600;       // jeda tanpa slide baru = dianggap selesai

    // Batas ukuran modal - SUDAH termasuk header, toolbar, dan footer.
    var MAX_VIEWPORT_H = 0.85;    // 85vh
    var MAX_VIEWPORT_W = 0.90;    // 90vw
    var STAGE_PADDING = 24;       // padding panggung (12px di tiap sisi)
    var DIALOG_GUTTER = 32;       // margin kiri+kanan .modal-dialog bawaan Bootstrap
    var MIN_FRAME_H = 180;        // jaring pengaman untuk jendela sangat pendek

    var state = {
        slides: [],
        index: 0,
        zoom: 1,
        loaded: false,      // library sudah dimuat?
        renderedUrl: null,  // berkas yang BERHASIL tampil, agar tidak dirender ulang
        observer: null,
        settleTimer: null,
        timeoutTimer: null,
        // Nomor urut permintaan render. PPTXjs mengambil berkas secara async
        // dan tidak bisa dibatalkan; token ini dipakai untuk membuang hasil
        // render lama yang datang terlambat agar tidak menimpa slide yang baru.
        renderToken: 0,
        // URL PPT yang sedang dibuka - dipakai tombol cadangan Office Viewer.
        currentPptUrl: null,
        // true selama skala masih hasil "fit" otomatis. Menjadi false begitu
        // pengguna menekan +/-, supaya ganti slide atau resize jendela tidak
        // diam-diam membatalkan zoom yang ia pilih sendiri.
        fitted: true
    };

    /* ------------------------------------------------------------------
     | Pemuat library (sekali saja)
     * ---------------------------------------------------------------- */

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            var existing = document.querySelector('script[src="' + src + '"]');
            if (existing) {
                if (existing.dataset.loaded === 'true') return resolve();
                existing.addEventListener('load', function () { resolve(); });
                existing.addEventListener('error', function () { reject(new Error(src)); });
                return;
            }

            var script = document.createElement('script');
            script.src = src;
            script.onload = function () {
                script.dataset.loaded = 'true';
                resolve();
            };
            script.onerror = function () {
                reject(new Error('Gagal memuat ' + src));
            };
            document.body.appendChild(script);
        });
    }

    function loadViewerLibraries() {
        if (state.loaded) {
            return Promise.resolve();
        }

        if (typeof window.jQuery === 'undefined') {
            return Promise.reject(new Error('jQuery tidak tersedia'));
        }

        var base = modalEl.dataset.vendorBase;

        // Urutan penting — PPTXjs memakai ketiganya saat diinisialisasi:
        //   jszip.min.js   API JSZip v2 (PPTXjs memanggil asText/asArrayBuffer,
        //                  yang sudah dihapus di JSZip v3)
        //   filereader.js  FileReaderJS; dirujuk tanpa syarat walaupun berkas
        //                  diambil lewat URL, bukan input berkas
        //   dingbat.js     tabel unicode untuk bullet berfont Wingdings/Webdings
        return loadScript(base + '/jszip.min.js')
            .then(function () { return loadScript(base + '/filereader.js'); })
            .then(function () { return loadScript(base + '/dingbat.js'); })
            .then(function () { return loadScript(base + '/pptxjs.js'); })
            .then(function () { state.loaded = true; });
    }

    /* ------------------------------------------------------------------
     | Tampilan
     * ---------------------------------------------------------------- */

    function show(node) { if (node) node.hidden = false; }
    function hide(node) { if (node) node.hidden = true; }

    function resetView() {
        clearTimeout(state.settleTimer);
        clearTimeout(state.timeoutTimer);

        // Naikkan token supaya render PPT yang masih berjalan (PPTXjs mengambil
        // berkas secara async dan tidak bisa dibatalkan) dianggap kedaluwarsa
        // dan hasilnya tidak menimpa berkas yang baru dibuka pengguna.
        state.renderToken++;

        if (state.observer) {
            state.observer.disconnect();
            state.observer = null;
        }

        hide(el.loading);
        hide(el.error);
        hide(el.officeFallback);
        hide(el.toolbar);
        hide(el.stage);
        hide(el.frame);
    }

    function fail(message) {
        resetView();
        el.errorText.textContent = message || 'Slide tidak dapat ditampilkan.';

        // Cadangan: Office Web Viewer milik Microsoft. URL berkas diambil
        // dari state (dinamis per level), bukan nilai tetap, lalu di-encode
        // agar aman dipakai sebagai query string.
        if (el.officeFallback) {
            if (state.currentPptUrl && OFFICE_VIEWER_USABLE) {
                el.officeFallback.href =
                    'https://view.officeapps.live.com/op/embed.aspx?src=' +
                    encodeURIComponent(state.currentPptUrl);
                show(el.officeFallback);
            } else {
                hide(el.officeFallback);
            }
        }

        show(el.error);
    }

    /* ------------------------------------------------------------------
     | Navigasi slide
     * ---------------------------------------------------------------- */

    function renderSlide() {
        state.slides.forEach(function (slide, i) {
            slide.style.display = i === state.index ? 'block' : 'none';
        });

        el.counter.textContent = 'Slide ' + (state.index + 1) + ' dari ' + state.slides.length;
        el.prev.disabled = state.index === 0;
        el.next.disabled = state.index >= state.slides.length - 1;
    }

    function goTo(index) {
        if (!state.slides.length) return;

        state.index = Math.min(Math.max(index, 0), state.slides.length - 1);
        renderSlide();

        // Slide dalam satu deck bisa berbeda ukuran. Selama masih memakai skala
        // fit, kotak dan skalanya dihitung ulang; kalau pengguna sedang memakai
        // zoom manual, hanya kotaknya yang disesuaikan agar zoom pilihannya
        // tidak dibatalkan diam-diam.
        if (state.fitted) {
            fitToStage();
        } else {
            applyZoom();
        }
    }

    /* ------------------------------------------------------------------
     | Zoom
     * ---------------------------------------------------------------- */

    /**
     * Ukuran ASLI slide yang sedang tampil.
     *
     * PPTXjs menulis ukuran slide sebagai gaya inline
     * (`<div class="slide" style="width:960px; height:540px">`) hasil konversi
     * dari EMU di dalam berkas .pptx. Karena itu rasionya DIBACA dari sana,
     * bukan dipatok 16:9 - deck 4:3 atau ukuran kustom pun ikut benar.
     *
     * style.width dipakai lebih dulu karena offsetWidth bernilai 0 untuk slide
     * yang sedang display:none (renderSlide menyembunyikan yang bukan aktif).
     */
    function slideSize() {
        var slide = state.slides[state.index] || state.slides[0];
        if (!slide) return null;

        var w = parseFloat(slide.style.width) || slide.offsetWidth || 0;
        var h = parseFloat(slide.style.height) || slide.offsetHeight || 0;

        return (w && h) ? { width: w, height: h } : null;
    }

    /** Tinggi header + toolbar + footer modal, yang ikut memakan jatah 85vh. */
    function chromeHeight() {
        var total = 0;

        [
            modalEl.querySelector('.modal-header'),
            el.toolbar,
            modalEl.querySelector('.modal-footer')
        ].forEach(function (node) {
            if (node && !node.hidden) total += node.offsetHeight;
        });

        return total;
    }

    /**
     * Menyetel ukuran KOTAK pratinjau agar mengikuti rasio slide.
     *
     * Sebelumnya kotak memakai `aspect-ratio: 16/9` di CSS, yang menghitung
     * tinggi dari LEBAR dialog dan sama sekali mengabaikan sisa ruang vertikal
     * setelah header/toolbar/footer. Kotak jadi terlalu tinggi: panggung
     * memunculkan scrollbar vertikal dan slide terpotong, sementara di kiri
     * kanan justru menganggur.
     *
     * Sekarang tingginya ditentukan lebih dulu dari ruang yang benar-benar
     * tersisa, lalu lebarnya diturunkan dari rasio - dan baru dibalik bila
     * hasilnya melebihi lebar yang boleh dipakai.
     */
    function layoutStage() {
        var size = slideSize();
        if (!size) return;

        var ratio = size.width / size.height;

        var availH = (window.innerHeight * MAX_VIEWPORT_H) - chromeHeight() - STAGE_PADDING;
        var availW = (window.innerWidth * MAX_VIEWPORT_W) - DIALOG_GUTTER - STAGE_PADDING;

        var h = Math.max(MIN_FRAME_H, availH);
        var w = h * ratio;

        if (w > availW) {
            w = availW;
            h = w / ratio;
        }

        modalEl.style.setProperty('--preview-w', Math.round(w + STAGE_PADDING) + 'px');
        modalEl.style.setProperty('--preview-h', Math.round(h + STAGE_PADDING) + 'px');
    }

    function applyZoom() {
        el.viewer.style.transform = 'scale(' + state.zoom + ')';
        el.zoomLevel.textContent = Math.round(state.zoom * 100) + '%';

        var size = slideSize();
        if (!size) return;

        // PENTING: transform scale() hanya mengubah tampilan, BUKAN kotak
        // layout. Kotak penampil karena itu diberi ukuran HASIL skala, supaya
        // tinggi/lebar yang dipesan sama persis dengan yang terlihat dan flex
        // bisa memusatkannya dengan benar.
        el.viewer.style.width = (size.width * state.zoom) + 'px';
        el.viewer.style.height = (size.height * state.zoom) + 'px';

        // Panggung hanya boleh digulir saat pengguna memperbesar melampaui
        // skala fit. Selama masih fit, seluruh slide muat sehingga scrollbar
        // tidak perlu ada sama sekali.
        el.stage.classList.toggle('is-zoomed', !state.fitted);

        if (state.fitted) {
            el.stage.scrollTop = 0;
            el.stage.scrollLeft = 0;
        }
    }

    function setZoom(value, isFit) {
        state.zoom = Math.min(Math.max(value, MIN_ZOOM), MAX_ZOOM);
        state.fitted = isFit === true;
        applyZoom();
    }

    /**
     * Skala agar seluruh slide terlihat utuh: dihitung dari min(lebar, tinggi)
     * panggung, bukan dari lebar saja.
     */
    function fitToStage() {
        layoutStage();

        var size = slideSize();
        if (!size) return;

        var byWidth = (el.stage.clientWidth - STAGE_PADDING) / size.width;
        var byHeight = (el.stage.clientHeight - STAGE_PADDING) / size.height;

        setZoom(Math.min(byWidth, byHeight), true);
    }

    /* ------------------------------------------------------------------
     | Render PPTX
     * ---------------------------------------------------------------- */

    /**
     * PPTXjs membungkus slide di dalam <div class="slides">, jadi elemen
     * .slide bukan anak langsung dari kontainer.
     */
    function collectSlides() {
        return Array.prototype.slice.call(el.viewer.querySelectorAll('div.slide'));
    }

    function finishRender(token, url) {
        // Hasil render yang sudah kedaluwarsa tidak boleh dipasang.
        if (token !== undefined && token !== state.renderToken) {
            return;
        }

        var slides = collectSlides();

        if (!slides.length) {
            fail('Slide tidak dapat dibaca dari berkas presentasi ini.');
            return;
        }

        // Baru sekarang berkas dianggap benar-benar tampil, sehingga cache
        // pada renderPptx() hanya dipakai bila slide-nya memang milik URL ini.
        state.renderedUrl = url || null;

        // Pesan bawaan library disembunyikan; indikator sendiri yang dipakai.
        var libraryMsg = el.viewer.querySelector('.slides-loadnig-msg');
        if (libraryMsg) libraryMsg.remove();

        state.slides = slides;
        state.index = 0;

        hide(el.loading);
        show(el.stage);
        show(el.toolbar);

        renderSlide();
        fitToStage();
    }

    function renderPptx(url) {
        // Berkas yang sama tidak perlu diproses ulang.
        if (state.renderedUrl === url && state.slides.length) {
            hide(el.loading);
            show(el.stage);
            show(el.toolbar);
            renderSlide();
            fitToStage();
            return;
        }

        el.viewer.innerHTML = '';
        el.viewer.style.transform = '';
        state.slides = [];

        // renderedUrl SENGAJA belum di-set di sini. Dulu nilainya ditulis di
        // titik ini - sebelum render selesai - sehingga bila render gagal atau
        // pengguna keburu membuka berkas lain, state menyimpan URL baru padahal
        // isi viewer masih slide berkas lama. Sekarang hanya diisi setelah
        // render benar-benar berhasil (lihat finishRender()).
        var token = ++state.renderToken;

        // PPTXjs menyisipkan slide satu per satu. Render dianggap selesai bila
        // tidak ada slide baru selama SETTLE_DELAY.
        state.observer = new MutationObserver(function () {
            clearTimeout(state.settleTimer);
            state.settleTimer = setTimeout(function () {
                // Permintaan sudah digantikan yang lebih baru -> abaikan.
                if (token !== state.renderToken) return;

                if (collectSlides().length) {
                    state.observer.disconnect();
                    state.observer = null;
                    clearTimeout(state.timeoutTimer);
                    finishRender(token, url);
                }
            }, SETTLE_DELAY);
        });

        state.observer.observe(el.viewer, { childList: true, subtree: true });

        state.timeoutTimer = setTimeout(function () {
            if (token !== state.renderToken) return;
            fail('Slide terlalu lama diproses. Silakan unduh berkasnya.');
        }, RENDER_TIMEOUT);

        try {
            window.jQuery('#pptxViewer').pptxToHtml({
                pptxFileUrl: url,
                slideMode: false,
                keyBoardShortCut: false,
                mediaProcess: true,
                themeProcess: true
            });
        } catch (error) {
            if (token === state.renderToken) {
                fail('Berkas presentasi tidak dapat diproses.');
            }
        }
    }

    /* ------------------------------------------------------------------
     | Pemicu
     * ---------------------------------------------------------------- */

    function openPreview(button) {
        var url = button.dataset.previewUrl;
        var kind = button.dataset.previewKind;

        resetView();

        el.title.textContent = button.dataset.previewTitle || 'Pratinjau Materi';

        // Ukuran modal menyesuaikan jenis berkas: PPT lanskap 16:9,
        // PDF tetap potret A4 seperti semula.
        modalEl.classList.toggle('is-ppt', kind === 'ppt');
        modalEl.classList.toggle('is-pdf', kind !== 'ppt');

        // Tombol Unduh SELALU menunjuk berkas asli. Ini satu-satunya aksi di
        // footer sejak "Buka di tab baru" dihapus.
        el.downloadLink.href = url;

        if (kind === 'ppt') {
            state.currentPptUrl = url;
            show(el.loading);
            modal.show();

            loadViewerLibraries()
                .then(function () { renderPptx(url); })
                .catch(function () {
                    fail('Komponen penampil slide gagal dimuat.');
                });
            return;
        }

        // PDF: cukup diserahkan ke browser.
        show(el.frame);
        el.frame.src = url;
        modal.show();
    }

    document.querySelectorAll('[data-preview-url]').forEach(function (button) {
        button.addEventListener('click', function () {
            openPreview(button);
        });
    });

    el.prev.addEventListener('click', function () { goTo(state.index - 1); });
    el.next.addEventListener('click', function () { goTo(state.index + 1); });
    el.zoomIn.addEventListener('click', function () { setZoom(state.zoom * 1.2); });
    el.zoomOut.addEventListener('click', function () { setZoom(state.zoom / 1.2); });
    el.zoomFit.addEventListener('click', fitToStage);

    // Panah kiri/kanan untuk pindah slide selama modal terbuka.
    modalEl.addEventListener('keydown', function (event) {
        if (el.toolbar.hidden) return;

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goTo(state.index - 1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            goTo(state.index + 1);
        }
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        if (!el.toolbar.hidden) fitToStage();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        // Hentikan pemuatan PDF; slide PPTX dibiarkan di memori agar
        // membuka ulang berkas yang sama terasa instan.
        el.frame.removeAttribute('src');
        resetView();
    });

    // Resize dijeda sebentar: kotak dihitung dari innerWidth/innerHeight, dan
    // menghitungnya di setiap piksel selama jendela ditarik terasa tersendat.
    var resizeTimer = null;

    window.addEventListener('resize', function () {
        if (el.toolbar.hidden) return;

        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (state.fitted) {
                fitToStage();
            } else {
                // Zoom manual dipertahankan; hanya kotaknya yang ikut jendela.
                layoutStage();
                applyZoom();
            }
        }, 120);
    });
})();
