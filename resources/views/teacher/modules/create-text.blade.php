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

/* Enhanced textarea styling for fallback */
#content {
    min-height: 500px;
    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
    font-size: 14px;
    line-height: 1.6;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    resize: vertical;
    transition: all 0.3s ease;
}

#content:focus {
    border-color: #1f7ae0;
    box-shadow: 0 0 0 0.2rem rgba(31, 122, 224, 0.15);
}

/* TinyMCE editor styling */
.tox-tinymce {
    border-radius: 0.75rem !important;
    border: 2px solid #e9ecef !important;
}

.tox-editor-container {
    border-radius: 0.75rem !important;
}

.tox-toolbar__group {
    border-radius: 0.5rem !important;
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
                        <i class="fas fa-align-left me-2"></i>Create Text Module
                    </h5>
                </div>
                <div class="card-body teacher-form-body">
                    <form action="{{ route('teacher.modules.store.text', $chapter) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="form-label">Module Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
                            <small class="form-text text-muted">
                                Gunakan editor WYSIWYG untuk formatting: bold, italic, heading, numbering, bullet points, links, images, tables, dan lainnya.
                            </small>
                            @error('content')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="estimated_duration" class="form-label">Estimasi Durasi (menit) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('estimated_duration') is-invalid @enderror" 
                                   id="estimated_duration" name="estimated_duration" value="{{ old('estimated_duration') }}" min="1" placeholder="Contoh: 30" required>
                            <small class="form-text text-muted">
                                Estimasi waktu yang dibutuhkan siswa untuk menyelesaikan module ini
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
                                <i class="fas fa-save me-2"></i>Create Module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include TinyMCE from different CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
    console.log('TinyMCE script loading...');
    
    // Check if TinyMCE loaded
    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE failed to load from CDN');
        // Fallback: make textarea look like editor
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.querySelector('#content');
            if (textarea) {
                textarea.style.minHeight = '500px';
                textarea.style.fontSize = '14px';
                textarea.style.lineHeight = '1.6';
                textarea.style.padding = '12px';
                textarea.style.border = '1px solid #ddd';
                textarea.style.borderRadius = '4px';
                textarea.style.fontFamily = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif';
            }
        });
    } else {
        console.log('TinyMCE loaded successfully, initializing...');
        
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#content',
                height: 500,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                         'alignleft aligncenter alignright alignjustify | ' +
                         'bullist numlist outdent indent | ' +
                         'forecolor backcolor removeformat | ' +
                         'link image media table | ' +
                         'preview code fullscreen | help',
                toolbar_mode: 'sliding',
                block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6; Preformatted=pre',
                content_style: 'body { font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size:14px; line-height:1.6; }',
                branding: false,
                promotion: false,
                menubar: 'file edit view insert format tools table help',
                
                // Fix form submission
                setup: function(editor) {
                    console.log('TinyMCE setup called');
                    editor.on('init', function() {
                        console.log('TinyMCE initialized successfully');
                    });
                    editor.on('error', function(e) {
                        console.error('TinyMCE error:', e);
                    });
                }
            });
            
            // Handle form submission properly
            const form = document.querySelector('form[action*="store.text"]');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            console.log('Looking for form with action containing "store.text"');
            console.log('Form found:', !!form);
            console.log('Submit button found:', !!submitBtn);
            
            if (form) {
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
            }
            
            if (form && submitBtn) {
                console.log('Form and submit button found, adding event listener');
                
                form.addEventListener('submit', function(e) {
                    console.log('Form submission triggered');
                    console.log('Form action:', form.action);
                    
                    // Sync TinyMCE content to textarea
                    const editor = tinymce.get('content');
                    if (editor) {
                        console.log('Syncing TinyMCE content...');
                        editor.save();
                        console.log('Content synced to textarea');
                    }
                    
                    // Let the form submit normally
                    return true;
                });
                
                // Also handle button click as fallback
                submitBtn.addEventListener('click', function(e) {
                    console.log('Submit button clicked');
                    console.log('Form action:', form.action);
                    
                    // Sync TinyMCE content to textarea
                    const editor = tinymce.get('content');
                    if (editor) {
                        console.log('Syncing TinyMCE content from button click...');
                        editor.save();
                        console.log('Content synced to textarea');
                        
                        // Check textarea content
                        const textarea = document.querySelector('#content');
                        if (textarea) {
                            console.log('Textarea content length:', textarea.value.length);
                            console.log('Textarea content preview:', textarea.value.substring(0, 100) + '...');
                        }
                    }
                    
                    // Submit the form
                    form.submit();
                });
            } else {
                console.log('Form or submit button not found');
                console.log('All forms on page:', document.querySelectorAll('form').length);
                console.log('All submit buttons:', document.querySelectorAll('button[type="submit"]').length);
                
                // Try alternative selectors
                const altForm = document.querySelector('form');
                const altSubmit = document.querySelector('button[type="submit"]');
                console.log('Alternative form found:', !!altForm);
                console.log('Alternative submit found:', !!altSubmit);
                
                if (altForm) {
                    console.log('Alternative form action:', altForm.action);
                    console.log('Alternative form method:', altForm.method);
                    
                    // Use the alternative form
                    console.log('Using alternative form for event handling');
                    
                    altForm.addEventListener('submit', function(e) {
                        console.log('Alternative form submission triggered');
                        console.log('Form action:', altForm.action);
                        
                        // Sync TinyMCE content to textarea
                        const editor = tinymce.get('content');
                        if (editor) {
                            console.log('Syncing TinyMCE content...');
                            editor.save();
                            console.log('Content synced to textarea');
                        }
                        
                        // Let the form submit normally
                        return true;
                    });
                    
                    // Also handle button click as fallback
                    if (altSubmit) {
                        altSubmit.addEventListener('click', function(e) {
                            console.log('Alternative submit button clicked');
                            console.log('Form action:', altForm.action);
                            
                            // Sync TinyMCE content to textarea
                            const editor = tinymce.get('content');
                            if (editor) {
                                console.log('Syncing TinyMCE content from button click...');
                                editor.save();
                                console.log('Content synced to textarea');
                                
                                // Check textarea content
                                const textarea = document.querySelector('#content');
                                if (textarea) {
                                    console.log('Textarea content length:', textarea.value.length);
                                    console.log('Textarea content preview:', textarea.value.substring(0, 100) + '...');
                                }
                            }
                            
                            // Submit the form
                            altForm.submit();
                        });
                    }
                }
            }
        });
    }
</script>

<style>
    /* Enhanced textarea styling for fallback */
    #content {
        min-height: 500px;
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
        font-size: 14px;
        line-height: 1.6;
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 0.75rem;
        resize: vertical;
        transition: all 0.3s ease;
    }
    
    #content:focus {
        border-color: #1f7ae0;
        box-shadow: 0 0 0 0.2rem rgba(31, 122, 224, 0.15);
    }
    
    /* TinyMCE editor styling */
    .tox-tinymce {
        border-radius: 0.75rem !important;
        border: 2px solid #e9ecef !important;
    }
    
    .tox-editor-container {
        border-radius: 0.75rem !important;
    }
    
    .tox-toolbar__group {
        border-radius: 0.5rem !important;
    }
</style>
@endsection
