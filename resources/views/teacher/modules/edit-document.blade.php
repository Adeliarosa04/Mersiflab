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

.file-preview {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.form-check {
    margin-bottom: 1rem;
}

.form-check-label {
    font-weight: 500;
    color: #2c3e50;
}
</style>
@endsection

@section('title', 'Edit Document Module')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card teacher-form-card">
                <div class="card-header teacher-form-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-pdf me-2"></i>Edit Document Module
                    </h5>
                </div>
                <div class="card-body teacher-form-body">
                    <form action="{{ route('teacher.modules.update', [$chapter, $module]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="{{ old('title', $module->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($module->file_path)
                        <div class="mb-4">
                            <label class="form-label">Current File</label>
                            <div class="file-preview">
                                <i class="fas fa-file-pdf me-2" style="color: #dc3545;"></i> {{ $module->file_name }}
                                <span class="text-muted">({{ number_format($module->file_size / 1024, 2) }} KB)</span>
                            </div>
                            
                            <!-- Opsi untuk mengganti file -->
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="replace_file" name="replace_file" value="1">
                                <label class="form-check-label" for="replace_file">
                                    <strong>Ganti File PDF</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">Centang untuk mengganti file dengan yang baru</small>
                        </div>
                        
                        <!-- File upload tersembunyi, muncul saat checkbox dicentang -->
                        <div class="mb-4" id="file_upload_section" style="display: none;">
                            <label for="file" class="form-label">File Baru <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx" 
                                   onchange="validateFile(this)">
                            <small class="form-text text-muted">
                                Format yang diizinkan: PDF, DOC, DOCX, PPT, PPTX. Maksimal 20MB.
                            </small>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @else
                        <div class="mb-4">
                            <label for="file" class="form-label">Upload File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx" 
                                   onchange="validateFile(this)" required>
                            <small class="form-text text-muted">
                                Format yang diizinkan: PDF, DOC, DOCX, PPT, PPTX. Maksimal 20MB.
                            </small>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="estimated_duration" class="form-label">Estimasi Durasi (menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('estimated_duration') is-invalid @enderror" 
                                           id="estimated_duration" name="estimated_duration" value="{{ old('estimated_duration', $module->estimated_duration) }}" min="1" placeholder="Contoh: 45" required>
                                    <small class="form-text text-muted">
                                        Estimasi waktu yang dibutuhkan siswa untuk membaca PDF ini
                                    </small>
                                    @error('estimated_duration')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <!-- Published status removed - modules follow course approval workflow -->
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="{{ back_url(route('teacher.chapters.edit', [$chapter, $chapter])) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle file upload section
document.getElementById('replace_file').addEventListener('change', function() {
    const fileSection = document.getElementById('file_upload_section');
    const fileInput = document.getElementById('file');
    
    if (this.checked) {
        fileSection.style.display = 'block';
        fileInput.setAttribute('required', 'required');
    } else {
        fileSection.style.display = 'none';
        fileInput.removeAttribute('required');
        fileInput.value = ''; // Clear file input
    }
});

// File validation
function validateFile(input) {
    const file = input.files[0];
    const maxSize = 20 * 1024 * 1024; // 20MB
    const allowedTypes = ['application/pdf', 'application/msword', 
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                         'application/vnd.ms-powerpoint', 
                         'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
    
    if (file) {
        if (file.size > maxSize) {
            alert('File terlalu besar. Maksimal 20MB.');
            input.value = '';
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak diizinkan. Gunakan PDF, DOC, DOCX, PPT, atau PPTX.');
            input.value = '';
            return false;
        }
    }
    
    return true;
}

// Form submission confirmation
document.querySelector('form').addEventListener('submit', function(e) {
    const replaceFile = document.getElementById('replace_file').checked;
    const fileInput = document.getElementById('file');
    
    if (replaceFile && !fileInput.files.length) {
        e.preventDefault();
        alert('Silakan pilih file baru untuk mengganti file yang ada.');
        return false;
    }
    
    // Confirmation if replacing a file that is already approved
    @if($module->approval_status === 'approved')
    if (replaceFile) {
        if (!confirm('Replacing the file will change the module status to "Pending Approval" and require re-approval from admin. Continue?')) {
            e.preventDefault();
            return false;
        }
    }
    
    // Confirmation for other changes to an already approved module
    if (!replaceFile) {
        if (!confirm('Changing an approved module will change the status to "Pending Approval" and require re-approval from admin. Continue?')) {
            e.preventDefault();
            return false;
        }
    }
    @endif
});
</script>
@endsection
