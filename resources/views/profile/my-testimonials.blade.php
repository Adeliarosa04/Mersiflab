@extends('layouts.app')

@section('title', 'My Testimonials')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
{{--
    Style khusus halaman testimoni siswa. Memakai palet MersifLab yang sudah
    ada (#1A76D1 primary, #4A9EE0 aksen, #6b7280 teks sekunder) dan struktur
    .profile-content yang sama dengan halaman profil lainnya, sehingga layout
    serta tipografinya konsisten.
--}}
<style>
    .testimonial-form-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 28px;
    }

    .testimonial-form-card .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .testimonial-form-card .form-control,
    .testimonial-form-card .form-select {
        font-size: 14px;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }

    .testimonial-form-card .form-control:focus,
    .testimonial-form-card .form-select:focus {
        border-color: #1A76D1;
        box-shadow: 0 0 0 3px rgba(26, 118, 209, 0.12);
        outline: none;
    }

    /* --- Rating bintang 1-5 --- */
    .rating-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 6px;
    }

    .rating-input input[type="radio"] {
        display: none;
    }

    .rating-input label {
        font-size: 28px;
        color: #d1d5db;
        cursor: pointer;
        transition: color 0.15s ease, transform 0.15s ease;
        margin: 0;
    }

    /* Bintang terpilih dan semua bintang sebelumnya ikut menyala.
       Urutan input dibalik (row-reverse) supaya selector ~ bekerja. */
    .rating-input input[type="radio"]:checked ~ label,
    .rating-input:hover label {
        color: #f5a623;
    }

    .rating-input label:hover ~ label {
        color: #f5a623;
    }

    .rating-input label:hover {
        color: #f5a623;
        transform: scale(1.1);
    }

    .rating-hint {
        font-size: 13px;
        color: #6b7280;
        margin-top: 6px;
    }

    /* --- Daftar testimoni milik siswa --- */
    .my-testimonial-item {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
    }

    .my-testimonial-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .my-testimonial-stars {
        color: #f5a623;
        font-size: 15px;
        letter-spacing: 1px;
    }

    .my-testimonial-text {
        font-size: 14px;
        line-height: 1.7;
        color: #374151;
        margin-bottom: 10px;
    }

    .my-testimonial-meta {
        font-size: 12.5px;
        color: #6b7280;
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: center;
    }

    .testimonial-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .testimonial-status-pending {
        background: #fff3e0;
        color: #f57c00;
    }

    .testimonial-status-approved {
        background: #e8f5e8;
        color: #2e7d32;
    }

    .testimonial-status-rejected {
        background: #ffebee;
        color: #c62828;
    }

    .testimonial-reject-note {
        margin-top: 10px;
        background: #fdecee;
        border-left: 3px solid #c62828;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
        color: #842029;
        line-height: 1.6;
    }

    .btn-submit-testimonial {
        background: linear-gradient(135deg, #1A76D1 0%, #4A9EE0 100%);
        color: #ffffff;
        border: none;
        padding: 12px 26px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-submit-testimonial:hover {
        box-shadow: 0 4px 14px rgba(26, 118, 209, 0.28);
        transform: translateY(-1px);
        color: #ffffff;
    }

    .btn-submit-testimonial:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .testimonial-empty {
        text-align: center;
        padding: 36px 20px;
        color: #6b7280;
    }

    .testimonial-empty i {
        font-size: 42px;
        color: #e0e0e0;
        margin-bottom: 12px;
        display: block;
    }
</style>
@endsection

@section('content')
<section class="profile-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                @include('profile.partials.sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-content">
                    <div class="profile-header">
                        <h2 class="profile-title">My Testimonials</h2>
                        <p class="profile-subtitle">Bagikan pengalaman belajarmu di MersifLab. Testimoni akan tampil di halaman utama setelah disetujui admin.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul style="margin: 0; padding-left: 18px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- ============ FORM PENGISIAN TESTIMONI ============ -->
                    <div class="testimonial-form-card">
                        <h5 style="font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 18px;">
                            <i class="fas fa-pen-to-square me-2" style="color: #1A76D1;"></i>Tulis Testimoni
                        </h5>

                        @if($pendingCount >= $maxPending)
                            <div class="alert alert-warning" style="margin-bottom: 0;">
                                <i class="fas fa-clock me-2"></i>
                                Anda punya {{ $pendingCount }} testimoni yang masih menunggu peninjauan admin.
                                Tunggu hingga ditinjau sebelum mengirim testimoni baru.
                            </div>
                        @else
                            <form action="{{ route('my-testimonials.store') }}" method="POST" id="testimonialForm">
                                @csrf

                                <!-- Rating bintang -->
                                <div class="mb-4">
                                    <label class="form-label d-block">Rating <span style="color: #c62828;">*</span></label>
                                    <div class="rating-input">
                                        @for($star = 5; $star >= 1; $star--)
                                            <input type="radio"
                                                   id="rating-{{ $star }}"
                                                   name="rating"
                                                   value="{{ $star }}"
                                                   {{ (int) old('rating') === $star ? 'checked' : '' }}>
                                            <label for="rating-{{ $star }}" title="{{ $star }} bintang">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        @endfor
                                    </div>
                                    <p class="rating-hint" id="ratingHint">Pilih 1 sampai 5 bintang.</p>
                                </div>

                                <!-- Pilihan kursus (opsional) -->
                                <div class="mb-4">
                                    <label class="form-label" for="course_id">Kursus yang diulas (opsional)</label>
                                    <select name="course_id" id="course_id" class="form-select">
                                        <option value="">— Testimoni umum tentang MersifLab —</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ (int) old('course_id') === $course->id ? 'selected' : '' }}>
                                                {{ $course->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($courses->isEmpty())
                                        <p class="rating-hint">Anda belum mengikuti kursus apa pun, jadi testimoni akan dikirim sebagai testimoni umum.</p>
                                    @endif
                                </div>

                                <!-- Isi testimoni -->
                                <div class="mb-4">
                                    <label class="form-label" for="content">Testimoni / Ulasan <span style="color: #c62828;">*</span></label>
                                    <textarea name="content"
                                              id="content"
                                              class="form-control"
                                              rows="5"
                                              maxlength="2000"
                                              placeholder="Ceritakan pengalaman belajarmu di MersifLab..."
                                              required>{{ old('content') }}</textarea>
                                    <p class="rating-hint">
                                        Minimal 20 karakter. <span id="charCount">0</span>/2000
                                    </p>
                                </div>

                                <button type="submit" class="btn-submit-testimonial">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Testimoni
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- ============ DAFTAR TESTIMONI SAYA ============ -->
                    <h5 style="font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 16px;">
                        Testimoni Saya ({{ $testimonials->count() }})
                    </h5>

                    @forelse($testimonials as $testimonial)
                        <div class="my-testimonial-item">
                            <div class="my-testimonial-head">
                                <div class="my-testimonial-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa{{ $i <= ($testimonial->rating ?? 0) ? 's' : 'r' }} fa-star"></i>
                                    @endfor
                                </div>

                                <span class="testimonial-status testimonial-status-{{ $testimonial->status }}">
                                    @if($testimonial->isApproved())
                                        <i class="fas fa-check-circle"></i> Dipublikasikan
                                    @elseif($testimonial->isRejected())
                                        <i class="fas fa-times-circle"></i> Ditolak
                                    @else
                                        <i class="fas fa-clock"></i> Menunggu Peninjauan
                                    @endif
                                </span>
                            </div>

                            <p class="my-testimonial-text">{{ $testimonial->content }}</p>

                            <div class="my-testimonial-meta">
                                <span><i class="far fa-calendar me-1"></i>{{ $testimonial->created_at->format('d M Y') }}</span>
                                @if($testimonial->course)
                                    <span><i class="fas fa-book me-1"></i>{{ $testimonial->course->name }}</span>
                                @endif

                                @if(!$testimonial->isApproved())
                                    <form action="{{ route('my-testimonials.destroy', $testimonial->id) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Hapus testimoni ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                style="background: none; border: none; color: #c62828; font-size: 12.5px; padding: 0; cursor: pointer;">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>

                            @if($testimonial->isRejected() && $testimonial->rejection_reason)
                                <div class="testimonial-reject-note">
                                    <strong>Catatan admin:</strong> {{ $testimonial->rejection_reason }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="testimonial-empty">
                            <i class="fas fa-quote-left"></i>
                            <span>Anda belum pernah mengirim testimoni.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Penghitung karakter untuk isi testimoni.
        const content = document.getElementById('content');
        const charCount = document.getElementById('charCount');

        if (content && charCount) {
            const updateCount = () => charCount.textContent = content.value.length;
            updateCount();
            content.addEventListener('input', updateCount);
        }

        // Keterangan rating yang dipilih.
        const hint = document.getElementById('ratingHint');
        const labels = {
            1: 'Sangat kurang',
            2: 'Kurang',
            3: 'Cukup',
            4: 'Bagus',
            5: 'Sangat bagus'
        };

        document.querySelectorAll('.rating-input input[type="radio"]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (hint) {
                    hint.textContent = this.value + ' bintang — ' + (labels[this.value] || '');
                }
            });

            if (input.checked && hint) {
                hint.textContent = input.value + ' bintang — ' + (labels[input.value] || '');
            }
        });

        // Cegah pengiriman ganda.
        const form = document.getElementById('testimonialForm');

        if (form) {
            form.addEventListener('submit', function () {
                const button = form.querySelector('button[type="submit"]');

                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
                }
            });
        }
    });
</script>
@endsection
