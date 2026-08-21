@extends('layouts.admin')

@section('title', 'Admin Details - ' . $admin->name)

@section('content')
<div class="page-title">
    <h1>Admin Details</h1>
    <div class="page-actions">
        <a href="{{ back_url(route('admin.admins.index')) }}" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 8px 16px; font-size: 13px; border-radius: 6px; color: white; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Admins
        </a>
        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-primary" style="background: #2F80ED; border: none; padding: 8px 16px; font-size: 13px; border-radius: 6px; color: white; text-decoration: none;">
            <i class="fas fa-edit"></i> Edit Admin
        </a>
        @if(auth()->user()->id !== $admin->id && !$admin->isSuperAdmin())
            <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="background: #dc3545; border: none; padding: 8px 16px; font-size: 13px; border-radius: 6px; color: white; cursor: pointer;" onclick="return confirm('Are you sure you want to remove this admin? This action cannot be undone and will delete all associated data.')">
                    <i class="fas fa-trash"></i> Remove Admin
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row">
    <!-- Profile Panel -->
    <div class="col-md-4">
        <div class="card-content" style="margin-bottom: 20px;">
            <div class="card-content-title">
                Profile Information
                <div class="profile-status">
                    @if($admin->isActive())
                        <span class="badge" style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 11px;">
                            <i class="fas fa-circle" style="font-size: 8px;"></i> Active
                        </span>
                    @else
                        <span class="badge" style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 12px; font-size: 11px;">
                            <i class="fas fa-circle" style="font-size: 8px;"></i> Inactive
                        </span>
                    @endif
                </div>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    @if($admin->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($admin->avatar))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($admin->avatar) }}" alt="{{ $admin->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="fas fa-user-shield" style="color: white; font-size: 32px;"></i>
                    @endif
                </div>
                <h4 style="margin: 0; color: #333; font-weight: 600;">{{ $admin->name }}</h4>
                <p style="margin: 5px 0 0; color: #828282; font-size: 14px;">{{ $admin->getAdminRoleLabel() }}</p>
            </div>

            <div class="profile-info">
                <div class="info-item">
                    <label style="color: #828282; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Email Address</label>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">{{ $admin->email }}</p>
                </div>

                <div class="info-item">
                    <label style="color: #828282; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">User ID</label>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">#{{ $admin->id }}</p>
                </div>

                <div class="info-item">
                    <label style="color: #828282; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Created By</label>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">
                        {{ $admin->createdBy ? $admin->createdBy->name : 'System' }}
                    </p>
                </div>

                <div class="info-item">
                    <label style="color: #828282; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Member Since</label>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">{{ $admin->created_at->format('F j, Y') }}</p>
                </div>

                <div class="info-item">
                    <label style="color: #828282; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Last Login</label>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">
                        @if($admin->last_login_at)
                            {{ $admin->last_login_at->format('M j, Y H:i') }} 
                            <small style="color: #828282;">({{ $admin->last_login_at->diffForHumans() }})</small>
                        @else
                            <span style="color: #828282;">Never</span>
                        @endif
                    </p>
                </div>

                <div class="info-item">
                    <label style="color: #828282; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Account Status</label>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">
                        @if($admin->is_banned)
                            <span style="color: #dc3545;">Banned</span>
                        @else
                            <span style="color: #28a745;">Active</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Panel -->
    <div class="col-md-8">
        <div class="card-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div class="card-content-title" style="margin: 0;">
                    Recent Activity
                </div>
                <a href="{{ route('admin.activities.user', $admin->id) }}" 
                   class="btn btn-sm" 
                   style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 6px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;"
                   onmouseover="this.style.background='#1976d2'; this.style.color='white'; this.style.borderColor='#1976d2';" 
                   onmouseout="this.style.background='#e3f2fd'; this.style.color='#1976d2'; this.style.borderColor='#90caf9';">
                    <i class="fas fa-list"></i> View All Activities
                </a>
            </div>

            @forelse($activities as $activity)
                <div class="activity-item" style="padding: 15px 0; border-bottom: 1px solid #f8f9fa;">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="width: 40px; height: 40px; background: #e3f2fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-{{ $activity['action'] === 'admin_created' ? 'user-plus' : ($activity['action'] === 'admin_updated' ? 'user-edit' : ($activity['action'] === 'admin_deleted' ? 'user-times' : 'circle')) }}" style="color: #1976d2; font-size: 14px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="margin-bottom: 5px;">
                                <span style="font-weight: 500; color: #333;">{{ $activity['description'] }}</span>
                            </div>
                            <div style="font-size: 12px; color: #828282;">
                                <i class="fas fa-clock"></i> {{ $activity['time_ago'] }}
                                <span style="margin-left: 15px;">
                                    <i class="fas fa-calendar"></i> {{ $activity['created_at'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 40px; color: #828282;">
                    <i class="fas fa-history" style="font-size: 48px; color: #e0e0e0; margin-bottom: 15px;"></i>
                    <p style="margin: 0; font-size: 14px;">No activities recorded yet</p>
                    <small>Activities will appear here when this admin performs actions in the system</small>
                </div>
            @endforelse

            @if($activities->count() >= 50)
                <div style="text-align: center; padding: 20px; border-top: 1px solid #f8f9fa;">
                    <small style="color: #828282;">Showing last 50 activities</small>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.page-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-title h1 {
    margin: 0;
    color: #333;
    font-size: 24px;
    font-weight: 600;
}

.page-actions {
    display: flex;
    gap: 10px;
}

.card-content-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.card-content-title h3 {
    margin: 0;
    color: #333;
    font-size: 16px;
    font-weight: 600;
}

.profile-status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-item {
    margin-bottom: 20px;
}

.info-item:last-child {
    margin-bottom: 0;
}

.activity-item:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .page-title {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .page-actions {
        width: 100%;
    }
    
    .page-actions .btn {
        flex: 1;
        text-align: center;
    }
}
</style>
@endsection
