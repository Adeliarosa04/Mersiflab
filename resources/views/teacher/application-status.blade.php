@extends('layouts.app')

@section('title', 'Teacher Application Status')

@section('content')
<section class="application-status-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="status-card">
                    <div class="status-header text-center mb-4">
                        <i class="fas fa-clipboard-check fa-3x mb-3" class="@if($application->isPending()) text-warning @elseif($application->isApproved()) text-success @else text-danger @endif"></i>
                        <h2 class="status-title">Application Status</h2>
                    </div>

                    <div class="status-content">
                        <!-- Status Badge -->
                        <div class="text-center mb-4">
                            @if($application->isPending())
                                <span class="badge bg-warning text-dark fs-6 px-4 py-2">
                                    <i class="fas fa-clock me-2"></i>Under Review
                                </span>
                            @elseif($application->isApproved())
                                <span class="badge bg-success fs-6 px-4 py-2">
                                    <i class="fas fa-check-circle me-2"></i>Approved
                                </span>
                            @else
                                <span class="badge bg-danger fs-6 px-4 py-2">
                                    <i class="fas fa-times-circle me-2"></i>Rejected
                                </span>
                            @endif
                        </div>

                        <!-- Application Details -->
                        <div class="application-details">
                            <h4 class="section-title">Application Details</h4>
                            
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Submitted:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $application->created_at->format('F d, Y - g:i A') }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Full Name:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $application->full_name }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Email:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $application->email }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Phone:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $application->phone }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Address:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $application->address }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Teaching Experience:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $application->teaching_experience }}
                                </div>
                            </div>

                            @if($application->reviewed_at)
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Reviewed:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $application->reviewed_at->format('F d, Y - g:i A') }}
                                    @if($application->reviewer)
                                        by {{ $application->reviewer->name }}
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($application->admin_notes)
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Admin Notes:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <div class="alert alert-info">
                                        {{ $application->admin_notes }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Submitted Documents -->
                        <div class="submitted-documents">
                            <h4 class="section-title">Submitted Documents</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="document-item">
                                        <i class="fas fa-id-card text-primary me-2"></i>
                                        <strong>KTP/ID Card:</strong>
                                        <a href="{{ $application->getFileUrl('ktp_file') }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="document-item">
                                        <i class="fas fa-certificate text-primary me-2"></i>
                                        <strong>Teaching Certificate:</strong>
                                        <a href="{{ $application->getFileUrl('teaching_certificate_file') }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="document-item">
                                        <i class="fas fa-building text-primary me-2"></i>
                                        <strong>Institution ID:</strong>
                                        <a href="{{ $application->getFileUrl('institution_id_file') }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="document-item">
                                        <i class="fas fa-briefcase text-primary me-2"></i>
                                        <strong>Portfolio:</strong>
                                        <a href="{{ $application->getFileUrl('portfolio_file') }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Messages -->
                        <div class="status-message text-center">
                            @if($application->isPending())
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>Your application is currently under review.</strong><br>
                                    Our team will carefully review your application and documents. This process typically takes 3-5 business days. We'll notify you via email once there's an update.
                                </div>
                            @elseif($application->isApproved())
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Congratulations! Your application has been approved.</strong><br>
                                    You can now access teacher features and start creating courses. Your account has been upgraded to teacher status.
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <strong>We're sorry, but your application has been rejected.</strong><br>
                                    Please review the admin notes above for more information. You can submit a new application after addressing the feedback.
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mt-4">
                            <a href="{{ back_url(route('profile')) }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </a>
                            
                            @if($application->isRejected())
                                <a href="{{ route('teacher.application.create') }}" class="btn btn-outline-primary ms-2">
                                    <i class="fas fa-redo me-2"></i>Submit New Application
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('styles')
<style>
.application-status-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.status-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 40px;
    margin-top: 20px;
}

.status-title {
    color: #333;
    font-weight: 600;
    margin-bottom: 10px;
}

.status-content {
    text-align: left;
}

.section-title {
    color: #333;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
    display: inline-block;
}

.document-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.badge {
    font-size: 1rem !important;
    padding: 0.75rem 1.5rem !important;
}
</style>
@endsection
