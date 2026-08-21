@extends('layouts.admin')

@section('title', 'Teacher Detail - ' . ($teacher->name ?? 'N/A'))

@section('content')
@php
    $isBanned = $teacher->isBanned();
    $isOnline = $teacher->last_login_at ? $teacher->last_login_at->diffInMinutes(now()) <= 15 : false;
    $totalChapters = $courses ? $courses->sum(fn($c) => $c->chapters ? $c->chapters->count() : 0) : 0;
    $totalModules = $courses ? $courses->sum(fn($c) => $c->chapters ? $c->chapters->sum(fn($ch) => $ch->modules ? $ch->modules->count() : 0) : 0) : 0;
@endphp

<div class="page-title">
    <div>
        <h1>Teacher Detail</h1>
        <p style="color: #828282; margin: 5px 0 0 0; font-size: 14px;">Teacher information, courses/chapters/modules, rating & activities</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Back to Teachers Button -->
<div style="margin-bottom: 20px; text-align: right;">
    <a href="{{ back_url(route('admin.teachers.index')) }}" 
       style="background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; white-space: nowrap;"
       onmouseover="this.style.background='#545b62'; this.style.transform='translateY(-1px)';" 
       onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
        <i class="fas fa-arrow-left"></i> Back to Teachers
    </a>
</div>

<!-- Teacher Header -->
<div class="card-content mb-4 teacher-header-card">
    <div class="teacher-header-inner">
        <div class="teacher-header-main">
            <div class="teacher-avatar-lg">
                @if($teacher->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($teacher->avatar))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($teacher->avatar) }}" alt="{{ $teacher->name }}" class="teacher-avatar-img-lg" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <i class="fas fa-chalkboard-user teacher-avatar-fallback-lg" style="display: none;"></i>
                @else
                    <i class="fas fa-chalkboard-user"></i>
                @endif
            </div>
            <div>
                <h2 class="teacher-title">{{ $teacher->name }}</h2>
                <div class="teacher-meta">
                    <span><strong>Email:</strong> {{ $teacher->email }}</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($isOnline)
                            <span class="badge" style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i> Online
                            </span>
                        @else
                            <span class="badge" style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i> Offline
                            </span>
                        @endif
                        <span class="teacher-status-badge {{ $isBanned ? 'status-banned' : 'status-active' }}">{{ $isBanned ? 'Banned' : 'Active' }}</span>
                    </div>
                </div>
                <div class="teacher-actions-header">
                    @if($isBanned)
                        <form action="{{ route('admin.teachers.toggleBan', $teacher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to unban this teacher?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-unban"><i class="fas fa-check"></i> Unban</button>
                        </form>
                    @else
                        <form action="{{ route('admin.teachers.toggleBan', $teacher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to ban this teacher? The teacher will not be able to log in until unbanned.');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ban"><i class="fas fa-ban"></i> Ban</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Teacher Details -->
<div class="card-content mb-4">
    <h3 class="panel-title">Teacher Details</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Joined</span>
            <strong>{{ $teacher->created_at ? $teacher->created_at->format('d M Y') : '—' }}</strong>
        </div>
        <div class="detail-item">
            <span class="detail-label">Total Courses</span>
            <strong>{{ $courses ? $courses->count() : 0 }}</strong>
        </div>
        <div class="detail-item">
            <span class="detail-label">Total Chapters</span>
            <strong>{{ $totalChapters }}</strong>
        </div>
        <div class="detail-item">
            <span class="detail-label">Total Modules</span>
            <strong>{{ $totalModules }}</strong>
        </div>
        <div class="detail-item">
            <span class="detail-label">Total Enrollments</span>
            <strong>{{ $totalEnrollments }}</strong>
        </div>
        <div class="detail-item">
            <span class="detail-label">Unique Students</span>
            <strong>{{ $uniqueStudents }}</strong>
        </div>
        @if($teacher->phone || $teacher->telephone)
            <div class="detail-item detail-full">
                <span class="detail-label">Phone</span>
                <span>{{ $teacher->phone ?? $teacher->telephone ?? '—' }}</span>
            </div>
        @endif
        @if($teacher->address)
            <div class="detail-item detail-full">
                <span class="detail-label">Address</span>
                <span>{{ $teacher->address }}</span>
            </div>
        @endif
        @if($teacher->bio || $teacher->biography)
            <div class="detail-item detail-full">
                <span class="detail-label">Bio</span>
                <p class="detail-bio">{{ $teacher->bio ?? $teacher->biography }}</p>
            </div>
        @endif
    </div>
</div>

<!-- Courses / Chapters / Modules -->
<div class="card-content mb-4">
    <h3 class="panel-title">Courses, Chapters & Modules</h3>
    @if($courses && $courses->count() > 0)
        @foreach($courses as $course)
            <div class="course-block">
                <div class="course-block-header">
                    <div class="course-block-title">
                        <i class="fas fa-book"></i>
                        <span>{{ $course->name }}</span>
                        <span class="course-meta-sm">{{ $course->chapters ? $course->chapters->count() : 0 }} chapters · {{ $course->chapters ? $course->chapters->sum(fn($ch) => $ch->modules ? $ch->modules->count() : 0) : 0 }} modules</span>
                    </div>
                    <a href="{{ route('admin.courses.approval', $course->id) }}" class="btn-moderation" title="Approval">
                        <i class="fas fa-cog"></i> Approval
                    </a>
                </div>

                @if($course->chapters && $course->chapters->count() > 0)
                    <div class="accordion teacher-curriculum" id="courseAccordion{{ $course->id }}">
                        @foreach($course->chapters as $chapter)
                            <div class="chapter-item">
                                <div class="chapter-header" data-bs-toggle="collapse" data-bs-target="#chapterCollapse{{ $chapter->id }}" aria-expanded="false" aria-controls="chapterCollapse{{ $chapter->id }}">
                                    <div class="chapter-title">
                                        <i class="fas fa-folder"></i>
                                        <span>{{ $chapter->title ?? 'Untitled Section' }}</span>
                                    </div>
                                    <i class="fas fa-chevron-down chapter-chevron"></i>
                                </div>
                                <div id="chapterCollapse{{ $chapter->id }}" class="accordion-collapse collapse" data-bs-parent="#courseAccordion{{ $course->id }}">
                                    <div class="accordion-body">
                                        @if($chapter->description)
                                            <p class="chapter-desc">{{ $chapter->description }}</p>
                                        @endif
                                        @if($chapter->modules && $chapter->modules->count() > 0)
                                            <div class="modules-table-wrap">
                                                <table class="modules-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Module</th>
                                                            <th>Type</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($chapter->modules as $module)
                                                            <tr>
                                                                <td>
                                                                    <i class="{{ $module->file_icon }}"></i>
                                                                    <span>{{ $module->title ?? 'Untitled Module' }}</span>
                                                                </td>
                                                                <td><span class="badge badge-type">{{ ucfirst($module->type ?? 'document') }}</span></td>
                                                                <td>
                                                                    <span class="badge {{ $module->is_published ? 'badge-pub' : 'badge-hidden' }}">{{ $module->is_published ? 'Published' : 'Hidden' }}</span>
                                                                    <small>Views: {{ $module->view_count ?? 0 }}</small>
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('admin.modules.preview', $module->id) }}" class="btn-open-module" target="_blank" title="Preview Module (Admin Mode)">
                                                                        <i class="fas fa-eye"></i> Preview
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted text-center py-3">No modules in this section</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">No chapters in this course</p>
                @endif
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <p>This teacher has not created any courses yet</p>
        </div>
    @endif
</div>

<!-- Rating & Reviews (from classes created by this teacher) -->
<div class="card-content mb-4">
    <h3 class="panel-title">Rating & Reviews</h3>
    <p class="rating-subtitle" style="color: #828282; font-size: 13px; margin-bottom: 24px;">From ratings and reviews of classes created by this teacher</p>

    @php
        $stats = $ratingStats ?? ['total' => 0, 'avg' => 0, 'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]];
    @endphp

    @if($stats['total'] > 0)
        <!-- Review Summary Header -->
        <div class="reviews-summary" style="background: #f8f9fa; border-radius: 12px; padding: 24px; margin-bottom: 32px;">
            <div class="rating-overview" style="display: flex; align-items: center; gap: 40px; flex-wrap: wrap;">
                <!-- Left: Big Score -->
                <div style="text-align: center;">
                    <div class="rating-number" style="font-size: 48px; font-weight: 700; color: #333333; line-height: 1; margin-bottom: 8px;">
                        {{ number_format($stats['avg'], 1) }}
                    </div>
                    <div class="rating-stars" style="display: flex; justify-content: center; gap: 4px; margin-bottom: 8px;">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= round($stats['avg']) ? 'filled' : '' }}" style="font-size: 20px; color: {{ $i <= round($stats['avg']) ? '#ffc107' : '#e0e0e0' }};"></i>
                        @endfor
                    </div>
                    <p class="rating-label" style="color: #828282; font-size: 13px; margin: 0;">
                        Teacher Rating based on {{ number_format($stats['total']) }} {{ $stats['total'] == 1 ? 'review' : 'reviews' }}
                    </p>
                </div>
                
                <!-- Right: Star Distribution Bars -->
                <div class="rating-bars" style="flex: 1; min-width: 250px;">
                    @for($i = 5; $i >= 1; $i--)
                        @php
                            $n = $stats['distribution'][$i] ?? 0;
                            $pct = $stats['total'] > 0 ? round(($n / $stats['total']) * 100, 1) : 0;
                        @endphp
                        <div class="rating-bar-row" style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <span class="star-label" style="font-size: 13px; color: #333; min-width: 50px; font-weight: 500;">
                                {{ $i }} <i class="fas fa-star" style="color: #ffc107; font-size: 12px;"></i>
                            </span>
                            <div class="rating-bar-bg" style="flex: 1; height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
                                <div class="rating-bar-fill" style="height: 100%; background: #ffc107; width: {{ $pct }}%; transition: width 0.3s ease;"></div>
                            </div>
                            <span class="percentage" style="font-size: 12px; color: #828282; min-width: 40px; text-align: right;">
                                {{ $pct }}%
                            </span>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        @php $ratingPerClass = $ratingPerClass ?? collect(); @endphp
        @if($ratingPerClass->isNotEmpty())
            <h4 style="font-size: 16px; font-weight: 600; color: #333333; margin-bottom: 16px;">Rating & Reviews per Class</h4>
            <div class="accordion" id="ratingPerClassAccordion">
                @foreach($ratingPerClass as $index => $item)
                    @php
                        $course = $item['course'];
                        $total = $item['total'];
                        $avg = $item['avg'];
                        $distribution = $item['distribution'];
                        $classReviews = $item['reviews'];
                        $collapseId = 'ratingClassCollapse' . $course->id;
                        $accordionId = 'ratingPerClassAccordion';
                    @endphp
                    <div class="accordion-item per-class-rating-item" style="border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 12px; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                <span class="d-flex align-items-center gap-3 flex-wrap">
                                    <i class="fas fa-book text-primary"></i>
                                    <strong>{{ $course->name }}</strong>
                                    @if($total > 0)
                                        <span class="badge bg-light text-dark border" style="font-size: 12px;">
                                            {{ number_format($avg, 1) }} <i class="fas fa-star text-warning" style="font-size: 10px;"></i> · {{ $total }} {{ $total == 1 ? 'review' : 'reviews' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size: 11px;">No reviews yet</span>
                                    @endif
                                </span>
                            </button>
                        </h2>
                        <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#{{ $accordionId }}">
                            <div class="accordion-body" style="background: #fafafa;">
                                @if($total > 0)
                                    <div class="reviews-summary per-class-summary" style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                                        <div class="rating-overview" style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                                            <div style="text-align: center;">
                                                <div style="font-size: 32px; font-weight: 700; color: #333;">{{ number_format($avg, 1) }}</div>
                                                <div class="rating-stars" style="display: flex; justify-content: center; gap: 2px; margin: 6px 0;">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star" style="font-size: 14px; color: {{ $i <= round($avg) ? '#ffc107' : '#e0e0e0' }};"></i>
                                                    @endfor
                                                </div>
                                                <span style="font-size: 12px; color: #828282;">{{ $total }} {{ $total == 1 ? 'review' : 'reviews' }}</span>
                                            </div>
                                            <div style="flex: 1; min-width: 200px;">
                                                @for($i = 5; $i >= 1; $i--)
                                                    @php
                                                        $n = $distribution[$i] ?? 0;
                                                        $pct = $total > 0 ? round(($n / $total) * 100, 1) : 0;
                                                    @endphp
                                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                                        <span style="font-size: 12px; color: #333; min-width: 36px;">{{ $i }} <i class="fas fa-star text-warning" style="font-size: 10px;"></i></span>
                                                        <div style="flex: 1; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden;">
                                                            <div style="height: 100%; background: #ffc107; width: {{ $pct }}%;"></div>
                                                        </div>
                                                        <span style="font-size: 11px; color: #828282; min-width: 32px; text-align: right;">{{ $pct }}%</span>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    @if($classReviews->isNotEmpty())
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                            <h5 style="font-size: 14px; font-weight: 600; color: #333; margin: 0;">Reviews in this class</h5>
                                            @if($total > 3)
                                                <a href="{{ route('admin.teachers.class.reviews', [$teacher->id, $course->id]) }}" class="btn btn-sm" style="background: #e3f2fd; color: #1976d2; border: none; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;">
                                                    <i class="fas fa-list"></i> View All ({{ $total }})
                                                </a>
                                            @endif
                                        </div>
                                        <div class="reviews-list" style="display: flex; flex-direction: column; gap: 12px;">
                                            @foreach($classReviews as $rev)
                                                <div class="review-card" style="background: white; border: 1px solid #e8e8e8; border-radius: 10px; padding: 14px;">
                                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                                                <span style="color: white; font-weight: 600; font-size: 12px;">{{ strtoupper(substr($rev->user->name ?? 'S', 0, 2)) }}</span>
                                                            </div>
                                                            <div>
                                                                <strong style="font-size: 13px;">{{ $rev->user->name ?? 'Student' }}</strong>
                                                                <small class="d-block text-muted" style="font-size: 11px;">{{ $rev->created_at?->format('d M Y, H:i') }}</small>
                                                            </div>
                                                        </div>
                                                        <span>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star" style="font-size: 12px; color: {{ $i <= ($rev->rating ?? 0) ? '#ffc107' : '#e0e0e0' }};"></i>
                                                            @endfor
                                                        </span>
                                                    </div>
                                                    @if(!empty($rev->comment))
                                                        <p class="mb-0" style="font-size: 13px; color: #333; line-height: 1.5;">{{ $rev->comment }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-4 text-muted" style="font-size: 14px;">
                                        <i class="fas fa-star" style="font-size: 32px; opacity: 0.3;"></i>
                                        <p class="mb-0 mt-2">No ratings or reviews for this class yet.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        @php $ratingPerClass = $ratingPerClass ?? collect(); @endphp
        @if($ratingPerClass->isNotEmpty())
            <h4 style="font-size: 16px; font-weight: 600; color: #333333; margin-bottom: 16px;">Rating & Reviews per Class</h4>
            <div class="accordion" id="ratingPerClassAccordionEmpty">
                @foreach($ratingPerClass as $index => $item)
                    @php
                        $course = $item['course'];
                        $total = $item['total'];
                        $avg = $item['avg'];
                        $distribution = $item['distribution'];
                        $classReviews = $item['reviews'];
                        $collapseId = 'ratingClassCollapseEmpty' . $course->id;
                        $accordionId = 'ratingPerClassAccordionEmpty';
                    @endphp
                    <div class="accordion-item per-class-rating-item" style="border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 12px; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                <span class="d-flex align-items-center gap-3 flex-wrap">
                                    <i class="fas fa-book text-primary"></i>
                                    <strong>{{ $course->name }}</strong>
                                    <span class="badge bg-secondary" style="font-size: 11px;">No reviews yet</span>
                                </span>
                            </button>
                        </h2>
                        <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#{{ $accordionId }}">
                            <div class="accordion-body" style="background: #fafafa;">
                                <div class="text-center py-4 text-muted" style="font-size: 14px;">
                                    <i class="fas fa-star" style="font-size: 32px; opacity: 0.3;"></i>
                                    <p class="mb-0 mt-2">No ratings or reviews for this class yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rating-placeholder" style="text-align: center; padding: 48px 24px; background: #f8f9fa; border-radius: 12px;">
                <i class="fas fa-star" style="font-size: 48px; color: #e0e0e0; margin-bottom: 16px; display: block;"></i>
<h4 style="color: #828282; font-size: 16px; font-weight: 600; margin-bottom: 8px;">No Ratings Yet</h4>
            <p style="color: #828282; font-size: 14px; margin: 0;">No ratings or reviews for classes created by this teacher yet.</p>
            </div>
        @endif
    @endif
</div>

<!-- Recent Activity -->
@if($activities && $activities->count() > 0)
    <div class="card-content mb-4">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 class="panel-title" style="margin: 0;">Recent Activity</h3>
            <a href="{{ route('admin.activities.user', $teacher->id) }}" 
               class="btn btn-sm" 
               style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 6px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;"
               onmouseover="this.style.background='#1976d2'; this.style.color='white'; this.style.borderColor='#1976d2';" 
               onmouseout="this.style.background='#e3f2fd'; this.style.color='#1976d2'; this.style.borderColor='#90caf9';">
                <i class="fas fa-list"></i> View All Activities
            </a>
        </div>
        <ul class="activity-list">
            @foreach($activities as $log)
                <li>
                    <span class="activity-action">
                        <i class="{{ $log->action_icon }}"></i>
                        {{ $log->formatted_description }}
                    </span>
                    <small class="activity-time">{{ $log->created_at?->format('d M Y, H:i') }}</small>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<style>
.teacher-header-card, .card-content { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.teacher-header-inner { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; }
.teacher-header-main { display: flex; align-items: flex-start; gap: 20px; flex-wrap: wrap; }
.teacher-avatar-lg { width: 64px; height: 64px; background: #e3f2fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.teacher-avatar-lg i { color: #1976d2; font-size: 28px; }
.teacher-avatar-lg .teacher-avatar-img-lg { width: 100%; height: 100%; object-fit: cover; }
.teacher-avatar-lg .teacher-avatar-fallback-lg { display: none; color: #1976d2; font-size: 28px; }
.teacher-avatar-lg .teacher-avatar-fallback-lg[style*="display: flex"] { display: flex !important; }
.teacher-title { font-size: 24px; font-weight: 700; color: #333; margin: 0 0 8px 0; }
.teacher-meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 12px; }
.teacher-meta span { color: #666; font-size: 14px; }
.teacher-status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.teacher-status-badge.status-active { background: #d4edda; color: #155724; }
.teacher-status-badge.status-banned { background: #f8d7da; color: #721c24; }
.teacher-actions-header .btn { padding: 6px 14px; font-size: 13px; border-radius: 6px; border: none; }
.teacher-actions-header .btn-ban { background: #fff3e0; color: #f57c00; }
.teacher-actions-header .btn-unban { background: #e8f5e9; color: #2e7d32; }

.panel-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px; }
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; }
.detail-item { }
.detail-label { display: block; color: #828282; font-size: 13px; margin-bottom: 4px; }
.detail-item strong { color: #333; font-size: 16px; }
.detail-full { grid-column: 1 / -1; }
.detail-bio { color: #333; font-size: 14px; line-height: 1.6; margin: 8px 0 0 0; }

.course-block { border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; margin-bottom: 16px; background: #fafafa; }
.course-block:last-child { margin-bottom: 0; }
.course-block-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
.course-block-title { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.course-block-title i { color: #2F80ED; }
.course-block-title span { font-weight: 600; color: #333; }
.course-meta-sm { font-weight: normal; color: #828282; font-size: 12px; }
.btn-moderation { background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 6px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
.btn-moderation:hover { background: #1976d2; color: white; border-color: #1976d2; }

.teacher-curriculum { border: none; }
.chapter-item { border: 1px solid #e8e8e8; border-radius: 8px; padding: 14px; margin-bottom: 12px; background: white; }
.chapter-header { display: flex; align-items: center; justify-content: space-between; cursor: pointer; }
.chapter-title { display: flex; align-items: center; gap: 10px; }
.chapter-title i { color: #2F80ED; font-size: 16px; }
.chapter-title span { font-size: 14px; color: #333; }
.chapter-chevron { color: #666; font-size: 14px; transition: transform 0.2s; }
.chapter-item .accordion-collapse.show + .chapter-header .chapter-chevron { transform: rotate(180deg); }
.chapter-item .accordion-body { padding: 16px 0 0 0; background: transparent; }
.chapter-desc { font-size: 13px; color: #666; margin-bottom: 16px; }
.modules-table-wrap { overflow-x: auto; }
.modules-table { width: 100%; font-size: 13px; min-width: 500px; border-collapse: collapse; }
.modules-table th { padding: 10px 8px; color: #828282; font-size: 11px; text-transform: uppercase; text-align: left; border-bottom: 1px solid #eee; }
.modules-table td { padding: 12px 8px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
.modules-table td i { color: #2F80ED; margin-right: 8px; }
.badge-type { background: #e3f2fd; color: #1976d2; font-size: 11px; padding: 4px 8px; border-radius: 4px; }
.badge-pub { background: #e8f5e9; color: #27AE60; font-size: 11px; padding: 4px 8px; border-radius: 4px; }
.badge-hidden { background: #fce4ec; color: #c2185b; font-size: 11px; padding: 4px 8px; border-radius: 4px; }
.modules-table td small { display: block; color: #828282; font-size: 10px; margin-top: 2px; }
.btn-open-module { background: transparent; color: #2F80ED; border: 1px solid #90caf9; padding: 6px 10px; font-size: 11px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.btn-open-module:hover { background: #e3f2fd; color: #1976d2; }

/* Rating & Reviews Styles */
.rating-subtitle {
    color: #828282;
    font-size: 13px;
    margin-bottom: 24px;
}

.reviews-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 32px;
}

.rating-overview {
    display: flex;
    align-items: center;
    gap: 40px;
    flex-wrap: wrap;
}

.rating-number {
    font-size: 48px;
    font-weight: 700;
    color: #333333;
    line-height: 1;
    margin-bottom: 8px;
}

.rating-stars {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-bottom: 8px;
}

.rating-stars i.filled {
    color: #ffc107;
}

.rating-stars i:not(.filled) {
    color: #e0e0e0;
}

.rating-label {
    color: #828282;
    font-size: 13px;
    margin: 0;
}

.rating-bars {
    flex: 1;
    min-width: 250px;
}

.rating-bar-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.star-label {
    font-size: 13px;
    color: #333;
    min-width: 50px;
    font-weight: 500;
}

.rating-bar-bg {
    flex: 1;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar-fill {
    height: 100%;
    background: #ffc107;
    transition: width 0.3s ease;
}

.percentage {
    font-size: 12px;
    color: #828282;
    min-width: 40px;
    text-align: right;
}

.review-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

.review-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.review-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.review-card-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.reviewer-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.avatar-initial {
    color: white;
    font-weight: 600;
    font-size: 18px;
}

.review-user-info {
    flex: 1;
}

.reviewer-name {
    font-size: 15px;
    font-weight: 600;
    color: #333333;
    margin: 0 0 4px 0;
}

.review-date {
    color: #828282;
    font-size: 12px;
}

.review-card-rating {
    margin-bottom: 12px;
}

.review-card-rating i.filled {
    color: #ffc107;
}

.review-card-rating i.empty {
    color: #e0e0e0;
}

.review-card-content {
    margin-top: 8px;
}

.review-text {
    color: #333333;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.rating-placeholder {
    text-align: center;
    padding: 48px 24px;
    background: #f8f9fa;
    border-radius: 12px;
}

.rating-placeholder i {
    font-size: 48px;
    color: #e0e0e0;
    margin-bottom: 16px;
    display: block;
}

.rating-placeholder h4 {
    color: #828282;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
}

.rating-placeholder p {
    color: #828282;
    font-size: 14px;
    margin: 0;
}

/* Rating per Kelas accordion */
.per-class-rating-item .accordion-button {
    background: #f8f9fa;
    font-size: 14px;
    padding: 14px 16px;
    box-shadow: none;
    border: none;
}

.per-class-rating-item .accordion-button:not(.collapsed) {
    background: #e3f2fd;
    color: #1976d2;
}

.per-class-rating-item .accordion-button::after {
    margin-left: auto;
}

.per-class-summary .rating-overview {
    gap: 20px;
}

/* Responsive Rating & Reviews */
@media (max-width: 768px) {
    .rating-overview {
        flex-direction: column;
        gap: 24px;
        align-items: stretch;
    }

    .rating-number {
        font-size: 36px;
    }

    .rating-stars i {
        font-size: 16px;
    }

    .rating-bars {
        min-width: 100%;
    }

    .review-card {
        padding: 16px;
    }

    .reviewer-avatar {
        width: 40px;
        height: 40px;
    }

    .avatar-initial {
        font-size: 16px;
    }

    .reviewer-name {
        font-size: 14px;
    }
}

.activity-list { list-style: none; padding: 0; margin: 0; }
.activity-list li { padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; flex-wrap: wrap; gap: 8px; align-items: baseline; }
.activity-list li:last-child { border-bottom: none; }
.activity-action { font-weight: 600; color: #333; font-size: 13px; }
.activity-desc { color: #666; font-size: 13px; flex: 1; min-width: 0; }
.activity-time { color: #999; font-size: 11px; }

.empty-state { text-align: center; padding: 40px; color: #828282; }
.empty-state i { font-size: 48px; color: #e0e0e0; display: block; margin-bottom: 12px; }

.teacher-footer-actions { display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; margin-top: 24px; margin-bottom: 24px; }
.teacher-footer-actions a { padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
.btn-back { background: #6c757d; color: white; }
.btn-back:hover { color: white; opacity: 0.9; }
.btn-dashboard { background: #2F80ED; color: white; }
.btn-dashboard:hover { color: white; opacity: 0.9; }

@media (max-width: 768px) {
    .teacher-header-card, .card-content { padding: 16px; }
    .teacher-header-main { flex-direction: column; align-items: flex-start; }
    .teacher-title { font-size: 20px; }
    .detail-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .course-block-header { flex-direction: column; align-items: flex-start; }
    .course-block { padding: 12px; }
    .chapter-item { padding: 12px; }
    .modules-table { font-size: 12px; min-width: 450px; }
    
    /* Rating & Reviews Responsive */
    .reviews-summary { padding: 20px; }
    .rating-overview { flex-direction: column; gap: 24px; align-items: stretch; }
    .rating-number { font-size: 36px; }
    .rating-stars i { font-size: 16px; }
    .rating-bars { min-width: 100%; }
    .review-card { padding: 16px; }
    .reviewer-avatar { width: 40px; height: 40px; }
    .avatar-initial { font-size: 16px; }
    .reviewer-name { font-size: 14px; }
}
@media (max-width: 480px) {
    .detail-grid { grid-template-columns: 1fr; }
    .teacher-footer-actions { flex-direction: column; }
    .teacher-footer-actions a { justify-content: center; }
    
    /* Rating & Reviews Mobile */
    .reviews-summary { padding: 16px; }
    .rating-number { font-size: 32px; }
    .rating-stars i { font-size: 14px; }
    .review-card { padding: 12px; }
    .review-card-user { flex-direction: column; align-items: flex-start; }
    .reviewer-avatar { width: 36px; height: 36px; }
    .avatar-initial { font-size: 14px; }
}
</style>

<script>
// Auto-refresh for real-time status updates
let refreshInterval;

function refreshTeacherStatus() {
    fetch('{{ route("admin.teachers.show", $teacher->id) }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse the HTML to extract status information
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Find the status badges in the parsed HTML
        const statusContainer = doc.querySelector('.teacher-meta div[style*="flex"]');
        const currentStatusContainer = document.querySelector('.teacher-meta div[style*="flex"]');
        
        if (statusContainer && currentStatusContainer) {
            currentStatusContainer.innerHTML = statusContainer.innerHTML;
        }
    })
    .catch(error => {
        console.log('Status refresh failed:', error);
    });
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Refresh status every 30 seconds
    refreshInterval = setInterval(refreshTeacherStatus, 30000);
    
    // Stop refresh when page is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(refreshInterval);
        } else {
            refreshInterval = setInterval(refreshTeacherStatus, 30000);
        }
    });
});

// Clean up interval when page unloads
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.teacher-curriculum .chapter-item').forEach(function(item) {
        var header = item.querySelector('.chapter-header');
        var collapse = item.querySelector('.accordion-collapse');
        var chevron = header ? header.querySelector('.chapter-chevron') : null;
        if (!collapse || !chevron) return;
        collapse.addEventListener('shown.bs.collapse', function() { chevron.style.transform = 'rotate(180deg)'; });
        collapse.addEventListener('hidden.bs.collapse', function() { chevron.style.transform = 'rotate(0deg)'; });
    });
});
</script>
@endsection
