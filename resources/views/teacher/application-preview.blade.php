@extends('layouts.app')

@section('title', 'Teacher Application Preview')

@section('content')
<section class="teacher-application-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="application-card">
                    <div class="application-header text-center mb-4">
                        <div class="icon-wrapper mb-3">
                            <i class="fas fa-eye fa-3x"></i>
                        </div>
                        <h2 class="application-title">Application Status & Preview</h2>
                        <p class="application-subtitle">View and manage your teacher application</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                <span>{{ session('info') }}</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Status Badge Section -->
                    <div class="status-section mb-4">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Application Status</h4>
                                <p class="section-description">Current status of your application</p>
                            </div>
                        </div>
                        
                        <div class="status-badge-container">
                            @if($application->isPending())
                                <div class="status-card status-pending">
                                    <div class="status-icon">
                                        <i class="fas fa-hourglass-half fa-3x"></i>
                                    </div>
                                    <div class="status-content">
                                        <h5 class="status-title">
                                            <i class="fas fa-spinner fa-pulse"></i> Pending Review
                                        </h5>
                                        <p class="status-text">Your application is being reviewed by our admin team. This usually takes 1-3 business days.</p>
                                    </div>
                                </div>
                            @elseif($application->isApproved())
                                <div class="status-card status-approved">
                                    <div class="status-icon">
                                        <i class="fas fa-check-circle fa-3x"></i>
                                    </div>
                                    <div class="status-content">
                                        <h5 class="status-title">
                                            <i class="fas fa-check"></i> Application Approved
                                        </h5>
                                        <p class="status-text">Congratulations! Your teacher application has been approved. You can now create and manage courses.</p>
                                        @if($application->reviewed_at)
                                            <small class="status-date">
                                                <i class="fas fa-calendar me-1"></i>Approved on {{ $application->reviewed_at->format('F d, Y \a\t h:i A') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @elseif($application->isRejected())
                                <div class="status-card status-rejected">
                                    <div class="status-icon">
                                        <i class="fas fa-times-circle fa-3x"></i>
                                    </div>
                                    <div class="status-content">
                                        <h5 class="status-title">
                                            <i class="fas fa-times"></i> Application Rejected
                                        </h5>
                                        <p class="status-text">Unfortunately, your application was not approved at this time.</p>
                                    </div>
                                </div>
                                @if($application->admin_notes)
                                    <div class="admin-notes-card">
                                        <div class="notes-header">
                                            <i class="fas fa-comment-dots me-2"></i>Admin Notes
                                        </div>
                                        <div class="notes-body">
                                            {{ $application->admin_notes }}
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Personal Information Section -->
                    <div class="application-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Personal Information</h4>
                                <p class="section-description">Your submitted personal details</p>
                            </div>
                        </div>
                        
                        <div class="section-content">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="detail-group">
                                        <label class="detail-label">
                                            <i class="fas fa-user me-2"></i>Full Name
                                        </label>
                                        <p class="detail-value">{{ $application->full_name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="detail-group">
                                        <label class="detail-label">
                                            <i class="fas fa-envelope me-2"></i>Email
                                        </label>
                                        <p class="detail-value">{{ $application->email }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="detail-group">
                                        <label class="detail-label">
                                            <i class="fas fa-phone me-2"></i>Phone Number
                                        </label>
                                        <p class="detail-value">{{ $application->phone }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="detail-group">
                                        <label class="detail-label">
                                            <i class="fas fa-map-marker-alt me-2"></i>Address
                                        </label>
                                        <p class="detail-value">{{ $application->address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Teaching Experience Section -->
                    <div class="application-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Teaching Experience</h4>
                                <p class="section-description">Your teaching background and expertise</p>
                            </div>
                        </div>
                        
                        <div class="section-content">
                            <div class="detail-group">
                                <p class="detail-value experience-text">{{ $application->teaching_experience }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Section -->
                    <div class="application-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Required Documents</h4>
                                <p class="section-description">Your uploaded supporting documents</p>
                            </div>
                        </div>
                        
                        <div class="section-content">
                            <div class="row">
                                <!-- KTP File -->
                                <div class="col-md-6 mb-3">
                                    <div class="document-card">
                                        <div class="document-icon-wrapper">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <div class="document-info">
                                            <h6 class="document-title">KTP/ID Card</h6>
                                            @if($application->ktp_file)
                                                <a href="{{ asset('storage/' . $application->ktp_file) }}" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-eye me-1"></i>View Document
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-times-circle me-1"></i>Not uploaded
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Teaching Certificate -->
                                <div class="col-md-6 mb-3">
                                    <div class="document-card">
                                        <div class="document-icon-wrapper">
                                            <i class="fas fa-certificate"></i>
                                        </div>
                                        <div class="document-info">
                                            <h6 class="document-title">Teaching Certificate</h6>
                                            @if($application->teaching_certificate_file)
                                                <a href="{{ asset('storage/' . $application->teaching_certificate_file) }}" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-eye me-1"></i>View Document
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-times-circle me-1"></i>Not uploaded
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Institution ID -->
                                <div class="col-md-6 mb-3">
                                    <div class="document-card">
                                        <div class="document-icon-wrapper">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="document-info">
                                            <h6 class="document-title">Institution ID Card</h6>
                                            @if($application->institution_id_file)
                                                <a href="{{ asset('storage/' . $application->institution_id_file) }}" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-eye me-1"></i>View Document
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-times-circle me-1"></i>Not uploaded
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Portfolio -->
                                <div class="col-md-6 mb-3">
                                    <div class="document-card">
                                        <div class="document-icon-wrapper">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div class="document-info">
                                            <h6 class="document-title">Portfolio</h6>
                                            @if($application->portfolio_file)
                                                <a href="{{ asset('storage/' . $application->portfolio_file) }}" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-eye me-1"></i>View Document
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-times-circle me-1"></i>Not uploaded
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Application Information Section -->
                    <div class="application-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Application Information</h4>
                                <p class="section-description">Submission and review details</p>
                            </div>
                        </div>
                        
                        <div class="section-content">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="detail-group">
                                        <label class="detail-label">
                                            <i class="fas fa-calendar-plus me-2"></i>Submitted Date
                                        </label>
                                        <p class="detail-value">{{ $application->created_at->format('F d, Y \a\t h:i A') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="detail-group">
                                        <label class="detail-label">
                                            <i class="fas fa-sync-alt me-2"></i>Last Updated
                                        </label>
                                        <p class="detail-value">{{ $application->updated_at->format('F d, Y \a\t h:i A') }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($application->reviewed_by)
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <div class="detail-group">
                                            <label class="detail-label">
                                                <i class="fas fa-user-shield me-2"></i>Reviewed By
                                            </label>
                                            <p class="detail-value">{{ $application->reviewer->name ?? 'Admin' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons Section -->
                    <div class="action-buttons">
                        @if($application->isRejected())
                            <a href="{{ route('teacher.application.edit') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-edit me-2"></i>Edit & Resubmit Application
                            </a>
                        @endif
                        <a href="{{ back_url(route('profile')) }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Back to Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('styles')
<style>
.teacher-application-section {
    background: #D7EBFF;
    min-height: 100vh;
    padding: 3rem 0;
}

.application-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    padding: 3rem;
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Header Section */
.application-header {
    position: relative;
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    background: linear-gradient(135deg, #1A76D1 0%, #4A9EE0 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(26, 118, 209, 0.3);
}

.icon-wrapper i {
    color: #ffffff;
}

.application-title {
    color: #1a1a1a;
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.application-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
    font-weight: 400;
}

/* Section Styles */
.application-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
}

.section-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f3f5;
}

.section-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #e7f3ff 0%, #d6ebff 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1A76D1;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.section-title {
    color: #1a1a1a;
    font-weight: 700;
    font-size: 1.5rem;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.section-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.4;
}

.section-content {
    padding: 0;
}

/* Status Card */
.status-badge-container {
    margin-top: 0.5rem;
}

.status-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.25rem;
    border-radius: 12px;
    border: 2px solid;
    margin-bottom: 0.75rem;
}

.status-pending {
    background: #fff8e1;
    border-color: #ffc107;
}

.status-approved {
    background: #d1f4e0;
    border-color: #198754;
}

.status-rejected {
    background: #fee;
    border-color: #dc3545;
}

.status-icon {
    flex-shrink: 0;
}

.status-pending .status-icon {
    color: #ffc107;
}

.status-approved .status-icon {
    color: #198754;
}

.status-rejected .status-icon {
    color: #dc3545;
}

.status-content {
    flex: 1;
}

.status-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.status-pending .status-title {
    color: #856404;
}

.status-approved .status-title {
    color: #155724;
}

.status-rejected .status-title {
    color: #c00;
}

.status-text {
    margin: 0;
    color: #333;
    line-height: 1.6;
}

.status-date {
    display: block;
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.85rem;
}

/* Admin Notes Card */
.admin-notes-card {
    background: #fff;
    border: 2px solid #dc3545;
    border-radius: 12px;
    overflow: hidden;
}

.notes-header {
    background: #dc3545;
    color: #fff;
    padding: 0.75rem 1rem;
    font-weight: 600;
}

.notes-body {
    padding: 1rem;
    color: #333;
    line-height: 1.6;
}

/* Detail Group */
.detail-group {
    margin-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: #555;
    font-size: 0.9rem;
    display: block;
    margin-bottom: 0.5rem;
}

.detail-value {
    color: #333;
    margin: 0;
    padding: 0.875rem 1rem;
    background-color: #f8f9fa;
    border-left: 3px solid #1A76D1;
    border-radius: 8px;
    line-height: 1.6;
}

.experience-text {
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* Document Cards */
.document-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.document-card:hover {
    border-color: #1A76D1;
    background: #e7f3ff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(26, 118, 209, 0.1);
}

.document-icon-wrapper {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #1A76D1 0%, #4A9EE0 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.5rem;
}

.document-info {
    margin-top: 1rem;
}

.document-title {
    color: #333;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    align-items: center;
    padding-top: 1.5rem;
    margin-top: 1.5rem;
    border-top: 2px solid #f1f3f5;
}

.btn {
    padding: 0.875rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
    border: none;
}

.btn-lg {
    padding: 0.875rem 2rem;
}

.btn-primary {
    background: linear-gradient(135deg, #1A76D1 0%, #4A9EE0 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(26, 118, 209, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(26, 118, 209, 0.4);
    background: linear-gradient(135deg, #1565c0 0%, #1A76D1 100%);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
    background: transparent;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

/* Alert Styles */
.alert {
    border-radius: 12px;
    border: none;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}

.alert-success {
    background: #d1f4e0;
    color: #155724;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
}

.alert-danger {
    background: #fee;
    color: #c00;
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .application-card {
        padding: 2rem;
    }

    .application-title {
        font-size: 1.75rem;
    }

    .action-buttons {
        flex-direction: column-reverse;
    }

    .action-buttons .btn {
        width: 100%;
    }
}

@media (max-width: 767.98px) {
    .teacher-application-section {
        padding: 2rem 0;
    }

    .application-card {
        padding: 1.5rem;
        border-radius: 15px;
    }

    .icon-wrapper {
        width: 60px;
        height: 60px;
    }

    .icon-wrapper i {
        font-size: 2rem;
    }

    .application-title {
        font-size: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
    }

    .status-card {
        flex-direction: column;
        text-align: center;
    }

    .status-icon {
        margin-bottom: 1rem;
    }
}
</style>
@endsection