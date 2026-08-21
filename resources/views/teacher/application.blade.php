@extends('layouts.app')

@section('title', 'Teacher Application')

@section('content')
<section class="teacher-application-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="application-card">
                    <div class="application-header text-center mb-5">
                        <div class="icon-wrapper mb-3">
                            <i class="fas fa-chalkboard-teacher fa-3x"></i>
                        </div>
                        <h2 class="application-title">Become a Teacher</h2>
                        <p class="application-subtitle">Share your knowledge and inspire the next generation</p>
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
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Progress Steps -->
                    <div class="progress-steps mb-5">
                        <div class="step active" data-step="1">
                            <div class="step-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="step-label">Personal Info</span>
                        </div>
                        <div class="step-line" data-step="1"></div>
                        <div class="step" data-step="2">
                            <div class="step-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <span class="step-label">Experience</span>
                        </div>
                        <div class="step-line" data-step="2"></div>
                        <div class="step" data-step="3">
                            <div class="step-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <span class="step-label">Documents</span>
                        </div>
                    </div>

                    <form action="{{ route('teacher.application.store') }}" method="POST" enctype="multipart/form-data" class="application-form" id="teacherApplicationForm">
                        @csrf

                        <!-- STEP 1: Personal Information -->
                        <div class="form-step active" data-step="1">
                            <div class="application-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Personal Information</h4>
                                        <p class="section-description">Tell us about yourself</p>
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
                                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                                       placeholder="Enter your full name" 
                                                       pattern="[A-Z][a-zA-Z\s\'-]*"
                                                       title="Nama harus dimulai dengan huruf kapital"
                                                       oninput="this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase()"
                                                       value="{{ old('full_name', Auth::user()->name) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">
                                                Email Address <span class="required">*</span>
                                            </label>
                                            <div class="input-group-custom">
                                                <i class="fas fa-envelope input-icon"></i>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       placeholder="your@email.com" 
                                                       value="{{ old('email', Auth::user()->email) }}" readonly
                                                       style="background-color: #f5f5f5; cursor: not-allowed;">
                                                <small class="text-muted d-block mt-1"><i class="fas fa-lock me-1"></i>Email from your account</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">
                                                Phone Number <span class="required">*</span>
                                            </label>
                                            <div class="input-group-custom">
                                                <i class="fas fa-phone input-icon"></i>
                                                <input type="tel" class="form-control" id="phone" name="phone" 
                                                       placeholder="08xx-xxxx-xxxx" 
                                                       inputmode="numeric"
                                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                       title="Nomor telepon hanya boleh mengandung angka"
                                                       value="{{ old('phone', Auth::user()->phone) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="address" class="form-label">
                                                Address <span class="required">*</span>
                                            </label>
                                            <div class="input-group-custom">
                                                <i class="fas fa-map-marker-alt input-icon"></i>
                                                <textarea class="form-control" id="address" name="address" rows="3" 
                                                          placeholder="Enter your full address" required>{{ old('address', Auth::user()->address ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="step-navigation">
                                <a href="{{ back_url(route('profile')) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                                </a>
                                <button type="button" class="btn btn-primary btn-next">
                                    Next Step <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 2: Teaching Experience -->
                        <div class="form-step" data-step="2">
                            <div class="application-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Teaching Experience</h4>
                                        <p class="section-description">Share your teaching background and expertise</p>
                                    </div>
                                </div>
                                
                                <div class="section-content">
                                    <div class="mb-3">
                                        <label for="teaching_experience" class="form-label">
                                            Describe Your Experience <span class="required">*</span>
                                        </label>
                                        <div class="input-group-custom">
                                            <i class="fas fa-book-open input-icon"></i>
                                            <textarea class="form-control" id="teaching_experience" name="teaching_experience" rows="8" 
                                                      placeholder="Tell us about your teaching journey, subjects you've taught, years of experience, teaching methodologies, and achievements..." required>{{ old('teaching_experience') }}</textarea>
                                        </div>
                                        <small class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Include specific details about your teaching background, certifications, and notable accomplishments
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="step-navigation">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    Next Step <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 3: Required Documents -->
                        <div class="form-step" data-step="3">
                            <div class="application-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Required Documents</h4>
                                        <p class="section-description">Upload your supporting documents</p>
                                    </div>
                                </div>
                                
                                <div class="section-content">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="file-upload-box">
                                                <label for="ktp_file" class="file-label">
                                                    <i class="fas fa-id-card file-icon"></i>
                                                    <span class="file-title">KTP/ID Card <span class="required">*</span></span>
                                                    <span class="file-requirements">PDF, JPG, PNG (Max: 5MB)</span>
                                                </label>
                                                <input type="file" class="file-input" id="ktp_file" name="ktp_file" 
                                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                                <div class="file-preview" id="ktp_preview"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="file-upload-box">
                                                <label for="teaching_certificate_file" class="file-label">
                                                    <i class="fas fa-certificate file-icon"></i>
                                                    <span class="file-title">Teaching Certificate <span class="required">*</span></span>
                                                    <span class="file-requirements">PDF, JPG, PNG (Max: 5MB)</span>
                                                </label>
                                                <input type="file" class="file-input" id="teaching_certificate_file" name="teaching_certificate_file" 
                                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                                <div class="file-preview" id="cert_preview"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="file-upload-box">
                                                <label for="institution_id_file" class="file-label">
                                                    <i class="fas fa-building file-icon"></i>
                                                    <span class="file-title">Institution ID Card <span class="required">*</span></span>
                                                    <span class="file-requirements">PDF, JPG, PNG (Max: 5MB)</span>
                                                </label>
                                                <input type="file" class="file-input" id="institution_id_file" name="institution_id_file" 
                                                       accept=".pdf,.jpg,.jpeg,.png" required>
                                                <div class="file-preview" id="inst_preview"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="file-upload-box">
                                                <label for="portfolio_file" class="file-label">
                                                    <i class="fas fa-briefcase file-icon"></i>
                                                    <span class="file-title">Portfolio <span class="required">*</span></span>
                                                    <span class="file-requirements">PDF, ZIP, DOC, DOCX (Max: 10MB)</span>
                                                </label>
                                                <input type="file" class="file-input" id="portfolio_file" name="portfolio_file" 
                                                       accept=".pdf,.zip,.doc,.docx" required>
                                                <div class="file-preview" id="portfolio_preview"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Documents Checklist Preview -->
                                    <div class="documents-checklist mb-4 mt-4">
                                        <h5 class="checklist-title mb-3">
                                            <i class="fas fa-list-check me-2"></i>Upload Status
                                        </h5>
                                        <div class="checklist-items">
                                            <div class="checklist-item" data-document="ktp_file">
                                                <div class="checklist-icon">
                                                    <i class="fas fa-id-card"></i>
                                                </div>
                                                <div class="checklist-content">
                                                    <h6>KTP/ID Card</h6>
                                                    <small>Identity verification document</small>
                                                </div>
                                                <div class="checklist-status">
                                                    <span class="status-badge status-pending">
                                                        <i class="fas fa-circle-notch"></i>
                                                        <span class="status-text">Not Uploaded</span>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="checklist-item" data-document="teaching_certificate_file">
                                                <div class="checklist-icon">
                                                    <i class="fas fa-certificate"></i>
                                                </div>
                                                <div class="checklist-content">
                                                    <h6>Teaching Certificate</h6>
                                                    <small>Professional teaching certificate</small>
                                                </div>
                                                <div class="checklist-status">
                                                    <span class="status-badge status-pending">
                                                        <i class="fas fa-circle-notch"></i>
                                                        <span class="status-text">Not Uploaded</span>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="checklist-item" data-document="institution_id_file">
                                                <div class="checklist-icon">
                                                    <i class="fas fa-school"></i>
                                                </div>
                                                <div class="checklist-content">
                                                    <h6>Institution ID Card</h6>
                                                    <small>Educational institution identification</small>
                                                </div>
                                                <div class="checklist-status">
                                                    <span class="status-badge status-pending">
                                                        <i class="fas fa-circle-notch"></i>
                                                        <span class="status-text">Not Uploaded</span>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="checklist-item" data-document="portfolio_file">
                                                <div class="checklist-icon">
                                                    <i class="fas fa-briefcase"></i>
                                                </div>
                                                <div class="checklist-content">
                                                    <h6>Portfolio</h6>
                                                    <small>Your work portfolio or teaching materials</small>
                                                </div>
                                                <div class="checklist-status">
                                                    <span class="status-badge status-pending">
                                                        <i class="fas fa-circle-notch"></i>
                                                        <span class="status-text">Not Uploaded</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Before submitting:</strong> Make sure all documents are uploaded and clearly visible. You can preview your documents by clicking on the status.
                                    </div>

                                    <div class="terms-box mt-4">
                                        <div class="custom-checkbox">
                                            <input type="checkbox" class="checkbox-input" id="terms" name="terms" required>
                                            <label class="checkbox-label" for="terms">
                                                <i class="fas fa-shield-alt me-2"></i>
                                                I certify that all information provided is true and accurate. I understand that false information may result in rejection of this application.
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="step-navigation">
                                <button type="button" class="btn btn-outline-secondary btn-prev">
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <button type="button" class="btn btn-primary" id="submitApplicationBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="confirmationModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Attention!
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning border-0" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Are you sure you want to apply to become a teacher?</strong>
                </div>
                
                <p class="mb-3">Before you proceed, please consider the following:</p>
                
                <div class="bg-light p-3 rounded mb-3">
                    <h6 class="text-danger mb-2">
                        <i class="fas fa-info-circle me-2"></i>Important:
                    </h6>
                    <ul class="mb-0 small">
                        <li>After your application is approved by admin, you will <strong>lose your student access rights</strong></li>
                        <li>Certificates you earned as a student <strong>cannot be accessed again</strong></li>
                        <li>Student modules and learning materials <strong>will no longer be available</strong> to you</li>
                        <li>Role change from student to teacher <strong>cannot be undone</strong></li>
                    </ul>
                </div>
                
                <p class="mb-0 text-muted small">
                    <i class="fas fa-shield-alt me-1"></i>
                    Make sure you have carefully considered this decision.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmSubmitBtn">
                    <i class="fas fa-check me-2"></i>Yes, I'm Sure
                </button>
            </div>
        </div>
    </div>
</div>
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

/* Progress Steps */
.progress-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
    position: relative;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 2;
    cursor: pointer;
    transition: all 0.3s ease;
}

.step-icon {
    width: 50px;
    height: 50px;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.step.active .step-icon {
    background: linear-gradient(135deg, #1A76D1 0%, #4A9EE0 100%);
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(26, 118, 209, 0.3);
    transform: scale(1.1);
}

.step.completed .step-icon {
    background: #198754;
    color: #ffffff;
}

.step.completed .step-icon::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.step.completed .step-icon i {
    display: none;
}

.step-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s ease;
}

.step.active .step-label {
    color: #1A76D1;
}

.step.completed .step-label {
    color: #198754;
}

.step-line {
    width: 100px;
    height: 2px;
    background: #e9ecef;
    position: relative;
    top: -20px;
    transition: all 0.3s ease;
}

.step-line.completed {
    background: #198754;
}

/* Form Steps */
.form-step {
    display: none;
    animation: fadeIn 0.4s ease;
}

.form-step.active {
    display: block;
}

/* Documents Checklist Styles */
.documents-checklist {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border-left: 4px solid #1A76D1;
}

.checklist-title {
    color: #1a1a1a;
    font-weight: 600;
    margin-bottom: 1rem !important;
}

.checklist-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #ffffff;
    padding: 1.25rem;
    border-radius: 10px;
    border: 1.5px solid #e9ecef;
    transition: all 0.3s ease;
}

.checklist-item:hover {
    border-color: #1A76D1;
    box-shadow: 0 4px 12px rgba(26, 118, 209, 0.1);
}

.checklist-item.uploaded {
    border-color: #28a745;
    background: #f0fdf4;
}

.checklist-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e3f2fd;
    color: #1A76D1;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.checklist-item.uploaded .checklist-icon {
    background: #d4edda;
    color: #28a745;
}

.checklist-content {
    flex: 1;
}

.checklist-content h6 {
    margin: 0 0 0.25rem 0;
    color: #1a1a1a;
    font-weight: 600;
}

.checklist-content small {
    color: #6c757d;
    display: block;
}

.checklist-status {
    flex-shrink: 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-pending:hover {
    background: #ffe69c;
    transform: scale(1.05);
}

.status-uploaded {
    background: #d4edda;
    color: #155724;
}

.status-uploaded:hover {
    background: #c3e6cb;
    transform: scale(1.05);
}

.status-text {
    margin-left: 0.25rem;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.file-preview-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
}

.file-name {
    flex: 1;
    word-break: break-all;
    font-weight: 500;
}

.btn-remove-file {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.btn-remove-file:hover {
    color: #c82333;
    transform: scale(1.2);
}

.file-upload-box.missing-file {
    border-color: #dc3545 !important;
    background: #fff5f5 !important;
    animation: shake 0.5s ease;
}

.file-upload-box.missing-file .file-label {
    color: #dc3545;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.file-preview-container {
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 10px;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-preview-container img {
    max-width: 100%;
    border-radius: 8px;
}

/* Application Section */
.application-section {
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
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
    padding: 1rem 0;
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

/* File Upload Box */
.file-upload-box {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    background: #f8f9fa;
    position: relative;
}

.file-upload-box:hover {
    border-color: #1A76D1;
    background: #e7f3ff;
}

.file-upload-box.has-file {
    border-color: #198754;
    background: #d1f4e0;
}

.file-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    margin: 0;
}

.file-icon {
    font-size: 2rem;
    color: #1A76D1;
    margin-bottom: 0.5rem;
}

.file-title {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.file-requirements {
    font-size: 0.8rem;
    color: #6c757d;
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    cursor: pointer;
}

.file-preview {
    margin-top: 1rem;
    font-size: 0.85rem;
    color: #198754;
    font-weight: 600;
}

/* Terms Box */
.terms-box {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
}

.custom-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.checkbox-input {
    width: 20px;
    height: 20px;
    margin-top: 2px;
    cursor: pointer;
    accent-color: #1A76D1;
}

.checkbox-label {
    font-size: 0.95rem;
    color: #495057;
    line-height: 1.6;
    cursor: pointer;
    margin: 0;
}

/* Step Navigation */
.step-navigation {
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    align-items: center;
    padding-top: 2rem;
    margin-top: 2rem;
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
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #d1f4e0;
    color: #155724;
}

.alert-danger {
    background: #fee;
    color: #c00;
}

.alert-info {
    background: #cfe2ff;
    color: #084298;
    border-left: 4px solid #0d6efd;
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .application-card {
        padding: 2rem;
    }

    .application-title {
        font-size: 1.75rem;
    }

    .progress-steps {
        padding: 1.5rem 0;
    }

    .step-line {
        width: 60px;
    }

    .step-navigation {
        flex-direction: column-reverse;
    }

    .step-navigation .btn {
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

    .progress-steps {
        padding: 1rem 0;
    }

    .step-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .step-line {
        width: 40px;
        top: -15px;
    }

    .step-label {
        font-size: 0.75rem;
    }

    .section-title {
        font-size: 1.25rem;
    }

    .file-upload-box {
        padding: 1rem;
    }

    .file-icon {
        font-size: 1.5rem;
    }
}

/* Modal Confirmation Styles */
#confirmationModal .modal-dialog {
    max-width: 500px;
    margin: 1.75rem auto;
}

#confirmationModal .modal-content {
    max-height: 90vh;
    overflow-y: auto;
}

#confirmationModal .modal-header {
    border-bottom: 2px solid #ffc107;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
}

#confirmationModal .modal-footer {
    border-top: 1px solid #dee2e6;
    background: #f8f9fa;
}

#confirmationModal .btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
    border: none;
    color: #000;
    font-weight: 600;
}

#confirmationModal .btn-warning:hover {
    background: linear-gradient(135deg, #ffb300 0%, #ff6f00 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

#confirmationModal .bg-light {
    background-color: #f8f9fa !important;
    border-left: 4px solid #ffc107;
}

#confirmationModal .text-danger {
    color: #dc3545 !important;
}

#confirmationModal ul li {
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

#confirmationModal ul li:last-child {
    margin-bottom: 0;
}

#confirmationModal .modal-body {
    padding: 1.5rem;
    max-height: 60vh;
    overflow-y: auto;
}

/* Fix multiple backdrops issue */
.modal-backdrop.show:not(:first-of-type) {
    display: none !important;
}

/* Enhanced overlay styling */
.modal-backdrop {
    z-index: 1050 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}

/* Ensure modal is above all content */
#confirmationModal {
    z-index: 1060;
}

.modal {
    z-index: 1055 !important;
}

/* Remove any extra backdrops */
.modal-backdrop ~ .modal-backdrop {
    display: none !important;
}

/* Force single backdrop */
body:not(.modal-open) > .modal-backdrop {
    display: none !important;
}

/* Ensure only one backdrop exists */
.modal-backdrop:first-of-type {
    z-index: 1050 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}

.modal-backdrop:not(:first-of-type) {
    display: none !important;
}

/* Modal overlay animation */
.modal-backdrop.fade {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-backdrop.show {
    opacity: 1;
}

@media (max-width: 576px) {
    #confirmationModal .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }
    
    #confirmationModal .modal-body {
        padding: 1rem;
        max-height: 50vh;
    }
    
    #confirmationModal .modal-content {
        max-height: 95vh;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 3;
    
    // File upload preview and checklist update
    const fileInputs = document.querySelectorAll('.file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const box = this.closest('.file-upload-box');
            const previewId = this.id + '_preview';
            const preview = document.getElementById(previewId);
            const inputId = this.id;
            
            if (file) {
                box.classList.add('has-file');
                if (preview) {
                    preview.innerHTML = `
                        <div class="file-preview-content">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span class="file-name">${file.name}</span>
                            <button type="button" class="btn-remove-file" title="Remove file">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    
                    // Add remove button functionality
                    const removeBtn = preview.querySelector('.btn-remove-file');
                    removeBtn.addEventListener('click', () => {
                        this.value = '';
                        box.classList.remove('has-file');
                        preview.innerHTML = '';
                        updateChecklistStatus(inputId, false);
                    });
                }
                
                updateChecklistStatus(inputId, true, file);
            } else {
                box.classList.remove('has-file');
                if (preview) {
                    preview.innerHTML = '';
                }
                updateChecklistStatus(inputId, false);
            }
        });
    });
    
    // Update checklist status
    function updateChecklistStatus(inputId, isUploaded, file = null) {
        const checklistItem = document.querySelector(`.checklist-item[data-document="${inputId}"]`);
        if (!checklistItem) return;
        
        const statusBadge = checklistItem.querySelector('.status-badge');
        
        if (isUploaded && file) {
            checklistItem.classList.add('uploaded');
            statusBadge.classList.remove('status-pending');
            statusBadge.classList.add('status-uploaded');
            statusBadge.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span class="status-text">Uploaded</span>
            `;
            statusBadge.style.cursor = 'pointer';
            
            // Add click to preview functionality
            statusBadge.onclick = (e) => {
                e.preventDefault();
                previewFile(file, inputId);
            };
        } else {
            checklistItem.classList.remove('uploaded');
            statusBadge.classList.add('status-pending');
            statusBadge.classList.remove('status-uploaded');
            statusBadge.innerHTML = `
                <i class="fas fa-circle-notch"></i>
                <span class="status-text">Not Uploaded</span>
            `;
            statusBadge.onclick = null;
        }
    }
    
    // Preview file function
    function previewFile(file, inputId) {
        const reader = new FileReader();
        const fileName = file.name.toLowerCase();
        
        // Create a modal for preview
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'filePreviewModal';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-eye me-2"></i>Preview: ${file.name}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="previewContent" class="file-preview-container">
                            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        reader.onload = function(e) {
            const previewContent = document.getElementById('previewContent');
            
            // Check file type
            if (fileName.endsWith('.pdf')) {
                previewContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-file-pdf me-2"></i>
                        <strong>PDF Document</strong>
                        <p class="mt-2 mb-0">File size: ${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                    <div class="mt-3">
                        <a href="${e.target.result}" download="${file.name}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                    </div>
                `;
            } else if (fileName.match(/\.(jpg|jpeg|png|gif)$/)) {
                previewContent.innerHTML = `
                    <img src="${e.target.result}" style="max-width: 100%; max-height: 400px; border-radius: 8px;" alt="Preview">
                    <p class="mt-3 text-muted">File size: ${(file.size / 1024).toFixed(2)} KB</p>
                `;
            } else if (fileName.endsWith('.zip')) {
                previewContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-file-archive me-2"></i>
                        <strong>ZIP Archive</strong>
                        <p class="mt-2 mb-0">File size: ${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                    <div class="mt-3">
                        <a href="${e.target.result}" download="${file.name}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                    </div>
                `;
            } else if (fileName.match(/\.(doc|docx)$/)) {
                previewContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-file-word me-2"></i>
                        <strong>Word Document</strong>
                        <p class="mt-2 mb-0">File size: ${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                    <div class="mt-3">
                        <a href="${e.target.result}" download="${file.name}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                    </div>
                `;
            } else {
                previewContent.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-file me-2"></i>
                        <strong>File Preview Not Available</strong>
                        <p class="mt-2 mb-0">File size: ${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                    <div class="mt-3">
                        <a href="${e.target.result}" download="${file.name}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                    </div>
                `;
            }
        };
        
        reader.readAsDataURL(file);
        
        // Show modal using Bootstrap
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Remove modal from DOM when hidden
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    // Step navigation functions
    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
        
        // Show current step
        const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
        if (currentStepElement) {
            currentStepElement.classList.add('active');
        }
        
        // Update progress indicators
        document.querySelectorAll('.step').forEach((s, index) => {
            const stepNum = index + 1;
            s.classList.remove('active', 'completed');
            
            if (stepNum < step) {
                s.classList.add('completed');
            } else if (stepNum === step) {
                s.classList.add('active');
            }
        });
        
        // Update step lines
        document.querySelectorAll('.step-line').forEach((line, index) => {
            const lineStep = index + 1;
            if (lineStep < step) {
                line.classList.add('completed');
            } else {
                line.classList.remove('completed');
            }
        });
        
        // Scroll to top of form
        document.querySelector('.application-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    function validateStep(step) {
        const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
        const inputs = currentStepElement.querySelectorAll('input[required], textarea[required]');
        
        let isValid = true;
        let errorMessages = [];
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                
                // Remove invalid class after user types
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                }, { once: true });
            }
        });
        
        // Validate full name format only on step 1
        if (step === 1) {
            const fullNameInput = document.getElementById('full_name');
            if (fullNameInput && fullNameInput.value.trim()) {
                const fullName = fullNameInput.value.trim();
                
                // Check if name contains only letters, spaces, hyphens, and apostrophes
                const nameRegex = /^[a-zA-Z\s\-']+$/;
                
                // Check if first character is uppercase
                const isFirstCharCapital = /^[A-Z]/.test(fullName);
                
                if (!nameRegex.test(fullName)) {
                    isValid = false;
                    fullNameInput.classList.add('is-invalid');
                    errorMessages.push('The full name field format is invalid.');
                    
                    fullNameInput.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, { once: true });
                } else if (!isFirstCharCapital) {
                    isValid = false;
                    fullNameInput.classList.add('is-invalid');
                    errorMessages.push('The full name field format is invalid.');
                    
                    fullNameInput.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, { once: true });
                }
            }
        }
        
        if (!isValid) {
            // Show validation message
            const existingAlert = currentStepElement.querySelector('.validation-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger validation-alert mt-3';
            
            if (errorMessages.length > 0) {
                alert.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i><ul class="mb-0">' + 
                    errorMessages.map(msg => `<li>${msg}</li>`).join('') + 
                    '<li>Please fill in all required fields before continuing.</li></ul>';
            } else {
                alert.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please fill in all required fields before continuing.';
            }
            
            const sectionContent = currentStepElement.querySelector('.section-content');
            if (sectionContent) {
                sectionContent.insertAdjacentElement('beforeend', alert);
            }
            
            // Remove alert after 4 seconds
            setTimeout(() => alert.remove(), 4000);
        }
        
        return isValid;
    }
    
    function validateDocuments() {
        const requiredDocuments = ['ktp_file', 'teaching_certificate_file', 'institution_id_file', 'portfolio_file'];
        let allUploaded = true;
        
        requiredDocuments.forEach(docId => {
            const input = document.getElementById(docId);
            const box = input.closest('.file-upload-box');
            
            if (!input.value) {
                allUploaded = false;
                box.classList.add('missing-file');
            } else {
                box.classList.remove('missing-file');
            }
        });
        
        if (!allUploaded) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger mt-3';
            alert.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please upload all required documents before submitting your application.';
            
            const step3 = document.querySelector('.form-step[data-step="3"]');
            const existingAlert = step3.querySelector('.doc-validation-alert');
            if (existingAlert) existingAlert.remove();
            alert.className = 'alert alert-danger mt-3 doc-validation-alert';
            step3.querySelector('.section-content').insertAdjacentElement('beforeend', alert);
            
            setTimeout(() => alert.remove(), 4000);
        }
        
        return allUploaded;
    }
    
    // Next button handlers
    document.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        });
    });
    
    // Previous button handlers
    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
    });
    
    // Step indicator click handlers
    document.querySelectorAll('.step').forEach((step, index) => {
        step.addEventListener('click', function() {
            const stepNum = index + 1;
            // Only allow going back to previous steps, not skipping forward
            if (stepNum <= currentStep) {
                currentStep = stepNum;
                showStep(currentStep);
            }
        });
    });
    
    // Form submission validation
    const form = document.getElementById('teacherApplicationForm');
    const submitBtn = document.getElementById('submitApplicationBtn');
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    
    // Initialize modal with proper options
    let confirmationModal;
    const modalElement = document.getElementById('confirmationModal');
    
    // Clean up any existing backdrops before initializing
    function cleanupBackdrops() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.forEach((backdrop, index) => {
                if (index > 0) {
                    backdrop.remove();
                }
            });
        }
    }
    
    try {
        confirmationModal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false,
            focus: true
        });
        
        // Add event listener to clean up backdrops when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function() {
            setTimeout(cleanupBackdrops, 100);
        });
        
    } catch (error) {
        console.error('Error initializing modal:', error);
    }
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // First validate all documents
            if (!validateDocuments()) {
                return false;
            }
            
            // Clean up any existing backdrops before showing modal
            cleanupBackdrops();
            
            // Show confirmation modal
            if (confirmationModal) {
                try {
                    confirmationModal.show();
                } catch (error) {
                    console.error('Error showing modal:', error);
                    // Fallback: submit directly if modal fails
                    if (confirm('Are you sure you want to apply to become a teacher? After approval, you will lose your student access rights.')) {
                        form.submit();
                    }
                }
            }
        });
    }
    
    if (confirmSubmitBtn) {
        confirmSubmitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Hide modal first
            if (confirmationModal) {
                try {
                    confirmationModal.hide();
                    
                    // Wait for modal to hide before submitting
                    modalElement.addEventListener('hidden.bs.modal', function() {
                        form.submit();
                    }, { once: true });
                } catch (error) {
                    console.error('Error hiding modal:', error);
                    // Fallback: submit directly
                    form.submit();
                }
            } else {
                // Fallback: submit directly
                form.submit();
            }
        });
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateDocuments()) {
                e.preventDefault();
                return false;
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

    // Full Name - auto capitalize first letter
    const fullNameInput = document.getElementById('full_name');
    if (fullNameInput) {
        fullNameInput.addEventListener('change', function() {
            if (this.value.trim()) {
                this.value = this.value.trim().charAt(0).toUpperCase() + this.value.trim().slice(1);
            }
        });
    }

    // Initialize first step
    showStep(1);
});
</script>
@endsection