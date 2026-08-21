@extends('layouts.app')

@section('title', 'Statistics - Teacher')

@section('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;

        /* Ikon dekoratif di sisi kanan sudah dihapus. Tanpa penyeimbang itu,
           teks rata kiri menyisakan ruang kosong lebar di kanan kartu; label
           dan angka karena itu dipusatkan. Flex kolom menjaga isi tetap di
           tengah secara vertikal walau tinggi kartu disamakan height: 100%
           dan kartu "Total Pendapatan" memakai ukuran font lebih kecil. */
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .stat-card .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2196f3;
        margin-bottom: 0.5rem;
    }

    .chart-placeholder {
        text-align: center;
        padding: 3rem;
        color: #ccc;
    }

    .chart-placeholder i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .performance-table {
        font-size: 0.9rem;
    }

    .progress-bar-custom {
        height: 8px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: #28a745;
        transition: width 0.3s ease;
    }

    .top-courses-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .course-rank-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 16px;
        background: white;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .course-rank-item:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    }

    .course-rank-item.no-sales {
        opacity: 0.7;
        background: #fafafa;
    }

    .course-rank-item.no-sales:hover {
        border-color: #e0e0e0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .rank-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 12px;
        font-weight: bold;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .rank-badge.rank-1 {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #333;
        font-size: 28px;
    }

    .rank-badge.rank-2 {
        background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
        color: #333;
        font-size: 28px;
    }

    .rank-badge.rank-3 {
        background: linear-gradient(135deg, #cd7f32 0%, #e8a76a 100%);
        font-size: 28px;
    }

    .rank-badge.rank-4,
    .rank-badge.rank-5,
    .rank-badge.rank-6 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-size: 20px;
    }

    .course-rank-content {
        flex: 1;
        min-width: 0;
    }

    .course-rank-title {
        margin: 0 0 8px 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .course-rank-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .stat-item {
        font-size: 13px;
        color: #666;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
    }

    .stat-item i {
        color: #667eea;
    }

    .course-progress {
        background-color: #f0f0f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .course-progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.5s ease;
    }

    @media (max-width: 768px) {
        .course-rank-item {
            flex-direction: column;
            gap: 12px;
        }

        .rank-badge {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        .rank-badge.rank-1,
        .rank-badge.rank-2,
        .rank-badge.rank-3 {
            font-size: 24px;
        }

        .course-rank-stats {
            gap: 12px;
        }

        .stat-item {
            font-size: 12px;
        }
    }
</style>
@endsection

@section('content')
<section class="profile-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                @include('teacher.partials.sidebar')
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-content">
                    <div class="profile-header mb-4">
                        <h2 class="profile-title">Statistics & Analytics</h2>
                        <p class="profile-subtitle">Track your teaching performance and student engagement</p>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Courses</div>
                                <div class="stat-value">{{ $totalCourses }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Chapters</div>
                                <div class="stat-value">{{ $totalChapters }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Modules</div>
                                <div class="stat-value">{{ $totalModules }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Students</div>
                                <div class="stat-value">{{ $totalStudents }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Enrollments</div>
                                <div class="stat-value">{{ $totalEnrollments }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Avg. Completion Rate</div>
                                <div class="stat-value">{{ number_format($avgCompletionRate, 1) }}%</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Published Courses</div>
                                <div class="stat-value">{{ $totalCourses }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-label">Total Pendapatan</div>
                                <div class="stat-value" style="font-size: 1.5rem;">Rp{{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row mb-4">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow-sm border-0" style="border-radius: 10px;">
                                <div class="card-header bg-light border-0 p-4" style="border-radius: 10px 10px 0 0;">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-line me-2"></i>Enrollment Trend (Last 6 Months)
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    @if($enrollmentTrend->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Month</th>
                                                        <th>Enrollments</th>
                                                        <th>Visual</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($enrollmentTrend as $trend)
                                                        @php
                                                            $maxCount = $enrollmentTrend->max('count');
                                                            $percentage = $maxCount > 0 ? ($trend->count / $maxCount) * 100 : 0;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ date('F Y', strtotime($trend->month . '-01')) }}</td>
                                                            <td><strong>{{ $trend->count }}</strong></td>
                                                            <td>
                                                                <div class="progress-bar-custom" style="width: 200px;">
                                                                    <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="chart-placeholder">
                                            <i class="fas fa-chart-bar"></i>
                                            <p class="mt-3 text-muted">No enrollment data available yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Stats Section -->
                    <div class="row mb-4">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow-sm border-0" style="border-radius: 10px;">
                                <div class="card-header bg-light border-0 p-4" style="border-radius: 10px 10px 0 0;">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>Courses Stats
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    @php
                                        // Build course stats with purchase data
                                        $courseStats = $classes->map(function($course) use ($purchases) {
                                            $coursePurchases = $purchases->where('class_id', $course->id)->where('status', 'success');
                                            return [
                                                'course' => $course,
                                                'count' => $coursePurchases->count(),
                                                'revenue' => $coursePurchases->sum('amount')
                                            ];
                                        })
                                        ->sortByDesc('count')
                                        ->values();
                                        
                                        // Get max count for progress bar
                                        $maxCount = $courseStats->max('count') ?? 1;
                                    @endphp

                                    @if($courseStats->count() > 0)
                                        <div class="top-courses-list">
                                            @foreach($courseStats as $index => $stat)
                                                <div class="course-rank-item {{ $stat['count'] == 0 ? 'no-sales' : '' }}">
                                                    <div class="rank-badge rank-{{ min($index + 1, 6) }}">
                                                        @if($index === 0 && $stat['count'] > 0)
                                                            <i class="fas fa-crown"></i>
                                                        @elseif($index === 1 && $stat['count'] > 0)
                                                            <i class="fas fa-medal"></i>
                                                        @elseif($index === 2 && $stat['count'] > 0)
                                                            <i class="fas fa-award"></i>
                                                        @else
                                                            {{ $index + 1 }}
                                                        @endif
                                                    </div>
                                                    <div class="course-rank-content">
                                                        <h6 class="course-rank-title">{{ $stat['course']->name ?? 'Course' }}</h6>
                                                        <div class="course-rank-stats">
                                                            <span class="stat-item">
                                                                <i class="fas fa-user-check me-1"></i>
                                                                {{ $stat['count'] }} pembeli
                                                            </span>
                                                            <span class="stat-item">
                                                                <i class="fas fa-money-bill-wave me-1"></i>
                                                                Rp {{ number_format($stat['revenue'], 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                        <div class="progress course-progress" style="height: 6px;">
                                                            <div class="progress-bar course-progress-bar" style="width: {{ $maxCount > 0 ? ($stat['count'] / $maxCount) * 100 : 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p class="mb-0">You haven't created any course yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Performance Table -->
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow-sm border-0" style="border-radius: 10px;">
                                <div class="card-header bg-light border-0 p-4" style="border-radius: 10px 10px 0 0;">
                                    <h5 class="mb-0">
                                        <i class="fas fa-graduation-cap me-2"></i>Student Performance
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table class="table table-hover performance-table mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Course</th>
                                                    <th>Progress</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($studentPerformance as $performance)
                                                    <tr>
                                                        <td>{{ $performance->student_name }}</td>
                                                        <td>{{ Str::limit($performance->course_name, 30) }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="progress-bar-custom me-2" style="width: 100px;">
                                                                    <div class="progress-fill" style="width: {{ $performance->progress }}%"></div>
                                                                </div>
                                                                <span class="small">{{ number_format($performance->progress, 1) }}%</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($performance->completed_at)
                                                                <span class="badge bg-success">Completed</span>
                                                            @elseif($performance->progress >= 50)
                                                                <span class="badge bg-info">In Progress</span>
                                                            @else
                                                                <span class="badge bg-warning">Started</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4 text-muted">
                                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                                            <p class="mb-0">No student data available yet</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
