<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /** Placeholder icon per category slug, used when a course has no image. */
    private const CATEGORY_ICONS = [
        'ai' => 'fa-brain',
        'development' => 'fa-code',
        'marketing' => 'fa-chart-line',
        'design' => 'fa-palette',
        'photography' => 'fa-camera',
    ];

    /**
     * Show home page with courses by category and trending courses
     */
    public function index()
    {
        // Get active categories from database
        $categories = Category::active()->ordered()->get();
        
        // Fallback to constant categories if database is empty
        if ($categories->isEmpty()) {
            $categories = collect(ClassModel::CATEGORIES)->map(function ($name, $slug) {
                return (object) [
                    'slug' => $slug,
                    'name' => $name,
                    'id' => null,
                ];
            });
        }

        // Get published classes by category (for home page categories section)
        $coursesByCategory = [];
        
        // Get purchased course IDs to exclude if user is student
        $purchasedCourseIds = collect();
        if (auth()->check() && auth()->user()->isStudent()) {
            $purchasedCourseIds = \App\Models\Purchase::where('user_id', auth()->id())
                ->where('status', 'success')
                ->pluck('class_id');
        }
        
        foreach ($categories as $category) {
            $query = ClassModel::publishedByCategory($category->slug)
                ->with('teacher')
                ->withCount(['chapters', 'modules', 'reviews']);
                
            // Exclude purchased courses if user is student
            if ($purchasedCourseIds->isNotEmpty()) {
                $query->whereNotIn('classes.id', $purchasedCourseIds);
            }
            
            $coursesByCategory[$category->slug] = $query->take(4)->get();
        }

        // Get trending courses (sorted by number of students)
        $trendingCoursesQuery = ClassModel::published()
            ->with('teacher')
            ->withCount(['chapters', 'modules', 'reviews'])
            ->leftJoin('class_student', 'classes.id', '=', 'class_student.class_id')
            ->select('classes.*', DB::raw('COUNT(DISTINCT class_student.user_id) as student_count'))
            ->groupBy('classes.id')
            ->orderByDesc(DB::raw('COUNT(DISTINCT class_student.user_id)'));

        // Exclude purchased courses if user is student
        if ($purchasedCourseIds->isNotEmpty()) {
            $trendingCoursesQuery->whereNotIn('classes.id', $purchasedCourseIds);
        }

        $trendingCourses = $trendingCoursesQuery->take(6)->get();

        // Get enrolled courses for authenticated user (only for students)
        // Include courses from purchases and subscriptions (both are in class_student table)
        $enrolledCourses = collect();
        if (auth()->check() && auth()->user() && auth()->user()->isStudent()) {
            try {
                $user = auth()->user();
                
                // Get enrolled course IDs from class_student (includes purchased and subscription courses)
                $enrolledCourseIds = \Illuminate\Support\Facades\DB::table('class_student')
                    ->where('user_id', $user->id)
                    ->pluck('class_id');
                
                if ($enrolledCourseIds->isNotEmpty()) {
                    // Get courses that have at least 1 completed module (progress tracking hanya muncul jika sudah complete minimal 1 module)
                    $coursesWithProgress = \Illuminate\Support\Facades\DB::table('module_completions')
                        ->where('user_id', $user->id)
                        ->whereIn('class_id', $enrolledCourseIds)
                        ->pluck('class_id')
                        ->unique()
                        ->values();
                    
                    // Only show courses that have progress (minimal 1 module completed)
                    if ($coursesWithProgress->isNotEmpty()) {
                        $enrolledCourses = ClassModel::whereIn('id', $coursesWithProgress)
                            ->with(['teacher' => function($q) {
                                $q->select('id', 'name'); // Explicitly select teacher fields
                            }])
                            ->withCount('modules')
                            ->orderBy('created_at', 'desc')
                            ->take(3)
                            ->get();
                        
                        // Add progress data for each enrolled course
                        foreach ($enrolledCourses as $course) {
                            $enrollment = \Illuminate\Support\Facades\DB::table('class_student')
                                ->where('class_id', $course->id)
                                ->where('user_id', $user->id)
                                ->first();
                            $course->progress = $enrollment->progress ?? 0;
                            $course->completed_modules = \Illuminate\Support\Facades\DB::table('module_completions')
                                ->where('class_id', $course->id)
                                ->where('user_id', $user->id)
                                ->count();
                        }
                    }
                }
            } catch (\Exception $e) {
                // If error, just show empty collection
                $enrolledCourses = collect();
            }
        }

        // Get teacher's courses (only for teachers)
        $teacherCourses = collect();
        if (auth()->check() && auth()->user() && auth()->user()->isTeacher()) {
            try {
                $teacherCourses = ClassModel::where('teacher_id', auth()->id())
                    ->with('teacher')
                    ->withCount(['chapters', 'modules'])
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();
            } catch (\Exception $e) {
                // If error, just show empty collection
                $teacherCourses = collect();
            }
        }

        // Testimonials for homepage.
        // Ditulis oleh siswa, dan HANYA yang berstatus 'approved' (sudah
        // disetujui admin) yang boleh tampil di halaman publik.
        $testimonials = \App\Models\Testimonial::approved()
            ->with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Most popular courses (most students enrolled)
        $featuredCoursesQuery = ClassModel::where('is_published', true)
            ->with('teacher')
            ->withCount(['chapters', 'modules', 'reviews'])
            ->leftJoin('class_student', 'classes.id', '=', 'class_student.class_id')
            ->select('classes.*', DB::raw('COUNT(DISTINCT class_student.user_id) as student_count'))
            ->groupBy('classes.id')
            ->orderByDesc(DB::raw('COUNT(DISTINCT class_student.user_id)'));

        // Exclude purchased courses if user is student
        if ($purchasedCourseIds->isNotEmpty()) {
            $featuredCoursesQuery->whereNotIn('classes.id', $purchasedCourseIds);
        }

        $featuredCourses = $featuredCoursesQuery->take(3)->get();

        return view('home', [
            'categories' => $categories,
            'coursesByCategory' => $coursesByCategory,
            'trendingCourses' => $trendingCourses,
            'enrolledCourses' => $enrolledCourses,
            'teacherCourses' => $teacherCourses,
            'testimonials' => $testimonials,
            'featuredCourses' => $featuredCourses,
            'homeStats' => $this->homeStats(),
            'previewCategories' => $this->previewCategories($categories, $coursesByCategory),
            // Tautan "Free Class" hanya ditampilkan bila admin sudah mengisi
            // kelas gratis, supaya beranda tidak menunjuk ke seksi kosong.
            'hasFreeClasses' => \App\Models\FreeClass::active()->exists(),
        ]);
    }

    /**
     * Homepage statistics (animated counters).
     *
     * Every entry keeps the same shape so a value can be swapped for a real
     * query without touching the view:
     *   key, label, icon, value (numeric), suffix, decimals
     *
     * Where a real source exists it is used. Metrics we do not track yet
     * (education partners) — and any real metric still below its `min`
     * threshold — fall back to the placeholder value instead, so a fresh or
     * seeded database does not advertise "3 learners".
     *
     * TO GO FULLY LIVE: drop the 'placeholder'/'min' keys from an entry (or
     * set 'min' => 1) and the real count is shown as-is.
     */
    private function homeStats(): array
    {
        try {
            $totalCourses = ClassModel::where('is_published', true)->count();
            $totalLearners = \App\Models\User::where('role', 'student')->count();
            $totalInstructors = \App\Models\User::where('role', 'teacher')->count();
            // Hands-on projects are not modelled yet — approximated with the
            // module count so the figure still moves with real content.
            $totalProjects = DB::table('modules')->count();
        } catch (\Exception $e) {
            $totalCourses = $totalLearners = $totalInstructors = $totalProjects = 0;
        }

        $metrics = [
            [
                'key' => 'courses',
                'label' => 'Total Courses',
                'icon' => 'fa-layer-group',
                'actual' => $totalCourses,
                'placeholder' => 120,
                'min' => 10,
            ],
            [
                'key' => 'learners',
                'label' => 'Active Learners',
                'icon' => 'fa-user-graduate',
                'actual' => $totalLearners,
                'placeholder' => 8500,
                'min' => 100,
            ],
            [
                'key' => 'instructors',
                'label' => 'Expert Instructors',
                'icon' => 'fa-chalkboard-user',
                'actual' => $totalInstructors,
                'placeholder' => 45,
                'min' => 5,
            ],
            [
                'key' => 'projects',
                'label' => 'Hands-on Projects',
                'icon' => 'fa-diagram-project',
                'actual' => $totalProjects,
                'placeholder' => 320,
                'min' => 25,
            ],
            [
                'key' => 'partners',
                'label' => 'Education Partners',
                'icon' => 'fa-handshake',
                // No partners table yet — mirrors the logos in the partnership
                // section further down the page.
                'actual' => 0,
                'placeholder' => 10,
                'min' => 1,
            ],
        ];

        return array_map(function ($metric) {
            $useReal = $metric['actual'] >= ($metric['min'] ?? 1);

            return [
                'key' => $metric['key'],
                'label' => $metric['label'],
                'icon' => $metric['icon'],
                'value' => $useReal ? $metric['actual'] : $metric['placeholder'],
                'is_placeholder' => !$useReal,
                'suffix' => '+',
                'decimals' => 0,
            ];
        }, $metrics);
    }

    /**
     * Category tabs for the homepage "Course Preview" section.
     *
     * Shape per entry: slug, name, icon, courses[] — one entry per active
     * category, so the filter dropdown and the card grid share a source.
     */
    private function previewCategories($categories, array $coursesByCategory): array
    {
        // When nothing is published anywhere, every tab shows the same demo
        // cards so the section still reads as designed. Remove this fallback
        // once the catalogue has real content.
        $catalogueIsEmpty = collect($coursesByCategory)
            ->every(fn ($courses) => count($courses) === 0);

        return collect($categories)->map(function ($category) use ($coursesByCategory, $catalogueIsEmpty) {
            return [
                'slug' => $category->slug,
                'name' => $category->name,
                'icon' => self::CATEGORY_ICONS[$category->slug] ?? 'fa-book-open',
                'courses' => $catalogueIsEmpty
                    ? $this->mockPreviewCourses()
                    : $this->previewCourses($coursesByCategory[$category->slug] ?? []),
            ];
        })->values()->all();
    }

    /**
     * Normalised course data for the "Course Preview" cards.
     *
     * Deliberately minimal — the preview only teases a course, so rating,
     * duration, lesson count, and price are not exposed here. Returns plain
     * arrays so the view never touches the model: swapping this for an API
     * response only means changing this method.
     */
    private function previewCourses($courses, int $limit = 4): array
    {
        return collect($courses)->take($limit)->map(function ($course) {
            return [
                'id' => $course->id,
                'url' => route('course.detail', $course->id),
                'title' => $course->name ?? 'Untitled Course',
                'description' => $course->description
                    ? \Illuminate\Support\Str::limit(strip_tags($course->description), 110)
                    : 'A hands-on course designed to help you build practical, job-ready skills.',
                'image' => $course->image ? asset('storage/' . $course->image) : null,
                'instructor' => $course->teacher->name ?? 'MersifLab Instructor',
                'instructor_avatar' => ($course->teacher && $course->teacher->avatar)
                    ? \Illuminate\Support\Facades\Storage::url($course->teacher->avatar)
                    : null,
                // No `level` column yet — derived from course size so it stays
                // meaningful. Replace with $course->level once the field exists.
                'level' => $this->deriveLevel((int) ($course->modules_count ?? 0)),
            ];
        })->values()->all();
    }

    /**
     * Mock course previews — shown only while no course is published.
     * Same shape as previewCourses() so the view is unaware of the difference.
     */
    private function mockPreviewCourses(): array
    {
        $samples = [
            [
                'title' => 'Introduction to Artificial Intelligence',
                'description' => 'Understand how AI works and build your first intelligent model from scratch.',
                'level' => 'Beginner',
            ],
            [
                'title' => 'Immersive Learning with AR & VR',
                'description' => 'Design immersive classroom experiences using augmented and virtual reality.',
                'level' => 'Intermediate',
            ],
            [
                'title' => 'Modern Web Development Fundamentals',
                'description' => 'Build responsive, production-ready web applications step by step.',
                'level' => 'Beginner',
            ],
            [
                'title' => 'Data Analysis for Beginners',
                'description' => 'Turn raw data into clear insights using practical, real-world datasets.',
                'level' => 'Beginner',
            ],
        ];

        return array_map(function ($sample) {
            return array_merge($sample, [
                'id' => null,
                'url' => route('courses'),
                'image' => null,
                'instructor' => 'MersifLab Instructor',
                'instructor_avatar' => null,
            ]);
        }, $samples);
    }

    /**
     * Rough difficulty label based on how much material a course contains.
     */
    private function deriveLevel(int $modulesCount): string
    {
        if ($modulesCount >= 25) {
            return 'Advanced';
        }

        if ($modulesCount >= 12) {
            return 'Intermediate';
        }

        return 'Beginner';
    }

}
