@php
    $currentRoute = Route::currentRouteName();
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="profile-sidebar">
    <!-- Profile Avatar -->
    <div class="profile-avatar-section text-center">
        <div class="profile-avatar-wrapper position-relative mx-auto">
            <div class="profile-avatar mx-auto" id="profileAvatar">
                @if(Auth::user()->avatar)
                    <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="avatar-image">
                @else
                    <span class="avatar-letter">{{ strtoupper(substr(Auth::user()->name ?? Auth::user()->email ?? 'S', 0, 1)) }}</span>
                @endif
            </div>
            <label for="avatarUpload" class="avatar-upload-btn" title="Upload Foto Profil">
                <i class="fas fa-camera"></i>
                <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display: none;">
            </label>
        </div>
        <h5 class="profile-name mt-3">{{ Auth::user()->name ?? 'Student' }}</h5>
        <p class="profile-email mt-2">{{ Auth::user()->email ?? 'student@gmail.com' }}</p>
        <span class="profile-role-badge {{ Auth::user()->isTeacher() ? 'badge-teacher' : 'badge-student' }}">
            <i class="fas {{ Auth::user()->isTeacher() ? 'fa-chalkboard-teacher' : 'fa-user-graduate' }} me-1"></i>
            {{ Auth::user()->isTeacher() ? 'Teacher' : 'Student' }}
        </span>
        
        @php
            $user = Auth::user();
            $isSubscriber = $user->is_subscriber;
            $subscriptionPlan = strtolower($user->subscription_plan ?? 'none');
            $isExpired = $user->subscription_expires_at && $user->subscription_expires_at->isPast();
        @endphp
        
        @if($isSubscriber && $subscriptionPlan !== 'none' && !$isExpired)
            <div class="profile-subscription-badge mt-2" style="display: inline-block;">
                @if($subscriptionPlan === 'premium')
                    <span class="badge" style="background: linear-gradient(135deg, #6a1b9a, #8e24aa); color: white; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-crown" style="color: #FFD700;"></i>
                        Premium
                    </span>
                @elseif($subscriptionPlan === 'standard')
                    <span class="badge" style="background: linear-gradient(135deg, #2e7d32, #43a047); color: white; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-star" style="color: #C0C0C0;"></i>
                        Standard
                    </span>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Navigation Menu -->
    <nav class="profile-nav mt-4">
        <a href="{{ route('profile') }}" class="profile-nav-item {{ $currentRoute === 'profile' ? 'active' : '' }}">
            <i class="fas fa-user me-2"></i> My Profile
        </a>
        <a href="{{ route('my-courses') }}" class="profile-nav-item {{ $currentRoute === 'my-courses' ? 'active' : '' }}">
            <i class="fas fa-book me-2"></i> My Courses
        </a>
        <a href="{{ route('my-certificates') }}" class="profile-nav-item {{ $currentRoute === 'my-certificates' ? 'active' : '' }}">
            <i class="fas fa-certificate me-2"></i> My Certificates
        </a>
        <a href="{{ route('my-testimonials') }}" class="profile-nav-item {{ $currentRoute === 'my-testimonials' ? 'active' : '' }}">
            <i class="fas fa-comment-dots me-2"></i> My Testimonials
        </a>
        <a href="{{ route('purchase-history') }}" class="profile-nav-item {{ $currentRoute === 'purchase-history' ? 'active' : '' }}">
            <i class="fas fa-history me-2"></i> Purchase History
        </a>
        <a href="{{ route('notification-preferences') }}" class="profile-nav-item {{ $currentRoute === 'notification-preferences' ? 'active' : '' }}">
            <i class="fas fa-bell me-2"></i> Notification Preferences
        </a>
    </nav>
    
    <!-- Logout Button -->
    <form action="{{ route('logout') }}" method="POST" class="mt-4">
        @csrf
        <button type="submit" class="btn btn-danger w-100">
            <i class="fas fa-sign-out-alt me-2"></i> Logout Account
        </button>
    </form>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarUpload = document.getElementById('avatarUpload');
    const profileAvatar = document.getElementById('profileAvatar');
    
    if (avatarUpload && profileAvatar) {
        avatarUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file
            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Tidak Valid',
                    text: 'Format file harus JPG, PNG, GIF, atau WEBP',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
            
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal 2MB',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
            
            // Show loading
            Swal.fire({
                title: 'Mengupload...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Create FormData
            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Upload via AJAX
            fetch('{{ route("profile.upload-avatar") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    // Update avatar image
                    profileAvatar.innerHTML = `<img src="${data.avatar_url}" alt="{{ Auth::user()->name }}" class="avatar-image">`;
                    
                    // Update navbar avatar if exists
                    const navbarAvatar = document.querySelector('.navbar-avatar img');
                    if (navbarAvatar) {
                        navbarAvatar.src = data.avatar_url;
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745',
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal mengupload foto profil. Silakan coba lagi.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
                console.error('Error:', error);
            });
        });
    }
});
</script>