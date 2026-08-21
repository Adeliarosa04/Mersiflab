<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Module;
use App\Models\Chapter;
use App\Models\Notification;
use App\Models\User;
use App\Models\Purchase;
use App\Models\TeacherWithdrawal;
use App\Models\TeacherBalance;
use App\Models\CommissionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherProfileController extends Controller
{
    /**
     * Display teacher's profile page
     */
    public function profile()
    {
        $user = auth()->user();
        return view('teacher.profile', compact('user'));
    }

    /**
     * Update teacher's profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'bio' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            
            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        } else {
            // Keep existing avatar if no new file uploaded
            unset($validated['avatar']);
        }
        
        $user->update($validated);
        
        return redirect()->route('teacher.profile')->with('success', 'Profile updated successfully');
    }

    /**
     * Display teacher's courses (classes)
     */
    public function myCourses()
    {
        $user = auth()->user();
        $courses = \App\Models\ClassModel::where('teacher_id', $user->id)
            ->withCount(['chapters', 'modules'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('teacher.my-courses', compact('courses', 'user'));
    }

    /**
     * Display teacher's notifications
     */
    public function notifications()
    {
        $user = auth()->user();
        // Load notifications from database
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('teacher.notifications', compact('notifications', 'user'));
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($id)
    {
        $user = auth()->user();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Display teacher's statistics
     */
    public function statistics()
    {
        $user = auth()->user();
        
        // Get teacher's classes
        $classes = ClassModel::where('teacher_id', $user->id)
            ->withCount(['chapters', 'modules'])
            ->get();
        
        // Calculate statistics
        $totalCourses = $classes->count();
        $totalChapters = $classes->sum('chapters_count');
        $totalModules = $classes->sum('modules_count');
        
        // Get total students enrolled in teacher's courses
        $totalStudents = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.id')
            ->join('users', 'class_student.user_id', '=', 'users.id')
            ->where('classes.teacher_id', $user->id)
            ->where('users.role', 'student')
            ->distinct('class_student.user_id')
            ->count('class_student.user_id');
        
        // Get total enrollments (can be more than total students if one student enrolls in multiple courses)
        $totalEnrollments = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.id')
            ->join('users', 'class_student.user_id', '=', 'users.id')
            ->where('classes.teacher_id', $user->id)
            ->where('users.role', 'student')
            ->count();
        
        // Calculate average completion rate
        $enrollmentsWithProgress = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $user->id)
            ->select('class_student.progress')
            ->get();
        
        $avgCompletionRate = $enrollmentsWithProgress->count() > 0 
            ? round($enrollmentsWithProgress->avg('progress'), 1) 
            : 0;
        
        // Get enrollment trend (last 6 months)
        $enrollmentTrend = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $user->id)
            ->select(
                DB::raw('DATE_FORMAT(class_student.enrolled_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('class_student.enrolled_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Get top courses by enrollment
        $topCourses = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $user->id)
            ->select(
                'classes.id',
                'classes.name',
                'classes.is_published',
                DB::raw('COUNT(class_student.id) as enrollments')
            )
            ->groupBy('classes.id', 'classes.name', 'classes.is_published')
            ->orderBy('enrollments', 'desc')
            ->limit(5)
            ->get();
        
        // Get student performance data
        $studentPerformance = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.id')
            ->join('users', 'class_student.user_id', '=', 'users.id')
            ->where('classes.teacher_id', $user->id)
            ->where('users.role', 'student')
            ->select(
                'users.name as student_name',
                'classes.name as course_name',
                'class_student.progress',
                'class_student.completed_at'
            )
            ->orderBy('class_student.progress', 'desc')
            ->limit(10)
            ->get();
        
        // Calculate total revenue from purchases
        $totalRevenue = Purchase::whereIn('class_id', $classes->pluck('id'))
            ->where('status', 'success')
            ->sum('amount');
        
        // Get all purchases for courses created by this teacher
        $purchases = Purchase::whereIn('class_id', $classes->pluck('id'))
            ->with(['course.teacher', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all courses created by this teacher for finance management
        $courses = ClassModel::where('teacher_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('teacher.statistics', compact(
            'user',
            'totalCourses',
            'totalChapters',
            'totalModules',
            'totalStudents',
            'totalEnrollments',
            'avgCompletionRate',
            'enrollmentTrend',
            'topCourses',
            'studentPerformance',
            'totalRevenue',
            'classes',
            'purchases'
        ));
    }

    /**
     * Display notification preferences for teacher
     */
    public function notificationPreferences()
    {
        $user = auth()->user();
        $preferences = $user->getNotificationPreference();
        // Refresh to get latest data from database
        $preferences->refresh();
        
        return view('teacher.notification-preferences', compact('preferences'));
    }

    /**
     * Update notification preferences for teacher
     */
    public function updateNotificationPreferences(Request $request)
    {
        $user = auth()->user();
        
        // Define all notification preference fields
        $preferenceFields = [
            'new_course',
            'new_chapter',
            'new_module',
            'module_approved',
            'student_enrolled',
            'course_rated',
            'course_completed',
            'announcements',
            'promotions',
            'course_recommendations',
            'learning_stats',
        ];

        // Convert checkbox values to boolean
        // Checkboxes that are checked will be in request, unchecked ones won't be sent
        $preferencesData = [];
        foreach ($preferenceFields as $field) {
            // Use request->boolean() which returns true if value is "1", "true", "on", or "yes", false otherwise
            $preferencesData[$field] = $request->boolean($field, false);
        }

        $preference = $user->getNotificationPreference();
        $preference->update($preferencesData);

        return redirect()->route('teacher.notification-preferences')
            ->with('success', 'Notification preferences updated successfully');
    }

    /**
     * Display teacher's finance management
     */
    public function financeManagement()
    {
        $user = auth()->user();
        
        // Get or create teacher balance
        $balance = TeacherBalance::firstOrCreate(
            ['teacher_id' => $user->id],
            [
                'balance' => 0,
                'total_earnings' => 0,
                'total_withdrawn' => 0,
                'pending_earnings' => 0,
            ]
        );
        
        // Get withdrawal history
        $withdrawals = TeacherWithdrawal::where('teacher_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get recent purchases
        //
        // HANYA transaksi yang benar-benar sudah dibayar. Kolom `status` pada
        // tabel purchases berupa enum ['pending','success','expired','cancelled']
        // dan hanya 'success' yang berarti lunas - status itu pula yang memicu
        // penambahan saldo guru di App\Models\Purchase.
        //
        // Tanpa saringan ini, transaksi 'pending' (checkout yang ditinggalkan)
        // ikut tampil di tabel "Penjualan Terbaru" seolah-olah sudah menghasilkan
        // uang, padahal tidak pernah masuk ke saldo.
        $recentPurchases = Purchase::whereHas('course', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
        ->where('status', 'success')
        ->with(['course', 'user'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
        
        // Get all courses for statistics
        $courses = ClassModel::where('teacher_id', $user->id)->get();

        // Ringkasan keuangan dihitung ulang dari transaksi yang lunas, bukan
        // dibaca dari kolom teacher_balances. Baris di tabel itu terlanjur
        // menyimpan angka BRUTO karena bug lama di Purchase::updateTeacherBalance
        // (lihat App\Support\TeacherEarnings), sehingga kalau dipakai langsung
        // kartu-kartu di halaman ini akan tetap menampilkan 100% harga kursus.
        $earnings = \App\Support\TeacherEarnings::summaryFor($user->id);

        return view('teacher.finance-management', compact(
            'user',
            'balance',
            'earnings',
            'withdrawals',
            'recentPurchases',
            'courses'
        ));
    }

    /**
     * Store withdrawal request
     */
    public function requestWithdrawal(Request $request)
    {
        $user = auth()->user();
        
        // Get commission settings for minimum amount validation
        $commissionSettings = \App\Models\CommissionSetting::getForTeacher($user->id);
        $minimumAmount = $commissionSettings->min_amount;
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:' . $minimumAmount,
            'bank_name' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Saldo yang boleh ditarik memakai perhitungan yang SAMA dengan kartu
        // di halaman - pendapatan bersih setelah komisi, dikurangi penarikan
        // yang sudah disetujui. Kalau di sini masih membaca kolom
        // teacher_balances yang bernilai bruto, guru bisa menarik lebih banyak
        // daripada angka yang ditampilkan kepadanya.
        $earnings = \App\Support\TeacherEarnings::summaryFor($user->id);
        $availableBalance = $earnings['available'];

        if ($availableBalance < $validated['amount']) {
            $errorMessage = 'Jumlah penarikan melebihi saldo yang tersedia. Saldo Anda: Rp ' . number_format($availableBalance, 0, ',', '.');
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
        
        try {
            // Create withdrawal request
            $withdrawal = TeacherWithdrawal::create([
                'teacher_id' => $user->id,
                'amount' => $validated['amount'],
                'bank_name' => $validated['bank_name'],
                'bank_account_name' => $validated['bank_account_name'],
                'bank_account_number' => $validated['bank_account_number'],
                // ?? null wajib: aturan 'nullable' membuat kunci ini absen dari
                // hasil validated() ketika field-nya tidak dikirim sama sekali,
                // dan akses langsung melempar "Undefined array key" yang
                // tertangkap catch di bawah sebagai kegagalan palsu.
                'notes' => $validated['notes'] ?? null,
            ]);
            
            // Send notification to all admin users
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'withdrawal_request',
                    'title' => 'Permintaan Penarikan Baru',
                    'message' => "{$user->name} mengajukan penarikan sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . ". Bank: {$validated['bank_name']}"
                ]);
            }
            
            $successMessage = 'Permintaan penarikan berhasil diajukan. Menunggu persetujuan admin.';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'withdrawal_id' => $withdrawal->id
                ]);
            }
            
            // Nama rutenya 'teacher.finance.management' (bertitik), bukan
            // 'teacher.finance-management'. Nama yang salah membuat setiap
            // pengajuan non-AJAX melempar RouteNotFoundException, tertangkap
            // catch di bawah, lalu menampilkan "Terjadi kesalahan" kepada guru
            // padahal barisnya sudah tersimpan.
            return redirect()->route('teacher.finance.management')
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            \Log::error('Withdrawal request error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $validated['amount'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Terjadi kesalahan saat mengajukan penarikan. Silakan coba lagi.';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Upload avatar via AJAX
     */
    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        
        try {
            // Delete old avatar if exists
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            
            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $avatarPath]);
            
            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diupload',
                'avatar_url' => \Illuminate\Support\Facades\Storage::url($avatarPath)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto profil: ' . $e->getMessage()
            ], 500);
        }
    }
}
