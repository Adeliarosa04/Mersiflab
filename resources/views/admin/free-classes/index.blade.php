@extends('layouts.admin')

@section('title', 'Free Course Management')

@section('content')
<div class="page-title">
    <h1>Free Course Management</h1>
</div>

<div class="card-content">
    <div class="card-content-title">
        <span>All Free Courses ({{ $freeClasses->count() }} total)</span>
        <div>
            <a href="{{ route('admin.free-classes.create') }}" class="btn btn-primary" style="font-size: 13px; padding: 6px 16px;">
                <i class="fas fa-plus me-1"></i>Add Free Course
            </a>
        </div>
    </div>

    @if(!empty($storageLinkMissing))
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-triangle-exclamation me-2"></i>
            <strong>Symbolic link storage belum dibuat.</strong>
            Video dan modul PDF yang diunggah tidak akan bisa dibuka pengunjung (error 404).
            Jalankan perintah berikut sekali di server:
            <code style="display: block; margin-top: 8px; background: #fff; padding: 6px 10px; border-radius: 4px;">php artisan storage:link</code>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width: 90px;">Thumb</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th style="width: 110px;">Levels</th>
                    <th style="width: 90px;">Order</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 190px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($freeClasses as $freeClass)
                    <tr>
                        <td>
                            @if($freeClass->thumbnail_url)
                                <img src="{{ $freeClass->thumbnail_url }}" alt="{{ $freeClass->title }}"
                                     style="width: 72px; height: 46px; object-fit: cover; border-radius: 4px;">
                            @else
                                <span style="color: #bdbdbd; font-size: 12px;">—</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $freeClass->title }}</strong>
                        </td>
                        <td>
                            <span style="color: #828282; font-size: 13px;">
                                {{ Str::limit($freeClass->description, 60) }}
                            </span>
                        </td>
                        <td>
                            @if($freeClass->levels_count > 0)
                                <span class="badge bg-info" style="font-size: 11px;">
                                    <i class="fas fa-layer-group me-1"></i>{{ $freeClass->levels_count }} level
                                </span>
                            @else
                                <span class="badge bg-warning text-dark" style="font-size: 11px;">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            <span style="color: #333; font-weight: 500;">{{ $freeClass->sort_order }}</span>
                        </td>
                        <td>
                            @if($freeClass->is_active)
                                <span class="badge bg-success" style="font-size: 11px;">Active</span>
                            @else
                                <span class="badge bg-secondary" style="font-size: 11px;">Inactive</span>
                            @endif
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                <a href="{{ route('admin.free-classes.edit', $freeClass) }}"
                                   style="color: #1976d2; text-decoration: none; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;"
                                   onmouseover="this.style.background='#e3f2fd'"
                                   onmouseout="this.style.background='transparent'"
                                   title="Edit Free Course">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>

                                <form action="{{ route('admin.free-classes.toggleActive', $freeClass) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit"
                                            style="background: none; border: none; color: #f57c00; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; cursor: pointer;"
                                            title="{{ $freeClass->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas fa-{{ $freeClass->is_active ? 'eye-slash' : 'eye' }} me-1"></i>{{ $freeClass->is_active ? 'Hide' : 'Show' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.free-classes.destroy', $freeClass) }}" method="POST" style="display: inline;"
                                      onsubmit="return confirm('Hapus free course ini? Video dan modul PDF yang diunggah ikut terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            style="background: none; border: none; color: #d32f2f; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; cursor: pointer;"
                                            title="Delete Free Course">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 32px; color: #828282;">
                            <i class="fas fa-gift mb-2" style="font-size: 28px; display: block; color: #bdbdbd;"></i>
                            Belum ada Free Course. Klik <strong>Add Free Course</strong> untuk menambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
