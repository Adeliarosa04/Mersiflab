@extends('layouts.admin')

@section('title', 'Teacher Application Details - #' . $teacherApplication->id)

@section('content')
<div class="page-title">
    <div>
        <h1>Teacher Application Details</h1>
    </div>
    <div class="page-actions" style="display: flex; align-items: center; gap: 10px; justify-content: flex-end;">
        <!-- Status Badge -->
        @if($teacherApplication->isPending())
            <span class="badge" style="background: #fff3cd; color: #856404; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">
                <i class="fas fa-clock me-2"></i>Under Review
            </span>
        @elseif($teacherApplication->isApproved())
            <span class="badge" style="background: #d4edda; color: #155724; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">
                <i class="fas fa-check-circle me-2"></i>Approved
            </span>
        @else
            <span class="badge" style="background: #f8d7da; color: #721c24; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">
                <i class="fas fa-times-circle me-2"></i>Rejected
            </span>
        @endif
        
        <a href="{{ back_url(route('admin.teacher-applications.index')) }}" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 8px 16px; font-size: 13px; border-radius: 6px; color: white; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Applications
        </a>
    </div>
</div>

    <div class="row">
        <!-- Applicant Information -->
        <div class="col-md-6">
            <div class="card-content" style="margin-bottom: 20px;">
                <div class="card-content-title">
                    <i class="fas fa-user me-2"></i>Applicant Information
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Full Name</label>
                        <p>{{ $teacherApplication->full_name }}</p>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <p>{{ $teacherApplication->email }}</p>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <p>{{ $teacherApplication->phone }}</p>
                    </div>
                    <div class="info-item">
                        <label>Address</label>
                        <p>{{ $teacherApplication->address }}</p>
                    </div>
                    <div class="info-item">
                        <label>User ID</label>
                        <p>#{{ $teacherApplication->user_id }}</p>
                    </div>
                    <div class="info-item">
                        <label>Current Role</label>
                        <p><span class="badge" style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">{{ $teacherApplication->user->role }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Timeline -->
        <div class="col-md-6">
            <div class="card-content" style="margin-bottom: 20px;">
                <div class="card-content-title">
                    <i class="fas fa-clock me-2"></i>Application Timeline
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Submitted</label>
                        <p>{{ $teacherApplication->created_at->format('F d, Y - g:i A') }}</p>
                    </div>
                    @if($teacherApplication->reviewed_at)
                        <div class="info-item">
                            <label>Reviewed</label>
                            <p>{{ $teacherApplication->reviewed_at->format('F d, Y - g:i A') }}</p>
                        </div>
                        @if($teacherApplication->reviewer)
                            <div class="info-item">
                                <label>Reviewed By</label>
                                <p>{{ $teacherApplication->reviewer->name }}</p>
                            </div>
                        @endif
                    @endif
                    <div class="info-item">
                        <label>Status</label>
                        <p>
                            @if($teacherApplication->isPending())
                                <span class="badge" style="background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Pending</span>
                            @elseif($teacherApplication->isApproved())
                                <span class="badge" style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Approved</span>
                            @else
                                <span class="badge" style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">Rejected</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teaching Experience -->
    <div class="card-content" style="margin-bottom: 20px;">
        <div class="card-content-title">
            <i class="fas fa-graduation-cap me-2"></i>Teaching Experience
        </div>
        <div style="background: #f8f9fa; padding: 16px; border-radius: 8px;">
            <p style="margin: 0; color: #333; line-height: 1.6;">{{ $teacherApplication->teaching_experience }}</p>
        </div>
    </div>

    <!-- Admin Notes -->
    @if($teacherApplication->admin_notes)
    <div class="card-content" style="margin-bottom: 20px;">
        <div class="card-content-title">
            <i class="fas fa-sticky-note me-2"></i>Admin Notes
        </div>
        <div style="background: #e3f2fd; padding: 16px; border-radius: 8px; color: #1976d2;">
            {{ $teacherApplication->admin_notes }}
        </div>
    </div>
    @endif

    <!-- File Verification Section -->
    <div class="card-content" style="margin-bottom: 20px;">
        <div class="card-content-title">
            <i class="fas fa-file-alt me-2"></i>Document Verification
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="document-card">
                    <div class="document-header">
                        <div>
                            <h6 style="margin: 0 0 4px 0; color: #333; font-size: 14px; font-weight: 600;">
                                <i class="fas fa-id-card text-primary me-2"></i>KTP/ID Card
                            </h6>
                            <small style="color: #828282;">Identity verification document</small>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('admin.teacher-applications.download', [$teacherApplication, 'ktp']) }}" 
                               class="btn btn-sm" 
                               style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;"
                               target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <a href="{{ $teacherApplication->getFileUrl('ktp_file') }}" 
                               target="_blank" 
                               class="btn btn-sm" 
                               style="background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                    <div class="document-preview">
                        @if(in_array(pathinfo($teacherApplication->ktp_file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                            <img src="{{ $teacherApplication->getFileUrl('ktp_file') }}" 
                                 style="width: 100%; max-height: 180px; object-fit: cover; border-radius: 6px;" 
                                 alt="KTP/ID Card">
                        @else
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; text-align: center;">
                                <i class="fas fa-file-pdf fa-2x text-muted mb-2"></i>
                                <p style="margin: 0; color: #6c757d; font-size: 12px;">PDF Document</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="document-card">
                    <div class="document-header">
                        <div>
                            <h6 style="margin: 0 0 4px 0; color: #333; font-size: 14px; font-weight: 600;">
                                <i class="fas fa-certificate text-primary me-2"></i>Teaching Certificate
                            </h6>
                            <small style="color: #828282;">Professional teaching qualification</small>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('admin.teacher-applications.download', [$teacherApplication, 'certificate']) }}" 
                               class="btn btn-sm" 
                               style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;"
                               target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <a href="{{ $teacherApplication->getFileUrl('teaching_certificate_file') }}" 
                               target="_blank" 
                               class="btn btn-sm" 
                               style="background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                    <div class="document-preview">
                        @if(in_array(pathinfo($teacherApplication->teaching_certificate_file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                            <img src="{{ $teacherApplication->getFileUrl('teaching_certificate_file') }}" 
                                 style="width: 100%; max-height: 180px; object-fit: cover; border-radius: 6px;" 
                                 alt="Teaching Certificate">
                        @else
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; text-align: center;">
                                <i class="fas fa-file-pdf fa-2x text-muted mb-2"></i>
                                <p style="margin: 0; color: #6c757d; font-size: 12px;">PDF Document</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="document-card">
                    <div class="document-header">
                        <div>
                            <h6 style="margin: 0 0 4px 0; color: #333; font-size: 14px; font-weight: 600;">
                                <i class="fas fa-building text-primary me-2"></i>Institution ID
                            </h6>
                            <small style="color: #828282;">Educational institution identification</small>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('admin.teacher-applications.download', [$teacherApplication, 'institution']) }}" 
                               class="btn btn-sm" 
                               style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;"
                               target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <a href="{{ $teacherApplication->getFileUrl('institution_id_file') }}" 
                               target="_blank" 
                               class="btn btn-sm" 
                               style="background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                    <div class="document-preview">
                        @if(in_array(pathinfo($teacherApplication->institution_id_file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                            <img src="{{ $teacherApplication->getFileUrl('institution_id_file') }}" 
                                 style="width: 100%; max-height: 180px; object-fit: cover; border-radius: 6px;" 
                                 alt="Institution ID">
                        @else
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; text-align: center;">
                                <i class="fas fa-file-pdf fa-2x text-muted mb-2"></i>
                                <p style="margin: 0; color: #6c757d; font-size: 12px;">PDF Document</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="document-card">
                    <div class="document-header">
                        <div>
                            <h6 style="margin: 0 0 4px 0; color: #333; font-size: 14px; font-weight: 600;">
                                <i class="fas fa-briefcase text-primary me-2"></i>Portfolio
                            </h6>
                            <small style="color: #828282;">Work samples and achievements</small>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('admin.teacher-applications.download', [$teacherApplication, 'portfolio']) }}" 
                               class="btn btn-sm" 
                               style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;"
                               target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <a href="{{ $teacherApplication->getFileUrl('portfolio_file') }}" 
                               target="_blank" 
                               class="btn btn-sm" 
                               style="background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; padding: 4px 8px; font-size: 11px; border-radius: 4px; text-decoration: none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                    <div class="document-preview">
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; text-align: center;">
                            <i class="fas fa-file-archive fa-2x text-muted mb-2"></i>
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">
                                {{ strtoupper(pathinfo($teacherApplication->portfolio_file, PATHINFO_EXTENSION)) }} File
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($teacherApplication->isPending())
    <div class="card-content">
        <div class="card-content-title">
            Application Review Actions
        </div>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <button type="button" class="btn" 
                    style="background: #e8f5e9; color: #27AE60; border: 1px solid #a5d6a7; padding: 10px 20px; font-size: 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                    onmouseover="this.style.background='#27AE60'; this.style.color='white'; this.style.borderColor='#27AE60';" 
                    onmouseout="this.style.background='#e8f5e9'; this.style.color='#27AE60'; this.style.borderColor='#a5d6a7';"
                    data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="fas fa-check-circle"></i>Approve Application
            </button>
            <button type="button" class="btn" 
                    style="background: #ffebee; color: #dc3545; border: 1px solid #f8bbd9; padding: 10px 20px; font-size: 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                    onmouseover="this.style.background='#dc3545'; this.style.color='white'; this.style.borderColor='#dc3545';" 
                    onmouseout="this.style.background='#ffebee'; this.style.color='#dc3545'; this.style.borderColor='#f8bbd9';"
                    data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="fas fa-times-circle"></i>Reject Application
            </button>
        </div>
    </div>
    @endif

<!-- Approve Modal -->
@if($teacherApplication->isPending())
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.teacher-applications.approve', $teacherApplication) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Approve Teacher Application
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Approving this application will upgrade the user's role to "teacher" and grant them access to teacher features.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Applicant Details:</h6>
                            <div class="bg-light p-3 rounded">
                                <div class="mb-2"><strong>Name:</strong> {{ $teacherApplication->full_name }}</div>
                                <div class="mb-2"><strong>Email:</strong> {{ $teacherApplication->email }}</div>
                                <div class="mb-2"><strong>Phone:</strong> {{ $teacherApplication->phone }}</div>
                                <div><strong>Submitted:</strong> {{ $teacherApplication->created_at->format('F d, Y - g:i A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Approval Message:</h6>
                            <textarea class="form-control" name="admin_notes" rows="6" 
                                      placeholder="Add any notes for this approval (optional)..."></textarea>
                            <small class="text-muted">This message will be visible to the applicant</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Approve Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.teacher-applications.reject', $teacherApplication) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        Reject Teacher Application
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Rejecting this application will prevent the user from becoming a teacher. They can submit a new application later.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Applicant Details:</h6>
                            <div class="bg-light p-3 rounded">
                                <div class="mb-2"><strong>Name:</strong> {{ $teacherApplication->full_name }}</div>
                                <div class="mb-2"><strong>Email:</strong> {{ $teacherApplication->email }}</div>
                                <div class="mb-2"><strong>Phone:</strong> {{ $teacherApplication->phone }}</div>
                                <div><strong>Submitted:</strong> {{ $teacherApplication->created_at->format('F d, Y - g:i A') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Rejection Reason <span class="text-danger">*</span></h6>
                            <textarea class="form-control" name="admin_notes" rows="6" required 
                                      placeholder="Please provide a clear reason for rejection..."></textarea>
                            <small class="text-muted">This message will be visible to the applicant and help them improve their application</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Reject Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

<style>
/* Info Grid Layout */
.info-grid {
    display: grid;
    gap: 16px;
}

.info-item {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 12px;
    align-items: start;
}

.info-item label {
    color: #828282;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin: 0;
    line-height: 1.4;
}

.info-item p {
    margin: 0;
    color: #333;
    font-size: 14px;
    line-height: 1.4;
}

/* Document Cards */
.document-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    height: 100%;
    transition: all 0.3s ease;
}

.document-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.document-header {
    padding: 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.document-preview {
    padding: 16px;
    background: white;
    min-height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 15px;
    }
    
    .page-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .info-item {
        grid-template-columns: 1fr;
        gap: 4px;
    }
    
    .info-item label {
        margin-bottom: 2px;
    }
    
    .document-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }
    
    .document-header > div:last-child {
        width: 100%;
        justify-content: flex-start;
    }
    
    .document-preview {
        min-height: 120px;
    }
}

@media (max-width: 576px) {
    .document-header > div:last-child {
        flex-wrap: wrap;
        gap: 6px;
    }
    
    .document-header .btn {
        flex: 1;
        min-width: 80px;
        text-align: center;
    }
}

/* Application Item Hover Effects */
.application-item:hover {
    background: #f8f9fa;
    margin: 0 -20px;
    padding: 20px !important;
    border-radius: 8px;
    transition: all 0.2s ease;
}

/* Badge Consistency */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Button Hover Effects */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Modal Enhancements */
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-bottom: 1px solid #e9ecef;
    padding: 20px 24px;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 16px 24px;
}

/* File Viewer Specific Styles */
.file-viewer-container {
    background: #525652;
    border-radius: 8px;
    overflow: hidden;
}

.image-viewer {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.loading-spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// Set up PDF.js worker
if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
}

document.addEventListener('DOMContentLoaded', function() {
    const fileViewerModal = document.getElementById('fileViewerModal');
    const fileViewerContent = document.getElementById('fileViewerContent');
    const fileViewerTitle = document.getElementById('fileViewerTitle');
    const downloadButton = document.getElementById('downloadButton');
    
    if (!fileViewerModal || !fileViewerContent) {
        console.error('File viewer elements not found');
        return;
    }
    
    fileViewerModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        if (!button) return;
        
        const fileUrl = button.getAttribute('data-file');
        const filename = button.getAttribute('data-filename');
        const fileType = button.getAttribute('data-type').toLowerCase();
        
        console.log('Loading file:', { fileUrl, filename, fileType });
        
        if (!fileUrl) {
            showError('File URL not provided');
            return;
        }
        
        fileViewerTitle.textContent = filename || 'Document Viewer';
        downloadButton.href = fileUrl;
        downloadButton.download = filename;
        
        // Show loading
        fileViewerContent.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                <div class="loading-spinner mb-3"></div>
                <div class="text-muted">Loading ${fileType.toUpperCase()} file...</div>
            </div>
        `;
        
        // Add small delay to show loading
        setTimeout(() => {
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileType)) {
                loadImage(fileUrl, filename);
            } else if (fileType === 'pdf') {
                loadPDF(fileUrl);
            } else {
                showFileInfo(filename, fileType, fileUrl);
            }
        }, 500);
    });
    
    function loadImage(url, filename) {
        console.log('Loading image:', url);
        const img = document.createElement('img');
        img.className = 'image-viewer';
        img.src = url;
        img.alt = filename;
        img.style.maxWidth = '100%';
        img.style.maxHeight = '100%';
        img.style.objectFit = 'contain';
        
        img.onload = function() {
            console.log('Image loaded successfully');
            fileViewerContent.innerHTML = '';
            fileViewerContent.appendChild(img);
        };
        
        img.onerror = function() {
            console.error('Failed to load image:', url);
            showError('Failed to load image. The file may be corrupted or not accessible.<br><br>URL: ' + url);
        };
    }
    
    function loadPDF(url) {
        console.log('Loading PDF:', url);
        
        // Check if PDF.js is available
        if (typeof pdfjsLib === 'undefined' || !pdfjsLib.getDocument) {
            console.error('PDF.js library not loaded');
            showPDFFallback(url);
            return;
        }
        
        // Try to load PDF
        pdfjsLib.getDocument({
            url: url,
            withCredentials: false
        }).promise.then(function(pdf) {
            console.log('PDF loaded successfully, pages:', pdf.numPages);
            
            if (pdf.numPages === 0) {
                showError('PDF has no pages');
                return;
            }
            
            // Create container for PDF pages
            const container = document.createElement('div');
            container.className = 'pdf-viewer';
            container.style.overflow = 'auto';
            container.style.height = '100%';
            container.style.padding = '10px';
            container.style.backgroundColor = '#525652';
            
            // Load first page
            pdf.getPage(1).then(function(page) {
                const scale = 1.5;
                const viewport = page.getViewport({ scale: scale });
                
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                canvas.className = 'page';
                canvas.style.margin = '0 auto 10px';
                canvas.style.display = 'block';
                canvas.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
                canvas.style.backgroundColor = 'white';
                
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                
                page.render(renderContext).promise.then(function() {
                    console.log('PDF page rendered successfully');
                    fileViewerContent.innerHTML = '';
                    
                    // Add page navigation if more than 1 page
                    if (pdf.numPages > 1) {
                        const pageInfo = document.createElement('div');
                        pageInfo.className = 'text-center text-white mb-2';
                        pageInfo.innerHTML = `<small>Page 1 of ${pdf.numPages}</small>`;
                        container.appendChild(pageInfo);
                    }
                    
                    container.appendChild(canvas);
                    fileViewerContent.appendChild(container);
                }).catch(function(error) {
                    console.error('Error rendering PDF page:', error);
                    showError('Failed to render PDF page: ' + error.message);
                });
            }).catch(function(error) {
                console.error('Error getting PDF page:', error);
                showError('Failed to read PDF page: ' + error.message);
            });
        }).catch(function(error) {
            console.error('Error loading PDF document:', error);
            console.error('Error details:', error.name, error.message);
            showPDFFallback(url);
        });
    }
    
    function showPDFFallback(url) {
        console.log('Showing PDF fallback');
        fileViewerContent.innerHTML = `
            <div class="file-info">
                <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                <h5>PDF Document</h5>
                <p class="text-muted">PDF preview is not available in this browser.</p>
                <p class="text-muted">Please download the file to view it.</p>
                <a href="${url}" class="btn btn-primary" target="_blank" download>
                    <i class="fas fa-download me-2"></i>Download PDF
                </a>
            </div>
        `;
    }
    
    function showFileInfo(filename, fileType, fileUrl) {
        console.log('Showing file info for:', fileType);
        const icons = {
            'doc': 'fa-file-word',
            'docx': 'fa-file-word',
            'zip': 'fa-file-archive',
            'rar': 'fa-file-archive',
            'txt': 'fa-file-alt',
            'default': 'fa-file'
        };
        
        const icon = icons[fileType] || icons['default'];
        
        fileViewerContent.innerHTML = `
            <div class="file-info">
                <i class="fas ${icon} fa-4x text-muted mb-3"></i>
                <h5>${filename}</h5>
                <p class="text-muted">File type: ${fileType.toUpperCase()}</p>
                <p class="text-muted small">This file type cannot be previewed. Please download to view.</p>
                <a href="${fileUrl}" class="btn btn-primary mt-2" target="_blank" download>
                    <i class="fas fa-download me-2"></i>Download File
                </a>
            </div>
        `;
    }
    
    function showError(message) {
        console.error('Showing error:', message);
        fileViewerContent.innerHTML = `
            <div class="file-info">
                <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                <h5>Error Loading File</h5>
                <p class="text-muted">${message}</p>
                <a href="${downloadButton.href}" class="btn btn-outline-primary mt-2" target="_blank" download>
                    <i class="fas fa-download me-2"></i>Download File Instead
                </a>
            </div>
        `;
    }
    
    // Clear content when modal is hidden
    fileViewerModal.addEventListener('hide.bs.modal', function() {
        fileViewerContent.innerHTML = '';
    });
});
</script>
@endpush
