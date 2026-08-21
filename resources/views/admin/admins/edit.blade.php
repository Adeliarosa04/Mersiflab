@extends('layouts.admin')

@section('title', 'Edit Admin - ' . $admin->name)

@section('content')
<div class="page-title">
    <h1>Edit Admin</h1>
    <div class="page-actions">
        <a href="{{ back_url(route('admin.admins.show', $admin->id)) }}" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 8px 16px; font-size: 13px; border-radius: 6px; color: white; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Detail Admin
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card-content">
            <div class="card-content-title">
                Edit Admin Information
                <div style="font-size: 12px; color: #828282;">
                    ID: #{{ $admin->id }} • Created: {{ $admin->created_at->format('M j, Y') }}
                </div>
            </div>

            <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">Foto Profil</label>
                    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                        <div style="width: 64px; height: 64px; border-radius: 50%; overflow: hidden; background: #e3f2fd; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            @if($admin->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($admin->avatar))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($admin->avatar) }}" alt="{{ $admin->name }}" style="width: 100%; height: 100%; object-fit: cover;" id="adminAvatarPreview">
                            @else
                                <span style="color: #1976d2; font-size: 24px; font-weight: 600;" id="adminAvatarLetter">{{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}</span>
                                <img src="" alt="" style="width: 100%; height: 100%; object-fit: cover; display: none;" id="adminAvatarPreview">
                            @endif
                        </div>
                        <div>
                            <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="form-control" style="max-width: 280px; padding: 8px 12px; font-size: 13px;">
                            <small style="color: #828282; font-size: 12px; display: block; margin-top: 6px;">JPG, PNG, GIF, WebP. Maks. 2 MB. Kosongkan jika tidak ingin mengubah.</small>
                            @error('avatar')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">
                                Full Name <span style="color: #dc3545;">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-control" 
                                value="{{ old('name', $admin->name) }}"
                                placeholder="Enter admin's full name"
                                required
                                style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s;"
                            >
                            @error('name')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">
                                Email Address <span style="color: #dc3545;">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                value="{{ old('email', $admin->email) }}"
                                placeholder="admin@example.com"
                                required
                                style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s;"
                            >
                            @error('email')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #2F80ED;">
                    <h4 style="margin: 0 0 15px 0; color: #333; font-size: 16px; font-weight: 600;">
                        <i class="fas fa-key" style="color: #2F80ED; margin-right: 8px;"></i>
                        Password Reset
                    </h4>
                    <p style="margin: 0 0 15px 0; color: #828282; font-size: 14px;">
                        Leave password fields empty to keep the current password. Fill them to set a new password.
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">
                                    New Password
                                </label>
                                <div style="position: relative;">
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        class="form-control" 
                                        placeholder="Enter new password (optional)"
                                        style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s; padding-right: 40px;"
                                    >
                                    <button type="button" onclick="togglePassword('password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #828282; cursor: pointer;">
                                        <i class="fas fa-eye" id="password-toggle"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                                @enderror
                                <small style="color: #828282; font-size: 12px; margin-top: 5px; display: block;">
                                    Password must be at least 8 characters long
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation" style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">
                                    Confirm New Password
                                </label>
                                <div style="position: relative;">
                                    <input 
                                        type="password" 
                                        id="password_confirmation" 
                                        name="password_confirmation" 
                                        class="form-control" 
                                        placeholder="Confirm new password"
                                        style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s; padding-right: 40px;"
                                    >
                                    <button type="button" onclick="togglePassword('password_confirmation')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #828282; cursor: pointer;">
                                        <i class="fas fa-eye" id="password_confirmation-toggle"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-radius: 6px; border-left: 3px solid #1976d2;">
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fas fa-info-circle" style="color: #1976d2; margin-top: 2px;"></i>
                            <div style="flex: 1;">
                                <strong style="color: #1976d2; font-size: 13px;">Email Notification</strong>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 12px; line-height: 1.4;">
                                    When you reset the password, the admin will receive an email notification about the password change for security purposes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ back_url(route('admin.admins.index')) }}" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; color: white; text-decoration: none;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" style="background: #2F80ED; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; color: white; cursor: pointer;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-content" style="margin-bottom: 20px;">
            <div class="card-content-title">
                <i class="fas fa-user" style="color: #2F80ED; margin-right: 8px;"></i>
                Current Admin Details
            </div>
            
            <div style="text-align: center; margin-bottom: 16px;">
                <div style="width: 64px; height: 64px; border-radius: 50%; overflow: hidden; background: #e3f2fd; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    @if($admin->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($admin->avatar))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($admin->avatar) }}" alt="{{ $admin->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span style="color: #1976d2; font-size: 22px; font-weight: 600;">{{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}</span>
                    @endif
                </div>
            </div>
            
            <div style="color: #828282; font-size: 14px; line-height: 1.6;">
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Full Name</strong>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">{{ $admin->name }}</p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Email Address</strong>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">{{ $admin->email }}</p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Role</strong>
                    <p style="margin: 5px 0;">
                        <span class="badge" style="padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 500; @if($admin->getAdminRoleLabel() == 'Super Admin') background: #f3e5f5; color: #4a148c; @else background: #e3f2fd; color: #1565c0; @endif">
                            {{ $admin->getAdminRoleLabel() }}
                        </span>
                    </p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Account Status</strong>
                    <p style="margin: 5px 0;">
                        @if($admin->isActive())
                            <span style="color: #28a745; font-size: 14px;">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> Active
                            </span>
                        @else
                            <span style="color: #dc3545; font-size: 14px;">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> Inactive
                            </span>
                        @endif
                    </p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Last Login</strong>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">
                        @if($admin->last_login_at)
                            {{ $admin->last_login_at->format('M j, Y H:i') }}
                            <small style="color: #828282;">({{ $admin->last_login_at->diffForHumans() }})</small>
                        @else
                            <span style="color: #828282;">Never</span>
                        @endif
                    </p>
                </div>
                
                <div>
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Member Since</strong>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">{{ $admin->created_at->format('F j, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="card-content">
            <div class="card-content-title">
                <i class="fas fa-shield-alt" style="color: #ffc107; margin-right: 8px;"></i>
                Security Information
            </div>
            
            <div style="color: #828282; font-size: 14px; line-height: 1.6;">
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Password Status</strong>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">
                        <i class="fas fa-lock" style="color: #28a745;"></i> Secure (Encrypted)
                    </p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Created By</strong>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">
                        {{ $admin->createdBy ? $admin->createdBy->name : 'System' }}
                    </p>
                </div>
                
                <div>
                    <strong style="color: #333; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Account ID</strong>
                    <p style="margin: 5px 0; color: #333; font-size: 14px;">#{{ $admin->id }}</p>
                </div>
            </div>
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
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.form-group {
    margin-bottom: 20px;
}

.form-control:focus {
    outline: none;
    border-color: #2F80ED;
    box-shadow: 0 0 0 3px rgba(47, 128, 237, 0.1);
}

@media (max-width: 768px) {
    .page-title {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .page-actions {
        width: 100%;
        flex-wrap: wrap;
    }
    
    .page-actions .btn {
        flex: 1;
        text-align: center;
        min-width: 120px;
    }
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + '-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    // You can add visual feedback here if needed
});

// Confirm password validation
document.getElementById('password_confirmation').addEventListener('input', function(e) {
    const password = document.getElementById('password').value;
    const confirmation = e.target.value;
    
    if (confirmation && password !== confirmation) {
        e.target.style.borderColor = '#dc3545';
    } else {
        e.target.style.borderColor = '#ddd';
    }
});

// Avatar preview
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('adminAvatarPreview');
    const letter = document.getElementById('adminAvatarLetter');
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.src = ev.target.result;
            preview.style.display = 'block';
            if (letter) letter.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
