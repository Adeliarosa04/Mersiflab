{{--
    Modal pratinjau materi, dipakai bersama oleh Modul PDF dan Slide PPT.

    - PDF  : dirender browser langsung di dalam <iframe>.
    - PPTX : dirender di sisi klien oleh PPTXjs (JSZip + parser OOXML) ke
             dalam #pptxViewer. Tidak memakai layanan luar seperti Google
             Docs Viewer, sehingga tetap berjalan di localhost / jaringan
             tertutup. Library dimuat baru saat tombol "Lihat" PPT ditekan.
--}}
@php
    // Office Web Viewer hanya berguna bila berkas dapat DIUNDUH oleh server
    // Microsoft. Di localhost / IP privat / environment local itu mustahil,
    // sehingga tautannya akan menampilkan "Terjadi Kesalahan".
    //
    // Flag ini kini HANYA dipakai tombol cadangan #pptxOfficeFallback yang
    // muncul saat render lokal gagal - bukan lagi untuk tombol footer.
    $previewHost = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();

    $officeViewerUsable = ! app()->environment('local')
        && ! in_array($previewHost, ['localhost', '127.0.0.1', '::1'], true)
        && ! preg_match('/^(10\.|127\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/', (string) $previewHost)
        && ! str_ends_with((string) $previewHost, '.test')
        && ! str_ends_with((string) $previewHost, '.local');
@endphp

<div class="modal fade" id="materialPreviewModal" tabindex="-1" aria-labelledby="materialPreviewLabel" aria-hidden="true"
     data-vendor-base="{{ asset('assets/vendor/pptx') }}"
     data-office-viewer="{{ $officeViewerUsable ? '1' : '0' }}">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content free-class-preview-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="materialPreviewLabel">Pratinjau Materi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            {{-- Kontrol slide — hanya tampil untuk PPT --}}
            <div class="free-class-pptx-toolbar" id="pptxToolbar" hidden>
                <div class="free-class-pptx-nav">
                    <button type="button" class="free-class-pptx-ctrl" id="pptxPrev" aria-label="Slide sebelumnya">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="free-class-pptx-counter" id="pptxCounter" aria-live="polite">Slide 1 dari 1</span>
                    <button type="button" class="free-class-pptx-ctrl" id="pptxNext" aria-label="Slide berikutnya">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <div class="free-class-pptx-zoom">
                    <button type="button" class="free-class-pptx-ctrl" id="pptxZoomOut" aria-label="Perkecil">
                        <i class="fas fa-magnifying-glass-minus"></i>
                    </button>
                    <span class="free-class-pptx-zoom-level" id="pptxZoomLevel">100%</span>
                    <button type="button" class="free-class-pptx-ctrl" id="pptxZoomIn" aria-label="Perbesar">
                        <i class="fas fa-magnifying-glass-plus"></i>
                    </button>
                    <button type="button" class="free-class-pptx-ctrl free-class-pptx-ctrl--text" id="pptxZoomFit">
                        Sesuaikan
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <div class="free-class-preview-frame">
                    {{-- Penampil PDF --}}
                    <iframe id="materialPreviewFrame" title="Pratinjau materi" allowfullscreen hidden></iframe>

                    {{-- Penampil PPTX --}}
                    <div class="free-class-pptx-stage" id="pptxStage" hidden>
                        <div id="pptxViewer" class="free-class-pptx-viewer"></div>
                    </div>

                    {{-- Indikator proses --}}
                    <div class="free-class-preview-status" id="pptxLoading" hidden>
                        <span class="free-class-spinner" aria-hidden="true"></span>
                        <p>Memuat slide presentasi…</p>
                    </div>

                    {{-- Pesan bila gagal --}}
                    <div class="free-class-preview-status free-class-preview-status--error" id="pptxError" hidden>
                        <i class="fas fa-circle-exclamation"></i>
                        <p id="pptxErrorText">Slide tidak dapat ditampilkan.</p>
                        <p class="mb-0">Silakan unduh berkasnya untuk membukanya di perangkat Anda.</p>

                        {{-- Cadangan: buka lewat Office Web Viewer milik Microsoft.
                             Hanya berguna bila aplikasi dapat diakses dari internet,
                             karena server Microsoft harus bisa mengunduh berkasnya —
                             karena itu tombolnya baru muncul saat render lokal gagal,
                             bukan sebagai penampil utama. --}}
                        <a id="pptxOfficeFallback"
                           class="free-class-btn free-class-btn-outline mt-3"
                           target="_blank" rel="noopener" hidden>
                            <i class="fas fa-up-right-from-square"></i>
                            Coba buka dengan Office Web Viewer
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tombol "Buka di tab baru" dihapus. Satu-satunya aksi yang tersisa
                 adalah Unduh, jadi footer dirapatkan ke kanan agar tidak timpang. --}}
            <div class="modal-footer">
                <a href="#" class="free-class-btn free-class-btn-primary" id="materialPreviewDownload" download>
                    <i class="fas fa-download"></i> Unduh
                </a>
            </div>
        </div>
    </div>
</div>
