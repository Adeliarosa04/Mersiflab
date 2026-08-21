@extends('layouts.app')

@section('title', 'Home')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/welcome.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/home-modern.css') }}">
{{-- Scroll-reveal is opt-in: without JS the .ml-js flag is never set and every
     section renders fully visible instead of staying stuck at opacity 0. --}}
<script>document.documentElement.classList.add('ml-js');</script>
<style>
/* Course Category Badge */
.course-category-badge {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    color: #1565c0;
    font-size: 11px;
    padding: 5px 11px;
    border-radius: 10px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(21, 101, 192, 0.2);
    border: 1.5px solid rgba(21, 101, 192, 0.15);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
}

.course-card:hover .course-category-badge {
    background: linear-gradient(135deg, #bbdefb 0%, #90caf9 100%);
    box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3);
    transform: scale(1.05);
}

/* Course Tier Badge - Standard */
.course-tier-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    padding: 5px 11px;
    border-radius: 10px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border: 1.5px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
}

.course-tier-badge i {
    font-size: 10px;
}

/* Standard Tier - Green */
.course-tier-standard {
    background: linear-gradient(135deg, #a5d6a7 0%, #81c784 100%);
    color: #1b5e20;
    border-color: rgba(27, 94, 32, 0.2);
}

.course-card:hover .course-tier-standard {
    background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%);
    box-shadow: 0 4px 12px rgba(27, 94, 32, 0.3);
    transform: scale(1.05);
}

/* Premium Tier - Purple */
.course-tier-premium {
    background: linear-gradient(135deg, #ce93d8 0%, #ba68c8 100%);
    color: #4a148c;
    border-color: rgba(74, 20, 140, 0.2);
}

.course-card:hover .course-tier-premium {
    background: linear-gradient(135deg, #ba68c8 0%, #ab47bc 100%);
    box-shadow: 0 4px 12px rgba(74, 20, 140, 0.3);
    transform: scale(1.05);
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .course-category-badge,
    .course-tier-badge {
        font-size: 10px;
        padding: 4px 9px;
    }
    
    .course-tier-badge i {
        font-size: 9px;
    }
}
</style>
@endsection

@section('content')

<div class="ml-home">

<!-- Continue Learning Section (Only for Authenticated Users, but not for Teachers) -->
@auth
@if(!Auth::user()->isTeacher())
<section class="py-5 continue-learning-section">
    <div class="container">
        <!-- Welcome Message -->
        <div class="welcome-banner">
            <div class="d-flex align-items-center gap-3">
                <div class="welcome-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <h5 class="mb-0">{{ __('Welcome,') }} <strong>{{ Auth::user()->name }}</strong>!</h5>
            </div>
        </div>

        <!-- Continue Learning Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="continue-learning-title mb-0">{{ __('Continue Learning') }}</h2>
            <a href="{{ route('my-courses') }}" class="view-all-btn">
                {{ __('View All') }} →
            </a>
        </div>

        <!-- Learning Progress Cards -->
        <div class="row g-4">
            @if(isset($enrolledCourses) && $enrolledCourses->count() > 0)
                @foreach($enrolledCourses->take(3) as $course)
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('course.detail', $course->id) }}" class="text-decoration-none">
                        <div class="learning-card">
                            <!-- Course Image -->
                            <div class="learning-image">
                                @if($course->image)
                                    <img src="{{ asset('storage/' . $course->image) }}" alt="{{ $course->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="learning-placeholder">
                                        <i class="fas fa-book-reader fa-2x"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Course Info -->
                            <div class="learning-content">
                                <h6 class="learning-title">{{ $course->name ?? 'Untitled Course' }}</h6>
                                <p class="learning-instructor">
                                    {{ ($course->teacher?->name) ?? "Teacher" }}
                                </p>

                                <!-- Progress Bar -->
                                <div class="learning-progress">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="progress-label">{{ __('Progress') }}</span>
                                        <span class="progress-percentage">{{ number_format($course->progress ?? 0, 1) }}%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" 
                                             role="progressbar" 
                                             style="width: {{ $course->progress ?? 0 }}%"
                                             aria-valuenow="{{ $course->progress ?? 0 }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <p class="progress-status mt-2">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $course->completed_modules ?? 0 }} of {{ $course->modules_count ?? 0 }} {{ __('lessons completed') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            @else
                <!-- Empty State -->
                <div class="col-12">
                    <div class="empty-learning-state">
                        <i class="fas fa-graduation-cap fa-4x mb-3"></i>
                        <h5>{{ __('Start Your Learning Journey') }}</h5>
                        <p class="text-muted mb-4">{{ __("You haven't enrolled in any courses yet. Explore our courses and start learning today!") }}</p>
                        <a href="{{ route('courses') }}" class="btn btn-primary">
                            {{ __('Browse Courses') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

<!-- Welcome Banner for Teachers -->
@if(Auth::check() && Auth::user()->isTeacher())
<!-- My Courses Section for Teachers -->
<section class="py-5 teacher-courses-section">
    <div class="container">
        <!-- Welcome Message -->
        <div class="welcome-banner">
            <div class="d-flex align-items-center gap-3">
                <div class="welcome-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <h5 class="mb-0">{{ __('Welcome,') }} <strong>{{ Auth::user()->name }}</strong>!</h5>
            </div>
        </div>

        <!-- My Courses Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="continue-learning-title mb-0">{{ __('My Courses') }}</h2>
            <a href="{{ route('teacher.courses') }}" class="view-all-btn">
                {{ __('View All') }} →
            </a>
        </div>

        <!-- My Courses Cards -->
        <div class="row g-4">
            @if(isset($teacherCourses) && $teacherCourses->count() > 0)
                @foreach($teacherCourses as $course)
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('teacher.course.detail', $course->id) }}" class="text-decoration-none">
                        <div class="learning-card">
                            <!-- Course Image -->
                            <div class="learning-image" style="position: relative;">
                                @if($course->image)
                                    <img src="{{ asset('storage/' . $course->image) }}" alt="{{ $course->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div class="learning-placeholder">
                                        <i class="fas fa-book fa-2x"></i>
                                    </div>
                                @endif
                                <!-- Status Badge (top-right) -->
                                @php
                                    $statusLabel = $course->status_label ?? ['text' => 'Unknown', 'color' => 'secondary'];
                                    $badgeClass = 'bg-' . $statusLabel['color'];
                                @endphp
                                <div style="position: absolute; top: 12px; right: 12px; z-index: 3;">
                                    <span class="badge {{ $badgeClass }} text-white" style="font-size: 0.75rem; font-weight: 600; padding: 6px 10px; border-radius: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                        {{ $statusLabel['text'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Course Info -->
                            <div class="learning-content">
                                <h6 class="learning-title">{{ $course->name ?? 'Untitled Course' }}</h6>
                                <p class="learning-instructor">
                                    {{ $course->chapters_count ?? 0 }} {{ __('Chapters') }}
                                </p>

                                <!-- Course Stats -->
                                <div class="learning-progress">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="progress-label">{{ __('Modules') }}</span>
                                        <span class="progress-percentage">{{ $course->modules_count ?? 0 }}</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" 
                                             role="progressbar" 
                                             style="width: 100%; background-color: #28a745;"
                                             aria-valuenow="100" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <p class="progress-status mt-2">
                                        <i class="far fa-calendar me-1"></i>
                                        Created {{ $course->created_at ? $course->created_at->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            @else
                <!-- Empty State -->
                <div class="col-12">
                    <div class="empty-learning-state">
                        <i class="fas fa-plus-circle fa-4x mb-3"></i>
                        <h5>{{ __('Start Creating Courses') }}</h5>
                        <p class="text-muted mb-4">{{ __("You haven't created any courses yet. Start creating your first course today!") }}</p>
                        <a href="{{ route('teacher.classes.create') }}" class="btn btn-primary">
                            {{ __('Create Course') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endif
@endauth

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="ml-hero" aria-labelledby="mlHeroTitle">
    <div class="container">
        <div class="ml-hero-grid">

            <!-- Copy -->
            <div class="ml-hero-copy ml-reveal">
                <span class="ml-hero-badge">
                    <span class="ml-dot" aria-hidden="true"></span>
                    {{ __('Powered by AI & Immersive Technology') }}
                </span>

                <h1 class="ml-hero-title" id="mlHeroTitle">
                    {{ __('Experience the Future of') }} <span class="ml-accent">{{ __('Learning') }}</span>
                </h1>

                <p class="ml-hero-sub">
                    {{ __('Discover innovative learning experiences powered by technology and immersive education — built for learners, educators, and schools ready to grow with the future.') }}
                </p>

                <!-- Site-wide search — suggests courses, pages, categories and
                     instructors, and goes straight to whatever you pick. -->
                <form class="ml-hero-search"
                      id="mlCourseSearchForm"
                      method="GET"
                      action="{{ route('search') }}"
                      data-suggest-url="{{ route('search.suggestions') }}"
                      role="search"
                      aria-label="Search MersifLab">
                    <div class="ml-search-field">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <label class="visually-hidden" for="mlCourseSearchInput">
                            {{ __('What do you want to learn?') }}
                        </label>
                        <input type="search"
                               class="ml-search-input"
                               id="mlCourseSearchInput"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="{{ __('Search courses, pages, topics, or instructors...') }}"
                               autocomplete="off"
                               role="combobox"
                               aria-expanded="false"
                               aria-controls="mlSearchSuggestions"
                               aria-autocomplete="list">
                        <span class="ml-search-spinner" aria-hidden="true"></span>

                        <ul class="ml-suggest"
                            id="mlSearchSuggestions"
                            role="listbox"
                            aria-label="Course suggestions"
                            hidden></ul>
                    </div>
                    <button type="submit" class="ml-btn ml-btn-primary ml-search-submit">
                        <i class="fas fa-magnifying-glass ml-search-submit-icon" aria-hidden="true"></i>
                        <span>{{ __('Search') }}</span>
                    </button>
                </form>
                <p class="visually-hidden" id="mlSearchStatus" role="status" aria-live="polite"></p>

                <div class="ml-hero-actions">
                    <a href="#course-preview" class="ml-btn ml-btn-onblue">
                        {{ __('Explore Courses') }}
                        <i class="fas fa-arrow-right ml-btn-arrow" aria-hidden="true"></i>
                    </a>
                    <a href="{{ url('/about') }}" class="ml-btn ml-btn-onblue-ghost">
                        {{ __('Learn More') }}
                    </a>
                </div>

                {{-- Qualitative on purpose: the statistics section below is the
                     single source of truth for numbers, so nothing here can
                     contradict it. --}}
                <div class="ml-hero-proof">
                    <span class="ml-hero-proof-item">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        {{ __('A growing community of learners') }}
                    </span>
                    <span class="ml-hero-proof-item">
                        <i class="fas fa-certificate" aria-hidden="true"></i>
                        {{ __('Hands-on materials & certificates') }}
                    </span>
                    <span class="ml-hero-proof-item">
                        <i class="fas fa-star" aria-hidden="true"></i>
                        {{ __('Trusted with high ratings') }}
                    </span>
                </div>
            </div>

            <!-- Visual -->
            <div class="ml-hero-visual ml-reveal" data-reveal-delay="120">
                <div class="ml-hero-frame">
                    <img src="{{ asset('assets/img/hero.png') }}"
                         alt="Students learning with MersifLab's immersive technology platform"
                         width="720" height="480" fetchpriority="high">
                </div>

                <div class="ml-hero-chip ml-hero-chip--tl" aria-hidden="true">
                    <span class="ml-hero-chip-icon"><i class="fas fa-cube"></i></span>
                    <span>
                        <span class="ml-hero-chip-value">{{ __('Immersive Labs') }}</span>
                        <span class="ml-hero-chip-label d-block">{{ __('AR & VR ready') }}</span>
                    </span>
                </div>

                <div class="ml-hero-chip ml-hero-chip--br" aria-hidden="true">
                    <span class="ml-hero-chip-icon"><i class="fas fa-award"></i></span>
                    <span>
                        <span class="ml-hero-chip-value">{{ __('Verified Certificate') }}</span>
                        <span class="ml-hero-chip-label d-block">{{ __('On course completion') }}</span>
                    </span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     STATISTICS
     ============================================================ -->
<section class="ml-section ml-stats-section" aria-labelledby="mlStatsTitle">
    <div class="container">
        <div class="ml-section-head is-centered ml-reveal">
            <span class="ml-eyebrow">{{ __('MersifLab by the numbers') }}</span>
            <h2 class="ml-section-title" id="mlStatsTitle">{{ __('Learn Today, Grow for Tomorrow') }}</h2>
            <p class="ml-section-subtitle">
                {{ __('A growing ecosystem of courses, learners, and education partners building practical skills together.') }}
            </p>
        </div>

        <div class="ml-stats-grid">
            @foreach($homeStats as $index => $stat)
            <div class="ml-stat-card ml-reveal" data-reveal-delay="{{ $index * 80 }}">
                <div class="ml-stat-icon" aria-hidden="true">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <p class="ml-stat-value">
                    <span data-count="{{ $stat['value'] }}" data-decimals="{{ $stat['decimals'] }}">0</span>
                    @if(!empty($stat['suffix']))
                        <span class="ml-stat-suffix">{{ $stat['suffix'] }}</span>
                    @endif
                </p>
                <p class="ml-stat-label">{{ __($stat['label']) }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ============================================================
     COURSE PREVIEW
     ============================================================ -->
<section id="course-preview" class="ml-section ml-preview-section" aria-labelledby="mlPreviewTitle">
    <div class="container">
        <div class="ml-preview-head ml-reveal">
            <div class="ml-section-head">
                <span class="ml-eyebrow">{{ __('Course preview') }}</span>
                <h2 class="ml-section-title" id="mlPreviewTitle">{{ __('Take a look inside our courses') }}</h2>
                <p class="ml-section-subtitle">
                    {{ __('A quick look at what you will learn and who teaches it — open a course for the full details.') }}
                </p>
            </div>
            <div class="ml-preview-actions">
                {{-- Tautan ke seksi Free Course, hanya bila datanya ada. --}}
                @if(!empty($hasFreeClasses))
                    <a href="{{ route('courses') }}#free-class" class="ml-btn ml-btn-outline">
                        {{ __('Free Course') }}
                        <i class="fas fa-arrow-right ml-btn-arrow" aria-hidden="true"></i>
                    </a>
                @endif
                <a href="{{ route('courses') }}#all-courses" class="ml-btn ml-btn-outline">
                    {{ __('Browse All Courses') }}
                    <i class="fas fa-arrow-right ml-btn-arrow" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <!-- Category filter -->
        <div class="course-category-wrapper ml-reveal">
            <label class="course-category-label" for="courseCategorySelect">
                <i class="fas fa-filter"></i>
                {{ __('Filter by Category') }}
            </label>
            <div class="course-category-select-wrapper">
                <select class="course-category-select" id="courseCategorySelect">
                    @foreach($previewCategories as $index => $category)
                    <option value="{{ $category['slug'] }}" {{ $index === 0 ? 'selected' : '' }}>
                        {{ $category['name'] }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="tab-content" id="coursePreviewTabContent">
            @foreach($previewCategories as $categoryIndex => $category)
            <div class="tab-pane fade {{ $categoryIndex === 0 ? 'show active' : '' }}"
                 id="{{ $category['slug'] }}"
                 role="tabpanel"
                 aria-label="{{ $category['name'] }} courses">

                <div class="ml-preview-grid">
                    @forelse($category['courses'] as $index => $preview)
                    <article class="ml-preview-card ml-reveal" data-reveal-delay="{{ $index * 80 }}">
                        <div class="ml-preview-thumb {{ $preview['image'] ? 'is-loading' : '' }}">
                            @if($preview['image'])
                                <img src="{{ $preview['image'] }}"
                                     alt="{{ $preview['title'] }}"
                                     loading="lazy">
                            @else
                                <div class="ml-preview-thumb-fallback" aria-hidden="true">
                                    <i class="fas {{ $category['icon'] }}"></i>
                                </div>
                            @endif
                            <span class="ml-preview-level">{{ $preview['level'] }}</span>
                        </div>

                        <div class="ml-preview-body">
                            <h3 class="ml-preview-title">{{ $preview['title'] }}</h3>

                            <p class="ml-preview-instructor">
                                <span class="ml-preview-avatar">
                                    @if($preview['instructor_avatar'])
                                        <img src="{{ $preview['instructor_avatar'] }}" alt="" loading="lazy">
                                    @else
                                        {{ strtoupper(substr($preview['instructor'], 0, 1)) }}
                                    @endif
                                </span>
                                {{ $preview['instructor'] }}
                            </p>

                            <p class="ml-preview-desc">{{ $preview['description'] }}</p>

                            <div class="ml-preview-foot">
                                <a href="{{ $preview['url'] }}"
                                   class="ml-btn ml-btn-primary ml-preview-cta"
                                   aria-label="View course: {{ $preview['title'] }}">
                                    {{ __('View Course') }}
                                    <i class="fas fa-arrow-right ml-btn-arrow" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                    @empty
                    <div class="ml-preview-empty">
                        <i class="fas {{ $category['icon'] }} d-block" aria-hidden="true"></i>
                        <p>{{ __('No courses yet for :category. Please check back soon.', ['category' => $category['name']]) }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Partnership Section -->
<section id="trust-section" class="py-5 partnership-section">
    <div class="container">
        <p class="text-center text-muted mb-4">{{ __('Trusted by over 10 schools and millions of learners') }}</p>
        
        <div class="partners-wrapper">
            <div class="partners-marquee">
                <div class="partner-logo">
                    <img src="{{ asset('images/partners/smkn2solo.png') }}" alt="SMK Negeri 2 Surakarta">
                </div>
                <div class="partner-logo">
                    <img src="{{ asset('images/partners/smkn5solo.png') }}" alt="SMK Negeri 5 Surakarta">
                </div>
                <div class="partner-logo">
                    <img src="{{ asset('images/partners/smkn1kra.png') }}" alt="SMK Negeri 1 Karanganyar">
                </div>
                <div class="partner-logo">
                    <img src="{{ asset('images/partners/smkn2klt.png') }}" alt="SMK Negeri 2 Klaten">
                </div>
                <div class="partner-logo">
                    <img src="{{ asset('images/partners/smkn4skh.png') }}" alt="SMK Negeri 4 Sukoharjo">
                </div>
                <!-- Duplicate for seamless loop -->
                <div class="partner-logo">
                    <img src="{{ asset('images/partners/smkn2solo.png') }}" alt="SMK Negeri 2 Surakarta">
                </div>
                <div class="partner-logo">
                    <img src="{{ asset('images/partners/smkn5solo.png') }}" alt="SMK Negeri 5 Surakarta">
                </div>
                <div class="partner-logo">
                    <img src="{{ asset('images/partners/smkn1kra.png') }}" alt="SMK Negeri 1 Karanganyar">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 testimonial-section">
    <div class="container">
        <h2 class="testimonial-heading text-center mb-5">
            {{ __('Join others transforming their lives through learning') }}
        </h2>

        <div class="row g-4">
            @if(isset($testimonials) && $testimonials->isNotEmpty())
                @foreach($testimonials as $t)
                    <div class="col-lg-4 col-md-6">
                        <div class="testimonial-card">
                            <div class="quote-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>

                            {{-- Rating bintang dari siswa (opsional: testimoni
                                 lama buatan admin tidak punya rating). --}}
                            @if($t->rating)
                                <div class="testimonial-rating" style="color: #f5a623; font-size: 14px; letter-spacing: 1px; margin-bottom: 10px;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa{{ $i <= $t->rating ? 's' : 'r' }} fa-star"></i>
                                    @endfor
                                </div>
                            @endif

                            <p class="testimonial-text">{{ $t->content }}</p>

                            <div class="testimonial-author">
                                <img src="{{ $t->avatar ? asset('storage/' . $t->avatar) : $t->avatarUrl() }}"
                                     alt="{{ $t->name }}" 
                                     class="author-avatar"
                                     onerror="this.src='{{ $t->avatarUrl() }}'">
                                <div class="author-info">
                                    <h6 class="author-name">{{ $t->name }}</h6>
                                    @if($t->position)
                                        <p class="author-position">{{ $t->position }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- fallback static testimonials -->
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="quote-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        
                        <p class="testimonial-text">
                            Course ini cukup membantu menambah wawasan, meskipun beberapa bagian bisa dijelaskan lebih detail.
                        </p>
                        
                        <div class="testimonial-author">
                            <img src="{{ asset('images/avatar/user1.jpg') }}" 
                                 alt="Tubagus Mukti" 
                                 class="author-avatar"
                                 onerror="this.src='https://ui-avatars.com/api/?name=Tubagus+Mukti&background=667eea&color=fff'">
                            <div class="author-info">
                                <h6 class="author-name">Tubagus Mukti</h6>
                                <p class="author-position">{{ __('Technical Co-Founder, CTO at Drivensiional') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="quote-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        
                        <p class="testimonial-text">
                            Penyampaian materi runtut dan tidak membosankan, sehingga nyaman diikuti sampai selesai.
                        </p>
                        
                        <div class="testimonial-author">
                            <img src="{{ asset('images/avatar/user2.jpg') }}" 
                                 alt="Rara Rawra" 
                                 class="author-avatar"
                                 onerror="this.src='https://ui-avatars.com/api/?name=Rara+Rawra&background=f093fb&color=fff'">
                            <div class="author-info">
                                <h6 class="author-name">Rara Rawra</h6>
                                <p class="author-position">{{ __('Product Account Manager at Amazon Web Service') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="quote-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        
                        <p class="testimonial-text">
                            Kontennya informatif dan terstruktur, walaupun durasi beberapa materi terasa agak panjang.
                        </p>
                        
                        <div class="testimonial-author">
                            <img src="{{ asset('images/avatar/user3.jpg') }}" 
                                 alt="Hamadafah Syahrani" 
                                 class="author-avatar"
                                 onerror="this.src='https://ui-avatars.com/api/?name=Hamadafah+Syahrani&background=4facfe&color=fff'">
                            <div class="author-info">
                                <h6 class="author-name">Hamadafah Syahrani</h6>
                                <p class="author-position">{{ __('Head of Capability Development, North America at Publicis Sapient') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Trending Courses Section -->
<section class="py-5 trending-section">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="trending-title mb-0">{{ __('Trending courses') }}</h2>
            <div class="trending-nav">
                <button class="trending-nav-btn prev" id="trendingPrev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="trending-nav-btn next" id="trendingNext">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="trending-carousel-wrapper">
            <div class="trending-carousel" id="trendingCarousel">
                <!-- Course 1 -->
                @if(isset($trendingCourses) && $trendingCourses->count() > 0)
                    @foreach($trendingCourses as $course)
                    <div class="trending-card">
                        <a href="{{ route('course.detail', $course->id) }}" class="text-decoration-none">
                            <div class="trending-image">
                                @if($course->image)
                                    <img src="{{ asset('storage/' . $course->image) }}" alt="{{ $course->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop" alt="{{ $course->name }}">
                                @endif
                                <!-- Tier Badge (top-right) -->
                                @php $tier = $course->price_tier ?? null; @endphp
                                @if($tier)
                                <div style="position: absolute; top: 12px; right: 12px; z-index: 3;">
                                    <span class="course-tier-badge course-tier-{{ $tier }}">
                                        @if($tier === 'standard')
                                            <i class="fas fa-star"></i> {{ ucfirst($tier) }}
                                        @else
                                            <i class="fas fa-crown"></i> {{ ucfirst($tier) }}
                                        @endif
                                    </span>
                                </div>
                                @endif
                            </div>
                            
                            <div class="trending-content">
                                <h6 class="trending-course-title">{{ $course->name }}</h6>
                                <p class="trending-instructor">
                                    <span class="trending-instructor-avatar">
                                        @if($course->teacher && !empty($course->teacher->avatar))
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($course->teacher->avatar) }}" alt="{{ $course->teacher->name ?? 'Teacher' }}">
                                        @elseif($course->teacher && $course->teacher->name)
                                            {{ strtoupper(substr($course->teacher->name, 0, 1)) }}
                                        @else
                                            T
                                        @endif
                                    </span>
                                    <span class="trending-instructor-name">{{ $course->teacher->name ?? "Teacher" }}</span>
                                </p>
                                
                                <div class="trending-meta">
                                    @php
                                        $avgRating = $course->average_rating ?? 0;
                                        $reviewsCount = $course->reviews_count ?? 0;
                                    @endphp
                                    @if($reviewsCount > 0)
                                    <div class="trending-rating">
                                        <span class="rating-score">{{ number_format($avgRating, 1) }}</span>
                                        <div class="stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($avgRating))
                                                    <i class="fas fa-star"></i>
                                                @elseif($i - 0.5 <= $avgRating)
                                                    <i class="fas fa-star-half-alt"></i>
                                                @else
                                                    <i class="far fa-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <span class="rating-count">({{ $reviewsCount }})</span>
                                    </div>
                                    @else
                                    <div class="trending-rating">
                                        <span class="rating-score text-muted">-</span>
                                        <div class="stars">
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                        <span class="rating-count text-muted">(0)</span>
                                    </div>
                                    @endif
                                    <div class="trending-duration">
                                        <i class="far fa-clock"></i>
                                        <span>{{ $course->modules_count ?? 0 }} {{ __('modules') }}</span>
                                    </div>
                                </div>

                                <div class="trending-price">
                                    @php 
                                        $trendingPrice = $course->discounted_price ?? $course->price ?? 0;
                                        $now = \Carbon\Carbon::now();
                                        $isDiscountActive = $course->has_discount && $course->discount && 
                                                          (!$course->discount_starts_at || $now->greaterThanOrEqualTo($course->discount_starts_at)) && 
                                                          (!$course->discount_ends_at || $now->lessThanOrEqualTo($course->discount_ends_at));
                                    @endphp
                                    @if($isDiscountActive)
                                        <span class="text-muted text-decoration-line-through" style="font-size: 0.9rem; font-weight: 500;">Rp{{ number_format($course->price ?? 0, 0, ',', '.') }}</span>
                                        Rp{{ number_format($trendingPrice, 0, ',', '.') }}
                                    @else
                                        Rp{{ number_format($course->price ?? 0, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                @else
                    <!-- Empty State -->
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">{{ __('No trending courses yet') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Carousel JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('trendingCarousel');
    const prevBtn = document.getElementById('trendingPrev');
    const nextBtn = document.getElementById('trendingNext');
    
    if (carousel && prevBtn && nextBtn) {
        const cardWidth = carousel.querySelector('.trending-card').offsetWidth;
        const gap = 24; // 1.5rem = 24px
        const scrollAmount = cardWidth + gap;
        
        prevBtn.addEventListener('click', () => {
            carousel.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });
        
        nextBtn.addEventListener('click', () => {
            carousel.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });
        
        // Update button states based on scroll position
        carousel.addEventListener('scroll', () => {
            const maxScroll = carousel.scrollWidth - carousel.clientWidth;
            
            prevBtn.disabled = carousel.scrollLeft <= 0;
            nextBtn.disabled = carousel.scrollLeft >= maxScroll - 1;
            
            prevBtn.style.opacity = prevBtn.disabled ? '0.3' : '1';
            nextBtn.style.opacity = nextBtn.disabled ? '0.3' : '1';
        });
        
        // Initial state
        prevBtn.style.opacity = '0.3';
        prevBtn.disabled = true;
    }
});
</script>

<!-- FAQ Section -->
<section id="faq-section" class="py-5 faq-section">
    <div class="container">
        <h2 class="faq-title mb-4">{{ __('Frequently Asked Question (FAQ)') }}</h2>

        <div class="faq-accordion" id="faqAccordion">
            <!-- FAQ Item 1 -->
            <div class="faq-item">
                <button class="faq-question active" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true">
                    <span>{{ __('How to register and start learning?') }}</span>
                    <i class="faq-icon fas fa-chevron-down"></i>
                </button>
                <div id="faq1" class="faq-answer collapse show" data-bs-parent="#faqAccordion">
                    <div class="faq-answer-content">
                        {{ __('Please click the Get Started button, create an account, and choose the course you want to take. You can start learning immediately after registration.') }}
                    </div>
                </div>
            </div>

            <!-- FAQ Item 2 -->
            <div class="faq-item">
                <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                    <span>{{ __('Is there a certificate after completing the course?') }}</span>
                    <i class="faq-icon fas fa-chevron-down"></i>
                </button>
                <div id="faq2" class="faq-answer collapse" data-bs-parent="#faqAccordion">
                    <div class="faq-answer-content">
                        {{ __('Yes, we provide a digital certificate after you complete all materials and quizzes in the course.') }}
                    </div>
                </div>
            </div>

            <!-- FAQ Item 3 -->
            <div class="faq-item">
                <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                    <span>{{ __('How long is access to course materials?') }}</span>
                    <i class="faq-icon fas fa-chevron-down"></i>
                </button>
                <div id="faq3" class="faq-answer collapse" data-bs-parent="#faqAccordion">
                    <div class="faq-answer-content">
                        {{ __('Access to course materials is lifetime. You can learn anytime at your own pace.') }}
                    </div>
                </div>
            </div>

            <!-- FAQ Item 4 -->
            <div class="faq-item">
                <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false">
                    <span>{{ __('What if I have difficulty with the material?') }}</span>
                    <i class="faq-icon fas fa-chevron-down"></i>
                </button>
                <div id="faq4" class="faq-answer collapse" data-bs-parent="#faqAccordion">
                    <div class="faq-answer-content">
                        {{ __('You can contact our support team via chat or email. Instructors are also ready to help answer your questions.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ JavaScript for Icon Rotation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqButtons = document.querySelectorAll('.faq-question');
    
    faqButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            faqButtons.forEach(btn => {
                if (btn !== this) {
                    btn.classList.remove('active');
                }
            });
            
            // Toggle active class on clicked button
            this.classList.toggle('active');
        });
    });
});
</script>

<!-- ============================================================
     FINAL CTA
     ============================================================ -->
<section class="ml-cta-section" aria-labelledby="mlCtaTitle">
    <div class="container">
        <div class="ml-cta-panel ml-reveal">
            <span class="ml-cta-badge">{{ __('Ready when you are') }}</span>

            <h2 class="ml-cta-title" id="mlCtaTitle">{{ __('Start Your Learning Journey') }}</h2>

            <p class="ml-cta-text">
                {{ __('Explore innovative learning experiences with MersifLab — practical courses, immersive technology, and certificates that prove what you have built.') }}
            </p>

            <div class="ml-cta-actions">
                <a href="{{ route('courses') }}#all-courses" class="ml-btn ml-btn-onblue">
                    {{ __('Explore Courses') }}
                    <i class="fas fa-arrow-right ml-btn-arrow" aria-hidden="true"></i>
                </a>
                @guest
                    <a href="{{ route('register') }}" class="ml-btn ml-btn-onblue-ghost">
                        {{ __('Create Free Account') }}
                    </a>
                @else
                    <a href="{{ url('/about') }}" class="ml-btn ml-btn-onblue-ghost">
                        {{ __('Learn More') }}
                    </a>
                @endguest
            </div>

            @guest
            <p class="ml-cta-note">{{ __('Free to join — no credit card required.') }}</p>
            @endguest
        </div>
    </div>
</section>

</div><!-- /.ml-home -->

@endsection

@section('scripts')
<script src="{{ asset('assets/js/home-modern.js') }}" defer></script>
@endsection