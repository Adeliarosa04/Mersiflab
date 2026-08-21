<?php

namespace App\Support;

use App\Models\Category;
use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Site-wide search used by the header/hero search bar.
 *
 * Covers four kinds of result:
 *   - course      published courses
 *   - category    course categories (links into the filtered catalogue)
 *   - instructor  teachers with at least one published course
 *   - page        the static/app pages listed in self::pages()
 *
 * Pages are filtered by what the current visitor can actually reach, so a
 * guest is never shown "My Certificates" and a teacher is never shown the
 * student dashboard.
 */
class SiteSearch
{
    /** Minimum term length before we hit the database. */
    public const MIN_LENGTH = 2;

    /**
     * The page index.
     *
     * To add a page: give it a route name, a title, a short description, and
     * the keywords people might actually type. `access` controls visibility
     * and mirrors how the navbar gates each page:
     *   public | public-non-teacher | guest | auth | auth-non-teacher
     *   | student | teacher
     */
    private static function pages(): array
    {
        return [
            [
                'title' => 'Home',
                'route' => 'home',
                'description' => 'MersifLab homepage — courses, statistics, and partners.',
                'keywords' => ['home', 'homepage', 'beranda', 'utama', 'main'],
                'icon' => 'fa-house',
                'access' => 'public',
            ],
            [
                'title' => 'Explore Courses',
                'route' => 'courses',
                'description' => 'Browse and filter every published course.',
                'keywords' => ['course', 'courses', 'kelas', 'katalog', 'catalogue', 'explore', 'belajar', 'learn'],
                'icon' => 'fa-graduation-cap',
                'access' => 'public',
            ],
            [
                'title' => 'About Us',
                'route' => 'about',
                'description' => 'Who MersifLab is and how to contact us.',
                'keywords' => ['about', 'tentang', 'contact', 'kontak', 'company', 'profil'],
                'icon' => 'fa-circle-info',
                'access' => 'public',
            ],
            [
                'title' => 'Subscription',
                'route' => 'subscription.page',
                'description' => 'Subscription plans and pricing.',
                'keywords' => ['subscription', 'langganan', 'plan', 'pricing', 'harga', 'paket', 'premium'],
                'icon' => 'fa-crown',
                // Navbar hides Subscription from teachers
                'access' => 'public-non-teacher',
            ],
            [
                'title' => 'Instructors',
                'route' => 'courses',
                'fragment' => 'popular-instructors',
                'description' => 'Meet the instructors teaching on MersifLab.',
                'keywords' => ['instructor', 'instruktur', 'teacher', 'guru', 'pengajar', 'mentor'],
                'icon' => 'fa-chalkboard-user',
                'access' => 'public',
            ],
            [
                'title' => 'Partnership',
                'route' => 'home',
                'fragment' => 'trust-section',
                'description' => 'Schools and institutions partnering with MersifLab.',
                'keywords' => ['partner', 'partnership', 'kerjasama', 'school', 'sekolah', 'mitra'],
                'icon' => 'fa-handshake',
                'access' => 'public',
            ],
            [
                'title' => 'FAQ',
                'route' => 'home',
                'fragment' => 'faq-section',
                'description' => 'Answers to the most common questions.',
                'keywords' => ['faq', 'question', 'pertanyaan', 'help', 'bantuan', 'tanya'],
                'icon' => 'fa-circle-question',
                'access' => 'public',
            ],

            // --- Guest only ---
            [
                'title' => 'Log In',
                'route' => 'login',
                'description' => 'Sign in to your MersifLab account.',
                'keywords' => ['login', 'masuk', 'sign in', 'signin'],
                'icon' => 'fa-right-to-bracket',
                'access' => 'guest',
            ],
            [
                'title' => 'Register',
                'route' => 'register',
                'description' => 'Create a free MersifLab account.',
                'keywords' => ['register', 'daftar', 'sign up', 'signup', 'account', 'akun'],
                'icon' => 'fa-user-plus',
                'access' => 'guest',
            ],

            // --- Learner-side pages (navbar hides these from teachers) ---
            [
                'title' => 'My Courses',
                'route' => 'my-courses',
                'description' => 'Courses you are enrolled in.',
                'keywords' => ['my courses', 'kelas saya', 'enrolled', 'learning', 'progress'],
                'icon' => 'fa-book-open',
                'access' => 'auth-non-teacher',
            ],
            [
                'title' => 'My Certificates',
                'route' => 'my-certificates',
                'description' => 'Certificates you have earned.',
                'keywords' => ['certificate', 'sertifikat', 'award', 'completion'],
                'icon' => 'fa-award',
                'access' => 'auth-non-teacher',
            ],
            [
                'title' => 'Cart',
                'route' => 'cart',
                'description' => 'Courses waiting in your cart.',
                'keywords' => ['cart', 'keranjang', 'checkout', 'basket'],
                'icon' => 'fa-cart-shopping',
                'access' => 'auth-non-teacher',
            ],
            [
                'title' => 'Purchase History',
                'route' => 'purchase-history',
                'description' => 'Everything you have bought.',
                'keywords' => ['purchase', 'history', 'pembelian', 'riwayat', 'order', 'transaksi'],
                'icon' => 'fa-receipt',
                'access' => 'auth-non-teacher',
            ],

            // --- Any signed-in user ---
            [
                'title' => 'Profile',
                'route' => 'profile',
                'description' => 'Your account details and settings.',
                'keywords' => ['profile', 'profil', 'account', 'akun', 'settings', 'pengaturan'],
                'icon' => 'fa-user',
                'access' => 'auth',
            ],
            [
                'title' => 'Invoices',
                'route' => 'invoices.index',
                'description' => 'Your invoices and payment records.',
                'keywords' => ['invoice', 'faktur', 'tagihan', 'billing', 'payment', 'pembayaran'],
                'icon' => 'fa-file-invoice',
                'access' => 'auth',
            ],
            [
                'title' => 'Notifications',
                'route' => 'notifications',
                'description' => 'Your latest notifications.',
                'keywords' => ['notification', 'notifikasi', 'alert', 'pemberitahuan'],
                'icon' => 'fa-bell',
                'access' => 'auth',
            ],
            [
                'title' => 'Notification Preferences',
                'route' => 'notification-preferences',
                'description' => 'Choose which notifications you receive.',
                'keywords' => ['notification preferences', 'preferensi', 'settings', 'email'],
                'icon' => 'fa-sliders',
                'access' => 'auth',
            ],
            [
                'title' => 'Become an Instructor',
                'route' => 'teacher.application.create',
                'description' => 'Apply to teach on MersifLab.',
                'keywords' => ['become instructor', 'apply', 'daftar guru', 'teach', 'mengajar', 'application'],
                'icon' => 'fa-chalkboard-user',
                'access' => 'auth-non-teacher',
            ],

            // --- Student ---
            [
                'title' => 'Student Dashboard',
                'route' => 'student.dashboard',
                'description' => 'Your learning overview.',
                'keywords' => ['dashboard', 'student', 'siswa', 'overview'],
                'icon' => 'fa-gauge',
                'access' => 'student',
            ],
            [
                'title' => 'My Progress',
                'route' => 'student.progress',
                'description' => 'Track how far you have come.',
                'keywords' => ['progress', 'kemajuan', 'tracking', 'completion'],
                'icon' => 'fa-chart-line',
                'access' => 'student',
            ],

            // --- Teacher ---
            [
                'title' => 'Teacher Dashboard',
                'route' => 'teacher.dashboard',
                'description' => 'Your teaching overview.',
                'keywords' => ['dashboard', 'teacher', 'guru', 'overview'],
                'icon' => 'fa-gauge',
                'access' => 'teacher',
            ],
            [
                'title' => 'My Courses',
                'route' => 'teacher.courses',
                'description' => 'The courses you teach.',
                'keywords' => ['my courses', 'kelas saya', 'teaching', 'mengajar'],
                'icon' => 'fa-book-open',
                'access' => 'teacher',
            ],
            [
                'title' => 'My Classes',
                'route' => 'teacher.classes.index',
                'description' => 'Create and manage the courses you teach.',
                'keywords' => ['my classes', 'kelas saya', 'manage', 'kelola', 'create course'],
                'icon' => 'fa-layer-group',
                'access' => 'teacher',
            ],
            [
                'title' => 'Analytics',
                'route' => 'teacher.analytics',
                'description' => 'How your courses are performing.',
                'keywords' => ['analytics', 'analitik', 'report', 'laporan', 'insight'],
                'icon' => 'fa-chart-column',
                'access' => 'teacher',
            ],
            [
                'title' => 'Statistics',
                'route' => 'teacher.statistics',
                'description' => 'Your teaching statistics.',
                'keywords' => ['statistics', 'statistik', 'numbers', 'data'],
                'icon' => 'fa-chart-pie',
                'access' => 'teacher',
            ],
            [
                'title' => 'Finance Management',
                'route' => 'teacher.finance.management',
                'description' => 'Earnings and withdrawals.',
                'keywords' => ['finance', 'keuangan', 'earning', 'penghasilan', 'withdraw', 'payout'],
                'icon' => 'fa-wallet',
                'access' => 'teacher',
            ],
        ];
    }

    /**
     * Run a search.
     *
     * @param  string $term
     * @param  int    $perGroup  max results per group
     * @return array{term:string,total:int,groups:array}
     */
    public function search(string $term, int $perGroup = 5): array
    {
        $term = trim($term);

        if (Str::length($term) < self::MIN_LENGTH) {
            return ['term' => $term, 'total' => 0, 'groups' => []];
        }

        $groups = array_filter([
            'courses' => $this->courses($term, $perGroup),
            'pages' => $this->matchPages($term, $perGroup),
            'categories' => $this->categories($term, $perGroup),
            'instructors' => $this->instructors($term, $perGroup),
        ], fn ($items) => count($items) > 0);

        $total = array_sum(array_map('count', $groups));

        return ['term' => $term, 'total' => $total, 'groups' => $groups];
    }

    /* ------------------------------------------------------------------
       Result groups
       ------------------------------------------------------------------ */

    private function courses(string $term, int $limit): array
    {
        $like = self::like($term);

        return ClassModel::published()
            ->with('teacher')
            ->where(function ($q) use ($like) {
                $q->where('classes.name', 'like', $like)
                  ->orWhere('classes.description', 'like', $like)
                  ->orWhere('classes.category', 'like', $like)
                  ->orWhereHas('teacher', fn ($t) => $t->where('name', 'like', $like));
            })
            // Name matches are what people mean; rank them above description hits
            ->orderByRaw('CASE WHEN classes.name LIKE ? THEN 0 ELSE 1 END', [$term . '%'])
            ->orderBy('classes.name')
            ->take($limit)
            ->get()
            ->map(fn ($course) => [
                'type' => 'course',
                'title' => $course->name,
                'subtitle' => ($course->teacher->name ?? 'MersifLab Instructor')
                    . ' · ' . (ClassModel::CATEGORIES[$course->category] ?? $course->category),
                'image' => $course->image ? asset('storage/' . $course->image) : null,
                'icon' => 'fa-graduation-cap',
                'url' => route('course.detail', $course->id),
            ])
            ->all();
    }

    private function categories(string $term, int $limit): array
    {
        $like = self::like($term);

        try {
            $rows = Category::active()->where('name', 'like', $like)->take($limit)->get()
                ->map(fn ($c) => ['slug' => $c->slug, 'name' => $c->name]);
        } catch (\Exception $e) {
            $rows = collect();
        }

        // Fall back to the model constant when the table is empty/unavailable
        if ($rows->isEmpty()) {
            $rows = collect(ClassModel::CATEGORIES)
                ->filter(fn ($name) => Str::contains(Str::lower($name), Str::lower($term)))
                ->map(fn ($name, $slug) => ['slug' => $slug, 'name' => $name])
                ->take($limit)
                ->values();
        }

        return $rows->map(fn ($row) => [
            'type' => 'category',
            'title' => $row['name'],
            'subtitle' => 'Course category',
            'image' => null,
            'icon' => 'fa-tag',
            'url' => route('courses', ['category' => $row['slug']]) . '#all-courses',
        ])->values()->all();
    }

    private function instructors(string $term, int $limit): array
    {
        $like = self::like($term);

        return User::where('role', 'teacher')
            ->where('is_banned', false)
            ->where('name', 'like', $like)
            ->withCount(['classes' => fn ($q) => $q->where('is_published', true)])
            ->having('classes_count', '>', 0)
            ->orderByDesc('classes_count')
            ->take($limit)
            ->get()
            ->map(fn ($teacher) => [
                'type' => 'instructor',
                'title' => $teacher->name,
                'subtitle' => $teacher->classes_count . ' ' . Str::plural('course', $teacher->classes_count),
                'image' => $teacher->avatar
                    ? \Illuminate\Support\Facades\Storage::url($teacher->avatar)
                    : null,
                'icon' => 'fa-chalkboard-user',
                // No public instructor profile yet — show their courses instead
                'url' => route('courses', ['search' => $teacher->name]) . '#all-courses',
            ])
            ->all();
    }

    private function matchPages(string $term, int $limit): array
    {
        $needle = Str::lower($term);
        $scored = [];

        foreach (self::pages() as $page) {
            if (!$this->canSee($page['access'])) {
                continue;
            }

            // A renamed/removed route must not blow up the whole search
            if (!Route::has($page['route'])) {
                continue;
            }

            $score = $this->scorePage($page, $needle);
            if ($score === null) {
                continue;
            }

            $url = route($page['route']);
            if (!empty($page['fragment'])) {
                $url .= '#' . $page['fragment'];
            }

            $scored[] = [
                'score' => $score,
                'item' => [
                    'type' => 'page',
                    'title' => $page['title'],
                    'subtitle' => $page['description'],
                    'image' => null,
                    'icon' => $page['icon'],
                    'url' => $url,
                ],
            ];
        }

        usort($scored, fn ($a, $b) => $a['score'] <=> $b['score']);

        return array_column(array_slice($scored, 0, $limit), 'item');
    }

    /**
     * Lower score = better match. Null means no match at all.
     */
    private function scorePage(array $page, string $needle): ?int
    {
        $title = Str::lower($page['title']);

        if (Str::startsWith($title, $needle)) {
            return 0;
        }

        if (Str::contains($title, $needle)) {
            return 1;
        }

        foreach ($page['keywords'] as $keyword) {
            $keyword = Str::lower($keyword);
            if (Str::startsWith($keyword, $needle) || Str::contains($keyword, $needle)) {
                return 2;
            }
        }

        if (Str::contains(Str::lower($page['description']), $needle)) {
            return 3;
        }

        return null;
    }

    /**
     * Can the current visitor actually open a page with this access level?
     */
    private function canSee(string $access): bool
    {
        $user = auth()->user();

        return match ($access) {
            'public' => true,
            'public-non-teacher' => $user === null || !$user->isTeacher(),
            'guest' => $user === null,
            'auth' => $user !== null,
            'auth-non-teacher' => $user !== null && !$user->isTeacher(),
            'student' => $user !== null && $user->isStudent(),
            'teacher' => $user !== null && $user->isTeacher(),
            default => false,
        };
    }

    /** Build a LIKE pattern with the user's wildcards escaped. */
    private static function like(string $term): string
    {
        return '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
    }

    /** Human label for a group key. */
    public static function groupLabel(string $key): string
    {
        return [
            'courses' => 'Courses',
            'pages' => 'Pages',
            'categories' => 'Categories',
            'instructors' => 'Instructors',
        ][$key] ?? ucfirst($key);
    }
}
