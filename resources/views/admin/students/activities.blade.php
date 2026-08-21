@extends('layouts.admin')

@section('title', 'Student Activities - ' . ($student->name ?? 'N/A'))

@section('content')
<div class="page-title">
    <div>
        <h1>Student Activities</h1>
        <p style="color: #828282; margin: 5px 0 0 0; font-size: 14px;">All activities performed by {{ $student->name }}</p>
    </div>
    <div>
        <a href="{{ back_url(route('admin.students.show', $student->id)) }}" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 8px 16px; font-size: 13px; border-radius: 6px; color: white; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Student Details
        </a>
    </div>
</div>

<!-- Student Info Header -->
<div class="card-content mb-4">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="width: 48px; height: 48px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-user-graduate" style="color: #2e7d32; font-size: 20px;"></i>
        </div>
        <div>
            <h3 style="margin: 0; color: #333; font-size: 18px;">{{ $student->name }}</h3>
            <p style="margin: 2px 0 0 0; color: #666; font-size: 14px;">{{ $student->email }} • ID: #{{ $student->id }}</p>
        </div>
        <div style="margin-left: auto;">
            <span class="badge" style="padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; {{ $student->isBanned() ? 'background: #f8d7da; color: #721c24;' : 'background: #d4edda; color: #155724;' }}">
                {{ $student->isBanned() ? 'Banned' : 'Active' }}
            </span>
        </div>
    </div>
</div>

<!-- Activities List -->
<div class="card-content">
    <div class="card-content-title">
        All Activities
        <div style="font-size: 12px; color: #828282;">
            {{ $allActivities->count() }} total activities
        </div>
    </div>

    @if($allActivities->count() > 0)
        <div class="activities-timeline">
            @foreach($allActivities as $activity)
                <div class="activity-item" style="padding: 20px 0; border-bottom: 1px solid #f8f9fa;">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                            @if($activity['type'] === 'activity')
                                background: #e3f2fd;
                            @elseif($activity['type'] === 'enrollment')
                                background: #f3e5f5;
                            @else
                                background: #e8f5e8;
                            @endif
                        ">
                            <i class="fas fa-{{ $activity['type'] === 'activity' ? 'clock' : ($activity['type'] === 'enrollment' ? 'user-plus' : 'check-circle') }}" style="
                                @if($activity['type'] === 'activity')
                                    color: #1976d2;
                                @elseif($activity['type'] === 'enrollment')
                                    color: #7b1fa2;
                                @else
                                    color: #388e3c;
                                @endif
                                font-size: 16px;
                            "></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                <span style="font-weight: 500; color: #333; font-size: 14px;">{{ $activity['action'] }}</span>
                                <span class="badge" style="
                                    padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 500;
                                    @if($activity['type'] === 'activity')
                                        background: #e3f2fd; color: #1976d2;
                                    @elseif($activity['type'] === 'enrollment')
                                        background: #f3e5f5; color: #7b1fa2;
                                    @else
                                        background: #e8f5e8; color: #388e3c;
                                    @endif
                                ">
                                    {{ ucfirst($activity['type']) }}
                                </span>
                            </div>
                            <div style="color: #666; font-size: 14px; margin-bottom: 8px;">{{ $activity['description'] }}</div>
                            <div style="font-size: 12px; color: #828282;">
                                <i class="fas fa-clock"></i> {{ $activity['time_ago'] }}
                                <span style="margin-left: 15px;">
                                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($activity['created_at'])->format('d M Y, H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px; color: #828282;">
            <i class="fas fa-history" style="font-size: 48px; color: #e0e0e0; margin-bottom: 15px;"></i>
            <h4 style="margin: 0 0 10px 0; color: #666;">No Activities Found</h4>
            <p style="margin: 0; font-size: 14px;">This student hasn't performed any activities yet.</p>
        </div>
    @endif
</div>

<style>
.activities-timeline {
    max-height: 600px;
    overflow-y: auto;
}

.activities-timeline::-webkit-scrollbar {
    width: 6px;
}

.activities-timeline::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.activities-timeline::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.activities-timeline::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.activity-item:hover {
    background: #f8f9fa;
    margin: 0 -20px;
    padding: 20px !important;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.badge {
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling
    const timeline = document.querySelector('.activities-timeline');
    if (timeline) {
        timeline.scrollTop = 0;
    }

    // Add activity type filtering (optional enhancement)
    const activityItems = document.querySelectorAll('.activity-item');
    activityItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>
@endsection
