@extends('layouts.app')

@section('title', 'Edit Teacher Application')

@section('content')
<section class="teacher-application-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="application-card">
                    <div class="application-header text-center mb-4">
                        <div class="icon-wrapper mb-3">
                            <i class="fas fa-edit fa-3x"></i>
                        </div>
                        <h2 class="application-title">Edit Your Application</h2>
                        <p class="application-subtitle">Update your teacher application information</p>
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Status Info -->
                    <div class="status-info-card">
                        <div class="status-info-header">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Current Status:</strong>
                        </div>
                        <div class="status-info-body">
                            @if($application->isPending())
                                <span class="status-badge status-badge-pending">
                                    <i class="fas fa-hourglass-half me-1"></i>Pending Review
                                </span>
                                <p class="status-note">Your application is being reviewed by our team.</p>
                            @elseif($application->isRejected())
                                <span class="status-badge status-badge-rejected">
                                    <i class="fas fa-times-circle me-1"></i>Rejected
                                </span>
                                <p class="status-note">Please address the admin notes below and resubmit your application.</p>
                            @else
                                <span class="status-badge status-badge-approved">
                                    <i class="fas fa-check-circle me-1"></i>Approved
                                </span>
                                <p class="status-note">Your application has been approved.</p>
                            @endif
                        </div>
                    </div>

                    @if($application->isRejected() && $application->admin_notes)
                        <div class="admin-notes-card">
                            <div class="notes-header">
                                <i class="fas fa-comment-dots me-2"></i>Admin Notes
                            </div>
                            <div class="notes-body">
                                {{ $application->admin_notes }}
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('teacher.application.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Personal Information -->
                        <div class="application-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">Personal Information</h4>
                                    <p class="section-description">Update your personal details</p>
                                </div>
                            </div>
                            
                            <div class="section-content">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="full_name" class="form-label">
                                            Full Name <span class="required">*</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="fas fa-user input-icon"></i>
                                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                                   id="full_name" name="full_name" 
                                                   placeholder="Enter your full name" 
                                                   pattern="[A-Z][a-zA-Z\s\'-]*"
                                                   title="Nama harus dimulai dengan huruf kapital"
                                                   value="{{ old('full_name', $application->full_name) }}" required>
                                            @error('full_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            Email Address <span class="required">*</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="fas fa-envelope input-icon"></i>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" name="email" 
                                                   placeholder="your@email.com" 
                                                   value="{{ old('email', $application->email) }}" readonly 
                                                   style="background-color: #f5f5f5; cursor: not-allowed;">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="form-text">
                                            <i class="fas fa-lock me-1"></i>Email address cannot be changed
                                        </small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            Phone Number <span class="required">*</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="fas fa-phone input-icon"></i>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" 
                                                   placeholder="08xx-xxxx-xxxx" 
                                                   inputmode="numeric"
                                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                   title="Nomor telepon hanya boleh mengandung angka"
                                                   value="{{ old('phone', $application->phone) }}" required>
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label">
                                            Address <span class="required">*</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="fas fa-map-marker-alt input-icon"></i>
                                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                                      id="address" name="address" rows="3" 
                                                      placeholder="Enter your full address" required>{{ old('address', $application->address) }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Teaching Experience -->
                        <div class="application-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">Teaching Experience</h4>
                                    <p class="section-description">Update your teaching background</p>
                                </div>
                            </div>
                            
                            <div class="section-content">
                                <div class="mb-3">
                                    <label for="teaching_experience" class="form-label">
                                        Describe Your Experience <span class="required">*</span>
                                    </label>
                                    <div class="input-group-custom">
                                        <i class="fas fa-book-open input-icon"></i>
                                        <textarea class="form-control @error('teaching_experience') is-invalid @enderror" 
                                                  id="teaching_experience" name="teaching_experience" rows="6" 
                                                  placeholder="Describe your teaching experience, subjects you teach, years of experience, etc." required>{{ old('teaching_experience', $application->teaching_experience) }}</textarea>
                                        @error('teaching_experience')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>Please provide detailed information about your teaching background
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Required Documents -->
                        <div class="application-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">Required Documents</h4>
                                    <p class="section-description">Update or replace your documents</p>
                                </div>
                            </div>
                            
                            <div class="section-content">
                                <div class="row">
                                    <!-- KTP File -->
                                    <div class="col-md-6 mb-3">
                                        <div class="document-upload-card">
                                            <div class="document-upload-header">
                                                <i class="fas fa-id-card me-2"></i>
                                                <span>KTP/ID Card</span>
                                                @if($application->ktp_file)
                                                    <span class="badge-uploaded">✓ Uploaded</span>
                                                @else
                                                    <span class="required">*</span>
                                                @endif
                                            </div>
                                            <div class="document-upload-body">
                                                <input type="file" class="form-control @error('ktp_file') is-invalid @enderror" 
                                                       id="ktp_file" name="ktp_file" 
                                                       accept=".pdf,.jpg,.jpeg,.png">
                                                <small class="form-text">
                                                    PDF, JPG, PNG (Max: 10MB). Leave empty to keep current file.
                                                </small>
                                                @if($application->ktp_file)
                                                    <div class="current-file-link">
                                                        <a href="{{ asset('storage/' . $application->ktp_file) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="fas fa-eye me-1"></i>View Current File
                                                        </a>
                                                    </div>
                                                @endif
                                                @error('ktp_file')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Teaching Certificate -->
                                    <div class="col-md-6 mb-3">
                                        <div class="document-upload-card">
                                            <div class="document-upload-header">
                                                <i class="fas fa-certificate me-2"></i>
                                                <span>Teaching Certificate</span>
                                                @if($application->teaching_certificate_file)
                                                    <span class="badge-uploaded">✓ Uploaded</span>
                                                @else
                                                    <span class="required">*</span>
                                                @endif
                                            </div>
                                            <div class="document-upload-body">
                                                <input type="file" class="form-control @error('teaching_certificate_file') is-invalid @enderror" 
                                                       id="teaching_certificate_file" name="teaching_certificate_file" 
                                                       accept=".pdf,.jpg,.jpeg,.png">
                                                <small class="form-text">
                                                    PDF, JPG, PNG (Max: 10MB). Leave empty to keep current file.
                                                </small>
                                                @if($application->teaching_certificate_file)
                                                    <div class="current-file-link">
                                                        <a href="{{ asset('storage/' . $application->teaching_certificate_file) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="fas fa-eye me-1"></i>View Current File
                                                        </a>
                                                    </div>
                                                @endif
                                                @error('teaching_certificate_file')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Institution ID -->
                                    <div class="col-md-6 mb-3">
                                        <div class="document-upload-card">
                                            <div class="document-upload-header">
                                                <i class="fas fa-building me-2"></i>
                                                <span>Institution ID Card</span>
                                                @if($application->institution_id_file)
                                                    <span class="badge-uploaded">✓ Uploaded</span>
                                                @else
                                                    <span class="required">*</span>
                                                @endif
                                            </div>
                                            <div class="document-upload-body">
                                                <input type="file" class="form-control @error('institution_id_file') is-invalid @enderror" 
                                                       id="institution_id_file" name="institution_id_file" 
                                                       accept=".pdf,.jpg,.jpeg,.png">
                                                <small class="form-text">
                                                    PDF, JPG, PNG (Max: 10MB). Leave empty to keep current file.
                                                </small>
                                                @if($application->institution_id_file)
                                                    <div class="current-file-link">
                                                        <a href="{{ asset('storage/' . $application->institution_id_file) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="fas fa-eye me-1"></i>View Current File
                                                        </a>
                                                    </div>
                                                @endif
                                                @error('institution_id_file')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Portfolio -->
                                    <div class="col-md-6 mb-3">
                                        <div class="document-upload-card">
                                            <div class="document-upload-header">
                                                <i class="fas fa-briefcase me-2"></i>
                                                <span>Portfolio</span>
                                                @if($application->portfolio_file)
                                                    <span class="badge-uploaded">✓ Uploaded</span>
                                                @else
                                                    <span class="required">*</span>
                                                @endif
                                            </div>
                                            <div class="document-upload-body">
                                                <input type="file" class="form-control @error('portfolio_file') is-invalid @enderror" 
                                                       id="portfolio_file" name="portfolio_file" 
                                                       accept=".pdf,.zip,.doc,.docx">
                                                <small class="form-text">
                                                    PDF, ZIP, DOC, DOCX (Max: 10MB). Leave empty to keep current file.
                                                </small>
                                                @if($application->portfolio_file)
                                                    <div class="current-file-link">
                                                        <a href="{{ asset('storage/' . $application->portfolio_file) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="fas fa-eye me-1"></i>View Current File
                                                        </a>
                                                    </div>
                                                @endif
                                                @error('portfolio_file')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <a href="{{ back_url(route('teacher.application.preview')) }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Back to Preview
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Full Name auto-capitalize
    const fullNameInput = document.getElementById('full_name');
    if (fullNameInput) {
        fullNameInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
            }
        });
    }

    // Phone Number - only digits
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>
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

/* Status Info Card */
.status-info-card {
    background: #e7f3ff;
    border: 2px solid #1A76D1;
    border-radius: 12px;
    margin-bottom: 1rem;
    overflow: hidden;
}

.status-info-header {
    background: #1A76D1;
    color: #fff;
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.95rem;
}

.status-info-body {
    padding: 1rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.status-badge-pending {
    background: #fff8e1;
    color: #856404;
    border: 2px solid #ffc107;
}

.status-badge-approved {
    background: #d1f4e0;
    color: #155724;
    border: 2px solid #198754;
}

.status-badge-rejected {
    background: #fee;
    color: #c00;
    border: 2px solid #dc3545;
}

.status-note {
    margin: 0.5rem 0 0 0;
    color: #333;
    font-size: 0.9rem;
}

/* Admin Notes Card */
.admin-notes-card {
    background: #fff;
    border: 2px solid #dc3545;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1rem;
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

/* Form Controls */
.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.required {
    color: #dc3545;
    margin-left: 3px;
}

.input-group-custom {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 1rem;
    color: #6c757d;
    z-index: 3;
    pointer-events: none;
}

/* Icon alignment for single-line inputs */
.input-group-custom:not(:has(textarea)) .input-icon {
    top: 50%;
    transform: translateY(-50%);
}

.input-group-custom .form-control {
    padding-left: 2.75rem;
}

.form-control {
    padding: 0.875rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-control:focus {
    border-color: #1A76D1;
    box-shadow: 0 0 0 0.2rem rgba(26, 118, 209, 0.15);
    outline: none;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control::placeholder {
    color: #adb5bd;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-text {
    color: #6c757d;
    font-size: 0.85rem;
    display: block;
    margin-top: 0.5rem;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Document Upload Card */
.document-upload-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.document-upload-card:hover {
    border-color: #1A76D1;
    background: #e7f3ff;
}

.document-upload-header {
    background: linear-gradient(135deg, #1A76D1 0%, #4A9EE0 100%);
    color: #fff;
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.badge-uploaded {
    background: #198754;
    color: #fff;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.document-upload-body {
    padding: 1rem;
}

.current-file-link {
    margin-top: 0.75rem;
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

.btn-outline-primary {
    border: 2px solid #1A76D1;
    color: #1A76D1;
    background: transparent;
}

.btn-outline-primary:hover {
    background: #1A76D1;
    color: #fff;
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
}
</style>
@endsection