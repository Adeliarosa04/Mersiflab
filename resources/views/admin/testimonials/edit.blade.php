@extends('layouts.admin')

@section('title', 'Edit Testimonial - Admin')

@section('content')
<div style="margin-bottom: 12px;">
    <a href="{{ route('admin.testimonials.show', $testimonial->id) }}"
       style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; color: #2F80ED; background: #ffffff; border: 1px solid #e0e0e0; padding: 8px 16px; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;"
       onmouseover="this.style.background='#f8f9fa'; this.style.borderColor='#2F80ED';"
       onmouseout="this.style.background='#ffffff'; this.style.borderColor='#e0e0e0';">
        <i class="fas fa-arrow-left"></i>Kembali ke Detail Testimoni
    </a>
</div>

<div class="page-title">
    <h1>Edit Testimonial</h1>
    <p>Perbaiki penulisan testimoni siswa bila perlu. Status publikasi diatur lewat tombol Approve / Reject.</p>
</div>

<div class="card-content">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0; padding-left: 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.testimonials.update', $testimonial->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $testimonial->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Position (optional)</label>
            <input type="text" name="position" class="form-control" value="{{ old('position', $testimonial->position) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="4" required>{{ old('content', $testimonial->content) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Avatar (optional)</label>
            <input type="file" name="avatar" class="form-control">
            @if($testimonial->avatar || ($testimonial->admin && $testimonial->admin->avatar))
                <div class="mt-2">
                    <img src="{{ $testimonial->avatar ? asset('storage/' . $testimonial->avatar) : $testimonial->avatarUrl() }}" alt="avatar" style="max-width:80px; border-radius:8px;">
                </div>
                <small class="text-muted">Current avatar (testimonial/admin)</small>
            @endif
        </div>
        {{-- Checkbox "Published" dihapus: status publikasi kini ditentukan
             lewat tombol Approve / Reject pada dashboard moderasi, supaya
             jejak siapa yang menyetujui dan kapan tetap tercatat. --}}
        <div class="mb-3">
            <label class="form-label">Rating (1-5, opsional)</label>
            <input type="number" name="rating" class="form-control" min="1" max="5"
                   value="{{ old('rating', $testimonial->rating) }}">
        </div>
        <div class="alert alert-info" style="background:#e3f2fd; color:#0d47a1; border:1px solid #bbdefb; border-radius:8px; padding:12px 16px; font-size:13px;">
            <i class="fas fa-info-circle me-2"></i>
            Status saat ini: <strong>{{ $testimonial->status_label }}</strong>.
            Ubah status melalui tombol Approve / Reject di halaman moderasi.
        </div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ back_url(route('admin.testimonials.index')) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
