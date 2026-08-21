@extends('layouts.app')

@section('title', $course->name)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/course-detail.css') }}">
@endsection

@section('content')
{{-- Halaman detail HARUS terbuka dari paling atas.

     Skrip ini sengaja inline & sinkron di awal konten, bukan di @section('scripts'),
     karena browser memulihkan posisi gulir jauh sebelum DOMContentLoaded. Menunggu
     event itu akan terlihat sebagai "lompatan" ke tengah lalu balik ke atas. --}}
<script>
    if ('scrollRestoration' in history) {
        // Matikan pemulihan gulir bawaan browser: halaman ini selalu dibuka
        // dari header, kecuali memang diminta melanjutkan modul lewat ?module=.
        history.scrollRestoration = 'manual';
    }
</script>
<div class="course-detail-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            {{-- Tombol kembali (kiri) & tombol tutup (kanan) --}}
            @include('partials.course-detail-back')

            <nav class="breadcrumb-nav">
                <a href="{{ route('courses') }}"><i class="fas fa-home"></i> Courses</a>
                <i class="fas fa-chevron-right"></i>
                <span>{{ Str::limit($course->name, 50) }}</span>
            </nav>
        </div>
    </div>

    <!-- Course Hero Section with Background Image -->
    <section class="course-hero" style="background-image: url('{{ $course->image ? asset("storage/" . $course->image) : asset("assets/images/default-course.jpg") }}'); background-size: cover; background-position: center; background-repeat: no-repeat; position: relative;">
        <!-- Floating decorative elements -->
        <div class="floating-element floating-element-1"></div>
        <div class="floating-element floating-element-2"></div>
        
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="hero-content-card">
                        {{-- Badge kategori & tier --}}
                        <div class="hero-badges-row">
                            <span class="badge-category">
                                <i class="fas fa-graduation-cap"></i>
                                {{ $course->category->name ?? $course->category_name ?? 'Uncategorized' }}
                            </span>
                            @php $tier = $course->price_tier ?? null; @endphp
                            @if($tier)
                                <span class="badge-tier badge-tier--{{ $tier }}">
                                    @if($tier === 'premium')
                                        <i class="fas fa-crown"></i>
                                    @else
                                        <i class="fas fa-star"></i>
                                    @endif
                                    {{ ucfirst($tier) }}
                                </span>
                            @endif
                        </div>

                        <h1 class="hero-title" style="margin-bottom: 15px;">{{ $course->name }}</h1>
                        
                        <p class="hero-description">{{ $course->description ?? 'Master React from basics to advanced concepts including Hooks, Context API, Redux, and modern best practices!' }}</p>
                        
                        <div class="course-stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon stat-icon-rating">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ number_format($ratingStats['average'] ?? 4.9, 1) }}</div>
                                    <div class="stat-label">Rating ({{ number_format($ratingStats['total'] ?? 10224) }})</div>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon stat-icon-students">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ $course->students_count ?? 430 }}</div>
                                    <div class="stat-label">Students</div>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon stat-icon-duration">
                                    <i class="far fa-clock"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value">{{ $course->formatted_total_duration ?? '8h' }}</div>
                                    <div class="stat-label">Duration</div>
                                </div>
                            </div>
                        </div>

                        <div class="hero-footer">
                            <div class="creator-info">
                                <div class="creator-avatar">
                                    @if($course->teacher && !empty($course->teacher->avatar))
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($course->teacher->avatar) }}" alt="{{ $course->teacher->name ?? 'Teacher' }}" />
                                    @else
                                        {{ strtoupper(substr($course->teacher->name ?? 'T', 0, 1)) }}
                                    @endif
                                </div>
                                <div class="creator-details">
                                    <small>Created by</small>
                                    <strong>{{ $course->teacher->name ?? 'Teacher\'s Name' }}</strong>
                                </div>
                            </div>
                            <div class="last-updated">
                                <i class="fas fa-calendar-alt"></i>
                                Updated {{ $course->updated_at ? $course->updated_at->format('M Y') : 'Jan 2024' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    @if($isEnrolled && $hasCompletedModules && !$showAsNotEnrolled)
                        {{-- Enrolled dengan progress → Progress Card --}}
                        @php
                            $firstChapter = $course->chapters->first();
                            $firstModule  = $firstChapter ? $firstChapter->modules->first() : null;

                            // Find last incomplete module for "Continue Learning"
                            $continueChapterId = null;
                            $continueModuleId  = null;

                            if ($progress > 0 && $progress < 100 && auth()->check()) {
                                $userId = auth()->id();
                                foreach ($course->chapters as $chap) {
                                    $approvedMods = $chap->modules->filter(fn($m) => $m->approval_status === 'approved');
                                    foreach ($approvedMods as $mod) {
                                        $isCompleted = \DB::table('module_completions')
                                            ->where('user_id', $userId)
                                            ->where('module_id', $mod->id)
                                            ->exists();
                                        if (!$isCompleted) {
                                            $continueChapterId = $chap->id;
                                            $continueModuleId  = $mod->id;
                                            break 2;
                                        }
                                    }
                                }
                            }

                            // "Learning Again" → first approved module of first chapter
                            $firstApprovedModule  = null;
                            $firstApprovedChapter = null;
                            foreach ($course->chapters as $chap) {
                                $approvedMods = $chap->modules->filter(fn($m) => $m->approval_status === 'approved');
                                if ($approvedMods->count() > 0) {
                                    $firstApprovedChapter = $chap;
                                    $firstApprovedModule  = $approvedMods->first();
                                    break;
                                }
                            }
                        @endphp
                        <div class="progress-card">
                            <div class="progress-badge">
                                <i class="fas fa-check-circle"></i>
                                <span>Enrolled</span>
                            </div>

                            @if($progress >= 100)
                                {{-- Learning Again → first module --}}
                                @if($firstApprovedModule)
                                    <button class="btn-start-learning" onclick="checkSubscriptionBeforeAccess({{ $course->id }}, {{ $firstApprovedChapter->id }}, {{ $firstApprovedModule->id }}, {{ $canAccessCourse ? 'true' : 'false' }}, @if($subscriptionStatus)@json($subscriptionStatus)@else null @endif, {{ $hasPurchase ? 'true' : 'false' }})" aria-live="polite">
                                        <i class="fas fa-redo"></i>
                                        Learning Again
                                    </button>
                                @else
                                    <button class="btn-start-learning" disabled>
                                        <i class="fas fa-info-circle"></i>
                                        No modules available
                                    </button>
                                @endif
                            @elseif($progress > 0 && $continueChapterId && $continueModuleId)
                                {{-- Continue Learning → last incomplete module --}}
                                <button class="btn-start-learning" onclick="checkSubscriptionBeforeAccess({{ $course->id }}, {{ $continueChapterId }}, {{ $continueModuleId }}, {{ $canAccessCourse ? 'true' : 'false' }}, @if($subscriptionStatus)@json($subscriptionStatus)@else null @endif, {{ $hasPurchase ? 'true' : 'false' }})" aria-live="polite">
                                    <i class="fas fa-play-circle"></i>
                                    Continue Learning
                                </button>
                            @else
                                {{-- Start Learning → first module --}}
                                @if($firstApprovedModule)
                                    <button class="btn-start-learning" onclick="checkSubscriptionBeforeAccess({{ $course->id }}, {{ $firstApprovedChapter->id }}, {{ $firstApprovedModule->id }}, {{ $canAccessCourse ? 'true' : 'false' }}, @if($subscriptionStatus)@json($subscriptionStatus)@else null @endif, {{ $hasPurchase ? 'true' : 'false' }})" aria-live="polite">
                                        <i class="fas fa-play-circle"></i>
                                        Start Learning
                                    </button>
                                @else
                                    <button class="btn-start-learning" disabled>
                                        <i class="fas fa-info-circle"></i>
                                        No modules available
                                    </button>
                                @endif
                            @endif

                            <div class="progress-info">
                                <small>Your Progress</small>
                                <div class="progress-bar-wrapper">
                                    <div class="progress-bar-fill" style="width: {{ $progress }}%"></div>
                                </div>
                                <span class="progress-text">{{ number_format($progress, 0) }}% Complete</span>
                            </div>

                            <div class="purchase-card-divider" style="margin-left:0;margin-right:0;"></div>
                            <div class="course-includes" style="padding: 20px 0 0;">
                                <small class="includes-title">This course includes:</small>
                                <ul class="includes-list">
                                    @if($course->formatted_includes && count($course->formatted_includes) > 0)
                                        @foreach($course->formatted_includes as $include)
                                            <li><i class="{{ $include['icon'] }}"></i>{{ $include['text'] }}</li>
                                        @endforeach
                                    @else
                                        <li><i class="fas fa-video"></i> Video pembelajaran on-demand</li>
                                        <li><i class="fas fa-infinity"></i> Akses seumur hidup</li>
                                        <li><i class="fas fa-certificate"></i> Sertifikat penyelesaian</li>
                                        <li><i class="fas fa-robot"></i> Tanya AI Assistant</li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                    @elseif($isEnrolled && !$hasCompletedModules)
                        {{-- Enrolled tapi belum mark as complete: Access Course --}}
                        @php
                            $firstChapter = $course->chapters->first();
                            $firstModule = $firstChapter ? $firstChapter->modules->first() : null;
                        @endphp
                        <div class="purchase-card">
                            <div class="price-section">
                                @if($course->price && $course->price > 0)
                                    <div class="current-price">RP{{ number_format($course->discounted_price ?? $course->price ?? 0, 0, ',', '.') }}</div>
                                @else
                                    <div class="current-price">FREE</div>
                                @endif
                            </div>
                            <div class="purchase-actions">
                                @if($firstModule)
                                    <button class="btn-add-cart" onclick="window.location.href='{{ route('module.show', [$course->id, $firstChapter->id, $firstModule->id]) }}'">
                                        <i class="fas fa-unlock"></i> Access Course
                                    </button>
                                @else
                                    <button class="btn-add-cart" disabled>
                                        <i class="fas fa-info-circle"></i> No modules available
                                    </button>
                                @endif
                            </div>
                            <div class="purchase-card-divider"></div>
                            <div class="course-includes">
                                <small class="includes-title">This course includes:</small>
                                <ul class="includes-list">
                                    @if($course->formatted_includes && count($course->formatted_includes) > 0)
                                        @foreach($course->formatted_includes as $include)
                                            <li><i class="{{ $include['icon'] }}"></i>{{ $include['text'] }}</li>
                                        @endforeach
                                    @else
                                        <li><i class="fas fa-video"></i> Video pembelajaran on-demand</li>
                                        <li><i class="fas fa-infinity"></i> Akses seumur hidup</li>
                                        <li><i class="fas fa-certificate"></i> Sertifikat penyelesaian</li>
                                        <li><i class="fas fa-robot"></i> Tanya AI Assistant</li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                    @else
                        {{-- Not Enrolled: Purchase Card --}}
                        <div class="purchase-card">
                            @if($isPopular)
                                <div class="card-ribbon">Popular</div>
                            @endif

                            {{-- Price Section --}}
                            <div class="price-section">
                                @if($course->price && $course->price > 0)
                                    @php
                                        $original    = $course->price;
                                        $current     = $course->discounted_price ?? $original;
                                        $discountPct = $course->discount_percentage ?? 0;
                                    @endphp
                                    @if($course->has_discount && $course->discount && $current < $original)
                                        <div class="price-label">Price</div>
                                        <div class="original-price text-muted text-decoration-line-through">RP{{ number_format($original, 0, ',', '.') }}</div>
                                        <div class="current-price">RP{{ number_format($current, 0, ',', '.') }}</div>
                                        <div class="discount-badge">
                                            <i class="fas fa-bolt"></i> -Rp{{ number_format($course->discount, 0, ',', '.') }} ({{ $discountPct }}% OFF)
                                        </div>
                                        @if($course->discount_ends_at)
                                            <div class="discount-countdown" id="countdown-{{ $course->id }}">
                                                <i class="fas fa-hourglass-half"></i>
                                                Discount ends in <span class="countdown-timer">--:--:--:--</span>
                                            </div>
                                            <script>
                                                (function() {
                                                    const endDate   = new Date('{{ $course->discount_ends_at->toIso8601String() }}').getTime();
                                                    const countdownEl = document.getElementById('countdown-{{ $course->id }}');
                                                    const timerEl   = countdownEl?.querySelector('.countdown-timer');
                                                    function updateCountdown() {
                                                        const now      = new Date().getTime();
                                                        const distance = endDate - now;
                                                        if (distance <= 0) { timerEl.textContent = 'EXPIRED'; return; }
                                                        const days    = Math.floor(distance / (1000 * 60 * 60 * 24));
                                                        const hours   = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                                        timerEl.textContent =
                                                            String(days).padStart(2, '0') + ':' +
                                                            String(hours).padStart(2, '0') + ':' +
                                                            String(minutes).padStart(2, '0') + ':' +
                                                            String(seconds).padStart(2, '0');
                                                    }
                                                    updateCountdown();
                                                    setInterval(updateCountdown, 1000);
                                                })();
                                            </script>
                                        @elseif($course->discount_starts_at)
                                            <div class="discount-duration">
                                                Discount starts {{ $course->discount_starts_at->format('d M Y H:i') }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="price-label">Price</div>
                                        <div class="current-price">RP{{ number_format($original ?? 150000, 0, ',', '.') }}</div>
                                    @endif
                                @else
                                    <div class="current-price free-text">FREE</div>
                                    <div class="free-badge">
                                        <i class="fas fa-gift"></i> Free Course
                                    </div>
                                @endif
                            </div>

                            {{-- CTA Buttons --}}
                            <div class="purchase-actions">
                                @auth
                                    @php
                                        $user = auth()->user();
                                        $isSubscribed = $user->is_subscriber && $user->subscription_expires_at && $user->subscription_expires_at > now();
                                        $subscriptionPlan = $user->subscription_plan;
                                        $courseTier = $course->price_tier ?? 'standard';
                                        $canAccessBySubscription = $isSubscribed && (
                                            ($subscriptionPlan === 'premium') ||
                                            ($subscriptionPlan === 'standard' && $courseTier === 'standard')
                                        );
                                    @endphp

                                    @if(auth()->user()->isStudent())
                                        @php
                                            // Pending course purchase
                                            $pendingPurchase = \App\Models\Purchase::where('user_id', $user->id)
                                                ->where('class_id', $course->id)
                                                ->where('status', 'pending')
                                                ->latest()->first();
                                            $hasPendingPurchaseLocal = (bool) $pendingPurchase;

                                            // Distinguish: has payment_method = "Bayar Sekarang" already clicked
                                            $pendingPaymentSent = $pendingPurchase && !empty($pendingPurchase->payment_method);

                                            // Pending subscription purchase
                                            $pendingSubscription = \App\Models\SubscriptionPurchase::where('user_id', $user->id)
                                                ->where('status', 'pending')
                                                ->latest()->first();
                                            $hasPendingSubscription = (bool) $pendingSubscription;
                                            $pendingSubPaymentSent = $pendingSubscription && !empty($pendingSubscription->payment_method);

                                            $hasPurchase = \App\Models\Purchase::where('user_id', $user->id)
                                                ->where('class_id', $course->id)
                                                ->where('status', 'success')
                                                ->exists();

                                            $showAccessCourse = !$showAsNotEnrolled && (($isEnrolled && !$hasCompletedModules) || ($canAccessBySubscription && !$hasCompletedModules));
                                        @endphp

                                        @if($showAccessCourse || ($canAccessBySubscription && !$hasCompletedModules && !$showAsNotEnrolled))
                                            @php
                                                $firstChapter = $course->chapters->first();
                                                $firstModule  = $firstChapter ? $firstChapter->modules->first() : null;
                                            @endphp
                                            @if($firstModule)
                                                <button class="btn-add-cart" onclick="window.location.href='{{ route('module.show', [$course->id, $firstChapter->id, $firstModule->id]) }}'" title="You have access to this course">
                                                    <i class="fas fa-unlock"></i> Access Course
                                                </button>
                                            @endif

                                        @elseif($canAccessBySubscription && $hasCompletedModules && !$showAsNotEnrolled)
                                            {{-- Handled by progress card above --}}

                                        @elseif($hasPurchase && !$hasCompletedModules && !$showAsNotEnrolled)
                                            @php
                                                $firstChapter = $course->chapters->first();
                                                $firstModule  = $firstChapter ? $firstChapter->modules->first() : null;
                                            @endphp
                                            @if($firstModule)
                                                <button class="btn-add-cart" onclick="window.location.href='{{ route('module.show', [$course->id, $firstChapter->id, $firstModule->id]) }}'" title="You have purchased this course">
                                                    <i class="fas fa-unlock"></i> Access Course
                                                </button>
                                            @endif

                                        @elseif($isSubscribed && $subscriptionPlan === 'standard' && $courseTier === 'premium' && !$showAsNotEnrolled)
                                            {{-- Standard subscriber → premium course: two options --}}
                                            <div class="upgrade-notice">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Premium Course</strong> — choose your option below
                                            </div>

                                            <div class="option-box option-box--purple">
                                                <p class="option-box__label">
                                                    <i class="fas fa-crown"></i> OPTION 1: UPGRADE TO PREMIUM
                                                </p>
                                                <p class="option-box__desc">
                                                    Get access to ALL premium courses + unlimited access + AI assistant (can upload files)
                                                </p>
                                                <a href="{{ route('subscription.payment', 'premium') }}" class="btn-add-cart" style="display:flex;text-decoration:none;">
                                                    <i class="fas fa-arrow-up"></i> Upgrade to Premium — Rp 150.000/mo
                                                </a>
                                            </div>

                                            <div class="option-divider">OR</div>

                                            <div class="option-box option-box--gray">
                                                <p class="option-box__label">
                                                    <i class="fas fa-shopping-cart"></i> OPTION 2: BUY THIS COURSE ONLY
                                                </p>
                                                <p class="option-box__desc">Get access to just this course only</p>

                                                @if($hasPendingPurchaseLocal)
                                                    <div class="pending-invoice-notice">
                                                        <i class="fas fa-exclamation-circle"></i>
                                                        <div>
                                                            <strong>{{ $pendingPaymentSent ? 'Invoice Sent!' : 'Order Not Completed!' }}</strong>
                                                            <p>{{ $pendingPaymentSent ? 'Complete your payment and confirm so the admin can approve your access.' : 'You have an unpaid order. Continue checkout to complete your purchase.' }}</p>
                                                        </div>
                                                    </div>
                                                    @if($pendingPaymentSent)
                                                        {{-- Already paid → invoice only, no cancel --}}
                                                        <a href="{{ route('invoice', $pendingPurchase->id) }}" class="btn-continue-purchase">
                                                            <i class="fas fa-file-invoice"></i> View Invoice & Pay
                                                        </a>
                                                    @else
                                                        {{-- Went to checkout but didn't pay → continue + cancel --}}
                                                        <a href="{{ route('checkout') }}" class="btn-continue-purchase">
                                                            <i class="fas fa-arrow-right"></i> Continue to Checkout
                                                        </a>
                                                        <button type="button" class="btn-cancel-purchase" onclick="confirmCancelOrder({{ $course->id }})">
                                                            <i class="fas fa-times"></i> Cancel Order
                                                        </button>
                                                    @endif
                                                @else
                                                    <form action="{{ route('cart.buyNow') }}" method="POST" style="margin-bottom:0;">
                                                        @csrf
                                                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                                                        <button type="submit" class="btn-add-cart">
                                                            Buy This Course — Rp{{ number_format($course->discounted_price ?? $course->price ?? 100000, 0, ',', '.') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                        @else
                                            {{-- Not subscribed / expired: Show subscription options --}}
                                            <div class="subscription-options-box">
                                                <p class="subscription-options-box__title">
                                                    <i class="fas fa-bolt"></i> Get Unlimited Access
                                                </p>
                                                @if($courseTier === 'standard')
                                                    <a href="{{ route('subscription.payment', 'standard') }}" class="btn-subscription btn-subscription--standard">
                                                        <i class="fas fa-star"></i> Subscribe Standard
                                                    </a>
                                                @endif
                                                <a href="{{ route('subscription.payment', 'premium') }}" class="btn-subscription btn-subscription--premium">
                                                    <i class="fas fa-crown"></i> Subscribe Premium
                                                </a>
                                            </div>

                                            <div class="option-divider">OR BUY INDIVIDUALLY</div>

                                            @if($hasPendingPurchaseLocal)
                                                <div class="pending-invoice-notice">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <div>
                                                        <strong>{{ $pendingPaymentSent ? 'Invoice Sent!' : 'Order Not Completed!' }}</strong>
                                                        <p>{{ $pendingPaymentSent ? 'Complete your payment and confirm to the admin so your access can be activated.' : 'You have an unpaid order. Continue checkout to complete your purchase.' }}</p>
                                                    </div>
                                                </div>
                                                @if($pendingPaymentSent)
                                                    {{-- Already sent payment → invoice only, no cancel --}}
                                                    <a href="{{ route('invoice', $pendingPurchase->id) }}" class="btn-continue-purchase">
                                                        <i class="fas fa-file-invoice"></i> View Invoice & Pay
                                                    </a>
                                                @else
                                                    {{-- Went to checkout but didn't pay → continue + cancel --}}
                                                    <a href="{{ route('checkout') }}" class="btn-continue-purchase">
                                                        <i class="fas fa-arrow-right"></i> Continue to Checkout
                                                    </a>
                                                    <button type="button" class="btn-cancel-purchase" onclick="confirmCancelOrder({{ $course->id }})">
                                                        <i class="fas fa-times"></i> Cancel Order
                                                    </button>
                                                @endif

                                            @elseif($hasPendingSubscription)
                                                <div class="pending-invoice-notice">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <div>
                                                        <strong>{{ $pendingSubPaymentSent ? 'Subscription Invoice Sent!' : 'Subscription Not Completed!' }}</strong>
                                                        <p>{{ $pendingSubPaymentSent ? 'Complete your subscription payment so the admin can activate your plan.' : 'You have a pending subscription. Continue to complete your payment.' }}</p>
                                                    </div>
                                                </div>
                                                @if($pendingSubPaymentSent)
                                                    <a href="{{ route('invoice', $pendingSubscription->id) }}" class="btn-continue-purchase">
                                                        <i class="fas fa-file-invoice"></i> View Subscription Invoice
                                                    </a>
                                                @else
                                                    <a href="{{ route('subscription.payment', $pendingSubscription->plan ?? 'standard') }}" class="btn-continue-purchase">
                                                        <i class="fas fa-arrow-right"></i> Continue Subscription
                                                    </a>
                                                    <button type="button" class="btn-cancel-purchase" onclick="confirmCancelSubscription()">
                                                        <i class="fas fa-times"></i> Cancel Subscription
                                                    </button>
                                                @endif

                                            @else
                                                {{-- No pending order: show buy options --}}
                                                @if($course->price && $course->price > 0)
                                                    <form action="{{ route('cart.add') }}" method="POST" style="margin-bottom:10px;">
                                                        @csrf
                                                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                                                        <button type="submit" class="btn-add-cart">
                                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('cart.buyNow') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                                                        <button type="submit" class="btn-buy-now">
                                                            Buy Now
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('course.enroll', $course->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn-add-cart">
                                                            <i class="fas fa-gift"></i> Enroll Free Course
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        @endif

                                    @elseif(auth()->user()->isTeacher())
                                        @php
                                            $firstChapter = $course->chapters->first();
                                            $firstModule  = $firstChapter ? $firstChapter->modules->first() : null;
                                        @endphp
                                        @if($firstModule)
                                            <button class="btn-add-cart" onclick="window.location.href='{{ route('module.show', [$course->id, $firstChapter->id, $firstModule->id]) }}'">
                                                <i class="fas fa-eye me-1"></i> Preview
                                            </button>
                                        @else
                                            <button class="btn-add-cart" disabled>
                                                <i class="fas fa-info-circle me-1"></i> No modules available
                                            </button>
                                        @endif
                                        <button class="btn-buy-now" onclick="window.location.href='{{ route('teacher.classes.edit', $course->id) }}'">
                                            <i class="fas fa-cog me-1"></i> Manage
                                        </button>

                                    @else
                                        <button class="btn-add-cart" onclick="window.location.href='{{ route('login') }}'">
                                            <i class="fas fa-sign-in-alt"></i> Login to Purchase
                                        </button>
                                    @endif
                                @else
                                    <button class="btn-add-cart" onclick="window.location.href='{{ route('login') }}'">
                                        <i class="fas fa-sign-in-alt"></i> Login to Purchase
                                    </button>
                                @endauth
                            </div>

                            <div class="purchase-card-divider"></div>

                            <div class="course-includes">
                                <small class="includes-title">This course includes:</small>
                                <ul class="includes-list">
                                    @if($course->formatted_includes && count($course->formatted_includes) > 0)
                                        @foreach($course->formatted_includes as $include)
                                            <li>
                                                <i class="{{ $include['icon'] }}"></i>
                                                {{ $include['text'] }}
                                            </li>
                                        @endforeach
                                    @else
                                        <li><i class="fas fa-video"></i> Video pembelajaran on-demand</li>
                                        <li><i class="fas fa-infinity"></i> Akses seumur hidup</li>
                                        <li><i class="fas fa-certificate"></i> Sertifikat penyelesaian</li>
                                        <li><i class="fas fa-robot"></i> Tanya AI Assistant</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- What you'll learn -->
            @if($course->what_youll_learn)
            <div class="content-section what-learn-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="title-icon"><i class="fas fa-lightbulb"></i></span>
                        What you'll learn
                    </h2>
                    <div class="section-subtitle-wrapper">
                        <div class="subtitle-line"></div>
                        <p class="section-subtitle">Master these key skills and concepts</p>
                        <div class="subtitle-line"></div>
                    </div>
                </div>
                <div class="learning-grid">
                    @php
                        $learningPoints = array_filter(explode("\n", $course->what_youll_learn));
                    @endphp
                    @foreach($learningPoints as $index => $point)
                        @if(trim($point))
                        <div class="learning-card" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="check-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>{{ trim($point) }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Course Content -->
            <div class="content-section course-content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="title-icon"><i class="fas fa-book-open"></i></span>
                        Course content
                    </h2>
                    @php
                        $durationText = $course->formatted_total_duration ?? '8 hours';
                        $durationText = str_replace('jam', 'hours', $durationText);
                        $durationText = str_replace('menit', 'minutes', $durationText);
                    @endphp
                    <div class="section-subtitle-wrapper">
                        <div class="subtitle-line"></div>
                        <p class="section-subtitle">{{ $course->chapters_count ?? 0 }} chapters · {{ $durationText }} total length</p>
                        <div class="subtitle-line"></div>
                    </div>
                </div>

                @if($course->chapters->count() > 0)
                    <div class="chapters-accordion">
                        @php
                            $visibleChapters = $course->chapters->filter(function($chapter) {
                                $approvedModulesCount = $chapter->modules->filter(function($module) {
                                    return $module->approval_status === 'approved';
                                })->count();
                                return $approvedModulesCount > 0;
                            });

                            $userLastModule  = null;
                            $userLastChapter = null;

                            if ($isEnrolled && auth()->check()) {
                                $userId = auth()->id();
                                foreach ($visibleChapters as $chap) {
                                    $approvedModules = $chap->modules->filter(function($mod) {
                                        return $mod->approval_status === 'approved';
                                    });
                                    foreach ($approvedModules as $mod) {
                                        $isCompleted = \DB::table('module_completions')
                                            ->where('user_id', $userId)
                                            ->where('module_id', $mod->id)
                                            ->exists();
                                        if (!$isCompleted) {
                                            $userLastModule  = $mod;
                                            $userLastChapter = $chap;
                                            break 2;
                                        }
                                    }
                                }
                                if (!$userLastModule && $visibleChapters->count() > 0) {
                                    $lastChapter = $visibleChapters->last();
                                    if ($lastChapter) {
                                        $lastApprovedModules = $lastChapter->modules->filter(function($mod) {
                                            return $mod->approval_status === 'approved';
                                        });
                                        if ($lastApprovedModules->count() > 0) {
                                            $userLastChapter = $lastChapter;
                                            $userLastModule  = $lastApprovedModules->last();
                                        }
                                    }
                                }
                            }
                        @endphp

                        @foreach($visibleChapters as $index => $chapter)
                        @php
                            $approvedModulesInChapter = $chapter->modules->filter(function($mod) {
                                return $mod->approval_status === 'approved';
                            });
                            $firstModuleInChapter = $approvedModulesInChapter->first();
                            $chapterDuration = $chapter->formatted_total_duration ?? '34 min';
                            $chapterDuration = str_replace('menit', 'min', $chapterDuration);

                            $allModulesCompleted = false;
                            $chapterStatus = 'locked';

                            if ($isEnrolled && auth()->check()) {
                                $userId = auth()->id();
                                $totalModules     = $approvedModulesInChapter->count();
                                $completedModules = 0;
                                foreach ($approvedModulesInChapter as $module) {
                                    $isCompleted = \DB::table('module_completions')
                                        ->where('user_id', $userId)
                                        ->where('module_id', $module->id)
                                        ->exists();
                                    if ($isCompleted) $completedModules++;
                                }
                                if ($totalModules > 0 && $completedModules === $totalModules) {
                                    $allModulesCompleted = true;
                                    $chapterStatus = 'completed';
                                } else {
                                    $chapterStatus = 'unlocked';
                                }
                            }

                            $chapterClickUrl    = null;
                            $isChapterClickable = false;

                            if ($isEnrolled && $firstModuleInChapter) {
                                $isChapterClickable = true;
                                $userHasReachedThisChapter = false;
                                if ($userLastChapter) {
                                    $currentChapterIndex = $visibleChapters->search(function($ch) use ($chapter) {
                                        return $ch->id === $chapter->id;
                                    });
                                    $userProgressChapterIndex = $visibleChapters->search(function($ch) use ($userLastChapter) {
                                        return $ch->id === $userLastChapter->id;
                                    });
                                    $userHasReachedThisChapter = $currentChapterIndex <= $userProgressChapterIndex;
                                }
                                if (!$userHasReachedThisChapter && $userLastModule && $userLastChapter) {
                                    $chapterClickUrl = route('module.show', [$course->id, $userLastChapter->id, $userLastModule->id]);
                                } else {
                                    $targetModule = null;
                                    foreach ($approvedModulesInChapter as $module) {
                                        $isCompleted = \DB::table('module_completions')
                                            ->where('user_id', auth()->id())
                                            ->where('module_id', $module->id)
                                            ->exists();
                                        if (!$isCompleted) { $targetModule = $module; break; }
                                    }
                                    if (!$targetModule && $firstModuleInChapter) {
                                        $targetModule = $firstModuleInChapter;
                                    }
                                    if ($targetModule) {
                                        $chapterClickUrl = route('module.show', [$course->id, $chapter->id, $targetModule->id]);
                                    }
                                }
                            }
                        @endphp
                        {{-- data-chapter-id & data-module-ids dipakai untuk
                             menemukan kembali chapter tempat modul terakhir
                             yang dipelajari siswa, lalu menyorotinya. --}}
                        <div class="chapter-card {{ $isChapterClickable ? 'clickable' : '' }}"
                             id="chapter-card-{{ $chapter->id }}"
                             data-chapter-id="{{ $chapter->id }}"
                             data-module-ids="{{ $approvedModulesInChapter->pluck('id')->implode(',') }}"
                             @if($isChapterClickable)
                             onclick="window.location.href='{{ $chapterClickUrl }}'"
                             style="cursor: pointer;"
                             title="Click to view this chapter"
                             @endif>
                            <div class="chapter-header-wrapper">
                                <div class="chapter-number">{{ $index + 1 }}</div>
                                <div class="chapter-content-info">
                                    <h6 class="chapter-title">{{ $chapter->title }}</h6>
                                    <div class="chapter-meta">
                                        <span><i class="fas fa-book-open"></i> {{ $approvedModulesInChapter->count() }} lessons</span>
                                        <span><i class="far fa-clock"></i> {{ $chapterDuration }}</span>
                                    </div>
                                </div>
                                @if($isEnrolled)
                                    @if($allModulesCompleted)
                                        <span class="access-badge access-completed">
                                            <i class="fas fa-check-circle"></i> Completed
                                        </span>
                                    @else
                                        <span class="access-badge access-unlocked">
                                            <i class="fas fa-unlock"></i> Unlocked
                                        </span>
                                    @endif
                                @else
                                    <span class="access-badge access-locked">
                                        <i class="fas fa-lock"></i> Locked
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>No chapters with approved modules available yet.</p>
                    </div>
                @endif
            </div>

            <!-- Requirements -->
            @if($course->requirement)
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="title-icon"><i class="fas fa-list-check"></i></span>
                        Requirements
                    </h2>
                </div>
                <ul class="requirements-list">
                    @foreach(explode("\n", $course->requirement) as $req)
                        @if(trim($req))
                        <li>{{ trim($req) }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Description -->
            @if($course->description)
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="title-icon"><i class="fas fa-align-left"></i></span>
                        Description
                    </h2>
                </div>
                <p class="description-text">{{ $course->description }}</p>
            </div>
            @endif

            <!-- Instructors -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="title-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                        Instructors
                    </h2>
                </div>
                <div class="instructor-card">
                    <div class="instructor-avatar">
                        @if(!empty($course->teacher->avatar))
                            <img src="{{ asset('storage/' . $course->teacher->avatar) }}" alt="{{ $course->teacher->name ?? 'Instructor' }}" class="avatar-image">
                        @else
                            <span>{{ strtoupper(substr($course->teacher->name ?? 'T', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="instructor-details">
                        <h5 class="instructor-name">{{ $course->teacher->name ?? 'Instructor Name' }}</h5>
                        <p class="instructor-bio">
                            @if($course->teacher->bio)
                                {{ $course->teacher->bio }}
                            @elseif($course->teacher->biography)
                                {{ $course->teacher->biography }}
                            @else
                                Passionate educator dedicated to helping students achieve their learning goals and master new skills.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Student Reviews -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="title-icon"><i class="fas fa-star"></i></span>
                        Student Reviews
                    </h2>
                </div>

                @if($ratingStats['total'] > 0)
                <div class="reviews-summary">
                    <div class="rating-overview">
                        <div class="rating-number">{{ number_format($ratingStats['average'] ?? 4.9, 1) }}</div>
                        <div class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($ratingStats['average'] ?? 4.9) ? 'filled' : '' }}"></i>
                            @endfor
                        </div>
                        <p class="rating-label">Course Rating based on {{ number_format($ratingStats['total'] ?? 0) }} {{ $ratingStats['total'] == 1 ? 'review' : 'reviews' }}</p>
                    </div>

                    <div class="rating-bars">
                        @for($i = 5; $i >= 1; $i--)
                        <div class="rating-bar-row">
                            <span class="star-label">{{ $i }} <i class="fas fa-star"></i></span>
                            <div class="rating-bar-bg">
                                <div class="rating-bar-fill" style="width: {{ $ratingStats['distribution'][$i]['percentage'] ?? (6-$i)*15 }}%"></div>
                            </div>
                            <span class="percentage">{{ $ratingStats['distribution'][$i]['percentage'] ?? (6-$i)*15 }}%</span>
                        </div>
                        @endfor
                    </div>
                </div>
                @endif

                <!-- Leave a Review (Only for Enrolled Students) -->
                @if($isEnrolled && auth()->check() && auth()->user()->isStudent())
                <div class="leave-review-box">
                    <h4 class="review-box-title">Leave a review</h4>
                    <form action="{{ route('course.rating.submit', $course->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Your Rating</label>
                            <div class="star-rating" id="starRating">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="far fa-star" data-rating="{{ $i }}"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" required>
                        </div>
                        <div class="form-group">
                            <label>Your Review</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience with this course...">{{ old('comment', $userReview->comment ?? '') }}</textarea>
                        </div>
                        <button type="submit" class="btn-submit-review">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                </div>
                @endif

                <!-- Reviews List -->
                @if($reviews->count() > 0)
                <div class="reviews-list">
                    @foreach($reviews as $review)
                    <div class="review-card">
                        <div class="review-card-header">
                            <div class="review-card-user">
                                <div class="reviewer-avatar">
                                    @if(isset($review->user->avatar) && $review->user->avatar)
                                        <img src="{{ asset('storage/' . $review->user->avatar) }}" alt="{{ $review->user->name ?? 'User' }}" class="avatar-image">
                                    @else
                                        <span class="avatar-initial">{{ strtoupper(substr($review->user->name ?? 'M', 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="review-user-info">
                                    <h6 class="reviewer-name">{{ $review->user->name ?? 'Anonymous User' }}</h6>
                                    <small class="review-date">{{ $review->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="review-card-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $review->rating ? 'filled' : 'empty' }}"></i>
                            @endfor
                        </div>

                        @if($review->comment)
                        <div class="review-card-content">
                            <p class="review-text">{{ $review->comment }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    <button class="btn-load-more">
                        <i class="fas fa-chevron-down"></i> Load More Reviews
                    </button>
                </div>
                @else
                <div class="empty-state" style="text-align: center; padding: 48px 24px; background: #f8f9fa; border-radius: 12px; margin-top: 24px;">
                    <i class="fas fa-comments" style="font-size: 48px; color: #e0e0e0; margin-bottom: 16px; display: block;"></i>
                    <h4 style="color: #828282; font-size: 16px; font-weight: 600; margin-bottom: 8px;">There is no review provided yet</h4>
                    <p style="color: #828282; font-size: 14px; margin: 0;">Be the first to leave a review for this course.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Subscription Expired Modal -->
<div class="modal fade" id="subscriptionExpiredModal" tabindex="-1" aria-labelledby="subscriptionExpiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #e0e0e0; padding: 20px 24px;">
                <h5 class="modal-title" id="subscriptionExpiredModalLabel" style="font-weight: 600; color: #333;">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Subscription Berakhir
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                    Subscription Anda sudah habis atau tidak sesuai dengan tier course ini.
                    Untuk melanjutkan belajar, silakan pilih salah satu opsi di bawah ini:
                </p>
                <div id="subscriptionModalContent">
                    <!-- Content will be filled by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Auto-show subscription expired modal if redirected from module access
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('subscription_expired'))
            @php
                $user = auth()->user();
                $hasPurchase = \App\Models\Purchase::where('user_id', $user->id)
                    ->where('class_id', $course->id)
                    ->where('status', 'success')
                    ->exists();
                $isSubscribed = $user->is_subscriber && $user->subscription_expires_at && $user->subscription_expires_at > now();
                $subscriptionPlan = $user->subscription_plan ?? 'standard';
                $courseTier = $course->price_tier ?? 'standard';
                $needsUpgrade = $isSubscribed && $subscriptionPlan === 'standard' && $courseTier === 'premium';
            @endphp
            const subscriptionStatus = {
                expired: {{ !$isSubscribed ? 'true' : 'false' }},
                needs_upgrade: {{ $needsUpgrade ? 'true' : 'false' }},
                plan: '{{ $subscriptionPlan }}',
                course_tier: '{{ $courseTier }}'
            };
            showSubscriptionExpiredModal(subscriptionStatus, {{ $course->id }});
        @endif
    });

    // Star rating interaction
    document.addEventListener('DOMContentLoaded', function() {
        const stars       = document.querySelectorAll('#starRating i');
        const ratingInput = document.getElementById('ratingInput');
        let selectedRating = 0;

        stars.forEach(star => {
            star.addEventListener('click', function() {
                selectedRating     = parseInt(this.getAttribute('data-rating'));
                ratingInput.value  = selectedRating;
                updateStars(selectedRating);
            });
            star.addEventListener('mouseover', function() {
                updateStars(parseInt(this.getAttribute('data-rating')));
            });
        });

        document.getElementById('starRating')?.addEventListener('mouseleave', function() {
            updateStars(selectedRating);
        });

        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });
        }
    });

    // Check subscription before accessing course
    function checkSubscriptionBeforeAccess(courseId, chapterId, moduleId, canAccess, subscriptionStatus, hasPurchase) {
        const canAccessBool  = canAccess  === true || canAccess  === 'true' || canAccess  === 1;
        const hasPurchaseBool = hasPurchase === true || hasPurchase === 'true' || hasPurchase === 1;

        let status = subscriptionStatus;
        if (typeof subscriptionStatus === 'string' && subscriptionStatus !== 'null' && subscriptionStatus !== '') {
            try { status = JSON.parse(subscriptionStatus); } catch (e) { status = null; }
        }
        if (subscriptionStatus === null || subscriptionStatus === 'null' || subscriptionStatus === undefined) {
            status = null;
        }

        if (canAccessBool || hasPurchaseBool) {
            window.location.href = '{{ route("module.show", [":courseId", ":chapterId", ":moduleId"]) }}'
                .replace(':courseId',  courseId)
                .replace(':chapterId', chapterId)
                .replace(':moduleId',  moduleId);
            return;
        }

        const finalStatus = (status && typeof status === 'object' && !Array.isArray(status))
            ? status
            : { expired: true, needs_upgrade: false, plan: 'standard', course_tier: 'standard' };
        showSubscriptionExpiredModal(finalStatus, courseId);
    }

    // Show subscription expired modal
    function showSubscriptionExpiredModal(subscriptionStatus, courseId) {
        const modalContent = document.getElementById('subscriptionModalContent');
        if (!modalContent) {
            Swal.fire({ icon: 'warning', title: 'Subscription Expired', text: 'Your subscription has ended. Please renew or purchase this course to continue.', confirmButtonColor: '#2563eb', confirmButtonText: 'OK' });
            return;
        }

        if (!subscriptionStatus || typeof subscriptionStatus !== 'object') {
            subscriptionStatus = { expired: true, needs_upgrade: false, plan: 'standard', course_tier: 'standard' };
        }

        let html = '';
        if (subscriptionStatus.needs_upgrade) {
            html = `
                <div style="background:#f3e8ff;padding:16px;border-radius:8px;margin-bottom:16px;border:1px solid #e1bee7;">
                    <p style="font-size:14px;color:#6a1b9a;margin-bottom:12px;font-weight:600;"><i class="fas fa-crown"></i> OPTION 1: UPGRADE TO PREMIUM</p>
                    <p style="font-size:13px;color:#666;margin-bottom:12px;">Get access to ALL premium courses + unlimited access + AI assistant</p>
                    <a href="{{ route('subscription.payment', 'premium') }}" class="btn btn-primary w-100"><i class="fas fa-arrow-up"></i> Upgrade to Premium — Rp 150.000/month</a>
                </div>
                <div style="text-align:center;color:#999;margin:16px 0;font-size:13px;font-weight:600;">OR</div>
                <div style="background:#f5f5f5;padding:16px;border-radius:8px;border:1px solid #e0e0e0;">
                    <p style="font-size:14px;color:#333;margin-bottom:12px;font-weight:600;"><i class="fas fa-shopping-cart"></i> OPTION 2: BUY THIS COURSE ONLY</p>
                    <p style="font-size:13px;color:#666;margin-bottom:12px;">Get lifetime access to this course only</p>
                    <form action="{{ route('cart.buyNow') }}" method="POST">
                        @csrf
                        <input type="hidden" name="course_id" value="${courseId}">
                        <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-shopping-cart"></i> Buy This Course</button>
                    </form>
                </div>`;
        } else {
            html = `
                <div style="background:#fff3cd;padding:16px;border-radius:8px;margin-bottom:16px;border:1px solid #ffc107;">
                    <p style="font-size:14px;color:#856404;margin-bottom:12px;font-weight:600;"><i class="fas fa-sync-alt"></i> Renew Your Subscription</p>
                    <p style="font-size:13px;color:#666;margin-bottom:12px;">Your subscription has ended. Renew to access all courses in your tier.</p>
                    <a href="{{ route('subscription.page') }}" class="btn btn-warning w-100" style="color:#fff;"><i class="fas fa-sync-alt"></i> Renew Subscription</a>
                </div>
                <div style="text-align:center;color:#999;margin:16px 0;font-size:13px;font-weight:600;">OR</div>
                <div style="background:#f5f5f5;padding:16px;border-radius:8px;border:1px solid #e0e0e0;">
                    <p style="font-size:14px;color:#333;margin-bottom:12px;font-weight:600;"><i class="fas fa-shopping-cart"></i> Buy This Course</p>
                    <p style="font-size:13px;color:#666;margin-bottom:12px;">Purchase for lifetime access without a subscription.</p>
                    <form action="{{ route('cart.buyNow') }}" method="POST">
                        @csrf
                        <input type="hidden" name="course_id" value="${courseId}">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-shopping-cart"></i> Buy Now</button>
                    </form>
                </div>`;
        }

        modalContent.innerHTML = html;

        const modalElement = document.getElementById('subscriptionExpiredModal');
        if (!modalElement || (typeof bootstrap === 'undefined' && typeof Bootstrap === 'undefined')) {
            Swal.fire({ icon: 'warning', title: 'Subscription Expired', text: 'Please renew or purchase this course.', confirmButtonColor: '#2563eb' });
            return;
        }

        const BootstrapModal = typeof bootstrap !== 'undefined' ? bootstrap.Modal : Bootstrap.Modal;
        let modal = BootstrapModal.getInstance(modalElement) || new BootstrapModal(modalElement, { backdrop: true, keyboard: true, focus: true });
        modal.show();
    }

    // ── Cancel pending COURSE order (belum bayar → ada tombol Cancel Order) ──
    function confirmCancelOrder(courseId) {
        Swal.fire({
            title: 'Cancel Order?',
            html: 'Are you sure you want to cancel this order?<br><small style="color:#888;">You can always purchase again later.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times"></i>&nbsp; Yes, Cancel Order',
            cancelButtonText: 'Keep Order',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Cancelling…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch('/cart/cancel-pending', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ course_id: courseId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Cancelled',
                        text: 'Your order has been successfully cancelled.',
                        confirmButtonColor: '#2563eb',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Failed to cancel order. Please try again.', confirmButtonColor: '#2563eb' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.', confirmButtonColor: '#2563eb' });
            });
        });
    }

    // ── Cancel pending SUBSCRIPTION (belum bayar → ada tombol Cancel Subscription) ──
    function confirmCancelSubscription() {
        Swal.fire({
            title: 'Cancel Subscription?',
            html: 'Are you sure you want to cancel your pending subscription?<br><small style="color:#888;">You can subscribe again at any time.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-times"></i>&nbsp; Yes, Cancel',
            cancelButtonText: 'Keep It',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Cancelling…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            fetch('/subscription/cancel-pending', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Subscription Cancelled',
                    text: 'Your pending subscription has been cancelled.',
                    confirmButtonColor: '#2563eb',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => window.location.reload());
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.', confirmButtonColor: '#2563eb' });
            });
        });
    }
</script>

<script>
/**
 * Posisi gulir awal halaman detail course.
 *
 * Aturan dasar: halaman SELALU terbuka dari paling atas (header course).
 *
 * Satu-satunya pengecualian adalah permintaan eksplisit lewat query parameter
 * ?module=ID, yang hanya dipasang tombol "Kembali ke Course" di halaman modul.
 * Di situ siswa memang meminta dikembalikan ke tempat ia belajar.
 *
 * Sebelumnya skrip ini juga membaca sessionStorage 'mersif.lastModule.course.*'
 * sebagai cadangan. Itulah sumber bug auto-scroll: penanda tersebut bertahan
 * 12 jam, sehingga kunjungan BARU dari katalog pun ikut tergulir ke tengah
 * halaman. Cadangan itu dihapus - niat "lanjutkan modul" harus datang dari
 * tautannya, bukan ditebak dari riwayat.
 *
 * Hanya menggulir & menyorot; tidak mengubah tautan atau alur pembelajaran.
 */
(function () {
    'use strict';

    function findChapterCardByModule(moduleId) {
        var cards = document.querySelectorAll('.chapter-card[data-module-ids]');

        for (var i = 0; i < cards.length; i++) {
            var ids = (cards[i].dataset.moduleIds || '')
                .split(',')
                .filter(function (id) { return id !== ''; });

            if (ids.indexOf(String(moduleId)) !== -1) {
                return cards[i];
            }
        }

        return null;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var params = new URLSearchParams(window.location.search);
        var moduleId = params.get('module');

        // Tanpa permintaan eksplisit: pastikan viewport di paling atas.
        // Hanya sekali saat pemuatan - gulir manual pengguna sesudahnya
        // sama sekali tidak diganggu.
        if (!moduleId) {
            window.scrollTo(0, 0);
            return;
        }

        var card = findChapterCardByModule(moduleId);

        if (!card) {
            window.scrollTo(0, 0);
            return;
        }

        card.scrollIntoView({ block: 'center', behavior: 'auto' });

        // Sorot sebentar supaya siswa langsung tahu sampai mana ia belajar.
        card.classList.add('chapter-card-resumed');
        window.setTimeout(function () {
            card.classList.remove('chapter-card-resumed');
        }, 2600);

        // Rapikan URL supaya refresh berikutnya tidak menggulir lagi.
        if (params.get('module') && window.history.replaceState) {
            params.delete('module');
            var query = params.toString();
            window.history.replaceState({}, '', window.location.pathname + (query ? '?' + query : ''));
        }
    });
})();
</script>
@endsection