<?php

namespace App\Providers;

use App\Listeners\MigrateGuestChatHistory;
use App\Models\Course;
use App\Models\Setting;
use App\Policies\CoursePolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        // $this->registerPolicies();

        // Riwayat chat tamu (Mersy AI Assistant) dipindahkan ke akun begitu
        // pengguna login atau selesai registrasi. Dipasang lewat event supaya
        // controller autentikasi yang sudah ada tidak perlu diubah.
        Event::listen(Login::class, MigrateGuestChatHistory::class);
        Event::listen(Registered::class, MigrateGuestChatHistory::class);

        // Alternative: Direct policy registration
        // Gate::policy(Course::class, CoursePolicy::class);

        // Share site logo and favicon to all views
        View::composer('*', function ($view) {
            // Logo
            $logoPath = Setting::get('site_logo', 'images/logo.png');
            
            // Check if logo is in storage
            if (Storage::disk('public')->exists($logoPath)) {
                $logoUrl = Storage::url($logoPath);
            } elseif (file_exists(public_path($logoPath))) {
                // Fallback to public path
                $logoUrl = asset($logoPath);
            } else {
                // Default logo
                $logoUrl = asset('images/favicon.png');
            }
            
            // Favicon
            $faviconPath = Setting::get('site_favicon', 'images/favicon.png');
            
            // Check if favicon is in storage
            if (Storage::disk('public')->exists($faviconPath)) {
                $faviconUrl = Storage::url($faviconPath);
            } elseif (file_exists(public_path($faviconPath))) {
                // Fallback to public path
                $faviconUrl = asset($faviconPath);
            } else {
                // Default favicon
                $faviconUrl = asset('images/favicon.png');
            }
            
            $view->with('siteLogoUrl', $logoUrl);
            $view->with('siteFaviconUrl', $faviconUrl);
        });
    }
}
