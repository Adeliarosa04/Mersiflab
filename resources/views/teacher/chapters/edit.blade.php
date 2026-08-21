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

/* Action Buttons - Consistent with Manage Content */
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.85rem;
    text-decoration: none;
    color: #1f7ae0;
}

.btn-action:hover {
    background: #1f7ae0;
    color: white;
    border-color: transparent;
}

.btn-action.delete {
    color: #ef4444;
}

.btn-action.delete:hover {
    background: #ef4444;
    color: white;
    border-color: transparent;
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

.form-check-input:checked {
    background-color: #1f7ae0;
    border-color: #1f7ae0;
}

.form-check-input:focus {
    border-color: #1f7ae0;
    box-shadow: 0 0 0 0.2rem rgba(31, 122, 224, 0.15);
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

.modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

.modal-header {
    border: none;
    border-radius: 1rem 1rem 0 0;
}

.modal-footer {
    border: none;
    border-radius: 0 0 1rem 1rem;
}

.btn-group-sm .btn {
    border-radius: 0.5rem;
    font-size: 0.875rem;
}

.badge {
    border-radius: 0.5rem;
    font-weight: 500;
}
</style>
@endsection

@section('title', 'Edit Chapter')

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

                    <form action="{{ route('teacher.chapters.update', [$chapter->class, $chapter]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="title" class="form-label">Chapter Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" placeholder="Chapter title"
                                   value="{{ old('title', $chapter->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Chapter Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4"
                                      placeholder="What will students learn in this chapter?" required>{{ old('description', $chapter->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-save me-2"></i>Update Chapter
                            </button>
                            <a href="{{ back_url(route('teacher.manage.content')) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- Modules Section -->
                    <div class="modules-section">
                        <h6 class="mb-3">
                            <i class="fas fa-layer-group me-2"></i>Modules in This Chapter
                            <a href="{{ route('teacher.modules.create', $chapter) }}" class="btn btn-sm btn-success float-end">
                                <i class="fas fa-plus me-1"></i>Add Module
                            </a>
                        </h6>

                        @if($chapter->modules()->count() > 0)
                            <div class="modules-list border rounded p-3">
                                @foreach($chapter->modules()->orderBy('order')->get() as $module)
                                    <div class="module-item d-flex justify-content-between align-items-center py-2 border-bottom" style="min-height: 60px;">
                                        <div>
                                            <h6 class="mb-1">
                                                @if($module->type === 'text')
                                                    <i class="fas fa-align-left text-primary me-2"></i>
                                                @elseif($module->type === 'document')
                                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                                @else
                                                    <i class="fas fa-video text-warning me-2"></i>
                                                @endif
                                                {{ $module->title }}
                                            </h6>
                                            <small class="text-muted">
                                                <strong>Type:</strong> {{ ucfirst($module->type) }} | 
                                                <strong>Views:</strong> {{ $module->view_count }}
                                                @if($module->is_published)
                                                    <span class="badge bg-success ms-2">Published</span>
                                                @else
                                                    <span class="badge bg-secondary ms-2">Draft</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('teacher.modules.edit', [$chapter, $module]) }}" class="btn-action" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('teacher.modules.destroy', [$chapter, $module]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this module?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action delete" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No modules yet. <a href="{{ route('teacher.modules.create', $chapter) }}" class="alert-link">Create your first module</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger border-2 shadow-sm mt-4">
                <div class="card-header bg-danger bg-opacity-10">
                    <h6 class="mb-0 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Deleting this chapter will permanently remove it and all its modules. This action cannot be undone.
                    </p>
                    <form action="{{ route('teacher.chapters.destroy', [$chapter->class, $chapter]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you absolutely sure? This will delete the chapter and all its modules.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete This Chapter
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
