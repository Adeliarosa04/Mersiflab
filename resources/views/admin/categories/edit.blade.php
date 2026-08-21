@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
<div class="page-title">
    <h1>Edit Category</h1>
</div>

<div class="card-content">
    <div class="card-content-title">
        <span>Edit Category: {{ $category->name }}</span>
        <a href="{{ back_url(route('admin.categories.index')) }}" class="btn btn-secondary" style="font-size: 13px; padding: 6px 16px;">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name', $category->name) }}" 
                   placeholder="e.g., Web Development" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Slug akan dibuat otomatis dari nama kategori.</small>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control @error('description') is-invalid @enderror" 
                      id="description" name="description" rows="3" 
                      placeholder="Deskripsi kategori..." required>{{ old('description', $category->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                       value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>
            <small class="form-text text-muted">Kategori aktif akan muncul di dropdown course.</small>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> Kategori ini digunakan oleh <strong>{{ $category->classes()->count() }}</strong> course(s).
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ back_url(route('admin.categories.index')) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Update Category
            </button>
        </div>
    </form>
</div>
@endsection
