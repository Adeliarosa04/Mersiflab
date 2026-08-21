@extends('layouts.admin')

@section('title', 'Edit Teacher')

@section('content')
<div class="page-title">
    <h1>Edit Teacher</h1>
</div>

<div class="card-content" style="background: white; border-radius: 12px; padding: 48px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
    <i class="fas fa-user-edit" style="font-size: 64px; color: #e0e0e0; margin-bottom: 20px;"></i>
    <h3 style="color: #333; margin-bottom: 12px;">Fitur Coming Soon</h3>
    <p style="color: #828282; margin-bottom: 24px; max-width: 400px; margin-left: auto; margin-right: auto;">
        Editing teacher profile will be available in a future version. For now use <strong>View</strong> to see details.
    </p>
    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('admin.teachers.show', $teacher->id) }}" class="btn" style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
            <i class="fas fa-eye me-2"></i>View Detail
        </a>
        <a href="{{ back_url(route('admin.teachers.index')) }}" class="btn" style="background: #2F80ED; color: white; border: none; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
            <i class="fas fa-arrow-left me-2"></i>Back to Teachers
        </a>
    </div>
</div>
@endsection
