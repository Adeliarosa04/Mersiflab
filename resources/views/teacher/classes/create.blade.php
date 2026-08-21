@extends('layouts.app')

@section('title', 'Create New Class')

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

.input-group-text {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid #e9ecef;
    border-right: none;
    border-radius: 0.75rem 0 0 0.75rem;
    font-weight: 600;
}

.input-group .form-control {
    border-radius: 0 0.75rem 0.75rem 0;
}

.text-muted {
    color: #6c757d !important;
}

.text-danger {
    color: #dc3545 !important;
}

.include-checkbox-grid {
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
                        <i class="fas fa-book me-2"></i>Course Information
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

                    <form action="{{ route('teacher.classes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label">Course Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" placeholder="e.g., Web Development 101"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Course Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="5"
                                      placeholder="Describe your course in detail..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="image" class="form-label">Course Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*" required>
                            <small class="text-muted">Upload a thumbnail image for this course (JPG, PNG, GIF, WEBP - max 5MB)</small>
                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail rounded-3" style="max-width: 200px; max-height: 200px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="price" class="form-label">Course Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" min="0" max="99999999.99" step="0.01" 
                                       placeholder="0.00" value="{{ old('price', 0) }}" required>
                            </div>
                            <small class="text-muted">Course price in Rupiah (Maximum: Rp 99,999,999.99)</small>
                            @error('price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_discount" name="has_discount" value="1" {{ old('has_discount') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_discount">Add discount for this course</label>
                                <small class="d-block text-muted mt-1">Check to add a discount amount</small>
                            </div>
                        </div>

                        <div class="mb-4" id="discountBlock" style="display: none;">
                            <label for="discount" class="form-label">Discount Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('discount') is-invalid @enderror" id="discount" name="discount" min="0" max="99999999.99" step="0.01" placeholder="0.00" value="{{ old('discount') }}">
                            </div>
                            <small class="text-muted">Enter discount amount (in Rupiah). Make sure this does not exceed the price.</small>
                            @error('discount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="row mt-3">
                                <div class="col-md-6 mb-2">
                                    <label for="discount_starts_at" class="form-label">Discount Start Date</label>
                                    <input type="date" class="form-control @error('discount_starts_at') is-invalid @enderror" id="discount_starts_at" name="discount_starts_at" value="{{ old('discount_starts_at') }}">
                                    @error('discount_starts_at')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="discount_ends_at" class="form-label">Discount End Date</label>
                                    <input type="date" class="form-control @error('discount_ends_at') is-invalid @enderror" id="discount_ends_at" name="discount_ends_at" value="{{ old('discount_ends_at') }}">
                                    @error('discount_ends_at')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="what_youll_learn" class="form-label">What Students Will Learn <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('what_youll_learn') is-invalid @enderror" 
                                      id="what_youll_learn" name="what_youll_learn" rows="6"
                                      placeholder="Enter the points students will learn, separated by new line..." required>{{ old('what_youll_learn') }}</textarea>
                            <small class="text-muted">Write what students will learn (one point per line)</small>
                            @error('what_youll_learn')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block mb-3">
                                Course Includes <span class="text-danger">*</span>
                            </label>
                            <small class="text-muted d-block mb-3">Select features included in this course to help students understand what they'll get</small>
                            
                            <div class="include-checkbox-grid">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_video" name="includes[]" value="video" {{ old('includes') && in_array('video', old('includes')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_video">
                                            <i class="fas fa-video text-primary me-2"></i>
                                            On-demand video lectures
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_lifetime" name="includes[]" value="lifetime" {{ old('includes') && in_array('lifetime', old('includes')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_lifetime">
                                            <i class="fas fa-infinity text-success me-2"></i>
                                            Lifetime access
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_certificate" name="includes[]" value="certificate" {{ old('includes') && in_array('certificate', old('includes')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_certificate">
                                            <i class="fas fa-certificate text-warning me-2"></i>
                                            Completion certificate
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_ai" name="includes[]" value="ai" {{ old('includes') && in_array('ai', old('includes')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_ai">
                                            <i class="fas fa-robot text-primary me-2"></i>
                                            AI Assistant support
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_mobile" name="includes[]" value="mobile" {{ old('includes') && in_array('mobile', old('includes')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_mobile">
                                            <i class="fas fa-mobile-alt text-success me-2"></i>
                                            Mobile & tablet access
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            @error('includes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="requirement" class="form-label">Course Requirements <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('requirement') is-invalid @enderror" 
                                      id="requirement" name="requirement" rows="4"
                                      placeholder="Enter requirements to take this course, separated by new line..." required>{{ old('requirement') }}</textarea>
                            <small class="text-muted">Write requirements needed to take this course (one point per line)</small>
                            @error('requirement')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="category" class="form-label">Course Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" name="category" required>
                                <option value="">-- Select Category --</option>
                                @if(isset($categories) && $categories->count() > 0)
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" {{ old('category') === $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @else
                                    {{-- Fallback to constant categories if database is empty --}}
                                    @foreach(\App\Models\ClassModel::CATEGORIES as $key => $label)
                                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Choose the category that best describes your course</small>
                            @error('category')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-3 justify-content-end">
                            <a href="{{ back_url(route('teacher.manage.content')) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card info-card mt-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Tips for Creating a Great Course
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="small text-muted mb-0">
                                <li class="mb-2">Give your course a clear, descriptive name</li>
                                <li class="mb-2">Write a detailed description to help students understand the content</li>
                                <li class="mb-2">Use high-quality images that represent your course</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="small text-muted mb-0">
                                <li class="mb-2">All courses start as draft and require admin approval</li>
                                <li class="mb-2">Add chapters and modules before requesting approval</li>
                                <li class="mb-2">You can edit your course until it's approved by admin</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Image preview with file size validation
    document.getElementById('image')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        
        // Clear previous errors
        const existingError = document.getElementById('image-size-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Remove invalid class
        document.getElementById('image').classList.remove('is-invalid');
        
        if (file) {
            // Check file size
            if (file.size > maxSize) {
                // Show error
                const errorDiv = document.createElement('div');
                errorDiv.id = 'image-size-error';
                errorDiv.className = 'invalid-feedback d-block';
                errorDiv.textContent = 'Image size must not exceed 5MB';
                
                document.getElementById('image').classList.add('is-invalid');
                document.getElementById('image').parentNode.appendChild(errorDiv);
                
                // Clear preview
                preview.style.display = 'none';
                
                // Clear the file input
                e.target.value = '';
                
                return;
            }
            
            // Show preview if file size is valid
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });

    // Discount toggle
    const hasDiscountCheckbox = document.getElementById('has_discount');
    const discountBlock = document.getElementById('discountBlock');
    const discountInput = document.getElementById('discount');

    function toggleDiscountBlock() {
        if (!hasDiscountCheckbox) return;
        if (hasDiscountCheckbox.checked) {
            discountBlock.style.display = 'block';
            if (discountInput) discountInput.required = true;
        } else {
            discountBlock.style.display = 'none';
            if (discountInput) {
                discountInput.required = false;
                discountInput.value = '';
            }
        }
    }

    hasDiscountCheckbox?.addEventListener('change', toggleDiscountBlock);
    // Initialize on load (in case of validation errors)
    if (hasDiscountCheckbox && hasDiscountCheckbox.checked) {
        toggleDiscountBlock();
    }
</script>
@endsection
