<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\ClassReview;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Show all published courses (classes) in a grid
     */
    public function index(Request $request)
    {
        // Free Class — kelas gratis yang dikelola admin, tampil di bagian atas
        // halaman. Query terpisah dan tidak memengaruhi daftar kursus utama.
        $freeClasses = \App\Models\FreeClass::active()->ordered()->get();

        // Get popular courses (most popular by student enrollment count)
        $popularCoursesQuery = ClassModel::published()
            ->with(['teacher','category'])
            ->withCount(['chapters', 'modules', 'reviews'])
            ->leftJoin('class_student', 'classes.id', '=', 'class_student.class_id')
            ->select('classes.*', DB::raw('COUNT(DISTINCT class_student.user_id) as student_count'))
            ->groupBy('classes.id')
            ->orderByDesc(DB::raw('COUNT(DISTINCT class_student.user_id)'));

        // Note: We no longer exclude purchased courses from the list
        // Purchased courses should remain visible and show as unlocked

        $popularCourses = $popularCoursesQuery->take(3)->get();

        // Build query for filtered courses
        $query = ClassModel::published()
            ->with(['teacher','category'])
            ->withCount(['chapters', 'modules', 'reviews']);

        // Note: We no longer exclude purchased courses from the list
        // Purchased courses should remain visible and show as unlocked

        // Keyword search (course name, description, category, instructor)
        // Driven by the homepage search bar.
        $this->applySearch($query, $request->input('search'));

        // Filter by category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by rating (average rating from reviews)
        if ($request->filled('rating') && $request->rating !== 'all') {
            $minRating = (float) $request->rating;
            // Filter courses where average rating >= minRating using subquery
            $query->whereRaw('(SELECT COALESCE(AVG(rating), 0) FROM class_reviews WHERE class_reviews.class_id = classes.id) >= ?', [$minRating]);
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Get all courses with pagination
        $courses = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->query());

        // Get popular instructors
        $popularInstructors = \App\Models\User::where('role', 'teacher')
            ->where('is_banned', false)
            ->withCount([
                'classes' => function($query) {
                    $query->where('is_published', true);
                }
            ])
            ->having('classes_count', '>', 0)
            ->orderBy('classes_count', 'desc')
            ->take(10)
            ->get()
            ->map(function($teacher) {
                $totalStudents = DB::table('class_student')
                    ->join('classes', 'class_student.class_id', '=', 'classes.id')
                    ->join('users', 'class_student.user_id', '=', 'users.id')
                    ->where('classes.teacher_id', $teacher->id)
                    ->where('classes.is_published', true)
                    ->where('users.role', 'student')
                    ->distinct('class_student.user_id')
                    ->count('class_student.user_id');
                
                $teacher->total_students = $totalStudents;
                return $teacher;
            })
            ->sortByDesc(function($teacher) {
                return ($teacher->total_students * 1000) + $teacher->classes_count;
            })
            ->take(6)
            ->values();

        // Get all active categories from database
        $categories = \App\Models\Category::active()->ordered()->get();

        // Featured courses (pinned by admin)
        $featuredCoursesQuery = ClassModel::where('is_featured', true)
            ->where('is_published', true)
            ->with(['teacher','category'])
            ->withCount(['chapters','modules','reviews'])
            ->orderBy('created_at', 'desc');

        // Note: We no longer exclude purchased courses from the list
        // Purchased courses should remain visible and show as unlocked

        $featuredCourses = $featuredCoursesQuery->take(6)->get();

        return view('courses', compact('courses', 'popularCourses', 'popularInstructors', 'categories', 'featuredCourses', 'freeClasses'));
    }

    /**
     * Apply the keyword filter shared by the catalogue and the autocomplete.
     *
     * Matches course name, description, category slug, and instructor name.
     * A blank term leaves the query untouched.
     */
    private function applySearch($query, $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        // Escape LIKE wildcards so "100%" is searched literally
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('classes.name', 'like', $like)
              ->orWhere('classes.description', 'like', $like)
              ->orWhere('classes.category', 'like', $like)
              ->orWhereHas('teacher', function ($teacher) use ($like) {
                  $teacher->where('name', 'like', $like);
              });
        });
    }

    /**
     * Show course detail page
     */
    public function detail($id)
    {
        $user = auth()->user();

        // 1. Jika Admin, redirect ke admin preview logic
        if ($user && $user->isAdmin()) {
            return redirect()->route('admin.courses.preview', $id);
        }

        // Pastikan role valid jika sudah login
        if ($user && !in_array($user->role, ['student', 'teacher', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $isTeacherOrAdmin = $user && ($user->isTeacher() || $user->isAdmin());
        
        // 2. Query Builder untuk Course
        $courseQuery = ClassModel::with(['teacher','category', 'chapters' => function($query) use ($isTeacherOrAdmin, $user) {
                // Public/Guest/Student hanya bisa lihat chapter published
                if (!$isTeacherOrAdmin) {
                    $query->where('is_published', true);
                } elseif ($user && $user->isTeacher() && !$user->isAdmin()) {
                    // Teacher melihat course sendiri
                    $query->whereHas('class', function($q) use ($user) {
                        $q->where('teacher_id', $user->id);
                    });
                }
                
                // Load modules logic
                $query->with(['modules' => function($q) use ($isTeacherOrAdmin) {
                    if (!$isTeacherOrAdmin) {
                        $q->where('is_published', true)->approved();
                    }
                    $q->orderBy('order');
                }])->orderBy('order');
            }])
            ->withCount(['chapters', 'modules']);
        
        // 3. Filter Published - Guest dan Student hanya bisa lihat published
        if (!$isTeacherOrAdmin) {
            $courseQuery->where('is_published', true);
        }

        // Execute Query
        $course = $courseQuery->findOrFail($id);
        
        // 4. Validasi Ownership untuk Teacher
        if ($isTeacherOrAdmin && $user) {
            if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
                // Jika teacher membuka course orang lain, harus published
                if (!$course->is_published) {
                    abort(403, 'This course has been suspended and is not available.');
                }
            }
        }
        
        // Hitung students count manual
        $course->students_count = DB::table('class_student')
            ->join('users', 'class_student.user_id', '=', 'users.id')
            ->where('class_student.class_id', $course->id)
            ->where('users.role', 'student')
            ->count();

        // 5. Cek Enrollment & Progress (Menggunakan kode BAWAH/Incoming agar alurnya benar setelah $course didapat)
        $isEnrolled = false;
        $progress = 0;
        $hasCompletedModules = false;
        $userReview = null;
        $hasPurchase = false; // Default value untuk digunakan di seluruh method

        if ($user && $user->isStudent()) {
            // Cek apakah user punya akses aktif (purchase success atau subscription aktif)
            // JANGAN hanya cek record di class_student, karena user bisa enroll dari subscription yang sudah habis
            // Purchase pending TIDAK dianggap sebagai akses aktif
            $hasPurchase = \App\Models\Purchase::where('user_id', $user->id)
                ->where('class_id', $course->id)
                ->where('status', 'success')
                ->exists();
            
            $isSubscribed = $user->is_subscriber && $user->subscription_expires_at && $user->subscription_expires_at > now();
            $subscriptionPlan = $user->subscription_plan ?? 'standard';
            $courseTier = $course->price_tier ?? 'standard';
            
            $canAccessViaSubscription = $isSubscribed && (
                ($subscriptionPlan === 'premium') ||
                ($subscriptionPlan === 'standard' && $courseTier === 'standard')
            );
            
            // User dianggap enrolled HANYA jika punya akses aktif (purchase success atau subscription aktif)
            $hasActiveAccess = $hasPurchase || $canAccessViaSubscription;
            
            // Cek apakah ada record di class_student (untuk progress tracking)
            $hasEnrollmentRecord = DB::table('class_student')
                ->where('class_id', $course->id)
                ->where('user_id', $user->id)
                ->exists();
            
            // User dianggap enrolled jika punya akses aktif DAN ada enrollment record
            $isEnrolled = $hasActiveAccess && $hasEnrollmentRecord;
            
            if ($hasEnrollmentRecord) {
                $enrollment = DB::table('class_student')
                    ->where('class_id', $course->id)
                    ->where('user_id', $user->id)
                    ->first();
                $progress = $enrollment->progress ?? 0;
                
                // Cek apakah user sudah mark as complete minimal 1 module
                // Progress tracking hanya muncul jika sudah complete minimal 1 module
                $completedModulesCount = DB::table('module_completions')
                    ->where('class_id', $course->id)
                    ->where('user_id', $user->id)
                    ->count();
                
                $hasCompletedModules = $completedModulesCount > 0;
                
                // Progress hanya dihitung jika sudah ada completed modules
                if (!$hasCompletedModules) {
                    $progress = 0;
                }
                
                // Get user's review if exists
                $userReview = ClassReview::where('class_id', $course->id)
                    ->where('user_id', $user->id)
                    ->first();
            }
        }

        // Get reviews and rating stats
        $reviews = ClassReview::where('class_id', $course->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $ratingStats = [
            'total' => ClassReview::where('class_id', $course->id)->count(),
            'average' => ClassReview::where('class_id', $course->id)->avg('rating') ?? 0,
            'distribution' => []
        ];

        // Calculate rating distribution
        for ($i = 5; $i >= 1; $i--) {
            $count = ClassReview::where('class_id', $course->id)
                ->where('rating', $i)
                ->count();
            $ratingStats['distribution'][$i] = [
                'count' => $count,
                'percentage' => $ratingStats['total'] > 0 ? round(($count / $ratingStats['total']) * 100, 1) : 0
            ];
        }

        // Check if course is in most popular (top 6 by student count)
        $popularCourseIds = ClassModel::published()
            ->leftJoin('class_student', 'classes.id', '=', 'class_student.class_id')
            ->select('classes.id', DB::raw('COUNT(DISTINCT class_student.user_id) as student_count'))
            ->groupBy('classes.id')
            ->orderByDesc(DB::raw('COUNT(DISTINCT class_student.user_id)'))
            ->take(3)
            ->pluck('id');
        $isPopular = $popularCourseIds->contains($course->id);

        // Check if user has pending purchase for this course
        $hasPendingPurchase = false;
        // $hasPurchase sudah didefinisikan di bagian atas (line ~178), gunakan variabel yang sama
        $subscriptionStatus = null;
        $canAccessCourse = true;
        $showAsNotEnrolled = false; // Flag untuk menampilkan sebagai "belum enroll" jika subscription habis
        
        if ($user && $user->isStudent()) {
            // $hasPurchase sudah didefinisikan di bagian atas, tidak perlu query ulang
            
            // Cek pending purchase: hanya tampilkan sebagai "pending" jika:
            // 1. Status masih 'pending' 
            // 2. BELUM ada invoice (artinya user belum klik "Bayar Sekarang")
            // 3. Purchase dibuat dalam 24 jam terakhir (untuk menghindari stale data)
            // Jika sudah ada invoice, berarti user sudah klik "Bayar Sekarang", jadi tidak perlu ditampilkan sebagai pending lagi
            $pendingPurchases = \App\Models\Purchase::where('user_id', $user->id)
                ->where('class_id', $course->id)
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subHours(24)) // Hanya pending purchase dalam 24 jam terakhir
                ->get();
            
            foreach ($pendingPurchases as $pendingPurchase) {
                // Cek apakah purchase ini sudah punya invoice
                $hasInvoice = \App\Models\Invoice::where('invoiceable_id', $pendingPurchase->id)
                    ->where('invoiceable_type', \App\Models\Purchase::class)
                    ->exists();
                
                // Hanya tampilkan sebagai "pending" jika belum ada invoice
                // Jika sudah ada invoice, berarti user sudah klik "Bayar Sekarang", jadi tidak perlu ditampilkan sebagai pending
                if (!$hasInvoice) {
                    $hasPendingPurchase = true;
                    break;
                }
            }
            
            // Cek subscription status untuk enrolled users
            // Hanya perlu cek jika user sudah mulai belajar (ada completed modules) dan belum beli course
            if ($isEnrolled && !$hasPurchase) {
                $courseTier = $course->price_tier ?? 'standard';
                $isSubscribed = $user->is_subscriber && $user->subscription_expires_at && $user->subscription_expires_at > now();
                $subscriptionPlan = $user->subscription_plan ?? 'standard';
                
                $canAccessViaSubscription = $isSubscribed && (
                    ($subscriptionPlan === 'premium') ||
                    ($subscriptionPlan === 'standard' && $courseTier === 'standard')
                );
                
                // Jika sudah ada completed modules, WAJIB cek subscription status
                // Jika belum ada completed modules, user masih bisa akses untuk mulai belajar
                if ($hasCompletedModules) {
                    if (!$canAccessViaSubscription) {
                        // Subscription habis atau tidak sesuai tier
                        $canAccessCourse = false;
                        $subscriptionStatus = [
                            'expired' => !$isSubscribed,
                            'plan' => $subscriptionPlan,
                            'course_tier' => $courseTier,
                            'needs_upgrade' => $isSubscribed && $subscriptionPlan === 'standard' && $courseTier === 'premium',
                        ];
                        // Tampilkan sebagai "belum enroll" jika subscription habis dan sudah pernah dipelajari
                        $showAsNotEnrolled = true;
                    } else {
                        // Subscription masih aktif dan sesuai tier
                        $canAccessCourse = true;
                        $subscriptionStatus = null;
                    }
                } else {
                    // Belum mulai belajar, bisa akses untuk mulai belajar
                    $canAccessCourse = true;
                    $subscriptionStatus = null;
                }
            }
        }

        return view('course-detail', compact('course', 'isEnrolled', 'progress', 'hasCompletedModules', 'userReview', 'reviews', 'ratingStats', 'isPopular', 'hasPendingPurchase', 'hasPurchase', 'subscriptionStatus', 'canAccessCourse', 'showAsNotEnrolled'));
    }

    /**
     * Submit rating and review for a course
     */
    public function submitRating(Request $request, $id)
    {
        $user = auth()->user();
        
        if (!$user || !$user->isStudent()) {
            return redirect()->back()->with('error', 'Hanya student yang bisa memberikan rating.');
        }

        $course = ClassModel::findOrFail($id);

        // Check if user is enrolled
        if (!$course->isEnrolledBy($user)) {
            return redirect()->back()->with('error', 'Anda harus enroll course ini terlebih dahulu untuk memberikan rating.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Update or create review
        $review = ClassReview::updateOrCreate(
            [
                'class_id' => $course->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        // Notifikasi ke teacher
        if ($course->teacher && $course->teacher->wantsNotification('course_rated')) {
            $ratingText = $validated['rating'] . ' bintang';
            $commentText = !empty($validated['comment']) ? " dengan komentar: \"{$validated['comment']}\"" : '';
            
            Notification::create([
                'user_id' => $course->teacher->id,
                'type' => 'course_rated',
                'title' => 'Course Mendapat Rating Baru',
                'message' => "Siswa '{$user->name}' memberikan rating {$ratingText} untuk course '{$course->name}' Anda{$commentText}.",
                'notifiable_type' => ClassModel::class,
                'notifiable_id' => $course->id,
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil disimpan.'
            ]);
        }
        
        return redirect()->back()->with('success', 'Rating berhasil disimpan.');
    }
}