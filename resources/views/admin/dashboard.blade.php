@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="page-title">
    <h1>Welcome, {{ auth()->user()->name }}!</h1>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-12 col-md-4 mb-3">
        <div class="stat-card-modern stat-card-teacher" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Blue Theme) -->
                <div class="stat-icon-container stat-icon-teacher-bg me-3" style="width: 70px; height: 70px; background: #e3f2fd; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-chalkboard-user" style="font-size: 2.5rem; color: #1976d2;"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1">
                    <div class="stat-label" style="font-size: 12px; color: #828282; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">Total Teacher</div>
                    <div class="stat-value counter" data-count="{{ $totalTeachers ?? 0 }}" style="font-size: 2rem; font-weight: 700; color: #333333; line-height: 1.2;">0</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="stat-card-modern stat-card-student" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Green Theme) -->
                <div class="stat-icon-container stat-icon-student-bg me-3" style="width: 70px; height: 70px; background: #e8f5e9; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-users" style="font-size: 2.5rem; color: #27AE60;"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1">
                    <div class="stat-label" style="font-size: 12px; color: #828282; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">Total Student</div>
                    <div class="stat-value counter" data-count="{{ $totalStudents ?? 0 }}" style="font-size: 2rem; font-weight: 700; color: #333333; line-height: 1.2;">0</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="stat-card-modern stat-card-course" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Red/Orange Theme) -->
                <div class="stat-icon-container stat-icon-course-bg me-3" style="width: 70px; height: 70px; background: #fff3e0; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-book" style="font-size: 2.5rem; color: #f57c00;"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1">
                    <div class="stat-label" style="font-size: 12px; color: #828282; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">Total Course</div>
                    <div class="stat-value counter" data-count="{{ $totalCourses ?? 0 }}" style="font-size: 2rem; font-weight: 700; color: #333333; line-height: 1.2;">0</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Section -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card-content">
            <div class="card-content-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Recent Activity</span>
                <a href="{{ route('admin.activities.index') }}" style="font-size: 13px; color: #2F80ED; text-decoration: none; font-weight: 500; padding: 4px 12px; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='transparent'">
                    View All <i class="fas fa-arrow-right" style="font-size: 11px; margin-left: 4px;"></i>
                </a>
            </div>
            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                @forelse($activities ?? [] as $activity)
                    <div class="list-group-item" style="border: none; border-bottom: 1px solid #f0f0f0; padding: 12px 0;">
                        <div class="d-flex align-items-start gap-3">
                            <div style="width: 40px; height: 40px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-circle" style="font-size: 8px; color: #2F80ED;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div style="font-size: 13px; color: #333333; margin-bottom: 4px; line-height: 1.4;">
                                    <strong style="font-weight: 600;">{{ $activity->user->name ?? 'System' }}</strong> {{ $activity->description }}
                                </div>
                                <div style="font-size: 12px; color: #828282;">
                                    <i class="far fa-clock me-1"></i>{{ $activity->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center" style="border: none; padding: 40px; color: #828282;">
                        <i class="fas fa-inbox" style="font-size: 48px; color: #e0e0e0; margin-bottom: 10px;"></i>
                        <p style="font-size: 14px; margin: 0;">No recent activities</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Top Course Table -->
<div class="card-content" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <div class="card-content-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 18px; font-weight: 600; color: #333333; margin: 0;">Top Course</h3>
        <div>
            {{-- Sama seperti filter kategori di admin/courses/index.blade.php:
                 padding-right diberi ruang untuk ikon panah agar teks panjang
                 seperti "Artificial Intelligence (AI)" tidak menimpanya. --}}
            <select id="categoryFilter" class="form-select d-inline w-auto" style="font-size: 13px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 6px 36px 6px 12px; min-width: 220px; text-overflow: ellipsis;">
                <option value="">Filter by Category: All</option>
                @php
                    $uniqueCategories = collect($topCourses ?? [])->pluck('category')->filter()->unique();
                    $categories = \App\Models\ClassModel::getAvailableCategories();
                @endphp
                @foreach($uniqueCategories as $categoryKey)
                    <option value="{{ $categoryKey }}">{{ $categories[$categoryKey] ?? 'Uncategorized' }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm" style="font-size: 13px; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">#</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Course Name</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Uploaded By</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Category</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Sales</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Price</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Chapters</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Action</th>
                </tr>
            </thead>
            <tbody id="courseTableBody">
                @forelse($topCourses ?? [] as $course)
                    <tr class="course-row" data-category="{{ $course->category ?? '' }}" style="border-bottom: 1px solid #f8f9fa;">
                        <td style="padding: 16px 8px; vertical-align: middle; color: #333333; font-weight: 500;">{{ $loop->iteration }}</td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; position: relative;">
                                    @if($course->image)
                                        <img src="{{ asset('storage/' . $course->image) }}" 
                                             alt="{{ $course->name ?? 'Untitled Course' }}" 
                                             style="width: 100%; height: 100%; object-fit: cover; {{ !$course->is_published ? 'opacity: 0.5; filter: grayscale(100%);' : '' }}"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                            <i class="fas fa-book" style="color: #2F80ED; font-size: 20px;"></i>
                                        </div>
                                    @else
                                        <i class="fas fa-book" style="color: #2F80ED; font-size: 20px;"></i>
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #333333; margin-bottom: 2px; font-size: 14px; {{ !$course->is_published ? 'opacity: 0.6;' : '' }}">{{ $course->name ?? 'Untitled Course' }}</div>
                                    <small style="color: #828282; font-size: 11px;">ID: {{ $course->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column;">
                                <strong style="color: #333333; font-size: 13px; margin-bottom: 2px;">{{ $course->teacher->name ?? 'N/A' }}</strong>
                                <small style="color: #828282; font-size: 11px;">{{ $course->teacher->email ?? 'N/A' }}</small>
                            </div>
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <span class="badge" style="background: #e3f2fd; color: #1976d2; font-size: 11px; padding: 4px 10px; border-radius: 4px; font-weight: 500;">
                                {{ $course->category_name }}
                            </span>
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle; color: #333333; font-weight: 500; font-size: 13px;">
                            {{ number_format($course->purchases_count ?? 0, 0, ',', '.') }}
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle; color: #333333; font-weight: 500; font-size: 13px;">
                            Rp {{ number_format($course->price ?? 0, 0, ',', '.') }}
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle; color: #333333; font-weight: 500; font-size: 13px;">
                            {{ $course->sections_count ?? $course->chapters_count ?? 0 }}
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                <!-- View Button (Text Link) -->
                                <a href="{{ route('admin.courses.approval', $course->id) }}" 
                                   style="color: #1976d2; text-decoration: none; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;"
                                   onmouseover="this.style.background='#e3f2fd'" 
                                   onmouseout="this.style.background='transparent'"
                                   title="View & Moderate">
                                    View
                                </a>
                                <!-- Delete Button -->
                                <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" style="display: inline;" class="delete-course-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm delete-course-btn" 
                                            style="background: #ffebee; color: #c62828; border: none; padding: 4px 8px; font-size: 11px; border-radius: 4px; cursor: pointer; transition: opacity 0.2s;"
                                            onmouseover="this.style.opacity='0.8'" 
                                            onmouseout="this.style.opacity='1'"
                                            title="Delete Course"
                                            onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 40px; color: #828282;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                <i class="fas fa-book" style="font-size: 48px; color: #e0e0e0;"></i>
                                <span style="font-size: 14px;">No courses found</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // Wait for DOM to load
    document.addEventListener('DOMContentLoaded', function() {
        // Count-Up Animation for Stat Cards
        function animateCountUp(element) {
            const target = parseInt(element.getAttribute('data-count')) || 0;
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps
            let current = 0;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        }

        // Initialize count-up animation for all stat cards with class "counter"
        const statValues = document.querySelectorAll('.counter[data-count]');
        statValues.forEach((element, index) => {
            // Stagger animation start for visual effect
            setTimeout(() => {
                animateCountUp(element);
            }, index * 200);
        });

        // Category Filter Functionality
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function() {
                const selectedCategory = this.value;
                const rows = document.querySelectorAll('.course-row');
                
                rows.forEach(row => {
                    const rowCategory = row.getAttribute('data-category');
                    if (selectedCategory === '' || rowCategory === selectedCategory) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

    });
</script>
@endsection
