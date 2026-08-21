@extends('layouts.app')

@section('title', 'Notifications - Teacher')

@section('content')
<section class="profile-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                @include('teacher.partials.sidebar')
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-content">
                    {{-- Hero banner biru bersama (lihat
                         resources/views/partials/notifications-hero.blade.php),
                         dipakai juga oleh halaman Notifikasi Siswa dan Admin. --}}
                    @include('partials.notifications-hero', [
                        'subtitle' => 'Stay updated with your latest activities',
                    ])
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <!-- Notifications List -->
                    <div class="notifications-list">
                        @if($notifications && $notifications->count() > 0)
                            @foreach($notifications as $notification)
                                <div class="notification-card card mb-3 {{ !$notification->is_read ? 'border-warning' : '' }}" style="{{ !$notification->is_read ? 'background-color: #fffbf0;' : '' }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div style="flex: 1;">
                                                <h6 class="card-title mb-2">
                                                    @if($notification->type === 'course_suspended')
                                                        <i class="fas fa-ban text-warning me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'course_activated')
                                                        <i class="fas fa-check-circle text-success me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'course_deleted')
                                                        <i class="fas fa-trash text-danger me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'chapter_suspended')
                                                        <i class="fas fa-ban text-warning me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'chapter_activated')
                                                        <i class="fas fa-check-circle text-success me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'chapter_deleted')
                                                        <i class="fas fa-trash text-danger me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'student_enrolled')
                                                        <i class="fas fa-user-plus text-success me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'module_approved')
                                                        <i class="fas fa-check-circle text-success me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'course_rated')
                                                        <i class="fas fa-star text-warning me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'course_completed')
                                                        <i class="fas fa-trophy text-primary me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'withdrawal_approved')
                                                        <i class="fas fa-check-circle text-success me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'withdrawal_processed')
                                                        <i class="fas fa-money-check-alt text-success me-2"></i>{{ $notification->title }}
                                                    @elseif($notification->type === 'withdrawal_rejected')
                                                        <i class="fas fa-times-circle text-danger me-2"></i>{{ $notification->title }}
                                                    @else
                                                        <i class="fas fa-bell text-info me-2"></i>{{ $notification->title ?? 'Notification' }}
                                                    @endif
                                                    @if(!$notification->is_read)
                                                        <span class="badge bg-warning ms-2">New</span>
                                                    @endif
                                                </h6>
                                                <p class="card-text text-muted small mb-2">{{ $notification->message ?? 'N/A' }}</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $notification->created_at ? $notification->created_at->diffForHumans() : 'Recently' }}
                                                </small>
                                            </div>
                                            <div class="ms-3">
                                                @if(!$notification->is_read)
                                                    <form action="{{ route('teacher.notifications.mark-read', $notification->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as Read">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info text-center" role="alert">
                                <i class="fas fa-bell-slash" style="font-size: 2rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <strong>No notifications</strong>
                                <p class="text-muted mt-2">You're all caught up! Check back later for updates.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
