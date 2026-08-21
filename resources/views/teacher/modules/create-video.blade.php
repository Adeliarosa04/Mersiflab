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
</style>
@endsection

@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card teacher-form-card">
                <div class="card-header teacher-form-header">
                    <h5 class="mb-0">
                        <i class="fas fa-video me-2"></i>Create Video Module
                    </h5>
                </div>
                <div class="card-body teacher-form-body">
                    {{-- Panduan standar materi, dibuka sebelum berkas dipilih --}}
                    <div class="d-flex justify-content-end mb-4">
                        @include('partials.guide-book')
                    </div>

                    <form action="{{ route('teacher.modules.store.video', $chapter) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="form-label">Module Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Video Type Selection -->
                        <div class="mb-4">
                            <label class="form-label">Video Type <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="video_type" id="video_upload" 
                                           value="upload" {{ old('video_type') === 'upload' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="video_upload">
                                        Upload Video File
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="video_type" id="video_url" 
                                           value="url" {{ old('video_type') === 'url' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="video_url">
                                        From URL (YouTube, Vimeo, etc)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- File Upload Field -->
                        <div class="mb-4" id="file-field">
                            <label for="file" class="form-label">Video File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept="video/*" required>
                            <small class="form-text text-muted">
                                Maximum file size: 500 MB. Supported formats: MP4, AVI, MOV, WMV
                            </small>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL Field -->
                        <div class="mb-4" id="url-field" style="display: none;">
                            <label for="video_url" class="form-label">Video URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control @error('video_url') is-invalid @enderror" 
                                   id="video_url" name="video_url" placeholder="https://youtube.com/watch?v=... or https://youtu.be/..." 
                                   value="{{ old('video_url') }}">
                            <small class="form-text text-muted">
                                <strong>Supported formats:</strong><br>
                                • YouTube: <code>https://youtube.com/watch?v=VIDEO_ID</code><br>
                                • YouTube Short: <code>https://youtu.be/VIDEO_ID</code><br>
                                • YouTube Embed: <code>https://youtube.com/embed/VIDEO_ID</code><br>
                                • Vimeo: <code>https://vimeo.com/VIDEO_ID</code>
                            </small>
                            @error('video_url')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="estimated_duration" class="form-label">Estimated Duration (minutes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('estimated_duration') is-invalid @enderror" 
                                   id="estimated_duration" name="estimated_duration" value="{{ old('estimated_duration') }}" min="1" placeholder="Contoh: 60" required>
                            <small class="form-text text-muted">
                                Estimated time needed for students to watch this video
                            </small>
                            @error('estimated_duration')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="{{ back_url(route('teacher.modules.create', $chapter)) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-video me-2"></i>Create Video Module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="video_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const fileField = document.getElementById('file-field');
            const urlField = document.getElementById('url-field');
            const fileInput = document.getElementById('file');
            const videoUrlInput = document.getElementById('video_url');
            
            if (this.value === 'upload') {
                fileField.style.display = 'block';
                urlField.style.display = 'none';
                fileInput.required = true;
                videoUrlInput.required = false;
            } else {
                fileField.style.display = 'none';
                urlField.style.display = 'block';
                fileInput.required = false;
                videoUrlInput.required = true;
            }
        });
    });

    // File size validation for video upload
    document.getElementById('file')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const maxSize = 500 * 1024 * 1024; // 500MB in bytes
        const allowedTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv'];
        
        // Clear previous errors
        const existingError = document.getElementById('file-size-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Remove invalid class
        document.getElementById('file').classList.remove('is-invalid');
        
        if (file) {
            // Check file type
            if (!allowedTypes.includes(file.type)) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'file-size-error';
                errorDiv.className = 'invalid-feedback d-block';
                errorDiv.textContent = 'Format video yang diperbolehkan: mp4, avi, mov, wmv';
                
                document.getElementById('file').classList.add('is-invalid');
                document.getElementById('file').parentNode.appendChild(errorDiv);
                
                // Clear the file input
                e.target.value = '';
                
                return;
            }
            
            // Check file size
            if (file.size > maxSize) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'file-size-error';
                errorDiv.className = 'invalid-feedback d-block';
                errorDiv.textContent = 'Ukuran file video tidak boleh lebih dari 500MB';
                
                document.getElementById('file').classList.add('is-invalid');
                document.getElementById('file').parentNode.appendChild(errorDiv);
                
                // Clear the file input
                e.target.value = '';
                
                return;
            }
        }
    });

    // Trigger on page load to set initial state
    document.querySelector('input[name="video_type"]:checked')?.dispatchEvent(new Event('change'));
</script>
@endsection
