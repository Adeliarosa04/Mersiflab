@extends('layouts.app')

@section('title', 'Add Chapter to ' . $class->name)

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
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            

            <div class="card teacher-form-card">
                <div class="card-header teacher-form-header">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>Chapter Information
                    </h5>
                </div>
                <div class="card-body teacher-form-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please fix the errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('teacher.chapters.store', $class) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="form-label">Chapter Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" placeholder="e.g., Introduction to HTML"
                                   value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Chapter Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4"
                                      placeholder="What will students learn in this chapter?" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="{{ back_url(route('teacher.chapters.index', $class)) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Chapter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Course Info -->
            <div class="card info-card mt-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>Course Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Course Name:</small>
                            <p class="mb-2"><strong>{{ $class->name }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Current Chapters:</small>
                            <p class="mb-0"><strong>{{ $class->chapters()->count() }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
