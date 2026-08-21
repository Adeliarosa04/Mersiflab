@extends('layouts.app')

@section('title', $freeClass->title)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/free-class.css') }}">
{{-- Gaya bawaan PPTXjs untuk elemen slide hasil render. --}}
<link rel="stylesheet" href="{{ asset('assets/vendor/pptx/pptxjs.css') }}">
@endsection

@section('content')
<div class="free-class-detail-page">
    <div class="container">

        <nav class="free-class-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('courses') }}"><i class="fas fa-home"></i> Courses</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('courses') }}#free-class">Free Course</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span>{{ Str::limit($freeClass->title, 50) }}</span>
        </nav>

        {{-- Banner biru — meniru header halaman modul course reguler
             (resources/views/module/show.blade.php): judul di kiri, progress
             bar + status di bawahnya, tombol "Detail Level" di kanan.

             Free Course tidak menyimpan progres per pengguna, jadi statusnya
             dihitung dari kelengkapan materi tiap level (video/PDF/PPT). --}}
        {{-- Progres berasal dari FreeClassController@show, dihitung per
             pengguna dari tabel free_class_level_completions — cara yang sama
             dengan course berbayar. Tamu selalu 0%. --}}
        <div class="free-course-banner">
            <div class="free-course-banner-content">
                <div class="free-course-banner-info">
                    <span class="free-course-banner-badge">
                        <i class="fas fa-unlock"></i> {{ __('Free Course') }}
                    </span>
                    <h1 class="free-course-banner-title">{{ $freeClass->title }}</h1>

                    @if($totalLevels > 0)
                        <div class="free-course-progress">
                            <div class="free-course-progress-bar">
                                <div class="free-course-progress-fill" style="width: {{ $progressPercentage }}%"></div>
                            </div>
                            <span class="free-course-progress-text">
                                {{ $progressPercentage }}% Complete
                                &middot; {{ $completedLevels }}/{{ $totalLevels }} Level
                            </span>
                        </div>

                        @guest
                            <p class="free-course-progress-hint">
                                <i class="fas fa-info-circle"></i>
                                Masuk untuk menyimpan progres belajar Anda.
                            </p>
                        @endguest
                    @endif
                </div>

                @if($totalLevels > 0)
                    <button type="button" class="btn-detail-level" id="btnDetailLevel">
                        <i class="fas fa-list me-2"></i>Detail Level
                    </button>
                @endif
            </div>
        </div>

        @if(filled($freeClass->description))
            <div class="free-class-head">
                <div class="free-class-detail-desc">
                    {!! nl2br(e($freeClass->description)) !!}
                </div>
            </div>
        @endif

        @if($levels->isEmpty())
            <div class="free-class-player-empty free-class-player-empty--standalone">
                <i class="fas fa-video-slash"></i>
                <span>Materi untuk kelas ini belum tersedia.</span>
            </div>
        @else
            <div class="row g-4 free-class-levels" data-free-class-levels>
                {{-- Navigasi level: tab vertikal di desktop, menumpuk di mobile --}}
                <div class="col-lg-4 order-lg-2">
                    <aside class="free-class-levels-nav">
                        <h2 class="free-class-levels-title">
                            <i class="fas fa-layer-group"></i>
                            Daftar Level
                            <span class="free-class-levels-count">{{ $levels->count() }}</span>
                        </h2>

                        <div class="free-class-level-list" role="tablist" aria-orientation="vertical">
                            @foreach($levels as $i => $level)
                                <button type="button"
                                        class="free-class-level-tab {{ $i === 0 ? 'is-active' : '' }}"
                                        id="level-tab-{{ $level->id }}"
                                        role="tab"
                                        aria-controls="level-panel-{{ $level->id }}"
                                        aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                                        data-level-target="level-panel-{{ $level->id }}">
                                    <span class="free-class-level-index">{{ $i + 1 }}</span>
                                    <span class="free-class-level-meta">
                                        <span class="free-class-level-name">{{ $level->name }}</span>
                                        <span class="free-class-level-tags">
                                            @if($level->hasPdf())
                                                <span class="free-class-tag"><i class="fas fa-file-pdf"></i> PDF</span>
                                            @endif
                                            @if($level->hasPpt())
                                                <span class="free-class-tag"><i class="fas fa-file-powerpoint"></i> PPT</span>
                                            @endif
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <a href="{{ back_url(route('courses') . '#free-class') }}" class="free-class-module-back">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke daftar Free Course
                        </a>
                    </aside>
                </div>

                {{-- Isi level yang sedang dipilih --}}
                <div class="col-lg-8 order-lg-1">
                    @foreach($levels as $i => $level)
                        <section class="free-class-level-panel {{ $i === 0 ? 'is-active' : '' }}"
                                 id="level-panel-{{ $level->id }}"
                                 role="tabpanel"
                                 aria-labelledby="level-tab-{{ $level->id }}"
                                 @if($i !== 0) hidden @endif>

                            <div class="free-class-player">
                                @if($level->is_embeddable)
                                    <iframe
                                        data-src="{{ $level->embed_url }}"
                                        @if($i === 0) src="{{ $level->embed_url }}" @endif
                                        title="{{ $level->name }} — {{ $freeClass->title }}"
                                        loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                                @elseif($level->video_file_url)
                                    <video controls preload="metadata" playsinline
                                           @if($freeClass->thumbnail_url) poster="{{ $freeClass->thumbnail_url }}" @endif>
                                        <source src="{{ $level->video_file_url }}">
                                        Browser Anda tidak mendukung pemutar video.
                                    </video>
                                @else
                                    <div class="free-class-player-empty">
                                        <i class="fas fa-video-slash"></i>
                                        <span>Video belum tersedia untuk level ini.</span>
                                    </div>
                                @endif
                            </div>

                            <div class="free-class-level-body">
                                @php $isLevelDone = in_array($level->id, $completedLevelIds ?? [], true); @endphp

                                <div class="free-class-level-headrow">
                                    <h2 class="free-class-level-heading">
                                        {{ $level->name }}
                                        @if($isLevelDone)
                                            <span class="free-course-level-done">
                                                <i class="fas fa-check-circle"></i> Selesai
                                            </span>
                                        @endif
                                    </h2>

                                    @auth
                                        {{-- Menandai/ membatalkan level selesai. Inilah yang
                                             menggerakkan progress bar di banner. --}}
                                        <form method="POST"
                                              action="{{ $isLevelDone
                                                    ? route('free-classes.levels.uncomplete', [$freeClass, $level])
                                                    : route('free-classes.levels.complete', [$freeClass, $level]) }}">
                                            @csrf
                                            @if($isLevelDone)
                                                @method('DELETE')
                                            @endif
                                            <button type="submit"
                                                    class="free-course-complete-btn {{ $isLevelDone ? 'is-done' : '' }}">
                                                <i class="fas {{ $isLevelDone ? 'fa-rotate-left' : 'fa-check' }}"></i>
                                                {{ $isLevelDone ? 'Batalkan' : 'Tandai Selesai' }}
                                            </button>
                                        </form>
                                    @endauth
                                </div>

                                @if($level->hasDownloads())
                                    <div class="free-class-materials">
                                        @if($level->hasPdf())
                                            @include('free-classes.partials.material', [
                                                'kind' => 'pdf',
                                                'icon' => 'fas fa-file-pdf',
                                                'iconColor' => '#d32f2f',
                                                'label' => 'Modul PDF',
                                                'fileName' => $level->pdf_display_name,
                                                'fileUrl' => $level->pdf_url,
                                                'downloadLabel' => 'Unduh PDF',
                                            ])
                                        @endif

                                        @if($level->hasPpt())
                                            @include('free-classes.partials.material', [
                                                'kind' => 'ppt',
                                                'icon' => 'fas fa-file-powerpoint',
                                                'iconColor' => '#d24726',
                                                'label' => 'Slide Presentasi',
                                                'fileName' => $level->ppt_display_name,
                                                'fileUrl' => $level->ppt_url,
                                                'downloadLabel' => 'Unduh PPT',
                                            ])
                                        @endif
                                    </div>
                                @else
                                    <p class="free-class-module-empty">
                                        <i class="fas fa-info-circle"></i>
                                        Belum ada modul PDF maupun slide PPT untuk level ini.
                                    </p>
                                @endif
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Kelas gratis lainnya --}}
        @if($otherFreeClasses->count() > 0)
            <section class="free-class-more">
                <h2 class="free-class-more-title">Free Course Lainnya</h2>

                <div class="row g-3">
                    @foreach($otherFreeClasses as $other)
                        <div class="col-lg-4 col-sm-6">
                            <a href="{{ route('free-classes.show', $other) }}" class="free-class-card">
                                <div class="free-class-thumb">
                                    @if($other->thumbnail_url)
                                        <img src="{{ $other->thumbnail_url }}" alt="{{ $other->title }}" loading="lazy">
                                    @else
                                        <div class="free-class-thumb-placeholder" aria-hidden="true">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                    @endif
                                    <span class="free-class-thumb-tag">Gratis</span>
                                </div>
                                <div class="free-class-body">
                                    <h3 class="free-class-title">{{ $other->title }}</h3>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>

@include("free-classes.partials.preview-modal")

@endsection

@section('scripts')
<script>
// Tombol "Detail Level" pada banner: menggulir ke daftar level.
// Sepadan dengan tombol "Detail Chapter" di halaman modul course reguler.
(function () {
    const btn = document.getElementById('btnDetailLevel');
    const target = document.querySelector('.free-class-levels-nav');
    if (!btn || !target) return;

    btn.addEventListener('click', function () {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
})();

(function () {
    const root = document.querySelector('[data-free-class-levels]');
    if (!root) return;

    const tabs = root.querySelectorAll('[data-level-target]');
    const panels = root.querySelectorAll('.free-class-level-panel');

    function stopMedia(panel) {
        panel.querySelectorAll('video').forEach(function (video) {
            video.pause();
        });

        // Menghentikan iframe: kosongkan src lalu pasang kembali saat dibuka.
        panel.querySelectorAll('iframe[data-src]').forEach(function (frame) {
            if (frame.getAttribute('src')) frame.removeAttribute('src');
        });
    }

    function activate(targetId) {
        panels.forEach(function (panel) {
            const isTarget = panel.id === targetId;

            panel.classList.toggle('is-active', isTarget);
            panel.hidden = !isTarget;

            if (isTarget) {
                panel.querySelectorAll('iframe[data-src]').forEach(function (frame) {
                    if (!frame.getAttribute('src')) frame.setAttribute('src', frame.dataset.src);
                });
            } else {
                stopMedia(panel);
            }
        });

        tabs.forEach(function (tab) {
            const isTarget = tab.dataset.levelTarget === targetId;
            tab.classList.toggle('is-active', isTarget);
            tab.setAttribute('aria-selected', isTarget ? 'true' : 'false');
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activate(tab.dataset.levelTarget);
        });


        // Navigasi keyboard antar tab.
        tab.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') return;

            event.preventDefault();
            const list = Array.prototype.slice.call(tabs);
            const current = list.indexOf(tab);
            const next = event.key === 'ArrowDown'
                ? (current + 1) % list.length
                : (current - 1 + list.length) % list.length;

            list[next].focus();
            activate(list[next].dataset.levelTarget);
        });
    });
})();
</script>

{{-- Pratinjau materi (PDF & PPTX). Library PPTXjs dimuat lazy dari dalam
     berkas ini saat tombol "Lihat" pada slide ditekan. --}}
<script src="{{ asset('assets/js/free-class-preview.js') }}"></script>
@endsection
