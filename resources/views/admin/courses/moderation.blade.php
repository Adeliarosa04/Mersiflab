@extends('layouts.admin')

@section('title', 'Course Approval')

@section('content')
<div class="page-title">
    <div>
        <h1>Course Approval</h1>
        <p style="color: #828282; margin: 5px 0 0 0; font-size: 14px;">Review and approve course for publication</p>
    </div>
</div>

@session('success')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endsession

@session('error')
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endsession

<!-- Back to Courses Button -->
<div style="margin-bottom: 20px; text-align: right;">
    <a href="{{ back_url(route('admin.courses.index')) }}" 
       style="background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; white-space: nowrap;"
       onmouseover="this.style.background='#545b62'; this.style.transform='translateY(-1px)';" 
       onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
</div>

<!-- Course Header Info -->
<div class="card-content mb-4" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1; min-width: 0;">
            <h2 style="font-size: 24px; font-weight: 700; color: #333333; margin-bottom: 10px; word-wrap: break-word;">
                {{ $course->name ?? 'Untitled Course' }}
            </h2>
            <div style="display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap; align-items: center;">
                <div>
                    <span style="color: #828282; font-size: 13px;">Teacher:</span>
                    <strong style="color: #333333; margin-left: 5px;">{{ $course->teacher->name ?? 'N/A' }}</strong>
                </div>
                <div>
                    <span style="color: #828282; font-size: 13px;">Email:</span>
                    <span style="color: #333333; margin-left: 5px;">{{ $course->teacher->email ?? 'N/A' }}</span>
                </div>
                <div>
                    <span style="color: #828282; font-size: 13px;">Status:</span>
                    @php
                        $statusLabel = $course->status_label;
                    @endphp
                    <span class="badge" style="background: 
                        @if($course->status === 'published') #e8f5e9; color: #27AE60;
                        @elseif($course->status === 'pending_approval') #fff3cd; color: #856404;
                        @elseif($course->status === 'rejected') #f8d7da; color: #721c24;
                        @else #e2e3e5; color: #383d41;
                        @endif; font-size: 12px; padding: 4px 10px; border-radius: 4px; font-weight: 500; text-transform: uppercase;">
                        {{ $statusLabel['text'] }}
                    </span>
                </div>
                @if($course->status === 'published')
                    <div>
                        <a href="{{ route('course.detail', $course->id) }}" 
                           class="btn btn-sm" 
                           style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 6px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;"
                           target="_blank"
                           title="View Published Course"
                           onmouseover="this.style.background='#1976d2'; this.style.color='white'; this.style.borderColor='#1976d2';" 
                           onmouseout="this.style.background='#e3f2fd'; this.style.color='#1976d2'; this.style.borderColor='#90caf9';">
                            <i class="fas fa-external-link-alt"></i> View Course
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @if($course->image)
            <div style="flex-shrink: 0;">
                <img src="{{ asset('storage/' . $course->image) }}" 
                     alt="{{ $course->name }}" 
                     style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            </div>
        @endif
    </div>
</div>

<!-- Course Details -->
<div class="card-content mb-4" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <h3 style="font-size: 18px; font-weight: 700; color: #333333; margin-bottom: 20px;">Course Details</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div>
            <span style="color: #828282; font-size: 13px; display: block; margin-bottom: 5px;">Category:</span>
            <span class="badge" style="background: #e3f2fd; color: #1976d2; font-size: 12px; padding: 4px 10px; border-radius: 4px;">
                {{ $course->category_name }}
            </span>
        </div>
        <div>
            <span style="color: #828282; font-size: 13px; display: block; margin-bottom: 5px;">Price:</span>
            <strong style="color: #333333; font-size: 16px;">Rp {{ number_format($course->price ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span style="color: #828282; font-size: 13px; display: block; margin-bottom: 5px;">Total Sales:</span>
            <strong style="color: #333333; font-size: 16px;">{{ number_format($course->purchases_count ?? 0, 0, ',', '.') }}</strong>
        </div>
        <div>
            <span style="color: #828282; font-size: 13px; display: block; margin-bottom: 5px;">Total Chapters:</span>
            <strong style="color: #333333; font-size: 16px;">{{ $course->chapters_count ?? 0 }}</strong>
        </div>
        <div>
            <span style="color: #828282; font-size: 13px; display: block; margin-bottom: 5px;">Total Modules:</span>
            <strong style="color: #333333; font-size: 16px;">{{ $course->modules_count ?? 0 }}</strong>
        </div>
        @if($course->formatted_total_duration)
        <div>
            <span style="color: #828282; font-size: 13px; display: block; margin-bottom: 5px;">Duration:</span>
            <strong style="color: #333333; font-size: 16px;">{{ $course->formatted_total_duration }}</strong>
        </div>
        @endif
    </div>
    @if($course->description)
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f0;">
            <span style="color: #828282; font-size: 13px; display: block; margin-bottom: 10px;">Description:</span>
            <p style="color: #333333; font-size: 14px; line-height: 1.6; margin: 0;">{{ $course->description }}</p>
        </div>
    @endif
    @if($course->admin_feedback)
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
            <span style="color: #856404; font-size: 13px; font-weight: 600; display: block; margin-bottom: 5px;">Admin Feedback:</span>
            <p style="color: #856404; font-size: 14px; line-height: 1.6; margin: 0;">{{ $course->admin_feedback }}</p>
        </div>
    @endif
</div>

<!-- Course Content -->
<div class="card-content mb-4" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <h3 style="font-size: 18px; font-weight: 700; color: #333333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-list" style="color: #2F80ED;"></i>
        Course Content
    </h3>
    
    @if($course->chapters && $course->chapters->count() > 0)
        <div class="accordion" id="curriculumAccordion" style="border: none; border-radius: 0;">
            @foreach($course->chapters as $chapterIndex => $chapter)
                <div class="chapter-item" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; margin-bottom: {{ $loop->last ? '0' : '16px' }}; background: white; border-left: 3px solid #2F80ED; transition: box-shadow 0.2s ease;">
                    <!-- Chapter Header (Clickable) -->
                    <div style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;"
                         data-bs-toggle="collapse" 
                         data-bs-target="#chapterCollapse{{ $chapter->id }}"
                         aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                         aria-controls="chapterCollapse{{ $chapter->id }}">
                        <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                            <i class="fas fa-folder" style="color: #2F80ED; font-size: 18px; flex-shrink: 0;"></i>
                            <div style="min-width: 0;">
                                <span style="font-size: 14px; font-weight: 600; color: #333333; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $chapter->title ?? 'Untitled Section' }}</span>
                                <small style="color: #828282; font-size: 12px;">{{ $chapter->modules->count() ?? 0 }} modules • {{ $chapter->formatted_total_duration ?? '0 min' }}</small>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down chapter-chevron" 
                           style="color: #333; font-size: 14px; transition: transform 0.2s ease-in-out; flex-shrink: 0; margin-left: 12px; {{ $loop->first ? 'transform: rotate(180deg);' : '' }}"></i>
                    </div>
                    
                    <!-- Collapsible Content -->
                    <div id="chapterCollapse{{ $chapter->id }}" 
                         class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                         aria-labelledby="chapterHeading{{ $chapter->id }}" 
                         data-bs-parent="#curriculumAccordion">
                        <div class="accordion-body" style="padding: 20px 0 0 0; background: transparent; position: relative;">
                            <!-- Description -->
                            @if($chapter->description)
                            <div style="margin-bottom: 20px; padding: 12px; background: #f8f9fa; border-radius: 6px;">
                                <span style="color: #6c757d; font-size: 12px; font-weight: 600; display: block; margin-bottom: 5px;">Description</span>
                                <p style="color: #333333; font-size: 13px; margin: 0; line-height: 1.5;">{{ $chapter->description }}</p>
                            </div>
                            @endif
                            
                            @if($chapter->modules && $chapter->modules->count() > 0)
                                <!-- Module List -->
                                <div style="margin-bottom: 20px;">
                                    @foreach($chapter->modules as $module)
                                        <div class="module-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; margin-bottom: 8px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #2F80ED; transition: all 0.2s ease;" 
                                             onmouseover="this.style.backgroundColor='#e9ecef'; this.style.transform='translateX(4px);'" 
                                             onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.transform='translateX(0);'">
                                            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                                                <div style="flex-shrink: 0;">
                                                    @if($module->type === 'text')
                                                        <i class="fas fa-file-alt" style="color: #007bff; font-size: 16px;"></i>
                                                    @elseif($module->type === 'document')
                                                        <i class="fas fa-file-pdf" style="color: #dc3545; font-size: 16px;"></i>
                                                    @else
                                                        <i class="fas fa-video" style="color: #17a2b8; font-size: 16px;"></i>
                                                    @endif
                                                </div>
                                                <div style="min-width: 0;">
                                                    <div style="font-weight: 500; color: #333333; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $module->title ?? 'Untitled Module' }}</div>
                                                    <small style="color: #6c757d; font-size: 11px;">
                                                        {{ ucfirst($module->type ?? 'document') }} • 
                                                        {{ $module->estimated_duration ?? 0 }} min • 
                                                        {{ $module->view_count ?? 0 }} views
                                                    </small>
                                                </div>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                                <a href="{{ route('admin.modules.preview', $module->id) }}" 
                                                   style="background: transparent; color: #2F80ED; border: 1px solid #90caf9; padding: 4px 8px; font-size: 10px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; white-space: nowrap;"
                                                   title="Preview Module"
                                                   target="_blank"
                                                   onmouseover="this.style.background='#e3f2fd'; this.style.borderColor='#2F80ED';" 
                                                   onmouseout="this.style.background='transparent'; this.style.borderColor='#90caf9';">
                                                    <i class="fas fa-eye" style="font-size: 9px;"></i>Preview
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="text-align: center; padding: 20px; color: #6c757d;">
                                    <i class="fas fa-folder-open" style="font-size: 24px; color: #dee2e6; margin-bottom: 8px;"></i>
                                    <p style="font-size: 13px; margin: 0;">No modules in this section</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 40px; color: #6c757d;">
            <i class="fas fa-book-open" style="font-size: 48px; color: #dee2e6; margin-bottom: 10px;"></i>
            <p style="font-size: 14px; margin: 0;">No chapters in this course</p>
        </div>
    @endif
</div>

<!-- Approval Actions -->
@if($course->status === 'pending_approval')
<div class="card-content mb-4" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <h3 style="font-size: 18px; font-weight: 700; color: #333333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-gavel" style="color: #007bff;"></i>
        Course Approval
    </h3>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <button type="button" 
                class="btn btn-success"
                data-bs-toggle="modal" 
                data-bs-target="#approveModal">
            <i class="fas fa-check-circle me-2"></i> Approve Course
        </button>
        <button type="button" 
                class="btn btn-danger"
                data-bs-toggle="modal" 
                data-bs-target="#rejectModal">
            <i class="fas fa-times-circle me-2"></i> Reject Course
        </button>
    </div>
</div>
@if($course->status === 'rejected')
<div class="card-content mb-4" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <h3 style="font-size: 18px; font-weight: 700; color: #333333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-times-circle" style="color: #dc3545;"></i>
        Course Rejected
    </h3>
    <div style="padding: 15px; background: #f8d7da; border-radius: 6px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; color: #721c24;">
            <i class="fas fa-times-circle" style="font-size: 20px;"></i>
            <span style="font-size: 14px;">This course has been rejected.</span>
        </div>
    </div>
    @if($course->admin_feedback)
        <div style="margin-bottom: 20px; padding: 15px; background: #fff3cd; border-radius: 6px; border-left: 3px solid #ffc107;">
            <strong style="color: #856404; font-size: 14px; display: block; margin-bottom: 8px;">Rejection Reason:</strong>
            <p style="color: #856404; font-size: 14px; margin: 0; line-height: 1.5;">{{ $course->admin_feedback }}</p>
        </div>
    @endif
</div>
@endif
@else
<div class="card-content mb-4" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <h3 style="font-size: 18px; font-weight: 700; color: #333333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-info-circle" style="color: #6c757d;"></i>
        Course Status
    </h3>
    <div style="padding: 15px; background: #e2e3e5; border-radius: 6px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; color: #383d41;">
            <i class="fas fa-info-circle" style="font-size: 20px;"></i>
            <span style="font-size: 14px;">This course is currently in <strong>{{ $statusLabel['text'] }}</strong> status.</span>
        </div>
    </div>
</div>
@endif

<style>
/* Main Styles */
.page-title {
    margin-bottom: 20px;
}

.card-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Chapter Accordion Styles */
#curriculumAccordion .chapter-item {
    transition: all 0.2s ease;
    border-left: 3px solid #2F80ED;
}

#curriculumAccordion .chapter-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

#curriculumAccordion .chapter-chevron {
    transition: transform 0.2s ease-in-out;
}

#curriculumAccordion .chapter-chevron[aria-expanded="true"] {
    transform: rotate(180deg);
}

#curriculumAccordion .accordion-body {
    background: transparent;
    border-top: none;
}

/* Module Item Styles */
.module-item {
    transition: all 0.2s ease;
    border-left: 3px solid #2F80ED;
}

.module-item:hover {
    background-color: #e9ecef !important;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Form Styles */
form textarea {
    font-family: inherit;
}

form button {
    font-family: inherit;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .page-title h1 {
        font-size: 24px;
    }
    
    .card-content {
        margin-bottom: 16px;
        padding: 16px;
    }
    
    #curriculumAccordion .chapter-item {
        padding: 12px;
        margin-bottom: 12px;
    }
    
    #curriculumAccordion .chapter-item > div:first-child {
        padding: 8px 0;
    }
    
    #curriculumAccordion .chapter-item .fas.fa-folder {
        font-size: 16px;
    }
    
    #curriculumAccordion .chapter-item span {
        font-size: 13px;
    }
    
    #curriculumAccordion .accordion-body {
        padding: 16px 0 0 0;
    }
    
    .module-item {
        padding: 10px;
        margin-bottom: 6px;
    }
    
    .module-item > div:first-child {
        gap: 8px;
    }
    
    .module-item .fas {
        font-size: 14px;
    }
    
    .module-item span {
        font-size: 12px;
    }
    
    .module-item a {
        font-size: 9px;
        padding: 3px 6px;
    }
    
    /* Approval Forms Mobile */
    div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
    }
    
    form textarea {
        min-height: 60px !important;
        font-size: 14px;
    }
    
    form button {
        padding: 10px 16px;
        font-size: 14px;
    }
    
    /* Course Details Mobile */
    div[style*="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr))"] {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
    }
    
    /* Header Mobile */
    div[style*="flex: 1; min-width: 0;"] h2 {
        font-size: 20px;
        word-break: break-word;
    }
    
    div[style*="flex: 1; min-width: 0;"] > div {
        gap: 12px;
    }
    
    div[style*="flex: 1; min-width: 0;"] > div > div {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    /* Course Image Mobile */
    div[style*="flex-shrink: 0;"] img {
        width: 80px !important;
        height: 80px !important;
    }
}

@media (max-width: 480px) {
    .card-content {
        padding: 12px;
        margin-bottom: 12px;
    }
    
    #curriculumAccordion .chapter-item {
        padding: 10px;
        margin-bottom: 10px;
    }
    
    #curriculumAccordion .chapter-item .fas.fa-folder {
        font-size: 14px;
    }
    
    #curriculumAccordion .chapter-item span {
        font-size: 12px;
    }
    
    .module-item {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 8px;
        padding: 8px;
    }
    
    .module-item > div:last-child {
        width: 100%;
        justify-content: space-between;
    }
    
    /* Course Details Extra Small */
    div[style*="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr))"] {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    /* Header Extra Small */
    .page-title h1 {
        font-size: 20px;
    }
    
    div[style*="flex: 1; min-width: 0;"] h2 {
        font-size: 18px;
    }
    
    /* Buttons Extra Small */
    div[style*="display: flex; gap: 12px; flex-wrap: wrap;"] {
        gap: 8px;
    }
    
    a[style*="padding: 12px 20px"] {
        padding: 10px 16px;
        font-size: 13px;
    }
}
</style>

<script>
// Initialize chevron rotation on collapse toggle
document.addEventListener('DOMContentLoaded', function() {
    const collapseElements = document.querySelectorAll('#curriculumAccordion .accordion-collapse');
    collapseElements.forEach(function(collapseElement) {
        const targetId = collapseElement.getAttribute('id');
        const chevron = document.querySelector('[data-bs-target="#' + targetId + '"] .chapter-chevron');
        
        if (chevron) {
            collapseElement.addEventListener('shown.bs.collapse', function() {
                chevron.style.transform = 'rotate(180deg)';
                const header = document.querySelector('[data-bs-target="#' + targetId + '"]');
                if (header) {
                    header.setAttribute('aria-expanded', 'true');
                }
            });
            collapseElement.addEventListener('hidden.bs.collapse', function() {
                chevron.style.transform = 'rotate(0deg)';
                const header = document.querySelector('[data-bs-target="#' + targetId + '"]');
                if (header) {
                    header.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
});
</script>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle me-2 text-success"></i>Approve Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this course?</p>
                <p class="text-muted">Once approved, this course will be published and visible to students.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <form action="{{ route('admin.courses.approve', $course->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Yes, Approve
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle me-2 text-danger"></i>Reject Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm" action="{{ route('admin.courses.reject', $course->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">
                            Rejection Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="admin_feedback" id="rejectionReason" class="form-control" rows="4" 
                                  placeholder="Explain why this course is being rejected..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectForm').submit()">
                    <i class="fas fa-check me-2"></i>Yes, Reject
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
