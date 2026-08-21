<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'Admin Dashboard') - MersifLab</title>
    
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}?v={{ time() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-pagination.css') }}">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        /* Sticky Notification Footer */
        .notification-dropdown .notification-footer-sticky {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            z-index: 10;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
        }

        .notification-dropdown .notification-footer-sticky .dropdown-item {
            padding: 12px 16px;
            margin: 0;
            border-radius: 0;
            transition: background-color 0.2s ease;
        }

        .notification-dropdown .notification-footer-sticky .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        /* Custom scrollbar for notifications */
        .notification-scrollable::-webkit-scrollbar {
            width: 4px;
        }

        .notification-scrollable::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }

        .notification-scrollable::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }

        .notification-scrollable::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        body {
            background: linear-gradient(180deg, #E2E8F0 10%, #4B5F8A 90%);
            /* This ensures the gradient stays locked to the screen and doesn't run out when scrolling */
            background-attachment: fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .sidebar {
            width: 200px;
            flex-shrink: 0;
            background: linear-gradient(180deg, #FFFFFF 0%, #F0F2F5 100%);
            border-right: 1px solid #e0e0e0;
            padding: 12px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .sidebar.minimized {
            width: 80px;
            align-items: center;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            position: relative;
            flex-shrink: 0;
        }

        .sidebar.minimized .sidebar-header {
            justify-content: center;
        }

        .sidebar-logo {
            flex-grow: 1;
        }

        .sidebar.minimized .sidebar-logo {
            padding-left: 0;
        }

        .sidebar-logo img {
            max-width: 90px;
            height: auto;
            transition: all 0.3s ease;
        }

        .sidebar.minimized .sidebar-logo img {
            max-width: 40px;
        }

        .sidebar-toggler {
            background: none;
            border: none;
            font-size: 20px;
            color: #64748b;
            cursor: pointer;
            padding: 5px;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }

        .sidebar.minimized .sidebar-toggler {
            right: -15px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
        }

        .sidebar-menu li {
            margin-bottom: 4px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            color: #475569;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #e0f2fe;
            color: #3b82f6;
        }

        .sidebar-menu a i {
            font-size: 15px;
            width: 16px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar.minimized .sidebar-menu a {
            justify-content: center;
            padding: 10px;
        }

        .sidebar.minimized .sidebar-menu a span {
            display: none;
        }

        .sidebar.minimized .sidebar-menu a {
            gap: 0;
        }

        .main-content {
            margin-left: 200px;
            padding: 10px 20px 20px 20px;
            transition: margin-left 0.3s ease;
        }

        .sidebar.minimized + .main-content {
            margin-left: 80px;
        }

        .topbar {
            background: transparent;
            box-shadow: none;
            position: sticky;
            top: 0;
            z-index: 900;
            padding: 15px 0px 15px 0px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            width: 100%;
        }

        .topbar.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 25px;
            position: fixed;
            left: 200px;
            right: 0;
            width: calc(100vw - 200px);
            transition: all 0.3s ease;
        }

        .sidebar.minimized ~ .main-content .topbar.scrolled {
            left: 80px;
            width: calc(100vw - 80px);
        }

        .sidebar.minimized .sidebar-logo {
            display: none;
        }

        .sidebar.minimized .sidebar-header {
            justify-content: center;
            padding: 10px 0;
        }


        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        .topbar-icon-btn {
            background: rgba(255, 255, 255, 0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            color: #64748b;
            font-size: 18px;
            position: relative;
            transition: all 0.3s;
            text-decoration: none;
        }

        .topbar-icon-btn:hover {
            background: white;
            color: #2F80ED;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .topbar.scrolled .topbar-icon-btn {
            background: rgba(0, 0, 0, 0.1);
            color: #475569;
        }

        .topbar.scrolled .topbar-icon-btn:hover {
            background: white;
            color: #2F80ED;
        }

        .notification-dot {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 8px;
            height: 8px;
            background: #EB5757;
            border-radius: 50%;
            border: 1px solid white;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.5);
            transition: background 0.3s;
        }

        .user-dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.9);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            line-height: 1.2;
        }

        .user-role {
            font-size: 11px;
            color: #64748b;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 10px;
            margin-top: 10px !important;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 13px;
            color: #475569;
        }

        .dropdown-item:hover {
            background-color: #F1F5F9;
            color: #2F80ED;
        }

        .dropdown-item.text-danger:hover {
            background-color: #FEF2F2;
            color: #DC2626;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-user img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .page-title {
            margin-bottom: 12px;
        }

        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333333;
            margin: 0;
        }

        .page-title p {
            color: #828282;
            margin: 5px 0 0 0;
            font-size: 13px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .stat-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 12px;
        }

        .stat-card-label {
            font-size: 13px;
            color: #828282;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .stat-card-value {
            font-size: 28px;
            font-weight: 700;
            color: #333333;
        }

        .stat-card-change {
            font-size: 12px;
            margin-top: 10px;
            font-weight: 600;
        }

        .stat-card-change.positive {
            color: #27AE60;
        }

        .stat-card-change.negative {
            color: #EB5757;
        }

        .icon-teacher {
            background: #2F80ED;
            color: white;
        }

        .icon-student {
            background: #27AE60;
            color: white;
        }

        .icon-course {
            background: #EB5757;
            color: white;
        }

        .card-content {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .card-content-title {
            font-size: 18px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Sidebar Overlay untuk Mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Mobile Header Left Icons */
        .mobile-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mobile-icon-btn {
            background: rgba(255, 255, 255, 0.8);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            color: #64748b;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-icon-btn:hover {
            background: white;
            color: #2F80ED;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .mobile-search-container {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            width: 100%;
            padding: 10px 15px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 902;
        }

        .mobile-search-container.show {
            display: block !important;
        }

        .mobile-search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            transition: all 0.3s ease;
        }

        .mobile-search-input:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 3px rgba(47, 128, 237, 0.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                left: -280px;
                background: white;
                z-index: 1050;
                transition: left 0.3s ease;
                position: fixed;
                top: 0;
                height: 100vh;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }

            .sidebar.mobile-show,
            .sidebar.show {
                left: 0 !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 10px 15px 20px 15px;
            }

            .topbar {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 10px 15px;
                position: relative;
            }

            .topbar.scrolled {
                left: 0 !important;
                width: 100vw !important;
            }

            .mobile-header-left {
                display: flex !important;
            }

            .topbar-right {
                gap: 8px;
                margin-left: auto;
            }

            .topbar-icon-btn {
                width: 36px;
                height: 36px;
                font-size: 15px;
            }

            .user-avatar {
                width: 32px;
                height: 32px;
            }

            .user-dropdown-toggle {
                padding: 3px 5px;
            }

            .user-info {
                display: none;
            }

            .page-title h1 {
                font-size: 22px;
            }

            .page-title p {
                font-size: 13px;
            }

            .stat-card {
                padding: 16px;
            }

            .card-content {
                padding: 16px;
            }

            .card-content-title {
                font-size: 15px;
            }

            /* Responsive tables */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                font-size: 12px;
            }

            table th,
            table td {
                padding: 8px 6px;
                white-space: nowrap;
            }

            /* Responsive buttons */
            .btn {
                font-size: 12px;
                padding: 6px 12px;
            }

            .btn-sm {
                font-size: 11px;
                padding: 4px 8px;
            }

            /* Responsive search bar */
            .page-title {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 15px;
            }

            .page-title > div:last-child {
                width: 100%;
                max-width: 100%;
            }

            /* Responsive dropdowns */
            .dropdown-menu {
                max-width: calc(100vw - 30px);
            }

            /* Responsive cards */
            .row {
                margin-left: -8px;
                margin-right: -8px;
            }

            .row > * {
                padding-left: 8px;
                padding-right: 8px;
            }

            /* Ensure tables are scrollable on mobile */
            .table-responsive {
                -webkit-overflow-scrolling: touch;
                overflow-x: auto;
            }

            /* Responsive forms */
            .form-control,
            .form-select {
                font-size: 13px;
            }

            /* Responsive modals */
            .modal-dialog {
                margin: 10px;
            }

            .modal-content {
                border-radius: 12px;
            }

            /* Responsive badges */
            .badge {
                font-size: 10px;
                padding: 4px 8px;
            }

            /* Responsive stat cards */
            .stat-card-modern {
                margin-bottom: 12px;
            }

            .stat-icon-container {
                width: 60px !important;
                height: 60px !important;
            }

            .stat-icon-container i {
                font-size: 2rem !important;
            }

            .stat-value {
                font-size: 1.75rem !important;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 260px;
            }

            .main-content {
                padding: 8px 12px 16px 12px;
            }

            .topbar {
                padding: 8px 12px;
            }

            .page-title h1 {
                font-size: 20px;
            }

            .stat-card {
                padding: 12px;
            }

            .card-content {
                padding: 12px;
            }

            table {
                font-size: 11px;
            }

            table th,
            table td {
                padding: 6px 4px;
            }
        }

        /* Desktop: Pastikan layout desktop tetap sama - semua ukuran desktop */
        @media (min-width: 769px) {
            .sidebar {
                left: 0 !important;
                display: flex !important;
                position: fixed !important;
                z-index: 1000 !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .main-content {
                margin-left: 200px !important;
            }

            .sidebar.minimized + .main-content,
            .sidebar.minimized ~ .main-content {
                margin-left: 80px !important;
            }

            .sidebar-overlay {
                display: none !important;
            }

            .mobile-header-left {
                display: none !important;
            }

            /* Pastikan sidebar tidak tersembunyi di desktop */
            .sidebar.mobile-show,
            .sidebar.show {
                left: 0 !important;
                display: flex !important;
            }
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="REKA MERSIF Logo" onerror="this.src='{{ asset('images/logo.png') }}'">
            </div>
            <button class="sidebar-toggler" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="@if(request()->routeIs('admin.dashboard')) active @endif">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.courses.index') }}" class="@if(request()->routeIs('admin.courses*')) active @endif">
                    <i class="fas fa-book"></i>
                    <span>Courses</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.free-classes.index') }}" class="@if(request()->routeIs('admin.free-classes*')) active @endif">
                    <i class="fas fa-gift"></i>
                    <span>Free Course</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.categories.index') }}" class="@if(request()->routeIs('admin.categories*')) active @endif">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.teachers.index') }}" class="@if(request()->routeIs('admin.teachers*')) active @endif">
                    <i class="fas fa-chalkboard-user"></i>
                    <span>Teachers</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.finance.dashboard') }}" class="@if(request()->routeIs('admin.finance*')) active @endif">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Finance Management</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.students.index') }}" class="@if(request()->routeIs('admin.students*')) active @endif">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
            </li>
            <li>
                <!-- Subscriptions menu removed: subscription info moved into Students pages -->
            </li>
            <li>
                <a href="{{ route('admin.admins.index') }}" class="@if(request()->routeIs('admin.admins*')) active @endif">
                    <i class="fas fa-user-shield"></i>
                    <span>Admins</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.activities.index') }}" class="@if(request()->routeIs('admin.activities*')) active @endif">
                    <i class="fas fa-history"></i>
                    <span>Activities</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.messages.index') }}" class="@if(request()->routeIs('admin.messages*')) active @endif">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    @php
                        $sidebarUnreadMessages = App\Models\Message::where('is_read', false)->count();
                    @endphp
                    @if($sidebarUnreadMessages > 0)
                        <span class="badge bg-danger ms-2" style="font-size: 10px;">{{ $sidebarUnreadMessages > 9 ? '9+' : $sidebarUnreadMessages }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.teacher-applications.index') }}" class="@if(request()->routeIs('admin.teacher-applications*')) active @endif">
                    <i class="fas fa-user-graduate"></i>
                    <span>Teacher Applications</span>
                    @php
                        $pendingTeacherApplications = App\Models\TeacherApplication::where('status', 'pending')->count();
                    @endphp
                    @if($pendingTeacherApplications > 0)
                        <span class="badge bg-danger ms-2" style="font-size: 10px;">{{ $pendingTeacherApplications > 9 ? '9+' : $pendingTeacherApplications }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.testimonials.index') }}" class="@if(request()->routeIs('admin.testimonials*')) active @endif">
                    <i class="fas fa-comments"></i>
                    <span>Testimonials</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.notifications.index') }}" class="@if(request()->routeIs('admin.notifications*')) active @endif">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    @php
                        $sidebarUnreadCount = auth()->user()->unreadNotificationsCount();
                    @endphp
                    @if($sidebarUnreadCount > 0)
                        <span class="badge bg-danger ms-2" style="font-size: 10px;">{{ $sidebarUnreadCount > 9 ? '9+' : $sidebarUnreadCount }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}" class="@if(request()->routeIs('admin.settings*')) active @endif">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="mobile-header-left d-md-none">
                <button class="mobile-icon-btn" onclick="toggleMobileSidebar()" aria-label="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="topbar-right">
                @php
                    $unreadMessages = App\Models\Message::where('is_read', false)->count();
                @endphp
                <div class="dropdown">
                    <a href="#" class="topbar-icon-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Messages" role="button" onclick="event.preventDefault();">
                        <i class="fas fa-envelope"></i>
                        @if($unreadMessages > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; padding: 2px 6px; min-width: 18px;">
                                {{ $unreadMessages > 9 ? '9+' : $unreadMessages }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 320px; max-height: 400px; overflow: hidden; position: relative;">
                        <li><h6 class="dropdown-header d-flex justify-content-between align-items-center">
                            <span>Messages</span>
                            @if($unreadMessages > 0)
                                <span class="badge bg-danger" style="font-size: 10px;">{{ $unreadMessages }} unread</span>
                            @endif
                        </h6></li>
                        
                        <!-- Scrollable Content Area -->
                        <div class="notification-scrollable" style="max-height: 320px; overflow-y: auto;">
                            @php
                                $recentMessages = App\Models\Message::latest()->take(5)->get();
                            @endphp
                            @forelse($recentMessages as $message)
                                <li>
                                    <a class="dropdown-item {{ !$message->is_read ? 'bg-light fw-bold' : '' }}" 
                                       href="{{ route('admin.messages.show', $message->id) }}"
                                       style="white-space: normal; padding: 12px; text-decoration: none; color: inherit;"
                                       onclick="if(event.target.tagName !== 'A') { window.location.href='{{ route('admin.messages.show', $message->id) }}'; }">
                                        <div style="font-size: 13px; margin-bottom: 4px; color: #333;">{{ $message->subject ?? 'No Subject' }}</div>
                                        <div style="font-size: 12px; color: #666; margin-bottom: 4px;">{{ Str::limit($message->message ?? 'No message content', 60) }}</div>
                                        <small style="color: #999; font-size: 11px;">{{ $message->created_at->diffForHumans() }}</small>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item-text text-muted text-center py-3">Tidak ada pesan</span></li>
                            @endforelse
                        </div>
                        
                        <!-- Sticky Footer -->
                        <li class="notification-footer-sticky">
                            <hr class="dropdown-divider" style="margin: 0;">
                            <a class="dropdown-item text-center fw-bold" href="{{ route('admin.messages.index') }}" style="color: #2F80ED;">View All Messages</a>
                        </li>
                    </ul>
                </div>

                @php
                    $unreadNotifications = auth()->user()->unreadNotificationsCount();
                @endphp
                <div class="dropdown">
                    <a href="#" class="topbar-icon-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" role="button" onclick="event.preventDefault();">
                        <i class="fas fa-bell"></i>
                        @if($unreadNotifications > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; padding: 2px 6px; min-width: 18px;">
                                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 320px; max-height: 400px; overflow: hidden; position: relative;">
                        <li><h6 class="dropdown-header d-flex justify-content-between align-items-center">
                            <span>Notifications</span>
                            @if($unreadNotifications > 0)
                                <span class="badge bg-danger" style="font-size: 10px;">{{ $unreadNotifications }} unread</span>
                            @endif
                        </h6></li>
                        
                        <!-- Scrollable Content Area -->
                        <div class="notification-scrollable" style="max-height: 320px; overflow-y: auto;">
                            @php
                                $recentNotifications = auth()->user()->notifications()->latest()->take(5)->get();
                            @endphp
                            @forelse($recentNotifications as $notif)
                                <li>
                                    <a class="dropdown-item {{ !$notif->is_read ? 'bg-light fw-bold' : '' }}" 
                                       href="{{ route('admin.notifications.show', $notif->id) }}"
                                       style="white-space: normal; padding: 12px; text-decoration: none; color: inherit;"
                                       onclick="if(event.target.tagName !== 'A') { window.location.href='{{ route('admin.notifications.show', $notif->id) }}'; }">
                                        <div style="font-size: 13px; margin-bottom: 4px; color: #333;">{{ $notif->title }}</div>
                                        <div style="font-size: 12px; color: #666; margin-bottom: 4px;">{{ Str::limit($notif->message, 60) }}</div>
                                        <small style="color: #999; font-size: 11px;">{{ $notif->created_at->diffForHumans() }}</small>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item-text text-muted text-center py-3">Tidak ada notifikasi</span></li>
                            @endforelse
                        </div>
                        
                        <!-- Sticky Footer -->
                        <li class="notification-footer-sticky">
                            <hr class="dropdown-divider" style="margin: 0;">
                            <a class="dropdown-item text-center fw-bold" href="{{ route('admin.notifications.index') }}" style="color: #2F80ED;">View All Notifications</a>
                        </li>
                    </ul>
                </div>

                <div class="dropdown">
                    <a href="#" class="user-dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-info d-none d-md-block">
                            <span class="user-name">{{ auth()->user()->name ?? 'Admin User' }}</span>
                            <span class="user-role">Administrator</span>
                        </div>
                        @if(auth()->user()->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->avatar))
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="user-avatar">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=2F80ED&color=fff" alt="User" class="user-avatar">
                        @endif
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.admins.show', auth()->user()->id) }}">
                                <i class="fas fa-user me-2"></i> My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Prevent back button after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was loaded from cache (back button)
                window.location.reload();
            }
        });
        
        // Clear cache on page load
        if (performance.navigation.type === 1) {
            // Page was reloaded
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    
    <script>
        // Desktop sidebar toggle (existing behavior)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            // Deteksi apakah mobile atau desktop
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Mobile: Toggle mobile-show class untuk off-canvas sidebar
                sidebar.classList.toggle('mobile-show');
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            } else {
                // Desktop: Toggle minimized class (existing behavior)
                sidebar.classList.toggle('minimized');
            }
        }

        // Mobile sidebar toggle
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (!sidebar || !overlay) {
                console.error('Sidebar or overlay element not found');
                return;
            }
            
            const isOpen = sidebar.classList.contains('mobile-show') || sidebar.classList.contains('show');
            
            if (isOpen) {
                // Close sidebar
                sidebar.classList.remove('mobile-show');
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            } else {
                // Open sidebar
                sidebar.classList.add('mobile-show');
                sidebar.classList.add('show');
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        // Close mobile sidebar
        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (!sidebar || !overlay) {
                return;
            }
            
            sidebar.classList.remove('mobile-show');
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }


        // Initialize overlay click handler
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('sidebarOverlay');
            
            overlay.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeMobileSidebar();
                }
            });
        });

        // Handle window resize untuk menutup sidebar mobile saat resize ke desktop
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (!sidebar || !overlay) {
                return;
            }
            
            if (window.innerWidth > 768) {
                // If resizing to desktop, ensure sidebar is visible and close mobile overlay
                sidebar.classList.remove('mobile-show');
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
                // Ensure sidebar visible on desktop
                sidebar.style.left = '0';
                sidebar.style.display = 'flex';
            } else {
                // If resizing to mobile, reset sidebar position if not open
                if (!sidebar.classList.contains('mobile-show') && !sidebar.classList.contains('show')) {
                    sidebar.style.left = '-280px';
                }
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mobileToggleBtn = document.querySelector('.mobile-icon-btn');
            
            if (window.innerWidth <= 768 && sidebar && overlay) {
                const isSidebarOpen = sidebar.classList.contains('mobile-show') || sidebar.classList.contains('show');
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggle = mobileToggleBtn && mobileToggleBtn.contains(event.target);
                
                if (isSidebarOpen && !isClickInsideSidebar && !isClickOnToggle) {
                    closeMobileSidebar();
                }
            }
        });

        // Scroll detection for topbar
        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const topbar = document.querySelector('.topbar');
            
            if (scrollTop > 50) {
                topbar.classList.add('scrolled');
            } else {
                topbar.classList.remove('scrolled');
            }
            
            lastScrollTop = scrollTop;
        });
    </script>
    
    <!-- Admin Pagination Enhancement Script -->
    <script src="{{ asset('js/admin-pagination.js') }}"></script>
    
    @yield('scripts')
</body>
</html>
