@extends('layouts.admin')

@section('title', 'User Activities - ' . $user->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1" style="color: #333; font-size: 24px; font-weight: 700;">User Activities</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="background: transparent; padding: 0; margin: 0;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" style="color: #1976d2; text-decoration: none; font-size: 14px;">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    @if($user->role === 'teacher')
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.teachers.index') }}" style="color: #1976d2; text-decoration: none; font-size: 14px;">Teachers</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.teachers.show', $user->id) }}" style="color: #1976d2; text-decoration: none; font-size: 14px;">{{ $user->name }}</a>
                        </li>
                    @elseif($user->role === 'student')
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.students.index') }}" style="color: #1976d2; text-decoration: none; font-size: 14px;">Students</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.students.show', $user->id) }}" style="color: #1976d2; text-decoration: none; font-size: 14px;">{{ $user->name }}</a>
                        </li>
                    @elseif($user->role === 'admin')
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.admins.index') }}" style="color: #1976d2; text-decoration: none; font-size: 14px;">Admins</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.admins.show', $user->id) }}" style="color: #1976d2; text-decoration: none; font-size: 14px;">{{ $user->name }}</a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page" style="color: #828282; font-size: 14px;">Activities</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($user->role === 'teacher')
                <a href="{{ back_url(route('admin.teachers.show', $user->id)) }}" 
                   class="btn" 
                   style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 8px 16px; font-size: 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                   onmouseover="this.style.background='#1976d2'; this.style.color='white'; this.style.borderColor='#1976d2';" 
                   onmouseout="this.style.background='#e3f2fd'; this.style.color='#1976d2'; this.style.borderColor='#90caf9';">
                    <i class="fas fa-arrow-left"></i>Back to Teacher
                </a>
            @elseif($user->role === 'student')
                <a href="{{ back_url(route('admin.students.show', $user->id)) }}" 
                   class="btn" 
                   style="background: #e8f5e9; color: #27AE60; border: 1px solid #a5d6a7; padding: 8px 16px; font-size: 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                   onmouseover="this.style.background='#27AE60'; this.style.color='white'; this.style.borderColor='#27AE60';" 
                   onmouseout="this.style.background='#e8f5e9'; this.style.color='#27AE60'; this.style.borderColor='#a5d6a7';">
                    <i class="fas fa-arrow-left"></i>Back to Student
                </a>
            @elseif($user->role === 'admin')
                <a href="{{ back_url(route('admin.admins.show', $user->id)) }}" 
                   class="btn" 
                   style="background: #fff3e0; color: #f57c00; border: 1px solid #ffcc02; padding: 8px 16px; font-size: 14px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;"
                   onmouseover="this.style.background='#f57c00'; this.style.color='white'; this.style.borderColor='#f57c00';" 
                   onmouseout="this.style.background='#fff3e0'; this.style.color='#f57c00'; this.style.borderColor='#ffcc02';">
                    <i class="fas fa-arrow-left"></i>Back to Admin
                </a>
            @endif
        </div>
    </div>

    <!-- User Info Card -->
    <div class="card mb-4" style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none;">
        <div class="d-flex align-items-center">
            <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-right: 24px; 
                @if($user->role === 'teacher') background: linear-gradient(135deg, #e3f2fd, #bbdefb);
                @elseif($user->role === 'student') background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
                @else background: linear-gradient(135deg, #fff3e0, #ffe0b2);
                @endif">
                @if($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar) }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <i class="fas fa-user" style="
                        @if($user->role === 'teacher') color: #1976d2;
                        @elseif($user->role === 'student') color: #27AE60;
                        @else color: #f57c00;
                        @endif font-size: 32px;"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <h3 class="mb-2" style="color: #333; font-size: 24px; font-weight: 700; margin: 0;">{{ $user->name }}</h3>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="badge" style="
                        @if($user->role === 'teacher') background: #e3f2fd; color: #1976d2;
                        @elseif($user->role === 'student') background: #e8f5e9; color: #27AE60;
                        @else background: #fff3e0; color: #f57c00;
                        @endif font-size: 13px; padding: 6px 12px; border-radius: 20px; text-transform: capitalize; font-weight: 600;">
                        {{ $user->role }}
                    </span>
                    <span style="color: #828282; font-size: 14px;">
                        <i class="fas fa-envelope me-2" style="color: #ccc;"></i>{{ $user->email }}
                    </span>
                    <span style="color: #828282; font-size: 14px;">
                        <i class="fas fa-calendar me-2" style="color: #ccc;"></i>Joined {{ $user->created_at?->format('M d, Y') }}
                    </span>
                </div>
            </div>
            <div class="text-end">
                <div style="color: #828282; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Total Activities</div>
                <div style="color: #333; font-size: 32px; font-weight: 700;">{{ $activities->total() }}</div>
            </div>
        </div>
    </div>

    <!-- Activities List -->
    <div class="card" style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0" style="color: #333; font-size: 20px; font-weight: 700;">
                <i class="fas fa-history me-2" style="color: #1976d2;"></i>All Activities
            </h3>
            <div class="d-flex align-items-center gap-3">
                <span class="badge" style="background: #f0f4f8; color: #64748b; font-size: 13px; padding: 6px 12px; border-radius: 20px;">
                    <i class="fas fa-list me-1"></i>{{ $activities->total() }} Total
                </span>
                @if($activities->hasPages())
                    <span class="badge" style="background: #e3f2fd; color: #1976d2; font-size: 13px; padding: 6px 12px; border-radius: 20px;">
                        <i class="fas fa-file-alt me-1"></i>Page {{ $activities->currentPage() }} of {{ $activities->lastPage() }}
                    </span>
                @endif
            </div>
        </div>

        @if($activities->count() > 0)
            <div class="table-responsive">
                <table class="table" style="font-size: 14px; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th style="border: none; padding: 16px 12px; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9;">
                                <i class="fas fa-bolt me-1"></i>Action
                            </th>
                            <th style="border: none; padding: 16px 12px; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9;">
                                <i class="fas fa-info-circle me-1"></i>Description
                            </th>
                            <th style="border: none; padding: 16px 12px; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9;">
                                <i class="fas fa-clock me-1"></i>Time
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr style="border-bottom: 1px solid #f8fafc; transition: background-color 0.2s;" 
                                onmouseover="this.style.backgroundColor='#f8fafc'" 
                                onmouseout="this.style.backgroundColor='transparent'">
                                <td style="padding: 20px 12px; vertical-align: middle;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8fafc;">
                                            <i class="{{ $activity->action_icon }}" style="font-size: 14px;"></i>
                                        </div>
                                        <span class="badge" style="
                                            @if($activity->action === 'created') background: #dcfce7; color: #166534;
                                            @elseif($activity->action === 'updated') background: #dbeafe; color: #1d4ed8;
                                            @elseif($activity->action === 'deleted') background: #fee2e2; color: #dc2626;
                                            @elseif($activity->action === 'login') background: #e0e7ff; color: #4f46e5;
                                            @elseif($activity->action === 'google_login') background: #fee2e2; color: #dc2626;
                                            @elseif($activity->action === 'logout') background: #f3f4f6; color: #6b7280;
                                            @elseif($activity->action === 'viewed') background: #fef3c7; color: #d97706;
                                            @elseif($activity->action === 'approved') background: #dcfce7; color: #166534;
                                            @elseif($activity->action === 'rejected') background: #fee2e2; color: #dc2626;
                                            @else background: #f1f5f9; color: #475569;
                                            @endif font-size: 11px; padding: 4px 8px; border-radius: 12px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">
                                            {{ $activity->action }}
                                        </span>
                                    </div>
                                </td>
                                <td style="padding: 20px 12px; vertical-align: middle;">
                                    <div style="color: #334155; font-weight: 500; line-height: 1.5;">
                                        {{ $activity->formatted_description }}
                                    </div>
                                </td>
                                <td style="padding: 20px 12px; vertical-align: middle;">
                                    <div style="color: #64748b; font-size: 13px;">
                                        <div style="margin-bottom: 2px;">{{ $activity->created_at?->format('M d, Y') }}</div>
                                        <div style="font-weight: 500;">{{ $activity->created_at?->format('H:i:s') }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($activities->hasPages())
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} activities
                    </div>
                    <div>
                        {{ $activities->links() }}
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fas fa-history" style="color: #cbd5e1; font-size: 32px;"></i>
                </div>
                <h4 style="color: #64748b; font-weight: 600; margin-bottom: 8px;">No Activities Found</h4>
                <p style="color: #94a3b8; font-size: 14px; margin: 0;">This user hasn't performed any activities yet.</p>
            </div>
        @endif
    </div>
</div>

<style>
/* Responsive pagination */
@media (max-width: 768px) {
    .activities-pagination-wrap {
        padding-left: 8px;
        padding-right: 8px;
    }
}
</style>
@endsection
