@extends('layouts.app')

@section('title', 'Edit Class')

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

.btn-action.view {
    color: #22c55e;
}

.btn-action.view:hover {
    background: #22c55e;
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

                    <form action="{{ route('teacher.classes.update', $class->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" placeholder="e.g., Web Development 101"
                                   value="{{ old('name', $class->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="5"
                                      placeholder="Describe your class..." required>{{ old('description', $class->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Class Image <span class="text-danger">*</span></label>
                            @if($class->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $class->image) }}" alt="Current image" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    <p class="small text-muted mt-1">Current image</p>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*" {{ !$class->image ? 'required' : '' }}>
                            <small class="text-muted">Upload a new image/thumbnail to replace the current image (JPG, PNG, GIF, WEBP, max 5MB)</small>
                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                <p class="small text-muted mt-1">New image preview</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" min="0" max="99999999.99" step="0.01" 
                                       placeholder="0.00" value="{{ old('price', $class->price ?? 0) }}" required>
                            </div>
                            <small class="text-muted">Class price in Rupiah (Maximum: Rp 99.999.999,99)</small>
                            @error('price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_discount" name="has_discount" value="1" {{ old('has_discount', $class->has_discount ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_discount">Offer discount for this class</label>
                                <small class="d-block text-muted mt-1">Check to add a discount amount.</small>
                            </div>
                        </div>

                        <div class="mb-3" id="discountBlock" style="display: none;">
                            <label for="discount" class="form-label">Discount Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('discount') is-invalid @enderror" id="discount" name="discount" min="0" max="99999999.99" step="0.01" placeholder="0.00" value="{{ old('discount', $class->discount ?? '') }}">
                            </div>
                            <small class="text-muted">Enter discount amount (in Rupiah). If you want to give a discount, make sure this value does not exceed the price.</small>
                            @error('discount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="row mt-3">
                                <div class="col-md-6 mb-2">
                                    <label for="discount_starts_at" class="form-label">Discount Start Date</label>
                                    <input type="date" class="form-control @error('discount_starts_at') is-invalid @enderror" id="discount_starts_at" name="discount_starts_at" value="{{ old('discount_starts_at', optional($class->discount_starts_at)->format('Y-m-d')) }}">
                                    @error('discount_starts_at')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="discount_ends_at" class="form-label">Discount End Date</label>
                                    <input type="date" class="form-control @error('discount_ends_at') is-invalid @enderror" id="discount_ends_at" name="discount_ends_at" value="{{ old('discount_ends_at', optional($class->discount_ends_at)->format('Y-m-d')) }}">
                                    @error('discount_ends_at')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="what_youll_learn" class="form-label">What You'll Learn <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('what_youll_learn') is-invalid @enderror" 
                                      id="what_youll_learn" name="what_youll_learn" rows="6"
                                      placeholder="Enter the points that will be learned, separated by new line..." required>{{ old('what_youll_learn', $class->what_youll_learn ?? '') }}</textarea>
                            <small class="text-muted">Write what students will learn in this class (one point per line)</small>
                            @error('what_youll_learn')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Course Includes <span class="text-danger">*</span></label>
                            <small class="text-muted d-block mb-3">Pilih fitur yang tersedia untuk kelas ini. Ini akan membantu siswa memahami apa yang mereka dapatkan.</small>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="include_video" name="includes[]" value="video" {{ old('includes') && in_array('video', old('includes')) ? 'checked' : (is_array($class->includes) && in_array('video', $class->includes) ? 'checked' : '') }}>
                                        <label class="form-check-label" for="include_video">
                                            <i class="fas fa-video text-primary me-2"></i>
                                            Video pembelajaran on-demand
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="include_lifetime" name="includes[]" value="lifetime" {{ old('includes') && in_array('lifetime', old('includes')) ? 'checked' : (is_array($class->includes) && in_array('lifetime', $class->includes) ? 'checked' : '') }}>
                                        <label class="form-check-label" for="include_lifetime">
                                            <i class="fas fa-infinity text-success me-2"></i>
                                            Akses seumur hidup
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="include_certificate" name="includes[]" value="certificate" {{ old('includes') && in_array('certificate', old('includes')) ? 'checked' : (is_array($class->includes) && in_array('certificate', $class->includes) ? 'checked' : '') }}>
                                        <label class="form-check-label" for="include_certificate">
                                            <i class="fas fa-certificate text-warning me-2"></i>
                                            Sertifikat penyelesaian
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="include_ai" name="includes[]" value="ai" {{ old('includes') && in_array('ai', old('includes')) ? 'checked' : (is_array($class->includes) && in_array('ai', $class->includes) ? 'checked' : '') }}>
                                        <label class="form-check-label" for="include_ai">
                                            <i class="fas fa-robot text-primary me-2"></i>
                                            Tanya AI Assistant
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="include_lifetime_2" name="includes[]" value="lifetime" {{ old('includes') && in_array('lifetime', old('includes')) ? 'checked' : (is_array($class->includes) && in_array('lifetime', $class->includes) ? 'checked' : '') }}>
                                        <label class="form-check-label" for="include_lifetime_2">
                                            <i class="fas fa-infinity text-success me-2"></i>
                                            Akses seumur hidup
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="include_mobile" name="includes[]" value="mobile" {{ old('includes') && in_array('mobile', old('includes')) ? 'checked' : (is_array($class->includes) && in_array('mobile', $class->includes) ? 'checked' : '') }}>
                                        <label class="form-check-label" for="include_mobile">
                                            <i class="fas fa-mobile-alt text-success me-2"></i>
                                            Akses mobile & tablet
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            @error('includes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="requirement" class="form-label">Requirements <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('requirement') is-invalid @enderror" 
                                      id="requirement" name="requirement" rows="4"
                                      placeholder="Enter requirements to take this class, separated by new line..." required>{{ old('requirement', $class->requirement ?? '') }}</textarea>
                            <small class="text-muted">Write the requirements needed to take this class (one point per line)</small>
                            @error('requirement')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" name="category" required>
                                <option value="">-- Select Category --</option>
                                @if(isset($categories) && $categories->count() > 0)
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" {{ old('category', $class->category ?? '') === $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @else
                                    {{-- Fallback to constant categories if database is empty --}}
                                    @foreach(\App\Models\ClassModel::CATEGORIES as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $class->category ?? '') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Choose the category that best describes your class</small>
                            @error('category')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Chapters Section -->
                        @if($chapters && count($chapters) > 0)
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="mb-3">Chapters in this class:</h6>
                            <ul class="list-unstyled">
                                @foreach($chapters as $chapter)
                                <li class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                                        <div>
                                            <strong>{{ $chapter->title ?? 'Untitled Chapter' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $chapter->modules->count() }} modules</small>
                                        </div>
                                        <div>
                                            <a href="{{ route('teacher.chapters.edit', [$class->id, $chapter->id]) }}" class="btn-action" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('teacher.chapters.index', $class->id) }}" class="btn-action view" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="d-flex gap-3 justify-content-end">
                            {{-- Cancel kembali ke halaman asal (mis. detail course /
                                 daftar chapter, My Courses, atau Manage Content).
                                 Fallback ke daftar chapter course ini bila halaman
                                 sebelumnya tidak diketahui - bukan langsung keluar
                                 ke Manage Content seperti sebelumnya. --}}
                            <a href="{{ back_url(route('teacher.chapters.index', $class->id)) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <button type="button" class="btn-action delete" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Delete Class
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this class? This action cannot be undone.</p>
                            <p><strong>{{ $class->name ?? 'Untitled' }}</strong> will be permanently deleted along with all its chapters and modules.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('teacher.classes.destroy', $class->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-2"></i>Delete Class
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Tips for Editing a Class
                    </h6>
                    <ul class="small text-muted mb-0">
                        <li>Update class details as needed</li>
                        <li>Add chapters to structure your content</li>
                        <li>Publish when ready for students to see</li>
                        <li>Changes are saved immediately</li>
                    </ul>
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
                errorDiv.textContent = 'Ukuran gambar tidak boleh lebih dari 5MB';
                
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
                // Do not clear value on edit hide to avoid accidental deletion; optional
            }
        }
    }

    hasDiscountCheckbox?.addEventListener('change', toggleDiscountBlock);
    // Initialize on load (in case of validation errors or existing data)
    if (hasDiscountCheckbox && hasDiscountCheckbox.checked) {
        toggleDiscountBlock();
    }
</script>
@endsection
