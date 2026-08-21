@extends('layouts.admin')

@section('title', 'Course Inspection & Moderation')

@section('content')
<div class="page-title">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Course Inspection & Moderation</h1>
            <p style="color: #828282; margin: 5px 0 0 0; font-size: 14px;">Review course content for policy violations</p>
        </div>
        <a href="{{ back_url(route('admin.dashboard')) }}" class="btn" style="background: #2F80ED; color: white; border: none; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500;">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<!-- Action Bar (Sticky at Top) -->
<div class="card-content mb-4" style="position: sticky; top: 20px; z-index: 100; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; gap: 10px; align-items: center;">
            <span style="color: #828282; font-size: 13px; font-weight: 600;">Course Actions:</span>
            @php
                $isCourseModel = $course instanceof App\Models\Course;
                $courseStatus = $isCourseModel ? $course->status : ($course->is_published ? 'active' : 'inactive');
                $courseId = $course->id;
            @endphp
            @if($isCourseModel)
                <!-- Approve Course Button -->
                @if($courseStatus === 'inactive')
                    <form action="{{ route('admin.courses.toggle-status', $courseId) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn" style="background: #27AE60; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 500;">
                            <i class="fas fa-check-circle me-2"></i>Approve Course
                        </button>
                    </form>
                @endif
                <!-- Suspend Course Button -->
                @if($courseStatus === 'active')
                    <form action="{{ route('admin.courses.toggle-status', $courseId) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn" style="background: #ff9800; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 500;"
                                onclick="return confirm('Are you sure you want to suspend this entire course?');">
                            <i class="fas fa-ban me-2"></i>Suspend Entire Course
                        </button>
                    </form>
                @endif
            @else
                <!-- For ClassModel (old structure) -->
                @if($course->is_published)
                    <form action="{{ route('admin.courses.toggle-status', $courseId) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn" style="background: #ff9800; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 500;"
                                onclick="return confirm('Are you sure you want to suspend this entire course?');">
                            <i class="fas fa-ban me-2"></i>Suspend Entire Course
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.courses.toggle-status', $courseId) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn" style="background: #27AE60; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 500;">
                            <i class="fas fa-check-circle me-2"></i>Approve Course
                        </button>
                    </form>
                @endif
            @endif
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <span class="badge" style="background: {{ $courseStatus === 'active' || ($course->is_published ?? false) ? '#27AE60' : '#828282' }}; color: white; font-size: 12px; padding: 6px 12px;">
                Status: {{ $courseStatus === 'active' || ($course->is_published ?? false) ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
</div>

<!-- Course Info Card -->
<div class="card-content mb-4">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div style="flex: 1;">
            <h2 style="font-size: 24px; font-weight: 700; color: #333333; margin-bottom: 10px;">
                {{ $isCourseModel ? $course->title : $course->name }}
            </h2>
            <div style="display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;">
                <div>
                    <span style="color: #828282; font-size: 13px;">Teacher:</span>
                    <span style="color: #333333; font-weight: 500; margin-left: 5px;">{{ $course->teacher->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span style="color: #828282; font-size: 13px;">Email:</span>
                    <span style="color: #333333; font-weight: 500; margin-left: 5px;">{{ $course->teacher->email ?? 'N/A' }}</span>
                </div>
                <div>
                    <span style="color: #828282; font-size: 13px;">Category:</span>
                    @if($isCourseModel && $course->category)
                        <span class="badge" style="background: #e3f2fd; color: #1976d2; font-size: 12px; padding: 4px 10px;">
                            {{ $course->category->name }}
                        </span>
                    @else
                        @php
                            $categories = \App\Models\ClassModel::getAvailableCategories();
                            $categoryLabel = $categories[$course->category ?? ''] ?? ucfirst($course->category ?? 'N/A');
                        @endphp
                        <span class="badge" style="background: #e3f2fd; color: #1976d2; font-size: 12px; padding: 4px 10px;">
                            {{ $categoryLabel }}
                        </span>
                    @endif
                </div>
                @php $tier = $course->price_tier ?? null; @endphp
                @if($tier)
                    <div>
                        <span style="color: #828282; font-size: 13px;">Tier:</span>
                        <span class="badge" style="background: {{ $tier === 'standard' ? '#e8f5e9' : '#f3e8ff' }}; color: {{ $tier === 'standard' ? '#2e7d32' : '#6a1b9a' }}; font-size: 12px; padding: 4px 10px;">
                            {{ ucfirst($tier) }}
                        </span>
                    </div>
                @endif
                @if($isCourseModel)
                    <div>
                        <span style="color: #828282; font-size: 13px;">Price:</span>
                        <span style="color: #333333; font-weight: 500; margin-left: 5px;">Rp {{ number_format($course->price ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span style="color: #828282; font-size: 13px;">Total Sales:</span>
                        <span style="color: #333333; font-weight: 500; margin-left: 5px;">{{ number_format($course->total_sales ?? 0, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
            @if($isCourseModel ? $course->description : ($course->description ?? null))
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <p style="color: #333333; font-size: 14px; line-height: 1.6; margin: 0;">
                        {{ $isCourseModel ? $course->description : $course->description }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Course Content: Materi (for Course model) or Chapters/Modules (for ClassModel) -->
<div class="card-content">
    <h3 style="font-size: 18px; font-weight: 700; color: #333333; margin-bottom: 20px;">
        <i class="fas fa-sitemap me-2"></i>Course Content
    </h3>

    @if($isCourseModel)
        <!-- Course Model Structure: Materi (Sections) -->
        @if($course->materi && $course->materi->count() > 0)
            <div class="accordion" id="materiAccordion">
                @foreach($course->materi as $materiIndex => $materi)
                    <div class="accordion-item" style="border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                        <h2 class="accordion-header" id="materiHeading{{ $materi->id }}">
                            <button class="accordion-button {{ $materiIndex === 0 ? '' : 'collapsed' }}" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#materiCollapse{{ $materi->id }}" 
                                    aria-expanded="{{ $materiIndex === 0 ? 'true' : 'false' }}" 
                                    aria-controls="materiCollapse{{ $materi->id }}"
                                    style="background: #f8f9fa; font-weight: 600; color: #333333;">
                                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-right: 15px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <i class="fas {{ $materi->type === 'pdf' ? 'fa-file-pdf' : 'fa-video' }}" style="color: {{ $materi->type === 'pdf' ? '#c62828' : '#2F80ED' }};"></i>
                                        <span>{{ $materi->title }}</span>
                                        <span class="badge" style="background: #e3f2fd; color: #1976d2; font-size: 11px; margin-left: 10px;">
                                            {{ strtoupper($materi->type) }}
                                        </span>
                                    </div>
                                    <div style="display: flex; gap: 8px;" onclick="event.stopPropagation();">
                                        <!-- Preview Button -->
                                        @if($materi->file_path)
                                            <a href="{{ route('admin.materi.preview', $materi->id) }}" 
                                               target="_blank"
                                               class="btn btn-sm" 
                                               style="background: #e3f2fd; color: #1976d2; border: none; padding: 4px 10px; font-size: 11px; border-radius: 4px; text-decoration: none;"
                                               title="Preview {{ strtoupper($materi->type) }}">
                                                <i class="fas {{ $materi->type === 'pdf' ? 'fa-eye' : 'fa-play' }}"></i> Preview
                                            </a>
                                        @endif
                                        <!-- Suspend/Hide Button -->
                                        <form action="{{ route('admin.materi.suspend', $materi->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm" 
                                                    style="background: #ff9800; color: white; border: none; padding: 4px 10px; font-size: 11px; border-radius: 4px;"
                                                    title="Suspend/Hide this content"
                                                    onclick="return confirm('Are you sure you want to suspend/hide this content?');">
                                                <i class="fas fa-ban"></i> Suspend
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="materiCollapse{{ $materi->id }}" 
                             class="accordion-collapse collapse {{ $materiIndex === 0 ? 'show' : '' }}" 
                             aria-labelledby="materiHeading{{ $materi->id }}" 
                             data-bs-parent="#materiAccordion">
                            <div class="accordion-body" style="padding: 20px; background: white;">
                                <div style="display: flex; flex-direction: column; gap: 15px;">
                                    <div>
                                        <strong style="color: #333333; font-size: 13px;">File Path:</strong>
                                        <span style="color: #828282; font-size: 12px; margin-left: 8px;">{{ $materi->file_path ?? 'N/A' }}</span>
                                    </div>
                                    @if($materi->file_path)
                                        <div>
                                            <a href="{{ route('admin.materi.preview', $materi->id) }}" 
                                               target="_blank"
                                               class="btn" 
                                               style="background: {{ $materi->type === 'pdf' ? '#c62828' : '#2F80ED' }}; color: white; border: none; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                                                <i class="fas {{ $materi->type === 'pdf' ? 'fa-file-pdf' : 'fa-play-circle' }}"></i>
                                                {{ $materi->type === 'pdf' ? 'View PDF' : 'Watch Video' }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #828282;">
                <i class="fas fa-inbox" style="font-size: 48px; color: #e0e0e0; margin-bottom: 15px;"></i>
                <p style="font-size: 14px;">No content found in this course</p>
            </div>
        @endif
    @else
        <!-- ClassModel Structure: Chapters & Modules (existing code) -->
        @if($course->chapters && $course->chapters->count() > 0)
            <div class="accordion" id="chaptersAccordion">
                @foreach($course->chapters as $chapterIndex => $chapter)
                    <div class="accordion-item" style="border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; overflow: hidden;">
                        <h2 class="accordion-header" id="chapterHeading{{ $chapter->id }}">
                            <button class="accordion-button {{ $chapterIndex === 0 ? '' : 'collapsed' }}" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#chapterCollapse{{ $chapter->id }}" 
                                    aria-expanded="{{ $chapterIndex === 0 ? 'true' : 'false' }}" 
                                    aria-controls="chapterCollapse{{ $chapter->id }}"
                                    style="background: #f8f9fa; font-weight: 600; color: #333333;">
                                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-right: 15px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <i class="fas fa-folder-open" style="color: #2F80ED;"></i>
                                        <span>{{ $chapter->title }}</span>
                                        <span class="badge" style="background: {{ $chapter->is_published ? '#27AE60' : '#828282' }}; color: white; font-size: 11px; margin-left: 10px;">
                                            {{ $chapter->is_published ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span class="badge bg-info" style="font-size: 11px; margin-left: 5px;">
                                            {{ $chapter->modules->count() }} Modules
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="chapterCollapse{{ $chapter->id }}" 
                             class="accordion-collapse collapse {{ $chapterIndex === 0 ? 'show' : '' }}" 
                             aria-labelledby="chapterHeading{{ $chapter->id }}" 
                             data-bs-parent="#chaptersAccordion">
                            <div class="accordion-body" style="padding: 20px; background: white;">
                                @if($chapter->modules && $chapter->modules->count() > 0)
                                    <div style="margin-top: 15px;">
                                        <h5 style="font-size: 14px; font-weight: 600; color: #333333; margin-bottom: 15px;">
                                            <i class="fas fa-list me-2"></i>Modules ({{ $chapter->modules->count() }})
                                        </h5>
                                        <div class="row">
                                            @foreach($chapter->modules as $module)
                                                <div class="col-md-6 mb-3">
                                                    <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; background: #fafafa;">
                                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                                            <div style="flex: 1;">
                                                                <h6 style="font-size: 14px; font-weight: 600; color: #333333; margin: 0 0 8px 0;">
                                                                    {{ $module->title }}
                                                                </h6>
                                                                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                                                                    <span class="badge" style="background: #e3f2fd; color: #1976d2; font-size: 10px;">
                                                                        {{ ucfirst($module->type) }}
                                                                    </span>
                                                                    <span class="badge" style="background: {{ $module->is_published ? '#27AE60' : '#828282' }}; color: white; font-size: 10px;">
                                                                        {{ $module->is_published ? 'Active' : 'Inactive' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div style="display: flex; gap: 6px; flex-direction: column;">
                                                                <!-- Preview Button -->
                                                                @if(($module->type === 'document' || $module->type === 'video') && $module->file_path)
                                                                    <a href="{{ route('admin.modules.preview', $module->id) }}" 
                                                                       target="_blank"
                                                                       class="btn btn-sm" 
                                                                       style="background: #e3f2fd; color: #1976d2; border: none; padding: 4px 8px; font-size: 10px; border-radius: 4px; text-decoration: none; width: 100%;"
                                                                       title="Preview">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                @endif
                                                                <!-- Suspend/Hide Button -->
                                                                <form action="{{ route('admin.modules.toggle-status', $module->id) }}" method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-sm" 
                                                                            style="background: #ff9800; color: white; border: none; padding: 4px 8px; font-size: 10px; border-radius: 4px; width: 100%;"
                                                                            title="Suspend/Hide Module">
                                                                        <i class="fas fa-ban"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <p style="color: #828282; font-size: 13px; text-align: center; padding: 20px;">
                                        <i class="fas fa-inbox"></i> No modules in this chapter
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #828282;">
                <i class="fas fa-folder-open" style="font-size: 48px; color: #e0e0e0; margin-bottom: 15px;"></i>
                <p style="font-size: 14px;">No chapters found in this course</p>
            </div>
        @endif
    @endif
</div>

@endsection
