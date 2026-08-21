<header class="header-section">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">

            <!-- Brand-->
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="REKA Logo" height="50" onerror="this.src='{{ asset('images/logo.png') }}'">
            </a>

            <!-- Hamburger -->
            <button class="navbar-toggler custom-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">

                <!-- CENTER MENU -->
                <ul class="navbar-nav mx-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                            {{ __('Home') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('courses*') ? 'active' : '' }}" href="{{ url('/courses') }}">
                            {{ __('Courses') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('about*') ? 'active' : '' }}" href="{{ url('/about') }}">
                            {{ __('About') }}
                        </a>
                    </li>
                    @if(!auth()->check() || !auth()->user()->isTeacher())
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('subscription*') ? 'active' : '' }}" href="{{ url('/subscription') }}">
                            {{ __('Subscription') }}
                        </a>
                    </li>
                    @endif
                </ul>

                <!-- RIGHT ACTION -->
                <ul class="navbar-nav align-items-center">

                    <!-- LANGUAGE SWITCHER -->
                    {{-- Selalu tampil, baik untuk tamu maupun pengguna login.
                         Pilihan disimpan di session oleh LanguageController,
                         lalu diterapkan tiap request oleh middleware SetLocale. --}}
                    <li class="nav-item me-2 dropdown">
                        <a class="nav-link ml-lang-toggle" href="#" id="languageDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           aria-label="{{ __('Language') }}" title="{{ __('Language') }}">
                            <i class="fas fa-globe" aria-hidden="true"></i>
                            <span class="ml-lang-code">{{ strtoupper(app()->getLocale()) }}</span>
                            <i class="fas fa-chevron-down ml-lang-caret" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end ml-lang-menu" aria-labelledby="languageDropdown">
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'id' ? 'active' : '' }}"
                                   href="{{ route('language.switch', 'id') }}">
                                    <span class="ml-lang-flag">🇮🇩</span>
                                    <span>{{ __('Indonesian') }}</span>
                                    @if(app()->getLocale() === 'id')
                                        <i class="fas fa-check ms-auto" aria-hidden="true"></i>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                                   href="{{ route('language.switch', 'en') }}">
                                    <span class="ml-lang-flag">🇬🇧</span>
                                    <span>{{ __('English') }}</span>
                                    @if(app()->getLocale() === 'en')
                                        <i class="fas fa-check ms-auto" aria-hidden="true"></i>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- GUEST -->
                    @guest
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-primary {{ Request::is('login') ? 'active' : '' }}"
                               href="{{ route('login') }}">
                                {{ __('Log In') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-primary {{ Request::is('register') ? 'active' : '' }}"
                               href="{{ route('register') }}">
                                {{ __('Sign Up') }}
                            </a>
                        </li>
                    @endguest

                    <!-- AUTH -->
                    @auth
                        <!-- Cart (Only for Students) -->
                        @if(!auth()->user()->isTeacher() && !auth()->user()->isAdmin())
                        <li class="nav-item me-3 position-relative">
                            <a class="nav-link icon-link" href="{{ route('cart') }}" style="color: #000;">
                                <i class="fas fa-shopping-cart" style="font-size: 1.2rem;"></i>
                                @php
                                    $cartCount = count(session('cart', []));
                                @endphp
                                @if($cartCount > 0)
                                    <span class="badge-notification cart-badge">
                                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        @endif

                        <!-- Notification -->
                        @auth
                        @if(auth()->user()->isTeacher())
                        <li class="nav-item me-3 dropdown position-relative">
                            @php
                                $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
                                    ->where('is_read', false)
                                    ->count();
                                $recentNotifications = \App\Models\Notification::where('user_id', auth()->id())
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            <a class="nav-link icon-link" href="#" id="notificationDropdown"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #000;">
                                <i class="fas fa-bell" style="font-size: 1.2rem;"></i>
                                @if($unreadCount > 0)
                                    <span class="badge-notification">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                @endif
                            </a>

                            <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                                <div class="notification-header">
                                    <h6 class="mb-0 fw-bold">Notifications</h6>
                                    @if($unreadCount > 0)
                                        <span class="badge bg-danger" style="font-size: 10px;">{{ $unreadCount }} unread</span>
                                    @endif
                                </div>

                                <div class="notification-body">
                                    @if($recentNotifications->count() > 0)
                                        @foreach($recentNotifications as $notif)
                                            <a href="{{ route('notifications') }}" class="notification-item {{ !$notif->is_read ? 'unread' : '' }}">
                                                <div class="notification-icon">
                                                    @if($notif->type === 'module_approved')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($notif->type === 'student_enrolled')
                                                        <i class="fas fa-user-plus text-success"></i>
                                                    @elseif($notif->type === 'course_rated')
                                                        <i class="fas fa-star text-warning"></i>
                                                    @elseif($notif->type === 'course_completed')
                                                        <i class="fas fa-trophy text-primary"></i>
                                                    @else
                                                        <i class="fas fa-bell text-info"></i>
                                                    @endif
                                                </div>
                                                <div class="notification-content">
                                                    <p class="notification-title">{{ $notif->title }}</p>
                                                    <p class="notification-text">{{ Str::limit($notif->message, 60) }}</p>
                                                    <span class="notification-time">{{ $notif->created_at->diffForHumans() }}</span>
                                                </div>
                                                @if(!$notif->is_read)
                                                    <span class="badge bg-warning ms-2" style="font-size: 0.6rem;">New</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    @else
                                        <div class="notification-empty">
                                            <i class="fas fa-bell-slash"></i>
                                            <p>No notifications</p>
                                        </div>
                                    @endif
                                </div>

                                @if($recentNotifications->count() > 0)
                                <div class="notification-footer">
                                    <a href="{{ route('notifications') }}" class="view-all-link">View all notifications</a>
                                </div>
                                @endif
                            </div>
                        </li>
                        @elseif(auth()->user()->isStudent() || (!auth()->user()->isTeacher() && !auth()->user()->isAdmin()))
                        <li class="nav-item me-3 dropdown position-relative">
                            @php
                                $studentUnreadCount = \App\Models\Notification::where('user_id', auth()->id())
                                    ->where('is_read', false)
                                    ->count();
                                $studentRecentNotifications = \App\Models\Notification::where('user_id', auth()->id())
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get();
                            @endphp
                            <a class="nav-link icon-link" href="#" id="studentNotificationDropdown"
                               role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #000;">
                                <i class="fas fa-bell" style="font-size: 1.2rem;"></i>
                                @if($studentUnreadCount > 0)
                                    <span class="badge-notification">{{ $studentUnreadCount > 99 ? '99+' : $studentUnreadCount }}</span>
                                @endif
                            </a>

                            <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                                <div class="notification-header">
                                    <h6 class="mb-0 fw-bold">Notifications</h6>
                                    @if($studentUnreadCount > 0)
                                        <span class="badge bg-danger" style="font-size: 10px;">{{ $studentUnreadCount }} unread</span>
                                    @endif
                                </div>

                                <div class="notification-body">
                                    @if($studentRecentNotifications->count() > 0)
                                        @foreach($studentRecentNotifications as $notif)
                                            <a href="{{ route('notifications') }}" class="notification-item {{ !$notif->is_read ? 'unread' : '' }}">
                                                <div class="notification-icon">
                                                    @if($notif->type === 'new_course')
                                                        <i class="fas fa-book-open text-primary"></i>
                                                    @elseif($notif->type === 'new_chapter')
                                                        <i class="fas fa-layer-group text-success"></i>
                                                    @elseif($notif->type === 'new_module')
                                                        <i class="fas fa-file-alt text-info"></i>
                                                    @elseif($notif->type === 'announcement')
                                                        <i class="fas fa-bullhorn text-warning"></i>
                                                    @else
                                                        <i class="fas fa-bell text-info"></i>
                                                    @endif
                                                </div>
                                                <div class="notification-content">
                                                    <p class="notification-title">{{ $notif->title }}</p>
                                                    <p class="notification-text">{{ Str::limit($notif->message, 60) }}</p>
                                                    <span class="notification-time">{{ $notif->created_at->diffForHumans() }}</span>
                                                </div>
                                                @if(!$notif->is_read)
                                                    <span class="badge bg-warning ms-2" style="font-size: 0.6rem;">New</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    @else
                                        <div class="notification-empty">
                                            <i class="fas fa-bell-slash"></i>
                                            <p>No notifications</p>
                                        </div>
                                    @endif
                                </div>

                                @if($studentRecentNotifications->count() > 0)
                                <div class="notification-footer">
                                    <a href="{{ route('notifications') }}" class="view-all-link">View all notifications</a>
                                </div>
                                @endif
                            </div>
                        </li>
                        @endif
                        @endauth

                        <!-- User Profile Card -->
                        <li class="nav-item">
                            @php
                                $user = Auth::user();
                                $profileRoute = 'profile';
                                if ($user) {
                                    if ($user->isTeacher()) {
                                        $profileRoute = 'teacher.profile';
                                    } elseif ($user->isStudent()) {
                                        $profileRoute = 'profile';
                                    } elseif ($user->isAdmin()) {
                                        $profileRoute = 'admin.dashboard';
                                    }
                                }
                            @endphp
                            <a class="btn btn-primary profile-card-btn d-flex align-items-center" href="{{ route($profileRoute) }}" style="border-radius: 25px; padding: 0.5rem 1rem;">
                                @if($user->avatar)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover; border: 2px solid rgba(255, 255, 255, 0.3);">
                                @else
                                    <i class="fas fa-user me-2"></i>
                                @endif
                                <span>{{ $user->name ?? 'User' }}</span>
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
/* ============================================================
   LANGUAGE SWITCHER
   Tombol pemilih bahasa di navbar. Memakai palet biru MersifLab
   (#1A76D1) agar konsisten dengan komponen navbar lainnya.
   ============================================================ */
.ml-lang-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #333 !important;
    font-weight: 500;
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.ml-lang-toggle:hover,
.ml-lang-toggle:focus,
.ml-lang-toggle[aria-expanded="true"] {
    color: #1A76D1 !important;
    border-color: #1A76D1;
    background: #f4f9ff;
}

.ml-lang-toggle .ml-lang-code {
    font-weight: 600;
    letter-spacing: 0.02em;
}

.ml-lang-toggle .ml-lang-caret {
    font-size: 0.65rem;
    transition: transform 0.2s ease;
}

.ml-lang-toggle[aria-expanded="true"] .ml-lang-caret {
    transform: rotate(180deg);
}

.ml-lang-menu {
    min-width: 190px;
    border: 1px solid #e9eef5;
    border-radius: 12px;
    padding: 6px;
    box-shadow: 0 8px 24px rgba(15, 27, 45, 0.12);
}

.ml-lang-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 0.9rem;
    color: #333;
}

.ml-lang-menu .dropdown-item:hover {
    background: #f4f9ff;
    color: #1A76D1;
}

.ml-lang-menu .dropdown-item.active {
    background: #eaf3ff;
    color: #1A76D1;
    font-weight: 600;
}

.ml-lang-menu .ml-lang-flag {
    font-size: 1.05rem;
    line-height: 1;
}

/* Di layar kecil navbar menumpuk, jadi tombol dibuat rata kiri. */
@media (max-width: 991.98px) {
    .ml-lang-toggle {
        width: fit-content;
        margin: 6px 0;
    }
}

/* Profile Card Button Styles */
.profile-card-btn {
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-weight: 500;
}

.profile-card-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    opacity: 0.95;
}

.profile-card-btn i {
    font-size: 0.875rem;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('header.header-section');
        
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }
    });
</script>