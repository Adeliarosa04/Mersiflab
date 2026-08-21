@php
    $currentRoute = Route::currentRouteName();
    use Illuminate\Support\Facades\Storage;
@endphp

{{-- Kelas teacher-sidebar dipakai agar aturan gulir di bawah hanya berlaku
     untuk panel guru. Halaman profil siswa memakai .profile-sidebar yang sama
     lewat profile/partials/sidebar.blade.php dan tidak boleh ikut berubah. --}}
<div class="profile-sidebar teacher-sidebar">
    <!-- Profile Avatar -->
    <div class="profile-avatar-section text-center">
        <div class="profile-avatar-wrapper position-relative mx-auto">
            <div class="profile-avatar mx-auto" id="profileAvatar">
                @if(Auth::user()->avatar)
                    <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="avatar-image">
                @else
                    <span class="avatar-letter">{{ strtoupper(substr(Auth::user()->name ?? Auth::user()->email ?? 'T', 0, 1)) }}</span>
                @endif
            </div>
            <label for="avatarUpload" class="avatar-upload-btn" title="Upload Foto Profil">
                <i class="fas fa-camera"></i>
                <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display: none;">
            </label>
        </div>
        <h5 class="profile-name mt-3">{{ Auth::user()->name ?? 'Teacher' }}</h5>
        <p class="profile-email mt-2">{{ Auth::user()->email ?? 'teacher@gmail.com' }}</p>
        <span class="profile-role-badge badge-teacher">
            <i class="fas fa-chalkboard-teacher me-1"></i>Teacher
        </span>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="profile-nav mt-4">
        <a href="{{ route('teacher.profile') }}" class="profile-nav-item {{ $currentRoute === 'teacher.profile' ? 'active' : '' }}">
            <i class="fas fa-user me-2"></i> My Profile
        </a>
        <a href="{{ route('teacher.courses') }}" class="profile-nav-item {{ $currentRoute === 'teacher.courses' ? 'active' : '' }}">
            <i class="fas fa-book me-2"></i> My Courses
        </a>
        <a href="{{ route('teacher.manage.content') }}" class="profile-nav-item {{ $currentRoute === 'teacher.manage.content' ? 'active' : '' }}">
            <i class="fas fa-folder-open me-2"></i> Manage Content
        </a>
        <a href="{{ route('teacher.statistics') }}" class="profile-nav-item {{ $currentRoute === 'teacher.statistics' ? 'active' : '' }}">
            <i class="fas fa-chart-bar me-2"></i> Statistics
        </a>
        <a href="{{ route('teacher.finance.management') }}" class="profile-nav-item {{ $currentRoute === 'teacher.finance.management' ? 'active' : '' }}">
            <i class="fas fa-wallet me-2"></i> Finance Management
        </a>
        <a href="{{ route('teacher.notification-preferences') }}" class="profile-nav-item {{ $currentRoute === 'teacher.notification-preferences' ? 'active' : '' }}">
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

<style>
/* ==========================================================
   PANEL SAMPING GURU - GULIR MANDIRI

   Masalah sebelumnya: .profile-sidebar sudah position: sticky (top: 100px)
   tetapi tanpa batas tinggi. Isinya - avatar 100px, nama, email, badge,
   enam menu, dan tombol logout - kira-kira 700px. Di laptop 1366x768 area
   pandang yang tersisa hanya ~640px, sehingga sidebar lebih tinggi daripada
   ruang tempatnya menempel. Elemen sticky yang lebih tinggi dari scrollport
   tidak punya tempat untuk dipaku: ia ikut tergulir sampai ujung bawahnya
   sejajar, persis seperti yang terlihat.

   Perbaikannya bukan mengubah struktur halaman, melainkan membatasi tinggi
   sidebar setinggi layar dan memberinya scrollbar sendiri.

   Catatan struktur: layout ini memakai Bootstrap 5, bukan Tailwind, dan
   <main> diikuti <footer> di layouts/app.blade.php. Membungkus halaman dalam
   kotak setinggi 100vh dengan overflow-hidden - seperti pola Tailwind
   h-[calc(100vh-70px)] - akan membuat footer tidak pernah bisa dijangkau,
   jadi area konten sengaja tetap memakai gulir jendela.
   ========================================================== */
@media (min-width: 992px) {
    .teacher-sidebar {
        /* Header .header-section memakai position: sticky; top: 0, jadi
           offset ini menahan sidebar tepat di bawahnya tanpa tertutup. */
        position: sticky;
        top: 100px;

        /* 100px offset atas + 20px napas bawah. */
        max-height: calc(100vh - 120px);
        overflow-y: auto;

        /* Gulir sidebar berhenti di ujungnya sendiri, tidak merembet
           menggulirkan halaman di belakangnya. */
        overscroll-behavior: contain;

        /* Scrollbar tipis, senada dengan panel Admin (Firefox). */
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    /* Scrollbar tipis untuk Chrome/Edge/Safari. */
    .teacher-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .teacher-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .teacher-sidebar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .teacher-sidebar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
}

/* Di layar sempit sidebar menumpuk di atas konten. Menempel dan membatasi
   tingginya di sana justru merusak: panel akan menutupi konten dan isinya
   terpotong. Jadi perilakunya dikembalikan ke aliran normal. */
@media (max-width: 991.98px) {
    .teacher-sidebar {
        position: static;
        max-height: none;
        overflow: visible;
        margin-bottom: 24px;
    }
}

/* Teacher Sidebar Profile Avatar Section */
.profile-avatar-section {
    text-align: center;
}

.profile-avatar-wrapper {
    width: 100px;
    height: 100px;
    margin: 0 auto 12px !important;
    position: relative;
}

.profile-avatar {
    width: 100px !important;
    height: 100px !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.profile-avatar .avatar-image {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.profile-avatar .avatar-letter {
    font-size: 2.5rem;
    font-weight: 700;
    color: #fff;
}

.avatar-upload-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 36px;
    height: 36px;
    background: #0d6efd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    border: 3px solid white;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.avatar-upload-btn:hover {
    background: #0b5ed7;
    transform: scale(1.1);
}

.profile-role-badge.badge-teacher {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);
    color: white;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
</style>