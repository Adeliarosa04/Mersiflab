@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/welcome.css') }}">
<style>
/* Teacher Page Styles - Consistent with Home Page */
.teacher-page-header {
    background: linear-gradient(135deg, #1f7ae0 0%, #1557a0 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 0 0 1rem 1rem;
}

.teacher-page-header h5 {
    font-weight: 600;
    margin: 0;
}

.teacher-form-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.teacher-form-header {
    background: linear-gradient(135deg, #1f7ae0 0%, #1557a0 100%);
    color: white;
    border: none;
    padding: 1.5rem;
    font-weight: 600;
}

.teacher-form-body {
    padding: 2rem;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1f7ae0;
    box-shadow: 0 0 0 0.2rem rgba(31, 122, 224, 0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #1f7ae0 0%, #1557a0 100%);
    border: none;
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1557a0 0%, #0d47a1 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(31, 122, 224, 0.3);
}

.btn-outline-secondary {
    border: 2px solid #6c757d;
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    transform: translateY(-2px);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    border: none;
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border: none;
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-info:hover {
    background: linear-gradient(135deg, #138496 0%, #0f6674 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
}

.btn-outline-danger {
    border: 2px solid #dc3545;
    border-radius: 0.75rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    transform: translateY(-2px);
}

.info-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.info-card .card-body {
    padding: 1.5rem;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin-bottom: 1.5rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #6c757d;
}

.breadcrumb-item a {
    color: #1f7ae0;
    text-decoration: none;
    font-weight: 500;
}

.breadcrumb-item a:hover {
    color: #1557a0;
    text-decoration: underline;
}

.alert {
    border: none;
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    color: #856404;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.text-muted {
    color: #6c757d !important;
}

.text-danger {
    color: #dc3545 !important;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.is-invalid {
    border-color: #dc3545;
}

.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
}

.module-type-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    overflow: hidden;
}

.module-type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.module-type-card .card-body {
    padding: 2rem;
    text-align: center;
}

.module-type-card i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.module-type-card h5 {
    font-weight: 600;
    margin-bottom: 1rem;
}

.module-type-card p {
    color: #6c757d;
    margin-bottom: 1.5rem;
}
</style>
@endsection

@section('title', 'Select Module Type - ' . $chapter->title)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">

            <div class="card teacher-form-card">
                <div class="card-header teacher-form-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i>Add New Module
                    </h5>
                </div>
                <div class="card-body teacher-form-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Class: <strong>{{ $chapter->class->name }}</strong></h6>
                            <h6 class="text-muted">Chapter: <strong>{{ $chapter->title }}</strong></h6>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted d-block mb-2">
                                Current modules: <strong>{{ $chapter->modules()->count() }}</strong>
                            </small>
                            {{-- Panduan standar materi, dibuka sebelum memilih tipe modul --}}
                            @include('partials.guide-book')
                        </div>
                    </div>

                    <h6 class="mb-4">Choose Module Type:</h6>

                    <div class="row">
                        <!-- Text Module -->
                        <div class="col-md-4 mb-3">
                            <div class="card module-type-card h-100">
                                <div class="card-body">
                                    <i class="fas fa-align-left" style="color: #1f7ae0;"></i>
                                    <h5 class="card-title">Text Module</h5>
                                    <p class="card-text">Rich text content with formatting, images, and embedded media</p>
                                    <div class="text-center mt-4">
                                        <a href="{{ route('teacher.modules.create.text', $chapter) }}" 
                                           class="btn btn-primary d-inline-flex align-items-center justify-content-center">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Module -->
                        <div class="col-md-4 mb-3">
                            <div class="card border-2 border-success h-100 module-type-card" style="cursor: pointer; transition: all 0.3s;">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-file-pdf" style="font-size: 3rem; color: #198754;"></i>
                                    <h5 class="card-title mt-3">Document Module</h5>
                                    <p class="card-text text-muted small">Upload PDF files (max 50MB) for downloadable resources</p>
                                    <div class="text-center mt-4">
                                        <a href="{{ route('teacher.modules.create.document', $chapter) }}" 
                                           class="btn btn-success btn-sm d-inline-flex align-items-center justify-content-center">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Video Module -->
                        <div class="col-md-4 mb-3">
                            <div class="card border-2 border-warning h-100 module-type-card" style="cursor: pointer; transition: all 0.3s;">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-video" style="font-size: 3rem; color: #ffc107;"></i>
                                    <h5 class="card-title mt-3">Video Module</h5>
                                    <p class="card-text text-muted small">Upload video file (max 500MB) or embed from YouTube/external URL</p>
                                    <div class="text-center mt-4">
                                        <a href="{{ route('teacher.modules.create.video', $chapter) }}" 
                                           class="btn btn-warning btn-sm d-inline-flex align-items-center justify-content-center">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Module Guidelines
                    </h6>
                    <ul class="small text-muted mb-0">
                        <li><strong>Text Module:</strong> Use for lessons, explanations, and content with embedded media</li>
                        <li><strong>Document Module:</strong> Upload study materials, guides, worksheets as PDF files</li>
                        <li><strong>Video Module:</strong> Upload educational videos or link to YouTube/external sources</li>
                        <li>All modules require admin approval before being visible to students</li>
                        <li>Modules can be edited until the course is approved by admin</li>
                    </ul>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ back_url(route('teacher.manage.content')) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Management
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.module-type-card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.module-type-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-4px);
}

.module-type-card .btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.module-type-card .btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.module-type-card .btn i {
    font-size: 14px;
    margin: 0;
}
</style>
@endsection
