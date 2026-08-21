@extends('layouts.app')

@section('title', 'Login Page')

@section('content')
<section class="auth-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <!-- Left Side - Illustration -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="auth-illustration">
                    <img src="{{ asset('images/illustration.png') }}" alt="Login Illustration" class="img-fluid">
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-lg-6">
                <div class="auth-card">
                    <h2 class="auth-title text-center mb-4">Log in to continue exploring your courses</h2>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error') && !auth()->check())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="youremail@gmail.com" value="{{ old('email') }}" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">Continue</button>
                        
                        <div class="divider mb-3">
                            <span>Or log in with</span>
                        </div>
                        
                        <button type="button" id="google-btn" data-href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 google-btn mb-3" aria-busy="false">
                            <img src="https://www.google.com/favicon.ico" alt="Google" width="20" class="me-2">
                            Log in with Google
                        </button>
                        
                        <p class="text-center mb-0">
                            Don't Have an Account? <a href="{{ route('register') }}" class="text-primary fw-bold">Sign Up</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword')?.addEventListener('click', function() {
        const password = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Loading state + cegah submit ganda pada form login.
    document.getElementById('loginForm')?.addEventListener('submit', function () {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.setAttribute('aria-busy', 'true');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing in…';
        }
    });

    // Show SweetAlert for general login errors (email/password wrong)
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            const errors = {!! json_encode($errors->all()) !!};
            if (errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    html: errors.join('<br>'),
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    @endif

    // Show SweetAlert for Google Auth errors (only if user is NOT logged in)
    @if(session('error') && !auth()->check())
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                html: '{{ session('error') }}',
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Close',
                allowOutsideClick: false
            });
        });
    @endif

    // Disable Google OAuth buttons after click to avoid duplicate/race navigations ⚠️
    document.querySelectorAll('.google-btn').forEach(btn => {
        btn.addEventListener('click', function (ev) {
            const href = this.dataset.href || this.getAttribute('data-href');
            if (!href) return;
            this.setAttribute('disabled', 'disabled');
            this.setAttribute('aria-busy', 'true');
            this.classList.add('disabled');
            this.dataset._orig = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing in…';
            // Navigate in same window (preserves session cookie reliably)
            location.assign(href);
        }, { once: true });
    });
</script>
@endsection