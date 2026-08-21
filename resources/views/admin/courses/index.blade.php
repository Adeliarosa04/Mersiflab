@extends('layouts.admin')

@section('title', 'Courses Management')

@section('content')
<div class="page-title" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <div>
        <h1>Courses Management</h1>
    </div>
    <div style="max-width: 350px; width: 100%; margin-top: 0;">
        <input type="text" id="courseSearch" placeholder="Search courses..." value="{{ request('search') }}" style="width: 100%; padding: 10px 15px; border: none; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-radius: 20px; font-size: 13px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; outline: none;" onfocus="this.style.background='white'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.1)';" onblur="this.style.background='rgba(255, 255, 255, 0.8)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.05)';">
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px; margin-bottom: 16px;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; margin-bottom: 16px;">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card-content">
    <div class="card-content-title">
        <span>All Courses ({{ $courses->total() }} total)</span>
        <div>
            {{-- padding-right 36px (setara 2.25rem bawaan .form-select Bootstrap)
                 memberi ruang untuk ikon panah, yang sebelumnya tertimpa teks
                 panjang seperti "Artificial Intelligence (AI)" karena shorthand
                 "padding: 6px 12px" menimpa padding-right bawaan Bootstrap.
                 min-width menjaga kotak tetap lega, w-auto tetap membiarkannya
                 melebar mengikuti pilihan terpanjang. --}}
            <select id="categoryFilter" class="form-select d-inline w-auto" style="font-size: 13px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 6px 36px 6px 12px; min-width: 220px; text-overflow: ellipsis;" onchange="filterByCategory(this.value)">
                <option value="all" {{ request('category') == 'all' || !request('category') ? 'selected' : '' }}>Filter by Category: All</option>
                @foreach(\App\Models\ClassModel::getAvailableCategories() as $key => $label)
                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Instructor</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Category</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Chapters</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Modules</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Sales</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Created</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr style="border-bottom: 1px solid #f8f9fa;">
                        <td style="padding: 16px 8px; vertical-align: middle; color: #333333; font-weight: 500;">{{ $loop->iteration + ($courses->currentPage() - 1) * $courses->perPage() }}</td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; position: relative;">
                                    @if($course->image)
                                        <img src="{{ asset('storage/' . $course->image) }}" 
                                             alt="{{ $course->name }}" 
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
                                    <div style="font-weight: 600; color: #333333; margin-bottom: 2px; font-size: 14px; {{ !$course->is_published ? 'opacity: 0.6;' : '' }}">{{ $course->name }}</div>
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
                        <td style="padding: 16px 8px; vertical-align: middle; color: #333333; font-weight: 500;">{{ $course->chapters_count ?? 0 }}</td>
                        <td style="padding: 16px 8px; vertical-align: middle; color: #333333; font-weight: 500;">{{ $course->modules_count ?? 0 }}</td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <span class="badge" style="background: #e8f5e9; color: #2e7d32; font-size: 11px; padding: 4px 10px; border-radius: 4px; font-weight: 500;">
                                {{ $course->purchases_count ?? 0 }}
                            </span>
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            @php
                                $statusLabel = $course->status_label;
                            @endphp
                            <span class="badge" style="background: 
                                @if($course->status === 'published') #d4edda; color: #155724;
                                @elseif($course->status === 'pending_approval') #fff3cd; color: #856404;
                                @elseif($course->status === 'rejected') #f8d7da; color: #721c24;
                                @else #e2e3e5; color: #383d41;
                                @endif; font-size: 11px; padding: 4px 10px; border-radius: 4px; font-weight: 500;">
                                {{ $statusLabel['text'] }}
                            </span>
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle; color: #828282; font-size: 12px;">
                            {{ $course->created_at ? $course->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                <!-- View Button (Text Link) -->
                                <a href="{{ route('admin.courses.approval', $course->id) }}" 
                                   style="color: #1976d2; text-decoration: none; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;"
                                   onmouseover="this.style.background='#e3f2fd'" 
                                   onmouseout="this.style.background='transparent'"
                                   title="View & Approve">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                
                                @php
                                    $canPin = !$course->is_featured && (empty($featuredCourseId) || $featuredCourseId == $course->id);
                                @endphp
                                <!-- Feature/Unfeature Button (Icon Only) - Hanya satu course yang boleh featured -->
                                <form action="{{ route('admin.courses.toggleFeature', $course->id) }}" method="POST" style="display: inline;" class="toggle-feature-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm toggle-feature-btn" 
                                            style="background: {{ $course->is_featured ? '#fff8e6' : ($canPin ? '#e9ecef' : '#f0f0f0') }}; color: {{ $course->is_featured ? '#ff8c00' : ($canPin ? '#495057' : '#999') }}; border: none; padding: 4px 8px; font-size: 11px; border-radius: 4px; cursor: {{ $canPin || $course->is_featured ? 'pointer' : 'not-allowed' }}; transition: opacity 0.2s;"
                                            onmouseover="if(this.form.checkValidity()) this.style.opacity='0.8'" 
                                            onmouseout="this.style.opacity='1'"
                                            title="{{ $course->is_featured ? 'Unpin (remove from featured)' : ($canPin ? 'Pin as featured course' : 'Unpin the current featured course first') }}"
                                            {{ !$canPin && !$course->is_featured ? 'disabled' : '' }}>
                                        <i class="fas fa-thumbtack" style="{{ $course->is_featured ? 'transform: rotate(30deg);' : '' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 40px; color: #828282;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                <i class="fas fa-book" style="font-size: 48px; color: #e0e0e0;"></i>
                                <span style="font-size: 14px;">No courses found</span>
                                <p class="text-muted small">Teachers haven't created any courses yet.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($courses->hasPages())
        <div class="pagination-container">
            {{ $courses->links() }}
        </div>
    @endif
</div>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('courseSearch');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            searchTimeout = setTimeout(function() {
                performSearch(searchTerm);
            }, 500); // Wait 500ms after user stops typing
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                performSearch(this.value.trim());
            }
        });
    }
    
    function performSearch(searchTerm) {
        const url = new URL(window.location.href);
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        // Reset to page 1 when searching
        url.searchParams.delete('page');
        
        window.location.href = url.toString();
    }
});

function filterByCategory(category) {
    const url = new URL(window.location.href);
    if (category === 'all') {
        url.searchParams.delete('category');
    } else {
        url.searchParams.set('category', category);
    }
    url.searchParams.delete('page'); // Reset to page 1
    window.location.href = url.toString();
}
</script>

<style>
/* Responsive Design for Mobile */
@media (max-width: 768px) {
    .page-title {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 15px !important;
        margin-bottom: 15px !important;
    }
    
    .page-title > div:first-child {
        order: 1;
    }
    
    .page-title > div:last-child {
        order: 2;
        max-width: 100% !important;
        margin-top: 0 !important;
    }
    
    .card-content-title {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px !important;
    }
    
    .card-content-title > div {
        width: 100% !important;
    }
    
    #categoryFilter {
        width: 100% !important;
        /* Di layar sempit dropdown sudah selebar penuh, jadi min-width
           dilepas supaya tidak memaksa kotak melebihi lebar kontainer.
           Ruang untuk ikon panah (padding-right) tetap dipertahankan. */
        min-width: 0 !important;
    }
    
    /* Table responsive improvements */
    .table-responsive {
        font-size: 12px !important;
    }
    
    .table th, .table td {
        padding: 8px 4px !important;
    }
    
    /* Hide some columns on very small screens */
    @media (max-width: 576px) {
        .table th:nth-child(5),
        .table td:nth-child(5),
        .table th:nth-child(6),
        .table td:nth-child(6) {
            display: none;
        }
    }
}

/* Search bar improvements */
#courseSearch {
    font-family: inherit;
}

#courseSearch::placeholder {
    color: #6c757d;
}

/* Ensure table doesn't break on small screens */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
</style>
@endsection
