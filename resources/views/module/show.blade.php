@extends('layouts.app')

@section('title', $module->title . ' - ' . $class->name)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/welcome.css') }}">
<link rel="stylesheet" href="{{ asset('css/module.css') }}">
@endsection

@section('content')
<!-- Sidebar Overlay (for mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Close button (visible when sidebar is open) -->
<button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close sidebar">
    <i class="fas fa-times"></i>
</button>

<div class="module-container">
    <!-- Sidebar Course Navigation -->
    <div class="module-sidebar" id="moduleSidebar">
        {{-- Parameter ?module= membawa modul yang sedang dipelajari, supaya
             halaman detail course langsung menyorot chapter tempat modul ini
             berada - bukan mendarat di paling atas. --}}
        <a href="{{ route('course.detail', $class->id) }}?module={{ $module->id }}"
           class="back-to-course-link text-decoration-none mb-3 d-block"
           data-last-module-id="{{ $module->id }}"
           data-last-chapter-id="{{ $chapter->id }}"
           data-course-id="{{ $class->id }}">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Course</span>
        </a>

        <h5 class="fw-bold mb-3">{{ $class->name }}</h5>

        @if($isEnrolled)
            <!-- Progress Bar -->
            <div class="course-progress">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small>Your Progress</small>
                    <strong>{{ number_format($progress, 0) }}%</strong>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif

        <hr class="my-3">

        <!-- Chapters & Modules List -->
        <ul class="chapter-list">
            @foreach($class->chapters as $chIndex => $ch)
                @php
                    // Check if all modules in previous chapters are completed
                    $allPreviousChaptersCompleted = true;
                    if ($chIndex > 0) {
                        for ($i = 0; $i < $chIndex; $i++) {
                            $prevChapter = $class->chapters[$i];
                            foreach ($prevChapter->modules as $prevMod) {
                                if (!isset($completedModules) || !in_array($prevMod->id, $completedModules)) {
                                    $allPreviousChaptersCompleted = false;
                                    break 2;
                                }
                            }
                        }
                    }
                @endphp
                <li class="chapter-item">
                    <div class="chapter-header {{ $ch->id == $chapter->id ? 'active' : '' }}" data-chapter-id="{{ $ch->id }}">
                        <span>{{ $ch->title }}</span>
                        <i class="fas fa-chevron-down chapter-toggle-icon"></i>
                    </div>
                    <ul class="module-list" id="chapter-{{ $ch->id }}" data-chapter-index="{{ $chIndex }}">
                        @foreach($ch->modules as $modIndex => $mod)
                            @php
                                $isCompleted = isset($completedModules) && in_array($mod->id, $completedModules);
                                
                                // Check if this module is unlocked
                                $isUnlocked = $allPreviousChaptersCompleted;
                                
                                // If previous chapters are completed, check module sequence in current chapter
                                if ($isUnlocked && $modIndex > 0) {
                                    $previousModuleInChapter = $ch->modules[$modIndex - 1];
                                    $isUnlocked = isset($completedModules) && in_array($previousModuleInChapter->id, $completedModules);
                                }
                                
                                // Current module is always unlocked (user is already viewing it)
                                if ($mod->id == $module->id) {
                                    $isUnlocked = true;
                                }
                                
                                // Completed modules are always unlocked (can be reviewed)
                                if ($isCompleted) {
                                    $isUnlocked = true;
                                }
                                
                                $lockStatus = $isUnlocked ? 'unlocked' : 'locked';
                            @endphp
                            <li>
                                <a href="{{ $isUnlocked ? route('module.show', [$class->id, $ch->id, $mod->id]) : 'javascript:void(0)' }}" 
                                   class="module-link {{ $mod->id == $module->id ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }} {{ $lockStatus }}"
                                   data-module-id="{{ $mod->id }}"
                                   {{ !$isUnlocked ? 'onclick="return false;"' : '' }}>
                                    <div class="module-link-content">
                                        <div class="module-link-title">{{ $mod->title }}</div>
                                        <div class="module-link-meta">
                                            @if(auth()->user() && auth()->user()->isAdmin())
                                                @if($mod->approval_status === 'pending_approval')
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @elseif($mod->approval_status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @elseif($mod->approval_status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @endif
                                            @endif
                                            
                                            @if($mod->type === 'video')
                                                <i class="fas fa-file-video"></i>
                                                <span>Video</span>
                                            @elseif($mod->type === 'document')
                                                <i class="fas fa-file-pdf"></i>
                                                <span>PDF</span>
                                            @elseif($mod->type === 'text')
                                                <i class="fas fa-file-alt"></i>
                                                <span>Text</span>
                                            @else
                                                <i class="fas fa-file"></i>
                                                <span>{{ $mod->type_label ?? 'Unknown' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="module-content" id="moduleContent">
        <!-- Fullscreen Header (shown when sidebar is collapsed) -->
        <div class="fullscreen-header" id="fullscreenHeader">
            <div class="fullscreen-header-content">
                <div class="fullscreen-course-info">
                    <h3 class="fullscreen-course-title">{{ $class->name }}</h3>
                    @if($isEnrolled)
                        <div class="fullscreen-progress">
                            <div class="fullscreen-progress-bar">
                                <div class="fullscreen-progress-fill" style="width: {{ $progress }}%"></div>
                            </div>
                            <span class="fullscreen-progress-text">{{ number_format($progress, 0) }}% Complete</span>
                        </div>
                    @endif
                </div>
                <button class="btn-detail-chapter" id="btnDetailChapter">
                    <i class="fas fa-list me-2"></i>Detail Chapter
                </button>
            </div>
        </div>

        <!-- Module Header -->
        <div class="module-header">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h2 class="fw-bold mb-0">{{ $module->title }}</h2>
                    @if(auth()->user() && auth()->user()->isAdmin())
                        @if($module->approval_status === 'pending_approval')
                            <span class="badge bg-warning text-dark">Pending Approval</span>
                        @elseif($module->approval_status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @elseif($module->approval_status === 'approved')
                            <span class="badge bg-success">Approved</span>
                        @endif
                    @endif
                </div>
                <p class="text-muted mb-0">{{ $chapter->title }}</p>
            </div>
            @if($isEnrolled)
                @php
                    $isModuleCompleted = isset($completedModules) && in_array($module->id, $completedModules);
                    $isLastModule = $nextModule === null;
                @endphp
                @if($isModuleCompleted)
                    <button class="btn btn-success" disabled>
                        <i class="fas fa-check-circle me-2"></i>Completed
                    </button>
                @elseif($isLastModule)
                    <!-- For last module, show Complete Course button in header -->
                    <button class="btn btn-success" id="completeCourseHeaderBtn" type="button">
                        <i class="fas fa-trophy me-2"></i>Complete Course
                    </button>
                @else
                    <button class="btn btn-primary" id="markCompleteBtn">
                        <i class="fas fa-check me-2"></i>Mark as Complete
                    </button>
                @endif
            @endif
        </div>

        <!-- Module Content Based on Type -->
        @if($module->type === 'video')
            <!-- Video Module -->
            <div class="video-player-container">
                @if($module->video_url)
                    @php
                        $videoId = null;
                        $videoType = null;
                        
                        // Check for YouTube
                        if (str_contains($module->video_url, 'youtube.com') || str_contains($module->video_url, 'youtu.be')) {
                            // Try multiple YouTube URL patterns
                            if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})(?:\?|$|&)/', $module->video_url, $matches)) {
                                $videoId = $matches[1];
                                $videoType = 'youtube';
                            }
                            elseif (preg_match('/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/', $module->video_url, $matches)) {
                                $videoId = $matches[1];
                                $videoType = 'youtube';
                            }
                            elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $module->video_url, $matches)) {
                                $videoId = $matches[1];
                                $videoType = 'youtube';
                            }
                            elseif (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $module->video_url, $matches)) {
                                $videoId = $matches[1];
                                $videoType = 'youtube';
                            }
                        }
                        // Check for Vimeo
                        elseif (str_contains($module->video_url, 'vimeo.com')) {
                            if (preg_match('/vimeo\.com\/(\d+)/', $module->video_url, $matches)) {
                                $videoId = $matches[1];
                                $videoType = 'vimeo';
                            }
                        }
                    @endphp
                    
                    @if($videoType === 'youtube' && $videoId)
                        <div class="youtube-video-wrapper">
                            <iframe id="youtubePlayer" 
                                    src="https://www.youtube.com/embed/{{ $videoId }}?rel=0&modestbranding=1&controls=1&disablekb=1&iv_load_policy=3&playsinline=1&showinfo=0&cc_load_policy=0&fs=0" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                    @elseif($videoType === 'vimeo' && $videoId)
                        <div class="youtube-video-wrapper">
                            <iframe src="https://player.vimeo.com/video/{{ $videoId }}" 
                                    frameborder="0" 
                                    allow="autoplay; fullscreen; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100 text-white" style="min-height: 400px;">
                            <div class="text-center">
                                <i class="fas fa-play-circle fa-4x mb-3"></i>
                                <p>Video Player</p>
                                <p class="small mb-3">Unable to load video. Please check the video URL.</p>
                                <a href="{{ $module->video_url }}" target="_blank" class="btn btn-light">
                                    <i class="fas fa-external-link-alt me-2"></i>Open Video in New Tab
                                </a>
                            </div>
                        </div>
                    @endif
                @elseif($module->file_path && ($canAccessFile ?? false))
                    <video controls controlsList="nodownload" style="width: 100%; height: 100%;" oncontextmenu="return false;">
                        <source src="{{ route('module.file', [$class->id, $chapter->id, $module->id]) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                @elseif($module->file_path)
                    <div class="d-flex align-items-center justify-content-center h-100 text-white" style="min-height: 300px;">
                        <div class="text-center">
                            <i class="fas fa-lock fa-3x mb-3"></i>
                            <p class="mb-1">Preview terbatas</p>
                            <p class="small mb-0">Enroll & login untuk menonton video ini.</p>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center justify-content-center h-100 text-white">
                        <div class="text-center">
                            <i class="fas fa-play-circle fa-4x mb-3"></i>
                            <p>Video Player</p>
                            <p class="small">Video content will be displayed here</p>
                        </div>
                    </div>
                @endif
            </div>

        @elseif($module->type === 'document')
            <!-- PDF/Document Module -->
            <div class="pdf-viewer-container">
            @if($module->file_path && ($canAccessFile ?? false))
                <div id="pdf-viewer-wrapper" style="width: 100%; height: auto; background: #f5f5f5; position: relative; user-select: none; border: 1px solid #ddd; border-radius: 4px; padding: 20px 0; margin-bottom: 20px;">
                    <div id="pdf-container" style="width: 100%; height: auto; overflow: visible; background: #525252; display: flex; justify-content: center; align-items: flex-start; padding: 30px;">
                        <div id="pdf-canvas-wrapper" style="position: relative; display: inline-block; background: white;">
                            <canvas id="pdf-canvas" style="display: block; max-width: 100%;"></canvas>
                            <div id="pdf-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; background: transparent; user-select: none; -webkit-user-select: none; -moz-user-select: none;"></div>
                        </div>
                    </div>
                    
                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// Fallback PDF.js loader if CDN fails
if (typeof pdfjsLib === 'undefined') {
    console.warn('Primary PDF.js CDN failed, loading fallback...');
    document.write('<script src="https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.min.js"><\/script>');
    document.write('<script>if (typeof pdfjsLib !== "undefined") { pdfjsLib.GlobalWorkerOptions.workerSrc = "https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.worker.min.js"; }<\/script>');
}
</script>
                <script>
                // PDF.js implementation with enhanced error handling
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('=== PDF VIEWER INIT ===');
                    console.log('Module type:', '{{ $module->type }}');
                    console.log('File path:', '{{ $module->file_path }}');
                    console.log('Can access file:', '{{ $canAccessFile ?? false }}');
                    
                    // Wait for PDF.js to load
                    function initPDFViewer() {
                        if (typeof pdfjsLib === 'undefined') {
                            console.error('PDF.js library not loaded, retrying...');
                            document.getElementById('pdf-container').innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p>Loading PDF viewer...</p></div>';
                            setTimeout(initPDFViewer, 1000);
                            return;
                        }
                        
                        console.log('PDF.js library loaded successfully');
                        
                        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                        
                        const pdfUrl = '{{ route("module.file", [$class->id, $chapter->id, $module->id]) }}';
                        const canvas = document.getElementById('pdf-canvas');
                        const container = document.getElementById('pdf-container');
                        const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
                        const overlay = document.getElementById('pdf-overlay');
                        const pageInfo = document.getElementById('pdf-page-info');
                        const prevBtn = document.getElementById('pdf-prev-btn');
                        const nextBtn = document.getElementById('pdf-next-btn');
                        
                        console.log('PDF URL:', pdfUrl);
                        console.log('Elements found:', { canvas, container, pageInfo, prevBtn, nextBtn });
                        
                        if (!canvas || !container) {
                            console.error('Required PDF elements not found');
                            container.innerHTML = '<div class="text-center p-4"><i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i><p>PDF viewer elements not found. Please refresh the page.</p></div>';
                            return;
                        }
                        
                        let pdfDoc = null;
                        let currentPage = 1;
                        let isRendering = false;
                        
                        // Show loading state
                        container.innerHTML = '<div class="text-center p-4" style="color: white;"><i class="fas fa-spinner fa-spin fa-3x mb-3"></i><p>Loading PDF...</p></div>';
                        
                        // Navigation functions
                        window.prevPage = function() {
                            if (currentPage <= 1) return;
                            currentPage--;
                            queueRenderPage(currentPage);
                        };
                        
                        window.nextPage = function() {
                            if (currentPage >= pdfDoc.numPages) return;
                            currentPage++;
                            queueRenderPage(currentPage);
                        };
                        
                        // Event listeners
                        if (prevBtn) prevBtn.addEventListener('click', prevPage);
                        if (nextBtn) nextBtn.addEventListener('click', nextPage);
                        
                        // Render queue
                        function queueRenderPage(num) {
                            if (isRendering) return;
                            renderPage(num);
                        }
                        
                        // Page render function
                        function renderPage(num) {
                            isRendering = true;
                            
                            // Reset container to show canvas
                            container.innerHTML = '';
                            container.appendChild(canvasWrapper);
                            
                            pdfDoc.getPage(num).then(page => {
                                const viewport = page.getViewport({ scale: 1.5 });
                                canvas.width = viewport.width;
                                canvas.height = viewport.height;
                                
                                const renderContext = {
                                    canvasContext: canvas.getContext('2d'),
                                    viewport: viewport
                                };
                                
                                page.render(renderContext).promise.then(() => {
                                    isRendering = false;
                                    
                                    // Update page info
                                    if (pageInfo) pageInfo.textContent = `Page ${num} of ${pdfDoc.numPages}`;
                                    if (prevBtn) prevBtn.disabled = num <= 1;
                                    if (nextBtn) nextBtn.disabled = num >= pdfDoc.numPages;
                                    
                                    console.log(`Page ${num} rendered successfully`);
                                }).catch(error => {
                                    console.error('Error rendering page:', error);
                                    isRendering = false;
                                    container.innerHTML = '<div class="text-center p-4"><i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i><p>Error rendering PDF page. Please try again.</p><button class="btn btn-light" onclick="location.reload()">Reload</button></div>';
                                });
                            }).catch(error => {
                                console.error('Error getting page:', error);
                                isRendering = false;
                                container.innerHTML = '<div class="text-center p-4"><i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i><p>Error accessing PDF page. Please try again.</p><button class="btn btn-light" onclick="location.reload()">Reload</button></div>';
                            });
                        }
                        
                        // Load PDF with enhanced error handling and debugging
                        console.log('Attempting to load PDF from:', pdfUrl);
                        
                        // Test if URL is accessible
                        fetch(pdfUrl, {
                            method: 'HEAD',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(response => {
                            console.log('PDF URL HEAD response:', response.status, response.statusText);
                            if (response.ok) {
                                console.log('PDF URL is accessible, loading PDF...');
                                loadPDFDocument();
                            } else {
                                console.error('PDF URL not accessible:', response.status);
                                handlePDFError(new Error(`HTTP ${response.status}: ${response.statusText}`), 'HTTP_ERROR');
                            }
                        }).catch(error => {
                            console.error('Error checking PDF URL:', error);
                            handlePDFError(error, 'NETWORK_ERROR');
                        });
                        
                        function loadPDFDocument() {
                            // Primary attempt: let PDF.js request ranges (fast when server supports it)
                            pdfjsLib.getDocument({
                                url: pdfUrl,
                                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                                cMapPacked: true,
                                withCredentials: true,
                                httpHeaders: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Cache-Control': 'no-cache',
                                    'Accept': 'application/pdf,*/*;q=0.8'
                                },
                                rangeChunkSize: 65536, // 64KB chunks
                                disableAutoFetch: false,
                                disableStream: false
                            }).promise.then(function(pdf) {
                                pdfDoc = pdf;
                                console.log('PDF loaded successfully, pages:', pdf.numPages);

                                // Reset container for PDF display
                                container.innerHTML = '';
                                container.appendChild(canvasWrapper);

                                // Render first page
                                renderPage(currentPage);
                            }).catch(function(error) {
                                console.error('Error loading PDF (primary):', error);

                                // If primary loading fails (often due to server not supporting range requests or unexpected response),
                                // attempt a full-file fetch fallback using fetch() + arrayBuffer and pass data to PDF.js.
                                if (!window._pdf_full_fetch_tried) {
                                    window._pdf_full_fetch_tried = true;
                                    console.warn('Attempting full-download fallback for PDF (this will download entire file)');

                                    fetch(pdfUrl, { credentials: 'include', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                        .then(resp => {
                                            if (!resp.ok) throw new Error('HTTP ' + resp.status + ' ' + resp.statusText);
                                            return resp.arrayBuffer();
                                        })
                                        .then(arrayBuffer => {
                                            // Load PDF from raw data (avoids range requests)
                                            pdfjsLib.getDocument({ data: arrayBuffer }).promise.then(function(pdf) {
                                                pdfDoc = pdf;
                                                console.log('PDF loaded via full-download fallback, pages:', pdf.numPages);

                                                container.innerHTML = '';
                                                container.appendChild(canvasWrapper);
                                                renderPage(currentPage);
                                            }).catch(function(err) {
                                                console.error('Fallback PDF.js load failed:', err);
                                                handlePDFError(err, 'LOAD_ERROR');
                                            });
                                        })
                                        .catch(err => {
                                            console.error('Full-download fallback failed:', err);
                                            handlePDFError(err, 'LOAD_ERROR');
                                        });
                                } else {
                                    handlePDFError(error, 'LOAD_ERROR');
                                }
                            });
                        }
                        
                        function handlePDFError(error, errorType) {
                            console.error('PDF Error Details:', {
                                type: errorType,
                                name: error.name,
                                message: error.message,
                                stack: error.stack
                            });
                            
                            let errorMessage = 'Failed to load PDF. Please try again later.';
                            let errorIcon = 'fa-exclamation-triangle';
                            let errorColor = 'text-danger';
                            let technicalDetails = '';
                            
                            if (errorType === 'HTTP_ERROR') {
                                errorMessage = 'PDF file not found or access denied. Please contact support.';
                                errorIcon = 'fa-file-excel';
                                errorColor = 'text-warning';
                                technicalDetails = `HTTP Error: ${error.message}`;
                            } else if (error.name === 'UnexpectedResponseException') {
                                errorMessage = 'PDF file not found or access denied. Please contact support.';
                                errorIcon = 'fa-file-excel';
                                errorColor = 'text-warning';
                                technicalDetails = `Server responded with: ${error.message}`;
                            } else if (error.name === 'InvalidPDFException') {
                                errorMessage = 'Invalid or corrupted PDF file.';
                                errorIcon = 'fa-file-times';
                                errorColor = 'text-danger';
                                technicalDetails = 'The PDF file appears to be corrupted or invalid.';
                            } else if (error.name === 'PasswordException') {
                                errorMessage = 'PDF file is password protected.';
                                errorIcon = 'fa-lock';
                                errorColor = 'text-warning';
                                technicalDetails = 'This PDF file requires a password to open.';
                            } else if (error.name === 'MissingPDFException') {
                                errorMessage = 'PDF file is missing or empty.';
                                errorIcon = 'fa-file-times';
                                errorColor = 'text-danger';
                                technicalDetails = 'The PDF file could not be found or is empty.';
                            } else if (error.name === 'UnknownErrorException') {
                                errorMessage = 'An unknown error occurred while loading the PDF.';
                                errorIcon = 'fa-exclamation-circle';
                                errorColor = 'text-warning';
                                technicalDetails = error.message || 'Unknown error occurred.';
                            } else if (errorType === 'NETWORK_ERROR') {
                                errorMessage = 'Network error occurred. Please check your connection and try again.';
                                errorIcon = 'fa-wifi';
                                errorColor = 'text-warning';
                                technicalDetails = 'Could not connect to the PDF server. Please check your internet connection.';
                            }
                            
                            container.innerHTML = `
                                <div class="text-center p-4">
                                    <i class="fas ${errorIcon} fa-3x ${errorColor} mb-3"></i>
                                    <h5 class="mb-3">PDF Loading Failed</h5>
                                    <p class="mb-2">${errorMessage}</p>
                                    <p class="small text-muted mb-3">${technicalDetails}</p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button class="btn btn-primary" onclick="location.reload()">
                                            <i class="fas fa-redo me-2"></i>Reload Page
                                        </button>
                                        <button class="btn btn-secondary" onclick="window.open('${pdfUrl}', '_blank')">
                                            <i class="fas fa-external-link-alt me-2"></i>Open in New Tab
                                        </button>
                                    </div>
                                    <details class="mt-3 text-start">
                                        <summary class="small text-muted cursor-pointer">Technical Details</summary>
                                        <pre class="small text-muted mt-2 p-2 bg-light rounded">
Error Type: ${errorType}
Error Name: ${error.name}
Error Message: ${error.message}
PDF URL: ${pdfUrl}
User Agent: ${navigator.userAgent}
Timestamp: ${new Date().toISOString()}
                                        </pre>
                                    </details>
                                </div>
                            `;
                        }
                        
                        // ===== PDF PROTECTION =====
                        // Prevent right-click on PDF
                        if (overlay) {
                            overlay.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                alert('Aksi ini tidak diizinkan untuk melindungi konten PDF.');
                                return false;
                            }, true);
                            
                            overlay.addEventListener('selectstart', function(e) {
                                e.preventDefault();
                                return false;
                            }, true);
                            
                            overlay.addEventListener('dragstart', function(e) {
                                e.preventDefault();
                                return false;
                            }, true);
                        }
                        
                        if (canvas) {
                            canvas.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                alert('Aksi ini tidak diizinkan untuk melindungi konten PDF.');
                                return false;
                            }, true);
                            
                            canvas.addEventListener('selectstart', function(e) {
                                e.preventDefault();
                                return false;
                            }, true);
                        }
                        
                        if (container) {
                            container.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                alert('Aksi ini tidak diizinkan untuk melindungi konten PDF.');
                                return false;
                            }, true);
                        }
                        
                        // Global document protection for PDF
                        document.addEventListener('contextmenu', function(e) {
                            if (e.target.closest('#pdf-container') || e.target.closest('#pdf-canvas-wrapper')) {
                                e.preventDefault();
                                e.stopPropagation();
                                alert('Aksi ini tidak diizinkan untuk melindungi konten PDF.');
                                return false;
                            }
                        }, true);
                        
                        document.addEventListener('copy', function(e) {
                            if (e.target.closest('#pdf-container') || e.target.closest('#pdf-canvas-wrapper')) {
                                e.preventDefault();
                                return false;
                            }
                        }, true);
                        
                        document.addEventListener('cut', function(e) {
                            if (e.target.closest('#pdf-container') || e.target.closest('#pdf-canvas-wrapper')) {
                                e.preventDefault();
                                return false;
                            }
                        }, true);
                        
                        window.addEventListener('beforeprint', function(e) {
                            if (e.target.closest('#pdf-container') || e.target.closest('#pdf-canvas-wrapper')) {
                                e.preventDefault();
                                return false;
                            }
                        }, true);

                        canvas.style.userSelect = 'none';
                        overlay.style.userSelect = 'none';
                        container.style.userSelect = 'none';
                    }
                    
                    // Initialize PDF viewer
                    initPDFViewer();
                });
                </script>
            @elseif($module->file_path)
                <div class="text-center py-5">
                    <i class="fas fa-lock fa-5x text-secondary mb-3"></i>
                    <h5>Preview terbatas</h5>
                    <p class="text-muted mb-0">Login dan enroll untuk melihat PDF ini.</p>
                    @if($module->file_name)
                        <p class="small text-muted mt-2">File: {{ $module->file_name }}</p>
                    @endif
                </div>
            @endif
            </div>

        @else
            <!-- Text Module -->
            <div class="text-content" id="text-module-content">
                {!! $module->content ?? '<p>No content available for this module.</p>' !!}
            </div>

            <!-- Text Module Protection Script -->
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const textContent = document.getElementById('text-module-content');
                if (!textContent) return;

                textContent.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('Klik kanan tidak diizinkan pada konten ini.');
                    return false;
                }, true);

                textContent.addEventListener('selectstart', function(e) {
                    e.preventDefault();
                    return false;
                }, true);

                textContent.addEventListener('dragstart', function(e) {
                    e.preventDefault();
                    return false;
                }, true);

                textContent.addEventListener('copy', function(e) {
                    e.preventDefault();
                    alert('Copy tidak diizinkan untuk melindungi konten.');
                    return false;
                }, true);

                textContent.addEventListener('cut', function(e) {
                    e.preventDefault();
                    return false;
                }, true);

                textContent.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'c') {
                        e.preventDefault();
                        alert('Copy tidak diizinkan untuk melindungi konten.');
                        return false;
                    }
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'x') {
                        e.preventDefault();
                        return false;
                    }
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'a') {
                        e.preventDefault();
                        return false;
                    }
                }, true);

                textContent.style.userSelect = 'none';
                textContent.style.webkitUserSelect = 'none';
                textContent.style.mozUserSelect = 'none';
                textContent.style.msUserSelect = 'none';
            });
            </script>
        @endif

        <!-- Page Navigation for PDF Documents -->
        @if($module->type === 'document')
        <div class="pdf-controls">
            <button id="pdf-prev-btn">
                <i class="fas fa-chevron-left"></i> Previous Page
            </button>
            <span id="pdf-page-info">Page 1 of ?</span>
            <button id="pdf-next-btn">
                Next Page <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        @endif

        <!-- Module Navigation -->
        <div class="module-navigation">
            @if($previousModule)
                <a href="{{ route('module.show', [$class->id, $previousModule->chapter_id, $previousModule->id]) }}" 
                   class="btn btn-outline-primary">
                    <i class="fas fa-chevron-left me-2"></i>Previous Module
                </a>
            @endif
            <div class="flex-grow-1"></div>
            @if($nextModule)
                <a href="{{ route('module.show', [$class->id, $nextModule->chapter_id, $nextModule->id]) }}" 
                   class="btn btn-primary">
                    Next Module<i class="fas fa-chevron-right ms-2"></i>
                </a>
            @else
                @if($isEnrolled)
                    @php
                        $isLastModule = $nextModule === null;
                    @endphp
                    {{-- Hide Complete Course button in footer for last module (already shown in header) --}}
                    @if(!$isLastModule)
                    <button id="completeCourseBtn" class="btn btn-success" type="button">
                        <i class="fas fa-check me-2"></i>Complete Course
                    </button>
                    @endif
                @endif
            @endif
        </div>
    </div>
</div>

<script>
// ===== SIDEBAR TOGGLE FUNCTIONALITY - FIXED =====
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('moduleSidebar');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const btnDetailChapter = document.getElementById('btnDetailChapter');
    const fullscreenHeader = document.getElementById('fullscreenHeader');
    const body = document.body;
    const moduleContent = document.getElementById('moduleContent');
    
    // Initialize sidebar state
    function initSidebar() {
        if (window.innerWidth > 768) {
            // Desktop: sidebar collapsed by default
            sidebar.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
            if (moduleContent) moduleContent.classList.add('full-screen');
            if (fullscreenHeader) fullscreenHeader.classList.add('show');
        } else {
            // Mobile: sidebar hidden by default
            sidebar.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
            if (moduleContent) moduleContent.classList.add('full-screen');
            if (fullscreenHeader) fullscreenHeader.classList.add('show');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('show');
                sidebarOverlay.setAttribute('aria-hidden', 'true');
            }
            body.style.overflow = '';
        }
        
        if (sidebar) sidebar.setAttribute('aria-hidden', 'true');
    }
    
    // Toggle sidebar function
    function toggleSidebar(forceClose = false) {
        if (!sidebar) return;
        
        const isCurrentlyCollapsed = sidebar.classList.contains('collapsed');
        
        if (forceClose) {
            // Force close sidebar
            closeSidebar();
        } else if (isCurrentlyCollapsed) {
            // Open sidebar
            openSidebar();
        } else {
            // Close sidebar
            closeSidebar();
        }
        
        console.log('Sidebar toggled. Collapsed:', sidebar.classList.contains('collapsed'));
    }
    
    function openSidebar() {
        sidebar.classList.remove('collapsed');
        body.classList.remove('sidebar-collapsed');
        if (moduleContent) moduleContent.classList.remove('full-screen');
        if (fullscreenHeader) fullscreenHeader.classList.remove('show');
        
        if (window.innerWidth <= 768) {
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('show');
                sidebarOverlay.setAttribute('aria-hidden', 'false');
            }
            body.style.overflow = 'hidden';
            
            const firstFocusable = sidebar.querySelector('a,button,[tabindex]:not([tabindex="-1"])');
            if (firstFocusable) firstFocusable.focus();
        }
        
        sidebar.setAttribute('aria-hidden', 'false');
    }
    
    function closeSidebar() {
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
        if (moduleContent) moduleContent.classList.add('full-screen');
        if (fullscreenHeader) fullscreenHeader.classList.add('show');
        
        if (window.innerWidth <= 768) {
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('show');
                sidebarOverlay.setAttribute('aria-hidden', 'true');
            }
            body.style.overflow = '';
        }
        
        sidebar.setAttribute('aria-hidden', 'true');
    }
    
    // Event listeners
    if (btnDetailChapter) {
        btnDetailChapter.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            toggleSidebar();
        });
    }
    
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            toggleSidebar(true); // Force close
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            toggleSidebar(true); // Force close
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = sidebar && sidebar.contains(e.target);
            const isClickOnButton = btnDetailChapter && btnDetailChapter.contains(e.target);
            const isClickOnCloseBtn = sidebarCloseBtn && sidebarCloseBtn.contains(e.target);
            const isSidebarOpen = sidebar && !sidebar.classList.contains('collapsed');
            
            if (!isClickInsideSidebar && !isClickOnButton && !isClickOnCloseBtn && isSidebarOpen) {
                toggleSidebar(true);
            }
        }
    });
    
    // Initialize on load
    initSidebar();
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768) {
                body.style.overflow = '';
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('show');
                }
            }
        }, 250);
    });
    
    // ===== CHAPTER DROPDOWN FUNCTIONALITY =====
    const chapterHeaders = document.querySelectorAll('.chapter-header');
    
    chapterHeaders.forEach(header => {
        const chapterId = header.getAttribute('data-chapter-id');
        const moduleList = document.getElementById('chapter-' + chapterId);
        
        // Open current chapter by default
        if (header.classList.contains('active')) {
            moduleList.classList.remove('collapsed');
            header.classList.remove('collapsed');
        } else {
            // Collapse other chapters by default
            moduleList.classList.add('collapsed');
            header.classList.add('collapsed');
        }
        
        header.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle current chapter
            this.classList.toggle('collapsed');
            moduleList.classList.toggle('collapsed');
        });
    });
    
    // ===== LOCKED MODULE CLICK PREVENTION =====
    const lockedModules = document.querySelectorAll('.module-link.locked');
    
    lockedModules.forEach(module => {
        module.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const moduleTitle = this.querySelector('.module-link-title').textContent;
            
            Swal.fire({
                icon: 'info',
                title: 'Module Terkunci',
                html: '<p>Selesaikan semua modul di chapter sebelumnya terlebih dahulu untuk membuka:</p><p class="fw-bold mt-2">"' + moduleTitle + '"</p>',
                confirmButtonColor: '#1e88e5',
                confirmButtonText: 'Mengerti'
            });
            
            return false;
        });
    });
    
    // ===== NEXT MODULE BUTTON LOCK - FIXED =====
    @if($isEnrolled && $nextModule)
        const isModuleCompleted = {{ isset($completedModules) && in_array($module->id, $completedModules) ? 'true' : 'false' }};
        
        const nextModuleLink = document.querySelector('.module-navigation a.btn-primary:not(.btn-outline-primary)');
        
        if (nextModuleLink && !isModuleCompleted) {
            const originalHref = nextModuleLink.getAttribute('href');
            
            nextModuleLink.removeAttribute('href');
            nextModuleLink.setAttribute('data-original-href', originalHref);
            
            nextModuleLink.classList.remove('btn-primary');
            nextModuleLink.classList.add('btn-secondary', 'disabled-next');
            nextModuleLink.style.opacity = '0.5';
            nextModuleLink.style.cursor = 'not-allowed';
            nextModuleLink.style.pointerEvents = 'auto';
            
            nextModuleLink.innerHTML = '<i class="fas fa-lock me-2"></i>Complete This Module First<i class="fas fa-chevron-right ms-2"></i>';
            
            nextModuleLink.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                Swal.fire({
                    icon: 'info',
                    title: 'Module Belum Selesai',
                    html: '<p>Silakan tandai module ini sebagai selesai terlebih dahulu sebelum melanjutkan ke module berikutnya.</p>',
                    confirmButtonColor: '#1e88e5',
                    confirmButtonText: 'Mengerti'
                });
                
                return false;
            }, true);
        }
    @endif
    
    // ===== MOBILE RESPONSIVE TEXT CONTENT - ENHANCED =====
    const textContent = document.getElementById('text-module-content');
    if (textContent) {
        function makeResponsive() {
            const allElements = textContent.querySelectorAll('*');
            allElements.forEach(element => {
                if (element.tagName !== 'BR') {
                    element.style.maxWidth = '100%';
                    element.style.wordWrap = 'break-word';
                    element.style.overflowWrap = 'break-word';
                }
            });
            
            const images = textContent.querySelectorAll('img');
            images.forEach(img => {
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.style.display = 'block';
                img.style.margin = '1rem auto';
            });
            
            const tables = textContent.querySelectorAll('table');
            tables.forEach(table => {
                if (!table.parentElement.classList.contains('table-wrapper')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-wrapper';
                    wrapper.style.width = '100%';
                    wrapper.style.overflowX = 'auto';
                    wrapper.style.marginBottom = '1rem';
                    wrapper.style.webkitOverflowScrolling = 'touch';
                    
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                    
                    table.style.width = '100%';
                    table.style.minWidth = '300px';
                }
            });
            
            const iframes = textContent.querySelectorAll('iframe');
            iframes.forEach(iframe => {
                iframe.style.maxWidth = '100%';
            });
            
            const preBlocks = textContent.querySelectorAll('pre');
            preBlocks.forEach(pre => {
                pre.style.maxWidth = '100%';
                pre.style.overflowX = 'auto';
                pre.style.whiteSpace = 'pre-wrap';
                pre.style.wordWrap = 'break-word';
            });
        }
        
        makeResponsive();
        
        let resizeTimer2;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer2);
            resizeTimer2 = setTimeout(makeResponsive, 250);
        });
    }
    
    // ===== MARK COMPLETE FUNCTIONALITY =====
    @if($isEnrolled)
        const markCompleteBtn = document.getElementById('markCompleteBtn');
        
        if (markCompleteBtn) {
            markCompleteBtn.style.position = 'relative';
            markCompleteBtn.style.zIndex = '99999';
            markCompleteBtn.style.pointerEvents = 'auto';
            markCompleteBtn.style.cursor = 'pointer';
            
            function handleMarkComplete(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                const btn = markCompleteBtn;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                
                fetch('{{ route("module.complete", [$class->id, $module->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Failed to mark as complete');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: data.courseCompleted ? 'Selamat!' : 'Berhasil!',
                            html: `
                                <div style="text-align: center;">
                                    <i class="fas fa-${data.courseCompleted ? 'trophy' : 'check-circle'}" style="font-size: 3rem; color: ${data.courseCompleted ? '#ffc107' : '#28a745'}; margin-bottom: 1rem;"></i>
                                    <p style="font-size: 1.1rem; margin-bottom: 0.5rem; font-weight: 600;">${data.message}</p>
                                    ${data.courseCompleted ? 
                                        '<p style="color: #6c757d; font-size: 0.9rem;">Anda telah menyelesaikan semua module dalam course ini!</p>' :
                                        `<p style="color: #6c757d; font-size: 0.9rem;">Progress: ${Math.round(data.progress)}% (${data.completed}/${data.total} modules)</p>`
                                    }
                                </div>
                            `,
                            confirmButtonText: data.courseCompleted ? 'Lihat Course' : 'Lanjutkan Belajar',
                            confirmButtonColor: '#1e88e5',
                            timer: data.courseCompleted ? 5000 : 3000,
                            timerProgressBar: true
                        }).then((result) => {
                            if (data.courseCompleted && result.isConfirmed) {
                                window.location.href = '{{ route("course.detail", $class->id) }}';
                            }
                        });
                        
                        btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Completed';
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-success');
                        btn.disabled = true;
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Failed to mark as complete',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: error.message || 'An error occurred. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                });
            }
            
            markCompleteBtn.addEventListener('click', handleMarkComplete, true);
            markCompleteBtn.addEventListener('click', handleMarkComplete, false);
            
            markCompleteBtn.addEventListener('mousedown', function(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            }, true);
            
            const observer = new MutationObserver(function() {
                markCompleteBtn.style.zIndex = '99999';
                markCompleteBtn.style.pointerEvents = 'auto';
                markCompleteBtn.style.position = 'relative';
                markCompleteBtn.style.cursor = 'pointer';
            });
            observer.observe(markCompleteBtn, { attributes: true, attributeFilter: ['style'] });
        }
        
        const completeCourseBtn = document.getElementById('completeCourseBtn');
        if (completeCourseBtn) {
            completeCourseBtn.addEventListener('click', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tandai Semua Module Selesai?',
                    text: 'Ini akan menandai semua module dalam course ini sebagai selesai dan progress akan langsung menjadi 100%.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Tandai Selesai',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = completeCourseBtn;
                        const originalText = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                        
                        fetch('{{ route("course.completeAll", $class->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Selamat!',
                                    html: `
                                        <div style="text-align: center;">
                                            <i class="fas fa-trophy" style="font-size: 3rem; color: #ffc107; margin-bottom: 1rem;"></i>
                                            <p style="font-size: 1.1rem; margin-bottom: 0.5rem; font-weight: 600;">${data.message}</p>
                                            <p style="color: #6c757d; font-size: 0.9rem;">Anda telah menyelesaikan semua module dalam course ini!</p>
                                        </div>
                                    `,
                                    confirmButtonText: 'Lihat Course',
                                    confirmButtonColor: '#1e88e5',
                                    timer: 5000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location.href = '{{ route("course.detail", $class->id) }}';
                                });
                            } else {
                                btn.disabled = false;
                                btn.innerHTML = originalText;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: data.message || 'Failed to complete course',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: error.message || 'An error occurred. Please try again.',
                                confirmButtonColor: '#dc3545'
                            });
                        });
                    }
                });
            });
        }
        
        const completeCourseHeaderBtn = document.getElementById('completeCourseHeaderBtn');
        if (completeCourseHeaderBtn) {
            completeCourseHeaderBtn.addEventListener('click', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Complete Course?',
                    text: 'Anda sudah menyelesaikan semua module dalam course ini. Tandai course sebagai selesai?',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Selesaikan Course',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = completeCourseHeaderBtn;
                        const originalText = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                        
                        fetch('{{ route("course.completeAll", $class->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Selamat!',
                                    html: `
                                        <div style="text-align: center;">
                                            <i class="fas fa-trophy" style="font-size: 3rem; color: #ffc107; margin-bottom: 1rem;"></i>
                                            <p style="font-size: 1.1rem; margin-bottom: 0.5rem; font-weight: 600;">${data.message}</p>
                                            <p style="color: #6c757d; font-size: 0.9rem;">Anda telah menyelesaikan course ini dengan sempurna!</p>
                                        </div>
                                    `,
                                    confirmButtonText: 'Lihat Course',
                                    confirmButtonColor: '#1e88e5',
                                    timer: 5000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location.href = '{{ route("course.detail", $class->id) }}';
                                });
                            } else {
                                btn.disabled = false;
                                btn.innerHTML = originalText;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: data.message || 'Failed to complete course',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: error.message || 'An error occurred. Please try again.',
                                confirmButtonColor: '#dc3545'
                            });
                        });
                    }
                });
            });
        }
    @endif
    
    // ===== PDF PROTECTION & NAVIGATION =====
    @if($module->type === 'document' && $module->file_path && ($canAccessFile ?? false))
        const pdfPath = '{{ route("module.file", [$class->id, $chapter->id, $module->id]) }}';
        
        function initPDFViewer() {
            if (typeof pdfjsLib === 'undefined') {
                console.error('PDF.js library not loaded');
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            let pdfDoc = null;
            let currentPage = 1;

            async function renderPage(pageNum) {
                try {
                    if (!pdfDoc) return;
                    const page = await pdfDoc.getPage(pageNum);
                    const canvas = document.getElementById('pdf-canvas');
                    if (!canvas) return;
                    
                    const ctx = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1.8 });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    
                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    
                    await page.render(renderContext).promise;
                    
                    const pageInfo = document.getElementById('pdf-page-info');
                    const prevBtn = document.getElementById('pdf-prev-btn');
                    const nextBtn = document.getElementById('pdf-next-btn');
                    
                    if (pageInfo) pageInfo.textContent = `Page ${pageNum} of ${pdfDoc.numPages}`;
                    if (prevBtn) prevBtn.disabled = pageNum <= 1;
                    if (nextBtn) nextBtn.disabled = pageNum >= pdfDoc.numPages;
                } catch (error) {
                    console.error('Error rendering page:', error);
                }
            }

            pdfjsLib.getDocument({
                url: pdfPath,
                withCredentials: true,
                httpHeaders: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).promise.then(function(doc) {
                pdfDoc = doc;
                renderPage(currentPage);
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
            });

            const prevBtn = document.getElementById('pdf-prev-btn');
            const nextBtn = document.getElementById('pdf-next-btn');

            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        renderPage(currentPage);
                    }
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    if (pdfDoc && currentPage < pdfDoc.numPages) {
                        currentPage++;
                        renderPage(currentPage);
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initPDFViewer, 100);
            });
        } else {
            setTimeout(initPDFViewer, 100);
        }
    @endif
    
    // ===== YOUTUBE VIDEO PROTECTION =====
    @if($module->type === 'video' && $module->video_url && (str_contains($module->video_url, 'youtube.com') || str_contains($module->video_url, 'youtu.be')))
        const youtubeWrapper = document.querySelector('.youtube-video-wrapper');
        if (youtubeWrapper) {
            function preventAll(e) {
                const target = e.target;
                if (target && (
                    target.id === 'markCompleteBtn' || 
                    target.closest('#markCompleteBtn') ||
                    target.closest('.module-header') ||
                    target.closest('.module-navigation') ||
                    target.closest('.btn-detail-chapter')
                )) {
                    return true;
                }
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }

            ['contextmenu', 'selectstart', 'copy', 'cut', 'paste', 'dragstart'].forEach(eventType => {
                youtubeWrapper.addEventListener(eventType, preventAll, true);
            });

            document.addEventListener('keydown', function(e) {
                if (youtubeWrapper.contains(document.activeElement)) {
                    if (e.ctrlKey || e.metaKey) {
                        if (['c', 'C', 'a', 'A', 's', 'S', 'p', 'P'].includes(e.key)) {
                            e.preventDefault();
                            return false;
                        }
                    }
                }
            }, true);
        }
    @endif
    
    console.log('Module page scripts loaded');
});
</script>

{{-- Catatan: penanda sessionStorage 'mersif.lastModule.course.*' yang dulu
     ditulis di sini sudah dihapus. Penanda itu bertahan 12 jam dan dibaca
     halaman detail course sebagai cadangan pemulihan posisi, sehingga
     kunjungan baru dari katalog ikut tergulir otomatis ke tengah halaman.

     Pemulihan posisi kini sepenuhnya ditangani parameter ?module= pada tombol
     "Kembali ke Course" di atas - eksplisit, sekali pakai, dan tidak
     meninggalkan sisa keadaan. --}}

@endsection