@extends('layouts.app')

@section('title', 'My Courses')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
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
                        <h2 class="profile-title">My Courses</h2>
                        <p class="profile-subtitle">Access and continue your enrolled courses</p>
                    </div>
                    
                    <!-- Course List -->
                    <div class="courses-list">
                        @if(isset($courses) && $courses->count() > 0)
                            @foreach($courses as $course)
                            {{-- id & data-course-id dipakai untuk mengembalikan
                                 siswa tepat ke kartu course ini saat kembali
                                 dari halaman detail course. --}}
                            <div class="course-card"
                                 id="course-card-{{ $course->id }}"
                                 data-course-id="{{ $course->id }}">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="course-thumbnail">
                                            @if($course->image)
                                                <img src="{{ asset('storage/' . $course->image) }}" alt="{{ $course->name }}">
                                            @else
                                                <i class="fas fa-book" style="font-size: 3rem; color: white;"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-3 mt-md-0">
                                        <h5 class="course-title">{{ $course->name ?? 'Untitled Course' }}</h5>
                                        <p class="course-meta mb-2">
                                            <i class="fas fa-chalkboard-teacher me-1"></i> 
                                            {{ $course->teacher->name ?? 'Teacher' }}
                                        </p>
                                        @if($course->description)
                                        <p class="text-muted small mb-2">{{ Str::limit($course->description, 100) }}</p>
                                        @endif
                                        @php
                                            $completedModules = $course->completed_modules ?? 0;
                                            $progress = ($completedModules > 0) ? ($course->progress ?? 0) : 0;
                                            
                                            // Check access status
                                            $hasLifetimeAccess = $course->has_lifetime_access ?? false;
                                            $hasSubscriptionAccess = $course->has_subscription_access ?? false;
                                            $canAccess = $hasLifetimeAccess || $hasSubscriptionAccess;
                                        @endphp
                                        
                                        @if($completedModules > 0)
                                        {{-- Progress tracking hanya muncul jika sudah complete minimal 1 module --}}
                                        <div class="progress-section">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="progress-label">Your Progress</span>
                                                <span class="progress-percentage">{{ number_format($progress, 1) }}%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="text-muted mt-1 d-block">
                                                <i class="fas fa-book-open me-1"></i>
                                                {{ $completedModules }} of {{ $course->modules_count ?? 0 }} modules completed
                                            </small>
                                        </div>
                                        @else
                                        {{-- Belum ada progress karena belum mark as complete module --}}
                                        <div class="progress-section">
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Belum ada progress. Selesaikan minimal 1 module untuk melihat progress Anda.
                                            </p>
                                        </div>
                                        @endif
                                        
                                        @if(!$canAccess && isset($course->enrolled_at))
                                        <div class="alert alert-warning mt-2 mb-0" style="font-size: 0.85rem; padding: 8px 12px;">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Subscription Anda sudah habis. Perpanjang subscription atau beli course ini untuk akses lifetime.
                                        </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                        <a href="{{ route('course.detail', $course->id) }}"
                                           class="btn btn-primary w-100 js-open-course"
                                           data-course-id="{{ $course->id }}">
                                            <i class="fas fa-play me-2"></i>Start Learning
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="empty-state text-center">
                                <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Courses Yet</h4>
                                <p class="text-muted">You haven't enrolled in any courses yet.</p>
                                <a href="{{ route('courses') }}" class="btn btn-primary mt-3">
                                    Browse Courses
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
/**
 * Mengingat posisi terakhir siswa di halaman My Courses.
 *
 * Saat siswa membuka detail sebuah course, posisi scroll dan course yang
 * dibuka disimpan. Ketika ia menekan "Kembali ke My Course", halaman ini
 * dikembalikan persis ke posisi semula dan kartu course-nya disorot sebentar.
 *
 * Dua sumber dipakai supaya tetap jalan di segala kondisi:
 *   1. Query parameter ?course=ID  - dibawa oleh tombol "Kembali ke My Course",
 *      tetap bekerja walau tab baru / sessionStorage kosong.
 *   2. sessionStorage              - menyimpan offset scroll persisnya.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'mersif.myCourses.lastPosition';

    function readStoredPosition() {
        try {
            var raw = sessionStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (error) {
            return null;
        }
    }

    function storePosition(courseId) {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
                courseId: courseId || null,
                scrollY: window.scrollY || window.pageYOffset || 0,
                savedAt: new Date().getTime()
            }));
        } catch (error) {
            // sessionStorage bisa diblokir (mode privat). Query parameter
            // pada tombol Kembali tetap menangani pemulihan posisi.
        }
    }

    // --- Simpan posisi sebelum meninggalkan halaman ---
    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('.js-open-course') : null;

        if (link) {
            storePosition(link.dataset.courseId);
        }
    });

    // --- Pulihkan posisi saat halaman dibuka kembali ---
    document.addEventListener('DOMContentLoaded', function () {
        var params = new URLSearchParams(window.location.search);
        var courseIdFromUrl = params.get('course');
        var stored = readStoredPosition();

        // Posisi tersimpan hanya berlaku 30 menit, supaya kunjungan lama
        // tidak tiba-tiba melompatkan halaman.
        var isFresh = stored && (new Date().getTime() - (stored.savedAt || 0)) < 30 * 60 * 1000;
        var targetCourseId = courseIdFromUrl || (isFresh ? stored.courseId : null);

        if (!targetCourseId) {
            return;
        }

        var card = document.getElementById('course-card-' + targetCourseId);

        if (!card) {
            return;
        }

        // Kalau offset scroll aslinya tersimpan, pakai itu supaya benar-benar
        // persis. Kalau tidak, cukup gulirkan ke kartunya.
        if (isFresh && typeof stored.scrollY === 'number' && stored.scrollY > 0) {
            window.scrollTo({ top: stored.scrollY, behavior: 'auto' });
        } else {
            card.scrollIntoView({ block: 'center', behavior: 'auto' });
        }

        // Sorot sebentar supaya siswa langsung tahu ia kembali ke course mana.
        card.classList.add('course-card-restored');
        window.setTimeout(function () {
            card.classList.remove('course-card-restored');
        }, 2400);

        // Bersihkan query parameter agar URL kembali rapi dan refresh
        // berikutnya tidak ikut menggulirkan halaman lagi.
        if (courseIdFromUrl && window.history.replaceState) {
            params.delete('course');
            var query = params.toString();
            window.history.replaceState(
                {},
                '',
                window.location.pathname + (query ? '?' + query : '')
            );
        }
    });
})();
</script>
@endsection