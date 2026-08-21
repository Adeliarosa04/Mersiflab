@extends('layouts.admin')

@section('title', 'Create New Admin')

@section('content')
<div class="page-title">
    <h1>Create New Admin</h1>
    <div class="page-actions">
        <a href="{{ back_url(route('admin.admins.index')) }}" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 8px 16px; font-size: 13px; border-radius: 6px; color: white; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Admins
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card-content">
            <div class="card-content-title">
                Admin Information
                <div style="font-size: 12px; color: #828282;">
                    All fields marked with * are required
                </div>
            </div>

            <form action="{{ route('admin.admins.store') }}" method="POST">
                @csrf

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
                                value="{{ old('name') }}"
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
                                value="{{ old('email') }}"
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">
                                Password <span style="color: #dc3545;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-control" 
                                    placeholder="Enter secure password"
                                    required
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
                                Confirm Password <span style="color: #dc3545;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="password" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    class="form-control" 
                                    placeholder="Confirm password"
                                    required
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role" style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">
                                Admin Role <span style="color: #dc3545;">*</span>
                            </label>
                            <select 
                                id="role" 
                                name="role" 
                                class="form-control"
                                required
                                style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s;"
                            >
                                <option value="">Select Role</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px;">
                                Account Status
                            </label>
                            <div style="padding: 10px 0;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" checked disabled style="margin: 0;">
                                    <span style="color: #28a745; font-size: 14px;">Active</span>
                                </label>
                                <small style="color: #828282; font-size: 12px;">New admin accounts are created as active by default</small>
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
                            <i class="fas fa-save"></i> Create Admin
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-content" style="margin-bottom: 20px;">
            <div class="card-content-title">
                <i class="fas fa-info-circle" style="color: #2F80ED; margin-right: 8px;"></i>
                Admin Information
            </div>
            
            <div style="color: #828282; font-size: 14px; line-height: 1.6;">
                <h4 style="color: #333; font-size: 16px; margin-bottom: 15px;">What happens next?</h4>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333;">1. Account Creation</strong>
                    <p style="margin: 5px 0;">The new admin account will be created immediately with the credentials you provide.</p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333;">2. Access Permissions</strong>
                    <p style="margin: 5px 0;">New admins will have access to all admin management features by default.</p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333;">3. Activity Tracking</strong>
                    <p style="margin: 5px 0;">All admin actions will be logged for security and audit purposes.</p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333;">4. First Login</strong>
                    <p style="margin: 5px 0;">The admin can log in immediately using the email and password you set.</p>
                </div>
            </div>
        </div>

        <div class="card-content">
            <div class="card-content-title">
                <i class="fas fa-shield-alt" style="color: #28a745; margin-right: 8px;"></i>
                Security Guidelines
            </div>
            
            <div style="color: #828282; font-size: 14px; line-height: 1.6;">
                <ul style="margin: 0; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Use strong, unique passwords</li>
                    <li style="margin-bottom: 8px;">Ensure email addresses are valid</li>
                    <li style="margin-bottom: 8px;">Only create admin accounts for trusted personnel</li>
                    <li style="margin-bottom: 8px;">Regularly review admin access</li>
                    <li style="margin-bottom: 8px;">Monitor admin activity logs</li>
                </ul>
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
    }
    
    .page-actions .btn {
        flex: 1;
        text-align: center;
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
</script>
@endsection
