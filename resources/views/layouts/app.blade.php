<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MersifLab') - MersifLab</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.png') }}?v={{ time() }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- AI Assistant CSS -->
    <link rel="stylesheet" href="{{ asset('css/ai-assistant.css') }}">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    @yield('styles')

    <!-- Lapisan penyempurnaan visual global (spacing, radius, shadow, tipografi).
         Sengaja dimuat paling akhir agar dapat merapikan gaya halaman
         tanpa mengubah berkas CSS yang sudah ada. -->
    <link rel="stylesheet" href="{{ asset('assets/css/ui-refinements.css') }}">
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>
    
    @include('layouts.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('layouts.footer')
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/custom.js') }}"></script>
    
    <!-- AI Assistant JS -->
    <script src="{{ asset('js/ai-assistant.js') }}"></script>
    
    <!-- Global Error Handler -->
    <script>
        // Show error popup for module approval errors
        @if(session('error') && str_contains(session('error'), 'belum disetujui'))
            Swal.fire({
                icon: 'warning',
                title: 'Access Restricted',
                html: `
                    <div style="text-align: center;">
                        <i class="fas fa-lock" style="font-size: 3rem; color: #ffc107; margin-bottom: 1rem;"></i>
                        <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: #333;">{{ session("error") }}</p>
                    </div>
                `,
                confirmButtonText: 'I Understand',
                confirmButtonColor: '#667eea',
                allowOutsideClick: false,
                allowEscapeKey: true,
                customClass: {
                    popup: 'animated-popup',
                    backdrop: 'swal2-backdrop-smooth'
                },
                showClass: {
                    popup: 'swal2-show-smooth',
                    backdrop: 'swal2-backdrop-show-smooth'
                },
                hideClass: {
                    popup: 'swal2-hide-smooth',
                    backdrop: 'swal2-backdrop-hide-smooth'
                }
            });
        @endif

        // Show error popup for invoice expired errors
        @if(session('error') && str_contains(session('error'), 'kadaluarsa'))
            Swal.fire({
                icon: 'error',
                title: 'Invoice Kadaluarsa',
                html: `
                    <div style="text-align: center;">
                        <i class="fas fa-clock" style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;"></i>
                        <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: #333;">{{ session("error") }}</p>
                        <p style="font-size: 0.9rem; color: #666; margin: 0;">Silakan melakukan pembelian kembali jika masih tertarik dengan course ini.</p>
                    </div>
                `,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#dc3545',
                allowOutsideClick: false,
                allowEscapeKey: true,
                customClass: {
                    popup: 'animated-popup',
                    backdrop: 'swal2-backdrop-smooth'
                },
                showClass: {
                    popup: 'swal2-show-smooth',
                    backdrop: 'swal2-backdrop-show-smooth'
                }
            });
        @endif

        // Handle AJAX errors for expired invoices
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            if (jqxhr.status === 403 && jqxhr.responseJSON && jqxhr.responseJSON.message) {
                var response = jqxhr.responseJSON;
                if (response.message.includes('kadaluarsa')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invoice Kadaluarsa',
                        html: `
                            <div style="text-align: center;">
                                <i class="fas fa-clock" style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;"></i>
                                <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: #333;">${response.message}</p>
                                <p style="font-size: 0.9rem; color: #666; margin: 0;">Silakan melakukan pembelian kembali jika masih tertarik dengan course ini.</p>
                            </div>
                        `,
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#dc3545',
                        allowOutsideClick: false,
                        allowEscapeKey: true,
                        customClass: {
                            popup: 'animated-popup',
                            backdrop: 'swal2-backdrop-smooth'
                        },
                        showClass: {
                            popup: 'swal2-show-smooth',
                            backdrop: 'swal2-backdrop-show-smooth'
                        }
                    });
                } else if (response.message.includes('dibatalkan')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invoice Dibatalkan',
                        html: `
                            <div style="text-align: center;">
                                <i class="fas fa-ban" style="font-size: 3rem; color: #ffc107; margin-bottom: 1rem;"></i>
                                <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: #333;">${response.message}</p>
                                <p style="font-size: 0.9rem; color: #666; margin: 0;">Jika ada pertanyaan, silakan hubungi admin untuk informasi lebih lanjut.</p>
                            </div>
                        `,
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#ffc107',
                        allowOutsideClick: false,
                        allowEscapeKey: true,
                        customClass: {
                            popup: 'animated-popup',
                            backdrop: 'swal2-backdrop-smooth'
                        },
                        showClass: {
                            popup: 'swal2-show-smooth',
                            backdrop: 'swal2-backdrop-show-smooth'
                        }
                    });
                }
            }
        });

        // Show error popup for cancelled invoices
        @if(session('error') && str_contains(session('error'), 'dibatalkan'))
            Swal.fire({
                icon: 'warning',
                title: 'Invoice Dibatalkan',
                html: `
                    <div style="text-align: center;">
                        <i class="fas fa-ban" style="font-size: 3rem; color: #ffc107; margin-bottom: 1rem;"></i>
                        <p style="font-size: 1.1rem; margin-bottom: 0.5rem; color: #333;">{{ session("error") }}</p>
                        <p style="font-size: 0.9rem; color: #666; margin: 0;">Jika ada pertanyaan, silakan hubungi admin untuk informasi lebih lanjut.</p>
                    </div>
                `,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#ffc107',
                allowOutsideClick: false,
                allowEscapeKey: true,
                customClass: {
                    popup: 'animated-popup',
                    backdrop: 'swal2-backdrop-smooth'
                },
                showClass: {
                    popup: 'swal2-show-smooth',
                    backdrop: 'swal2-backdrop-show-smooth'
                }
            });
        @endif

        // Show error popup for other errors
        @if(session('error') && !str_contains(session('error'), 'belum disetujui') && !str_contains(session('error'), 'kadaluarsa') && !str_contains(session('error'), 'dibatalkan'))
            @php
                $errorMessage = session('error');
                // Do not show Google Auth errors on home page if user is already logged in
                // (these errors will be handled on login page)
                $isGoogleAuthError = str_contains($errorMessage, 'sudah terdaftar sebagai') || 
                                     str_contains($errorMessage, 'tidak dapat login sebagai') ||
                                     str_contains($errorMessage, 'Email Google ini sudah digunakan');
            @endphp
            @if(!$isGoogleAuthError && !auth()->check())
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '{{ $errorMessage }}',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545',
                customClass: {
                    popup: 'animated-popup',
                    backdrop: 'swal2-backdrop-smooth'
                },
                showClass: {
                    popup: 'swal2-show-smooth',
                    backdrop: 'swal2-backdrop-show-smooth'
                },
                hideClass: {
                    popup: 'swal2-hide-smooth',
                    backdrop: 'swal2-backdrop-hide-smooth'
                }
            });
            @endif
        @endif
    </script>
    
    <style>
        /* Smooth animations for popup */
        .animated-popup {
            animation: slideInDown 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-30px) scale(0.95);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }
        
        /* Smooth backdrop show animation */
        .swal2-backdrop-show-smooth {
            animation: fadeInBackdrop 0.25s ease-out !important;
        }
        
        /* Smooth backdrop hide animation */
        .swal2-backdrop-hide-smooth {
            animation: fadeOutBackdrop 0.3s ease-out !important;
        }
        
        @keyframes fadeInBackdrop {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeOutBackdrop {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
        
        /* Smooth popup show animation */
        .swal2-show-smooth {
            animation: slideInDownSmooth 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }
        
        @keyframes slideInDownSmooth {
            from {
                transform: translateY(-30px) scale(0.95);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }
        
        /* Smooth popup hide animation */
        .swal2-hide-smooth {
            animation: fadeOutUpSmooth 0.3s cubic-bezier(0.55, 0.055, 0.675, 0.19) !important;
        }
        
        @keyframes fadeOutUpSmooth {
            from {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            to {
                transform: translateY(-15px) scale(0.96);
                opacity: 0;
            }
        }
        
        /* Override default SweetAlert animations for smoother transitions */
        .swal2-popup.swal2-hide {
            animation: fadeOutUpSmooth 0.3s cubic-bezier(0.55, 0.055, 0.675, 0.19) !important;
        }
        
        /* Ensure smooth container transitions */
        .swal2-container {
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .swal2-container.swal2-backdrop-show {
            animation: fadeInBackdrop 0.25s ease-out !important;
        }
        
        .swal2-container.swal2-backdrop-hide {
            animation: fadeOutBackdrop 0.3s ease-out !important;
        }
        
        /* Sidebar Styles for Teacher Pages */
        .sidebar {
            width: 200px;
            flex-shrink: 0;
            background: linear-gradient(180deg, #FFFFFF 0%, #F0F2F5 100%);
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-x: hidden;
            z-index: 1000;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
            flex-shrink: 0;
        }

        .sidebar-logo {
            flex-grow: 1;
        }

        .sidebar-logo img {
            max-width: 90px;
            height: auto;
            transition: all 0.3s ease;
        }

        .sidebar-toggler {
            background: none;
            border: none;
            font-size: 20px;
            color: #6b7280;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .sidebar-toggler:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
            min-height: 0;
        }

        .sidebar-menu li {
            margin-bottom: 4px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
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

        .sidebar-menu a span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Main content adjustment */
        .main-content {
            margin-left: 200px;
            flex: 1;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .content-area {
            flex: 1;
            padding: 20px;
            background: #f8fafc;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 8px 0;
        }

        .page-title p {
            font-size: 0.9rem;
            color: #6b7280;
            margin: 0;
        }

        /* Badge styling to match admin layout exactly */
        .badge {
            font-size: 10px;
            padding: 4px 8px;
            font-weight: 500;
            border-radius: 4px;
            line-height: 1;
            display: inline-block;
        }

        /* Specific sidebar badge styling */
        .sidebar-menu li a .badge {
            font-size: 10px !important;
            padding: 4px 8px !important;
            font-weight: 500 !important;
            border-radius: 4px !important;
            line-height: 1 !important;
            display: inline-block !important;
            min-width: 18px;
            text-align: center;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                left: -280px;
                background: white;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }

            .sidebar.mobile-show,
            .sidebar.show {
                left: 0 !important;
            }

            .main-content {
                margin-left: 0;
            }
        }

        /* Desktop: Pastikan layout desktop tetap sama */
        @media (min-width: 769px) {
            .sidebar {
                left: 0 !important;
                display: flex !important;
                position: fixed !important;
            }

            .main-content {
                margin-left: 200px !important;
            }

            .sidebar-overlay {
                display: none !important;
            }
        }

        /* Sidebar Overlay untuk Mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
            visibility: visible;
        }

        .sidebar-overlay.hidden {
            display: none;
            opacity: 0;
            visibility: hidden;
        }
    </style>
    
    <script>
        // Sidebar functions for teacher pages
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('minimized');
                overlay.classList.toggle('hidden');
            }
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('mobile-show');
                overlay.classList.toggle('show');
            }
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar && overlay) {
                sidebar.classList.remove('mobile-show');
                overlay.classList.remove('show');
            }
        }

        // Mobile sidebar handling
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('minimized');
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-show');
                    overlay.classList.remove('show');
                }
            });

            // Handle overlay click
            if (overlay) {
                overlay.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        closeMobileSidebar();
                    }
                });
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>