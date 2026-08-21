<footer class="footer-section">
    <div class="container py-5">
        <div class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-3 col-md-6">
                <div class="footer-brand mb-3">
                    <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="REKA Logo" height="60" onerror="this.src='{{ asset('images/logo.png') }}'">
                </div>
                <p class="text-white mb-3">PT. Reka Mersif Abadi</p>
                <address class="text-white mb-3">
                    Nuryawan, Kepanjen, Delanggu,<br>
                    Klaten Regency, Central Java 57471
                </address>
                <p class="text-white mb-2">
                    <strong>Phone:</strong> <a href="tel:+6282226841782" class="text-white text-decoration-none">+62 822-2684-1782</a>
                </p>
                <p class="text-white">
                    <strong>Email:</strong> <a href="mailto:support@ptreka.com" class="text-white text-decoration-none">support@ptreka.com</a>
                </p>
                
                <!-- Social Media -->
                <div class="social-links mt-3">
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                </div>
            </div>

            <!-- MAPS -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-3">{{ __('Our Location') }}</h5>
                <div class="footer-map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3955.371572540131!2d110.76814567431835!3d-7.534390774365012!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a1476ffffffff%3A0x4fefce7d4aab5646!2sEnuma%20Technology!5e0!3m2!1sid!2sid!4v1768798990373!5m2!1sid!2sid"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
            
            <!-- Mersif Lab -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-white mb-3">MersifLab</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/about') }}">{{ __('About Us') }}</a></li>
                    <li><a href="{{ url('/#trust-section') }}">{{ __('Partnership') }}</a></li>
                    <li><a href="{{ url('/subscription') }}">{{ __('Subscription') }}</a></li>
                </ul>
            </div>
            
            <!-- Products -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-white mb-3">{{ __('Products') }}</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/courses') }}">{{ __('Courses') }}</a></li>
                    <li><a href="{{ url('/courses#popular-instructors') }}">{{ __('Instructors') }}</a></li>
                </ul>
            </div>
            
            <!-- Others -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-white mb-3">{{ __('Others') }}</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/#faq-section') }}">FAQ</a></li>
                    <li><a href="{{ route('terms') }}">{{ __('Terms and Conditions') }}</a></li>
                    <li><a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></li>
                </ul>
            </div>
        </div>

        <!-- Payment -->
        <div class="payment-wrapper">
            <h6 class="text-white text-center">
                Payment Methods
            </h6>
            <div class="payment-methods">
                <img src="{{ asset('images/payment/bri.png') }}" class="payment-icon">
                <img src="{{ asset('images/payment/bankjateng.png') }}" class="payment-icon">
                <img src="{{ asset('images/payment/spay.png') }}" class="payment-icon">
                <img src="{{ asset('images/payment/dana.png') }}" class="payment-icon">
                <img src="{{ asset('images/payment/qris.png') }}" class="payment-icon">
            </div>
        </div>
    </div>
    
    <!-- Copyright -->
    <div class="footer-bottom">
        <div class="container">
            <p class="text-center text-black fw-bold mb-0 py-3">
                &copy; Copyright {{ date('Y') }} | All Rights Reserved by PT Reka Mersif Abadi
            </p>
        </div>
    </div>
</footer>