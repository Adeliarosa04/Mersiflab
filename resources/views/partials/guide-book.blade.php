{{--
    Guide Book LMS — pemicu + modal panduan penyusunan materi.

    Fitur pelengkap (helper): tidak menyentuh alur form unggah mana pun.
    Cukup sisipkan @include('partials.guide-book') di header form atau di
    dekat tombol Simpan/Unggah.

    Aset (CSS, markup modal, JS) dibungkus @once sehingga aman walaupun
    partial ini di-include lebih dari sekali dalam satu halaman — hanya
    tombolnya yang tercetak berulang.

    Butuh Bootstrap 5, yang sudah dimuat layouts/admin maupun layouts/app.
--}}

@once
    <link rel="stylesheet" href="{{ asset('assets/css/guide-book.css') }}">
@endonce

{{-- type="button" wajib: pemicu ini kerap berada di dalam <form> --}}
<button type="button"
        class="guide-book-trigger"
        data-bs-toggle="modal"
        data-bs-target="#guideBookModal">
    {{-- Ikon buku 📘 dihapus sesuai permintaan agar header & pemicu tampil
         bersih; teksnya sudah cukup menjelaskan fungsinya. --}}
    <span>Panduan Penyusunan Materi</span>
</button>

@once
<div class="modal fade guide-book-modal"
     id="guideBookModal"
     tabindex="-1"
     aria-labelledby="guideBookModalLabel"
     aria-hidden="true">
    {{-- modal-dialog-scrollable: isi panduan bergulir sendiri di layar pendek --}}
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h2 class="guide-book-modal-title" id="guideBookModalLabel">
                        PANDUAN PENYUSUNAN MATERI LMS MERSIF LAB
                    </h2>
                    <p class="guide-book-modal-subtitle">
                        Standar materi yang perlu dipenuhi sebelum berkas diunggah.
                    </p>
                </div>

                <button type="button"
                        class="guide-book-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup panduan">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>

            <div class="modal-body">

                <section class="guide-book-section">
                    <div class="guide-book-section-head">
                        <span class="guide-book-section-icon" aria-hidden="true">🎥</span>
                        <h3 class="guide-book-section-title">1. Ketentuan Video Pembelajaran</h3>
                    </div>

                    <ul class="guide-book-list">
                        <li><strong>Durasi Ideal:</strong> 5 – 10 Menit (Micro-learning).</li>
                        <li><strong>Format:</strong> MP4 (H.264), maks. 100 MB.</li>
                        <li><strong>Jenis:</strong> Animasi, Screencast/Tutorial, Explainer, Demonstrasi, atau Talking Head.</li>
                    </ul>
                </section>

                <section class="guide-book-section">
                    <div class="guide-book-section-head">
                        <span class="guide-book-section-icon" aria-hidden="true">📚</span>
                        <h3 class="guide-book-section-title">2. Ketentuan Modul Pembelajaran (PDF)</h3>
                    </div>

                    <ul class="guide-book-list">
                        <li><strong>Halaman:</strong> 15 – 80 Halaman.</li>
                        <li><strong>Format:</strong> PDF, maks. 20 MB.</li>
                        <li><strong>Isi:</strong> Materi utama, Penugasan/Latihan, Studi Kasus.</li>
                    </ul>

                    <p class="guide-book-note">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                        <span><strong>Catatan:</strong> 1 Topik boleh lebih dari 1 modul.</span>
                    </p>
                </section>

                <section class="guide-book-section">
                    <div class="guide-book-section-head">
                        <span class="guide-book-section-icon" aria-hidden="true">📊</span>
                        <h3 class="guide-book-section-title">3. Ketentuan Slide Presentasi (PPT)</h3>
                    </div>

                    <ul class="guide-book-list">
                        <li><strong>Jumlah Slide:</strong> Maks. 30 Slide.</li>
                        <li><strong>Format:</strong> .ppt / .pptx, maks. 25 MB.</li>
                        <li><strong>Fokus:</strong> Ringkasan visual &amp; poin kunci.</li>
                    </ul>
                </section>

                {{-- Tips penyusunan course — disajikan sebagai kartu tersendiri
                     dengan grid 2 kolom (menumpuk jadi 1 kolom di layar sempit). --}}
                <section class="guide-book-section guide-book-tips">
                    <div class="guide-book-section-head">
                        <span class="guide-book-section-icon" aria-hidden="true">💡</span>
                        <h3 class="guide-book-section-title">Tips Membuat Course yang Berkualitas</h3>
                    </div>

                    <div class="guide-book-tips-grid">
                        <ul class="guide-book-list">
                            <li>Berikan nama course yang jelas dan deskriptif</li>
                            <li>Tulis deskripsi rinci untuk membantu siswa memahami isi materi</li>
                            <li>Gunakan gambar sampul berkualitas tinggi yang relevan dengan course</li>
                        </ul>

                        <ul class="guide-book-list">
                            <li>Semua course awal berstatus Draft dan memerlukan persetujuan Admin</li>
                            <li>Tambahkan bab (chapter) dan modul sebelum mengajukan persetujuan (Request Approve)</li>
                            <li>Anda dapat terus mengedit course hingga disetujui oleh Admin</li>
                        </ul>
                    </div>
                </section>

            </div>

            <div class="modal-footer">
                <p class="guide-book-footer-hint">
                    Panduan ini dapat dibuka kembali kapan saja lewat tombol
                    <strong>Panduan Penyusunan Materi</strong>.
                </p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    Saya Mengerti
                </button>
            </div>

        </div>
    </div>
</div>

<script src="{{ asset('assets/js/guide-book.js') }}"></script>
@endonce
