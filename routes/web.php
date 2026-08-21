<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FreeClassController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\Teacher\ClassController;
use App\Http\Controllers\Teacher\ChapterController;
use App\Http\Controllers\Teacher\ModuleController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherProfileController;
use App\Http\Controllers\TeacherApplicationController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Api\ModuleController as ApiModuleController;
use App\Http\Controllers\CertificateController;

// ============================
// PUBLIC ROUTES (No Auth)
// ============================

// Apply maintenance mode middleware to public routes
Route::middleware(['maintenance'])->group(function () {
    // Debug route
    Route::get('/debug/courses', [DebugController::class, 'test']);
    Route::get('/debug/verify-email', [DebugController::class, 'showVerifyEmailDebug'])->name('debug.verify');
    Route::post('/debug/verify-email', [DebugController::class, 'verifyEmailDebug'])->name('debug.verify');

    // Public & Home route
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // ============================
    // AI Assistant (Public)
    // ============================
    Route::get('/ai-assistant/check-limit', [AiAssistantController::class, 'checkLimit']);
    Route::get('/ai-assistant/history', [AiAssistantController::class, 'getHistory']);
    Route::post('/ai-assistant/chat', [AiAssistantController::class, 'chat']);

    // ============================
    // Pemilih Bahasa (Language Switcher)
    // ============================
    // Menyimpan pilihan bahasa ke session lalu kembali ke halaman asal.
    // Dibatasi ke bahasa yang didukung lewat where(), jadi URL sembarangan
    // menghasilkan 404 dan tidak pernah masuk ke controller.
    Route::get('/language/{locale}', [\App\Http\Controllers\LanguageController::class, 'switch'])
        ->whereIn('locale', \App\Http\Middleware\SetLocale::SUPPORTED)
        ->name('language.switch');

    // Site-wide search (courses, pages, categories, instructors)
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

    // Guest & Auth Routes - Public Course Routes
    Route::get('/courses', [CourseController::class, 'index'])->name('courses');
    Route::get('/course/{id}', [CourseController::class, 'detail'])->name('course.detail');

    // Free Course detail (video player, deskripsi, modul PDF, slide PPT)
    Route::get('/free-classes/{freeClass}', [FreeClassController::class, 'show'])->name('free-classes.show');

    // Progres belajar per level (butuh login; controller yang memeriksanya
    // agar tamu tetap bisa membuka halaman detail tanpa dialihkan).
    Route::post('/free-classes/{freeClass}/levels/{level}/complete', [FreeClassController::class, 'completeLevel'])
        ->name('free-classes.levels.complete');
    Route::delete('/free-classes/{freeClass}/levels/{level}/complete', [FreeClassController::class, 'uncompleteLevel'])
        ->name('free-classes.levels.uncomplete');

    // Module API Public Routes
    Route::get('/chapters/{chapterId}/modules', [ApiModuleController::class, 'index']);
    Route::get('/modules/{id}', [ApiModuleController::class, 'show']);

    // Subscription page (public view)
    Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription.page');
});

// Enrollment Routes (Protected)
Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::post('/course/{classId}/enroll', [\App\Http\Controllers\EnrollmentController::class, 'enroll'])->name('course.enroll');
    Route::post('/course/{classId}/module/{moduleId}/complete', [\App\Http\Controllers\EnrollmentController::class, 'markComplete'])->name('module.complete');
    Route::post('/course/{classId}/complete-all', [\App\Http\Controllers\EnrollmentController::class, 'completeAllModules'])->name('course.completeAll');
    
    // Rating Routes
    Route::post('/course/{id}/rating', [CourseController::class, 'submitRating'])->name('course.rating.submit');
});

// Module Routes (Protected: view + file)
Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::get('/course/{classId}/chapter/{chapterId}/module/{moduleId}', [\App\Http\Controllers\ModuleViewController::class, 'show'])->name('module.show');
    Route::get('/course/{classId}/chapter/{chapterId}/module/{moduleId}/file', [\App\Http\Controllers\ModuleViewController::class, 'serveFile'])->name('module.file');
    Route::get('/modules/{id}/download', [ApiModuleController::class, 'download']);
});

// ============================
// AUTH ROUTES (Login/Register)
// ============================

// Auth Routes - Apply maintenance mode
Route::middleware(['maintenance'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Registration routes with registration.enabled middleware
    Route::middleware('registration.enabled')->group(function () {
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    });
    
    // Email Verification Routes
    Route::get('/email-verification-pending', [AuthController::class, 'showVerify'])->name('email.verification.pending');
    Route::get('/verify-email', [AuthController::class, 'verifyEmail'])->name('email.verify');
    Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->name('verify.resend');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Message Route (Public - untuk About page) - Apply maintenance mode
Route::middleware(['maintenance'])->group(function () {
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
});

Route::middleware(['maintenance'])->group(function () {
    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::get('/checkout', [\App\Http\Controllers\CartController::class, 'showCheckout'])->name('checkout');

    // Legal pages — URL disamakan dengan link yang sudah ada di footer
    // dan halaman Sign Up (/syarat-ketentuan, /privacy-policy).
    Route::get('/syarat-ketentuan', [LegalController::class, 'terms'])->name('terms');
    Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('privacy');
});

// ============================
// STUDENT ROUTES
// ============================
Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'role:student', 'maintenance'])
    ->group(function () {
        // Student Dashboard
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/course/{id}', [StudentDashboardController::class, 'courseDetail'])->name('course.detail');
        Route::get('/progress', [StudentDashboardController::class, 'progress'])->name('progress');
    });

// ============================
// TEACHER ROUTES
// ============================
Route::prefix('teacher')
    ->name('teacher.')
    ->middleware(['auth', 'role:teacher', 'maintenance'])
    ->group(function () {
        // Teacher Dashboard
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::get('/course/{id}', [TeacherDashboardController::class, 'courseDetail'])->name('course.detail');
        Route::get('/analytics', [TeacherDashboardController::class, 'analytics'])->name('analytics');
        Route::get('/materi-management', [TeacherDashboardController::class, 'materiManagement'])->name('materi.management');

        // Content Management
        Route::get('/manage-content', [ClassController::class, 'manageContent'])->name('manage.content');
        
        // Classes
        Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/create', [ClassController::class, 'create'])->name('classes.create');
        Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
        Route::get('/classes/{class}/edit', [ClassController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{class}', [ClassController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');
        Route::post('/classes/{class}/request-approval', [ClassController::class, 'requestApproval'])->name('classes.request-approval');
        
        // Chapters
        Route::get('/classes/{class}/chapters', [ChapterController::class, 'index'])->name('chapters.index');
        Route::get('/classes/{class}/chapters/create', [ChapterController::class, 'create'])->name('chapters.create');
        Route::post('/classes/{class}/chapters', [ChapterController::class, 'store'])->name('chapters.store');
        Route::get('/classes/{class}/chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');
        Route::get('/classes/{class}/chapters/{chapter}/edit', [ChapterController::class, 'edit'])->name('chapters.edit');
        Route::put('/classes/{class}/chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('/classes/{class}/chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');
        Route::post('/chapters/reorder', [ChapterController::class, 'reorder'])->name('chapters.reorder');
        
        // Modules
        Route::get('/chapters/{chapter}/modules/create', [ModuleController::class, 'create'])->name('modules.create');
        Route::get('/chapters/{chapter}/modules/create/text', [ModuleController::class, 'createText'])->name('modules.create.text');
        Route::get('/chapters/{chapter}/modules/create/document', [ModuleController::class, 'createDocument'])->name('modules.create.document');
        Route::get('/chapters/{chapter}/modules/create/video', [ModuleController::class, 'createVideo'])->name('modules.create.video');
        
        Route::post('/chapters/{chapter}/modules/text', [ModuleController::class, 'storeText'])->name('modules.store.text');
        Route::post('/chapters/{chapter}/modules/document', [ModuleController::class, 'storeDocument'])->name('modules.store.document');
        Route::post('/chapters/{chapter}/modules/video', [ModuleController::class, 'storeVideo'])->name('modules.store.video');
        
        Route::get('/chapters/{chapter}/modules/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
        Route::put('/chapters/{chapter}/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
        Route::delete('/chapters/{chapter}/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('/modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');
        
        // Teacher Profile Routes
        Route::get('/profile', [TeacherProfileController::class, 'profile'])->name('profile');
        Route::put('/profile/update', [TeacherProfileController::class, 'updateProfile'])->name('profile.update');
        Route::get('/my-courses', [TeacherProfileController::class, 'myCourses'])->name('courses');
        Route::get('/statistics', [TeacherProfileController::class, 'statistics'])->name('statistics');
        Route::get('/notifications', [TeacherProfileController::class, 'notifications'])->name('notifications');
        Route::patch('/notifications/{id}/mark-read', [TeacherProfileController::class, 'markNotificationAsRead'])->name('notifications.mark-read');
        Route::get('/notification-preferences', [TeacherProfileController::class, 'notificationPreferences'])->name('notification-preferences');
        Route::put('/notification-preferences/update', [TeacherProfileController::class, 'updateNotificationPreferences'])->name('notification-preferences.update');
        Route::get('/finance-management', [TeacherProfileController::class, 'financeManagement'])->name('finance.management');
        Route::post('/withdrawal/request', [TeacherProfileController::class, 'requestWithdrawal'])->name('withdrawal.request');
        
    });


// ============================
// SHARED AUTHENTICATED ROUTES
// ============================

// Protected Routes (Authenticated users)
Route::middleware(['auth'])->group(function () {
    // Backward compatibility (redirect to role-specific dashboard)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        } elseif ($user->isAdmin()) {
            // Sebelumnya admin ikut dilempar ke home sehingga tidak punya
            // jalan menuju panelnya sendiri.
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('home');
    })->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.upload-avatar');
    // Subscription (instant subscribe without payment)
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
    
    // Subscription Payment Routes
    Route::get('/subscription/payment/{plan}', [SubscriptionController::class, 'showPayment'])->name('subscription.payment');
    Route::post('/subscription/process-payment', [SubscriptionController::class, 'processPayment'])->name('subscription.process.payment');
    Route::post('/subscription/cancel-pending', [SubscriptionController::class, 'cancelPendingPurchase'])->name('subscription.cancelPending');

    // Pembatalan LANGGANAN AKTIF (berbeda dari cancel-pending di atas, yang
    // hanya membatalkan pembelian yang belum dibayar). Berlaku aturan minimal
    // 1 bulan masa aktif sebelum langganan boleh dibatalkan.
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel');

    // My Courses
    Route::get('/my-courses', [ProfileController::class, 'myCourses'])->name('my-courses');

    // Testimoni siswa: form pengisian + daftar testimoni miliknya sendiri.
    // Testimoni tersimpan dengan status 'pending' dan baru tampil di halaman
    // publik setelah disetujui admin.
    Route::get('/my-testimonials', [\App\Http\Controllers\TestimonialController::class, 'index'])->name('my-testimonials');
    Route::post('/my-testimonials', [\App\Http\Controllers\TestimonialController::class, 'store'])->name('my-testimonials.store');
    Route::delete('/my-testimonials/{testimonial}', [\App\Http\Controllers\TestimonialController::class, 'destroy'])->name('my-testimonials.destroy');
    
    // Purchase History
    Route::get('/purchase-history', [ProfileController::class, 'purchaseHistory'])->name('purchase-history');
    Route::get('/invoice/{id}', [ProfileController::class, 'invoice'])->name('invoice');
    Route::get('/invoice/{id}/download', [ProfileController::class, 'downloadInvoice'])->name('invoice.download');
    
    // Notification Preferences
    Route::get('/notification-preferences', [ProfileController::class, 'notificationPreferences'])->name('notification-preferences');
    Route::put('/notification-preferences/update', [ProfileController::class, 'updateNotificationPreferences'])->name('notification-preferences.update');
    
    // My Certificates
    Route::get('/my-certificates', [CertificateController::class, 'index'])->name('my-certificates');
    Route::get('/certificate/{id}/preview', [CertificateController::class, 'preview'])->name('certificate.preview');
    Route::get('/certificate/{id}/download', [CertificateController::class, 'download'])->name('certificate.download');
    
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    
    // Invoice Routes
    Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoiceNumber}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoiceNumber}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('/invoices/course/create', [\App\Http\Controllers\InvoiceController::class, 'createCourseInvoice'])->name('invoices.course.create');
    Route::post('/invoices/subscription/create', [\App\Http\Controllers\InvoiceController::class, 'createSubscriptionInvoice'])->name('invoices.subscription.create');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->name('cart.buyNow');
    Route::post('/cart/prepare-checkout', [CartController::class, 'prepareCheckout'])->name('cart.prepareCheckout');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/cart/process-payment', [CartController::class, 'processPayment'])->name('cart.processPayment');
    Route::post('/cart/cancel-pending', [CartController::class, 'cancelPendingPurchase'])->name('cart.cancelPending');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // Teacher Application Routes
    Route::get('/teacher-application', [TeacherApplicationController::class, 'create'])->name('teacher.application.create');
    Route::post('/teacher-application', [TeacherApplicationController::class, 'store'])->name('teacher.application.store');
    Route::get('/teacher-application/status', [TeacherApplicationController::class, 'show'])->name('teacher.application.status');
    Route::get('/teacher-application/preview', [TeacherApplicationController::class, 'preview'])->name('teacher.application.preview');
    Route::get('/teacher-application/edit', [TeacherApplicationController::class, 'edit'])->name('teacher.application.edit');
    Route::put('/teacher-application', [TeacherApplicationController::class, 'update'])->name('teacher.application.update');
});

// ============================
// INSTRUCTOR PROTECTED ROUTES
// ============================

// Module API Protected Routes (Instructor)
Route::middleware(['auth'])
    ->prefix('instructor')
    ->group(function () {
        // Module CRUD operations
        Route::post('/chapters/{chapterId}/modules', [ApiModuleController::class, 'store']);
        Route::put('/modules/{id}', [ApiModuleController::class, 'update']);
        Route::delete('/modules/{id}', [ApiModuleController::class, 'destroy']);
    });

// ============================
// GOOGLE OAUTH ROUTES
// ============================

// Google OAuth routes - Apply maintenance mode
Route::middleware(['maintenance'])->group(function () {
    // Google OAuth routes - callback harus didefinisikan dulu sebelum route dengan parameter
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
});

// ============================
// ADMIN ROUTES
// ============================

// Admin login routes (separate from public login) - No maintenance mode for admin login
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin Dashboard Routes (Protected by auth and admin middleware)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['admin'])
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard')->middleware('activity.logger');

        // Activities (Activity Logs)
        Route::get('/activities', [AdminActivityController::class, 'index'])->name('activities.index')->middleware('activity.logger');
        Route::get('/activities/user/{userId}', [AdminActivityController::class, 'userActivities'])->name('activities.user')->middleware('activity.logger');

        // Serve module file for admin preview
        Route::get('modules/{id}/file', [AdminController::class, 'serveModuleFile'])->name('modules.file');
        
        // Categories Management
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->middleware('activity.logger');

        // Free Class Management
        Route::resource('free-classes', \App\Http\Controllers\Admin\FreeClassController::class)
            ->parameters(['free-classes' => 'freeClass'])
            ->except(['show'])
            ->middleware('activity.logger');
        Route::post('free-classes/{freeClass}/toggle-active', [\App\Http\Controllers\Admin\FreeClassController::class, 'toggleActive'])
            ->name('free-classes.toggleActive')
            ->middleware('activity.logger');
        
        // Courses Management
        Route::resource('courses', AdminCourseController::class);
        Route::post('courses/{id}/toggle-feature', [AdminCourseController::class, 'toggleFeatured'])->name('courses.toggleFeature')->middleware('activity.logger');
        Route::get('courses/{id}/approval', [AdminCourseController::class, 'approval'])->name('courses.approval');
        Route::post('courses/{id}/approve', [AdminCourseController::class, 'approveCourse'])->name('courses.approve');
        Route::post('courses/{id}/reject', [AdminCourseController::class, 'rejectCourse'])->name('courses.reject');
        Route::get('courses/{id}/preview', [AdminCourseController::class, 'previewCourse'])->name('courses.preview');
        
        // Chapters Moderation
        Route::delete('chapters/{id}', [AdminController::class, 'destroyChapter'])->name('chapters.destroy');
        
        // Modules Moderation
        Route::post('modules/{id}/approve', [AdminCourseController::class, 'approveModule'])->name('modules.approve');
        Route::post('modules/{id}/reject', [AdminCourseController::class, 'rejectModule'])->name('modules.reject');
        Route::delete('modules/{id}', [AdminController::class, 'destroyModule'])->name('modules.destroy');
        Route::get('modules/{id}/preview', [AdminController::class, 'previewModule'])->name('modules.preview');
        
        // Materi Moderation (for Course model)
        Route::get('materi/{id}/preview', [AdminController::class, 'previewMateri'])->name('materi.preview');
        Route::patch('materi/{id}/suspend', [AdminController::class, 'suspendMateri'])->name('materi.suspend');
        
        // Teachers Management
        Route::resource('teachers', AdminTeacherController::class)->middleware(['ajax.handler', 'activity.logger']);
        Route::post('teachers/{id}/toggle-ban', [AdminTeacherController::class, 'toggleBan'])->name('teachers.toggleBan');
        Route::get('teachers/{teacherId}/class/{classId}/reviews', [AdminTeacherController::class, 'classReviews'])->name('admin.teachers.class.reviews')->middleware('activity.logger');
        
        // Students Management
        Route::resource('students', AdminStudentController::class)->middleware(['ajax.handler', 'activity.logger']);
        Route::post('students/{id}/toggle-ban', [AdminStudentController::class, 'toggleBan'])->name('students.toggleBan');
        Route::post('students/{id}/subscription', [AdminStudentController::class, 'updateSubscription'])->name('students.updateSubscription');
        Route::get('students/{id}/activities', [AdminStudentController::class, 'activities'])->name('students.activities')->middleware('activity.logger');
        Route::post('students/{student}/unlock-course/{purchase}', [AdminStudentController::class, 'unlockCourse'])->name('students.unlock-course');
        Route::post('students/{student}/unlock-all-courses', [AdminStudentController::class, 'unlockAllCourses'])->name('students.unlock-all-courses');
        Route::post('students/{student}/approve-subscription/{subscription}', [AdminStudentController::class, 'approveSubscription'])->name('students.approve-subscription');
        Route::post('students/{student}/reject-subscription/{subscription}', [AdminStudentController::class, 'rejectSubscription'])->name('students.reject-subscription');
        
        // Admin Management
        Route::resource('admins', AdminManagementController::class)->middleware(['log.admin', 'ajax.handler', 'activity.logger']);
        Route::post('admins/{id}/toggle-status', [AdminManagementController::class, 'toggleStatus'])->name('admins.toggleStatus');
        
        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('activity.logger');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update')->middleware('activity.logger');
        Route::post('/settings/upload-logo', [SettingController::class, 'uploadLogo'])->name('settings.uploadLogo')->middleware('activity.logger');
        Route::post('/settings/upload-favicon', [SettingController::class, 'uploadFavicon'])->name('settings.uploadFavicon');
        
        // Messages Management
        Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages.index')->middleware('activity.logger');
        Route::get('/messages/{message}', [AdminMessageController::class, 'show'])->name('messages.show')->middleware('activity.logger');
        Route::delete('/messages/{message}', [AdminMessageController::class, 'destroy'])->name('messages.destroy');
        Route::post('/messages/{message}/mark-read', [AdminMessageController::class, 'markRead'])->name('messages.mark-read');
        Route::get('/messages/unread-count', [AdminMessageController::class, 'unreadCount'])->name('messages.unreadCount');
        
        // Notifications Management
        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'show'])->name('notifications.show');
        Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        
        // Financial Management
        Route::get('/finance', [AdminFinanceController::class, 'dashboard'])->name('finance.dashboard');
        Route::get('/finance/teacher/{teacherId}', [AdminFinanceController::class, 'teacherFinance'])->name('finance.teacher');
        Route::post('/finance/teacher/{teacherId}/commission', [AdminFinanceController::class, 'updateCommissionSettings'])->name('finance.teacher.commission');
        Route::get('/finance/withdrawal/{withdrawalId}', [AdminFinanceController::class, 'showWithdrawal'])->name('finance.withdrawal.show');
        Route::post('/finance/withdrawal/{withdrawalId}/process', [AdminFinanceController::class, 'processWithdrawal'])->name('finance.withdrawal.process');
        Route::post('/finance/teacher/{teacherId}/approve-earnings', [AdminFinanceController::class, 'approveEarnings'])->name('finance.teacher.approve-earnings');
        Route::post('/finance/purchase/{purchaseId}/approve', [AdminFinanceController::class, 'approvePurchase'])->name('finance.purchase.approve');
        Route::get('/finance/export', [AdminFinanceController::class, 'exportReport'])->name('finance.export');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/unlock-course/{purchaseId}', [\App\Http\Controllers\Admin\NotificationController::class, 'unlockCourse'])->name('notifications.unlock-course');
        
        // Purchases Management
        Route::get('/purchases/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'showPurchase'])->name('purchases.show');
        
        // Invoice Management
        Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'adminIndex'])->name('admin.invoices.index');
        Route::get('/invoices/{invoiceNumber}', [\App\Http\Controllers\InvoiceController::class, 'adminShow'])->name('admin.invoices.show');
        Route::post('/invoices/{invoiceNumber}/mark-paid', [\App\Http\Controllers\InvoiceController::class, 'markAsPaid'])->name('admin.invoices.mark-paid');
        Route::post('/invoices/{invoiceNumber}/cancel', [\App\Http\Controllers\InvoiceController::class, 'cancel'])->name('admin.invoices.cancel');
        
        // Testimonials Management (Admin)
        Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class)->middleware('activity.logger');
        Route::post('testimonials/{testimonial}/toggle-publish', [\App\Http\Controllers\Admin\TestimonialController::class, 'togglePublish'])->name('testimonials.togglePublish')->middleware('activity.logger');

        // Moderasi testimoni siswa: setujui / tolak / kembalikan ke pending.
        Route::post('testimonials/{testimonial}/approve', [\App\Http\Controllers\Admin\TestimonialController::class, 'approve'])->name('testimonials.approve')->middleware('activity.logger');
        Route::post('testimonials/{testimonial}/reject', [\App\Http\Controllers\Admin\TestimonialController::class, 'reject'])->name('testimonials.reject')->middleware('activity.logger');
        Route::post('testimonials/{testimonial}/unpublish', [\App\Http\Controllers\Admin\TestimonialController::class, 'unpublish'])->name('testimonials.unpublish')->middleware('activity.logger');

        // Teacher Applications Management
        Route::get('/teacher-applications', [\App\Http\Controllers\Admin\TeacherApplicationController::class, 'index'])->name('teacher-applications.index');
        Route::get('/teacher-applications/{teacherApplication}', [\App\Http\Controllers\Admin\TeacherApplicationController::class, 'show'])->name('teacher-applications.show');
        Route::post('/teacher-applications/{teacherApplication}/approve', [\App\Http\Controllers\Admin\TeacherApplicationController::class, 'approve'])->name('teacher-applications.approve');
        Route::post('/teacher-applications/{teacherApplication}/reject', [\App\Http\Controllers\Admin\TeacherApplicationController::class, 'reject'])->name('teacher-applications.reject');
        Route::delete('/teacher-applications/{teacherApplication}', [\App\Http\Controllers\Admin\TeacherApplicationController::class, 'destroy'])->name('teacher-applications.destroy');
        Route::get('/teacher-applications/{teacherApplication}/download/{fileType}', [\App\Http\Controllers\Admin\TeacherApplicationController::class, 'downloadFile'])->name('teacher-applications.download');

        // Test Route for Storage Debugging
        Route::get('/test-storage', function() {
            try {
                $files = \Storage::disk('public')->files('teacher-applications');
                return response()->json([
                    'storage_path' => storage_path('app/public'),
                    'public_path' => public_path(),
                    'storage_link_exists' => file_exists(public_path('storage')),
                    'files_in_directory' => $files,
                    'test_file_exists' => \Storage::disk('public')->exists('teacher-applications/test.txt'),
                    'url' => \Storage::disk('public')->url('teacher-applications/test.txt')
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });

        // Route Logout
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Subscriptions Monitoring
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index')->middleware('activity.logger');
    });

// API Routes
Route::get('/api/invoice/by-number/{invoiceNumber}', [ProfileController::class, 'getInvoiceByNumber'])->name('api.invoice.by-number');