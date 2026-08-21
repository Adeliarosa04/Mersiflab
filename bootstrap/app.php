<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'maintenance' => \App\Http\Middleware\CheckMaintenanceMode::class,
            'registration.enabled' => \App\Http\Middleware\CheckRegistrationEnabled::class,
            'log.admin' => \App\Http\Middleware\LogAdminActivity::class,
            'update.login' => \App\Http\Middleware\UpdateLastLogin::class,
            'ajax.handler' => \App\Http\Middleware\HandleAjaxRequests::class,
            'activity.logger' => \App\Http\Middleware\AdminActivityLogger::class,
            'log.user.activity' => \App\Http\Middleware\LogUserActivity::class,
            'check.banned' => \App\Http\Middleware\CheckBannedUser::class,
        ]);
        
        // Add middleware to web group for all authenticated requests
        //
        // SetLocale WAJIB memakai append, bukan prepend: prepend akan
        // menempatkannya sebelum StartSession, sehingga session belum ada saat
        // middleware membaca pilihan bahasa dan hasilnya selalu jatuh ke
        // default. Dengan append, ia berjalan setelah session siap dan sebelum
        // controller/view dirender.
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\CheckBannedUser::class,
            \App\Http\Middleware\LogUserActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
