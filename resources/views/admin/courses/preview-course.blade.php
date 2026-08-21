<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Mode: {{ $course->name ?? 'Course' }} - MersifLab Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/course-detail.css') }}">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(180deg, #E2E8F0 10%, #4B5F8A 90%);
            background-attachment: fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .preview-header-bar {
            background: white;
            border-bottom: 2px solid #e0e0e0;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .preview-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .preview-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 6px 14px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .preview-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .btn-close-preview {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #e0e0e0;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-close-preview:hover {
            background: #e0e0e0;
            border-color: #ccc;
            color: #000;
        }

        /* Override course-detail styles for preview mode */
        .course-detail-page {
            padding-top: 0;
        }

        /* Hide student-specific elements */
        .btn-add-cart,
        .btn-buy-now,
        .btn-start-learning,
        .progress-card,
        .leave-review-box,
        .access-badge {
            display: none !important;
        }

        /* Show all chapters/modules for admin preview */
        .chapter-card {
            opacity: 1 !important;
        }

        .access-badge {
            display: none !important;
        }
    </style>
</head>
<body>
    <!-- Preview Header Bar -->
    <div class="preview-header-bar">
        <div class="preview-header-left">
            <span class="preview-badge">
                <i class="fas fa-eye"></i> Admin Preview Mode
            </span>
            <span class="preview-title">{{ $course->name ?? 'Untitled Course' }}</span>
        </div>
        <a href="{{ back_url(route('admin.courses.approval', $course->id)) }}" class="btn-close-preview">
            <i class="fas fa-arrow-left"></i> Back to Approval
        </a>
    </div>

    <!-- Course Detail Content (using existing course-detail view structure) -->
    <div class="course-detail-page">
        <!-- Breadcrumb -->
        <div class="breadcrumb-section">
            <div class="container">
                <nav class="breadcrumb-nav">
                    <a href="{{ route('admin.courses.index') }}"><i class="fas fa-home"></i> Admin Courses</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>{{ Str::limit($course->name, 50) }}</span>
                </nav>
            </div>
        </div>

        <!-- Course Hero Section -->
        <section class="course-hero" style="position: relative;">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="hero-content-card">
                            <div class="course-badge" style="position: absolute; top: 20px; left: 20px; z-index: 5; display:flex; gap:8px; align-items:center; background: rgba(255,255,255,0.95); padding:8px 12px; border-radius:12px;">
                                <i class="fas fa-graduation-cap" style="color: #1976d2;"></i>
                                <span style="color:#1976d2; font-weight:600;">{{ $course->category->name ?? $course->category_name ?? 'Uncategorized' }}</span>
                                @php $tier = $course->price_tier ?? null; @endphp
                                @if($tier)
                                    <span style="background: {{ $tier === 'standard' ? '#e8f5e9' : '#f3e8ff' }}; color: {{ $tier === 'standard' ? '#2e7d32' : '#6a1b9a' }}; padding:6px 10px; border-radius:12px; font-size:12px; font-weight:600; margin-left:6px;">{{ ucfirst($tier) }}</span>
                                @endif
                            </div>
                            
                            <h1 class="hero-title">{{ $course->name }}</h1>
                            <p class="hero-description">{{ $course->description ?? 'Course description' }}</p>
                            
                            <div class="course-stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon stat-icon-rating">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-value">{{ number_format($ratingStats['average'] ?? 0, 1) }}</div>
                                        <div class="stat-label">Rating ({{ number_format($ratingStats['total'] ?? 0) }})</div>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon stat-icon-students">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-value">{{ $course->students_count ?? 0 }}</div>
                                        <div class="stat-label">Students</div>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon stat-icon-duration">
                                        <i class="far fa-clock"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-value">{{ $course->formatted_total_duration ?? '0h' }}</div>
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
                                        <strong>{{ $course->teacher->name ?? 'Teacher' }}</strong>
                                    </div>
                                </div>
                                <div class="last-updated">
                                    <i class="fas fa-calendar-alt"></i>
                                    Updated {{ $course->updated_at ? $course->updated_at->format('M Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Admin Preview Info Card (no enroll/buy buttons) -->
                        <div class="progress-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <div class="progress-badge" style="background: #e3f2fd; color: #1976d2; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; margin-bottom: 16px;">
                                <i class="fas fa-eye"></i> Preview Only
                            </div>
                            
                            <div style="margin-bottom: 16px;">
                                @if($course->price && $course->price > 0)
                                    <div class="current-price">Rp{{ number_format($course->price, 0, ',', '.') }}</div>
                                @else
                                    <div class="current-price">FREE</div>
                                @endif
                            </div>

                            <hr style="margin: 20px 0; border-color: #e0e0e0;">
                            
                            <div class="course-includes">
                                <small class="includes-title">Course Information:</small>
                                <ul class="includes-list">
                                    <li><i class="fas fa-book"></i> {{ $course->chapters_count ?? 0 }} Chapters</li>
                                    <li><i class="fas fa-file-alt"></i> {{ $course->modules_count ?? 0 }} Modules</li>
                                    <li><i class="fas fa-users"></i> {{ $course->students_count ?? 0 }} Students</li>
                                    <li><i class="fas fa-clock"></i> {{ $course->formatted_total_duration ?? '0h' }} Duration</li>
                                </ul>
                            </div>

                            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                                <small style="color: #666; font-size: 12px;">
                                    <i class="fas fa-info-circle"></i> 
                                    This is a preview. Admin can view course content but cannot enroll or purchase.
                                </small>
                            </div>
                        </div>
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
                        <p class="section-subtitle">Master these key skills and concepts</p>
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
                        <p class="section-subtitle">{{ $course->chapters_count ?? 0 }} chapters · {{ $course->formatted_total_duration ?? '0 hours' }} total length</p>
                    </div>
                    
                    @if($course->chapters->count() > 0)
                        <div class="chapters-accordion">
                            @foreach($course->chapters as $index => $chapter)
                            <div class="chapter-card">
                                <div class="chapter-header-wrapper">
                                    <div class="chapter-number">{{ $index + 1 }}</div>
                                    <div class="chapter-content-info">
                                        <h6 class="chapter-title">{{ $chapter->title }}</h6>
                                        <div class="chapter-meta">
                                            <span><i class="fas fa-play-circle"></i> {{ $chapter->modules->count() }} lessons</span>
                                            <span><i class="far fa-clock"></i> {{ $chapter->formatted_total_duration ?? '0 min' }}</span>
                                        </div>
                                    </div>
                                    <span class="access-badge" style="background: #e3f2fd; color: #1976d2; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-eye"></i> Preview
                                    </span>
                                </div>
                                
                                <!-- Modules List (Admin can see all modules) -->
                                @if($chapter->modules->count() > 0)
                                <div class="modules-list" style="margin-top: 12px; padding-left: 60px;">
                                    @foreach($chapter->modules as $moduleIndex => $module)
                                    <div class="module-item" style="display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                        <i class="{{ $module->file_icon }}" style="font-size: 16px;"></i>
                                        <span style="font-size: 13px; color: #333;">{{ $module->title }}</span>
                                        <a href="{{ route('admin.modules.preview', $module->id) }}" 
                                           target="_blank"
                                           class="btn btn-sm" 
                                           style="background: #e3f2fd; color: #1976d2; border: none; padding: 4px 8px; font-size: 10px; border-radius: 4px; text-decoration: none; margin-left: auto;">
                                            <i class="fas fa-eye"></i> Preview
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <p>No chapters available yet.</p>
                        </div>
                    @endif
                </div>

                <!-- Requirements -->
                @if($course->requirement)
                <div class="content-section">
                    <h2 class="section-title">Requirements</h2>
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
                    <h2 class="section-title">Description</h2>
                    <p class="description-text">{{ $course->description }}</p>
                </div>
                @endif

                <!-- Instructors -->
                <div class="content-section">
                    <h2 class="section-title">Instructors</h2>
                    <div class="instructor-card">
                        <div class="instructor-avatar">
                            <span>{{ strtoupper(substr($course->teacher->name ?? 'T', 0, 1)) }}</span>
                        </div>
                        <div class="instructor-details">
                            <h5 class="instructor-name">{{ $course->teacher->name ?? 'Teacher' }}</h5>
                            <p class="instructor-bio">{{ $course->teacher->biography ?? 'Course instructor' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Student Reviews -->
                <div class="content-section">
                    <h2 class="section-title">Student Reviews</h2>
                    
                    @if($ratingStats['total'] > 0)
                    <div class="reviews-summary">
                        <div class="rating-overview">
                            <div class="rating-number">{{ number_format($ratingStats['average'] ?? 0, 1) }}</div>
                            <div class="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= round($ratingStats['average'] ?? 0) ? 'filled' : '' }}"></i>
                                @endfor
                            </div>
                            <p class="rating-label">Course Rating based on {{ number_format($ratingStats['total'] ?? 0) }} {{ $ratingStats['total'] == 1 ? 'review' : 'reviews' }}</p>
                        </div>
                        
                        <div class="rating-bars">
                            @for($i = 5; $i >= 1; $i--)
                            <div class="rating-bar-row">
                                <span class="star-label">{{ $i }} <i class="fas fa-star"></i></span>
                                <div class="rating-bar-bg">
                                    <div class="rating-bar-fill" style="width: {{ $ratingStats['distribution'][$i]['percentage'] ?? 0 }}%"></div>
                                </div>
                                <span class="percentage">{{ $ratingStats['distribution'][$i]['percentage'] ?? 0 }}%</span>
                            </div>
                            @endfor
                        </div>
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
                                        <span class="avatar-initial">{{ strtoupper(substr($review->user->name ?? 'U', 0, 2)) }}</span>
                                    </div>
                                    <div class="review-user-info">
                                        <h6 class="reviewer-name">{{ $review->user->name ?? 'Anonymous' }}</h6>
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
                    @else
                        <div class="empty-state text-center">
                            <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Reviews Yet</h4>
                            <p class="text-muted">This course doesn't have any reviews yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
