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

.video-preview {
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

@extends('layouts.app')

@section('title', 'Edit Video Module')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card teacher-form-card">
                <div class="card-header teacher-form-header">
                    <h5 class="mb-0">
                        <i class="fas fa-video me-2"></i>Edit Video Module
                    </h5>
                </div>
                <div class="card-body teacher-form-body">
                    <form action="{{ route('teacher.modules.update', [$chapter, $module]) }}" method="POST" enctype="multipart/form-data">
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

                        <div class="mb-4">
                            <label class="form-label">Current Video Type</label>
                            <div class="video-preview">
                                @if($module->file_path)
                                    <i class="fas fa-video me-2" style="color: #dc3545;"></i> Uploaded Video File
                                    <span class="text-muted">({{ number_format($module->file_size / 1024 / 1024, 2) }} MB)</span>
                                    <br><small class="text-muted">{{ $module->file_name }}</small>
                                @elseif($module->video_url)
                                    <i class="fas fa-link me-2" style="color: #17a2b8;"></i> Embedded Video URL
                                @else
                                    <i class="fas fa-question me-2" style="color: #6c757d;"></i> Unknown video type
                                @endif
                            </div>
                        </div>

                        @if($module->video_url)
                        <div class="mb-4" id="current_url_section">
                            <label class="form-label">Current Video URL</label>
                            <input type="text" class="form-control bg-light" value="{{ old('video_url', $module->video_url) }}" readonly>
                            <input type="hidden" name="video_url" value="{{ old('video_url', $module->video_url) }}">
                        </div>
                        @endif

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="replace_video" name="replace_video" value="1">
                                <label class="form-check-label" for="replace_video">
                                    <strong>Ganti Video</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">Centang untuk mengganti video dengan yang baru</small>
                        </div>
                        
                        <!-- Video Type Selection (Hidden by default) -->
                        <div id="video_replacement_section" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">New Video Type <span class="text-danger">*</span></label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="new_video_type" id="new_video_upload" 
                                               value="upload" required>
                                        <label class="form-check-label" for="new_video_upload">
                                            Upload Video File
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="new_video_type" id="new_video_url" 
                                               value="url">
                                        <label class="form-check-label" for="new_video_url">
                                            Embed from URL (YouTube, Vimeo, etc)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- File Upload Field -->
                            <div class="mb-4" id="new_file_field" style="display: none;">
                                <label for="new_file" class="form-label">New Video File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="new_file" name="new_file" accept="video/*">
                                <small class="form-text text-muted">
                                    Maximum file size: 500 MB. Supported formats: MP4, AVI, MOV, WMV
                                </small>
                            </div>

                            <!-- URL Field -->
                            <div class="mb-4" id="new_url_field" style="display: none;">
                                <label for="new_video_url" class="form-label">New Video URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="new_video_url" name="new_video_url" 
                                       placeholder="https://youtube.com/watch?v=... or https://youtu.be/...">
                                <small class="form-text text-muted">
                                    <strong>Supported formats:</strong><br>
                                    • YouTube: <code>https://youtube.com/watch?v=VIDEO_ID</code><br>
                                    • YouTube Short: <code>https://youtu.be/VIDEO_ID</code><br>
                                    • YouTube Embed: <code>https://youtube.com/embed/VIDEO_ID</code><br>
                                    • Vimeo: <code>https://vimeo.com/VIDEO_ID</code>
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="estimated_duration" class="form-label">Estimasi Durasi (menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('estimated_duration') is-invalid @enderror" 
                                           id="estimated_duration" name="estimated_duration" value="{{ old('estimated_duration', $module->estimated_duration) }}" min="1" placeholder="Contoh: 60" required>
                                    <small class="form-text text-muted">
                                        Estimasi waktu yang dibutuhkan siswa untuk menonton video ini
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
// Toggle video replacement section
document.getElementById('replace_video').addEventListener('change', function() {
    const replacementSection = document.getElementById('video_replacement_section');
    const currentUrlSection = document.getElementById('current_url_section');
    const hiddenVideoUrl = currentUrlSection ? currentUrlSection.querySelector('input[name="video_url"]') : null;
    
    if (this.checked) {
        replacementSection.style.display = 'block';
        if (currentUrlSection) currentUrlSection.style.display = 'none';
        if (hiddenVideoUrl) hiddenVideoUrl.removeAttribute('name'); // jangan kirim URL lama saat ganti video
    } else {
        replacementSection.style.display = 'none';
        if (currentUrlSection) currentUrlSection.style.display = 'block';
        if (hiddenVideoUrl) hiddenVideoUrl.setAttribute('name', 'video_url');
        // Reset radio buttons
        document.querySelectorAll('input[name="new_video_type"]').forEach(radio => radio.checked = false);
        document.getElementById('new_file_field').style.display = 'none';
        document.getElementById('new_url_field').style.display = 'none';
        document.getElementById('new_file').value = '';
        document.getElementById('new_video_url').value = '';
    }
});

// Handle new video type selection
document.querySelectorAll('input[name="new_video_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const fileField = document.getElementById('new_file_field');
        const urlField = document.getElementById('new_url_field');
        const fileInput = document.getElementById('new_file');
        const videoUrlInput = document.getElementById('new_video_url');
        
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

// File validation for video upload
document.getElementById('new_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const maxSize = 500 * 1024 * 1024; // 500MB in bytes
    const allowedTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv'];
    
    // Clear previous errors
    const existingError = document.getElementById('video-size-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Remove invalid class
    document.getElementById('new_file').classList.remove('is-invalid');
    
    if (file) {
        // Check file type
        if (!allowedTypes.includes(file.type)) {
            const errorDiv = document.createElement('div');
            errorDiv.id = 'video-size-error';
            errorDiv.className = 'invalid-feedback d-block';
            errorDiv.textContent = 'Format video yang diperbolehkan: mp4, avi, mov, wmv';
            
            document.getElementById('new_file').classList.add('is-invalid');
            document.getElementById('new_file').parentNode.appendChild(errorDiv);
            
            // Clear file input
            e.target.value = '';
            
            return;
        }
        
        // Check file size
        if (file.size > maxSize) {
            const errorDiv = document.createElement('div');
            errorDiv.id = 'video-size-error';
            errorDiv.className = 'invalid-feedback d-block';
            errorDiv.textContent = 'Ukuran file video tidak boleh lebih dari 500MB';
            
            document.getElementById('new_file').classList.add('is-invalid');
            document.getElementById('new_file').parentNode.appendChild(errorDiv);
            
            // Clear file input
            e.target.value = '';
            
            return;
        }
    }
});

// Form submission validation (tanpa dialog konfirmasi - langsung submit, pesan sukses & notifikasi setelah berhasil)
document.querySelector('form').addEventListener('submit', function(e) {
    const replaceVideo = document.getElementById('replace_video').checked;
    const newVideoType = document.querySelector('input[name="new_video_type"]:checked');
    const fileInput = document.getElementById('new_file');
    const videoUrlInput = document.getElementById('new_video_url');
    
    if (replaceVideo) {
        if (!newVideoType) {
            e.preventDefault();
            alert('Silakan pilih tipe video baru (Upload File atau Embed URL).');
            return false;
        }
        
        if (newVideoType.value === 'upload' && !fileInput.files.length) {
            e.preventDefault();
            alert('Silakan pilih file video untuk diupload.');
            return false;
        }
        
        if (newVideoType.value === 'url' && !videoUrlInput.value.trim()) {
            e.preventDefault();
            alert('Please enter a valid video URL.');
            return false;
        }
    }
});
</script>
@endsection
