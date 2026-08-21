@extends('layouts.admin')

@section('title', 'Class Reviews - ' . ($class->name ?? 'N/A') . ' - ' . ($teacher->name ?? 'N/A'))

@section('content')
<div class="page-title">
    <div>
        <h1>Class Reviews</h1>
        <p style="color: #828282; margin: 5px 0 0 0; font-size: 14px;">
            All reviews for <strong>{{ $class->name ?? 'N/A' }}</strong> by <strong>{{ $teacher->name ?? 'N/A' }}</strong>
        </p>
    </div>
</div>

<!-- Back Button -->
<div class="card-content mb-4">
    <a href="{{ back_url(route('admin.teachers.show', $teacher->id)) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Teacher Detail
    </a>
</div>

<!-- Class Info & Rating Summary -->
<div class="card-content mb-4">
    <div class="row">
        <div class="col-md-6">
            <h3 class="panel-title">Class Information</h3>
            <div class="class-info">
                <p><strong>Class Name:</strong> {{ $class->name ?? 'N/A' }}</p>
                <p><strong>Teacher:</strong> {{ $teacher->name ?? 'N/A' }}</p>
                <p><strong>Total Reviews:</strong> {{ $totalReviews }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <h3 class="panel-title">Rating Summary</h3>
            <div class="rating-summary">
                <div class="d-flex align-items-center gap-4 mb-3">
                    <div class="text-center">
                        <div class="rating-number" style="font-size: 36px; font-weight: 700; color: #333;">
                            {{ number_format($averageRating, 1) }}
                        </div>
                        <div class="rating-stars" style="display: flex; justify-content: center; gap: 2px; margin: 6px 0;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star" style="font-size: 16px; color: {{ $i <= round($averageRating) ? '#ffc107' : '#e0e0e0' }};"></i>
                            @endfor
                        </div>
                        <span style="font-size: 12px; color: #828282;">{{ $totalReviews }} {{ $totalReviews == 1 ? 'review' : 'reviews' }}</span>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        @for($i = 5; $i >= 1; $i--)
                            @php
                                $count = $ratingDistribution[$i] ?? 0;
                                $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100, 1) : 0;
                            @endphp
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="font-size: 12px; color: #333; min-width: 36px;">{{ $i }} <i class="fas fa-star text-warning" style="font-size: 10px;"></i></span>
                                <div style="flex: 1; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden;">
                                    <div style="height: 100%; background: #ffc107; width: {{ $percentage }}%;"></div>
                                </div>
                                <span style="font-size: 11px; color: #828282; min-width: 32px; text-align: right;">{{ $percentage }}%</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reviews List -->
<div class="card-content mb-4">
    <h3 class="panel-title">All Reviews ({{ $reviews->total() }})</h3>
    
    @if($reviews->count() > 0)
        <div class="reviews-list">
            @foreach($reviews as $review)
                <div class="review-card" style="background: white; border: 1px solid #e8e8e8; border-radius: 12px; padding: 20px; margin-bottom: 16px;">
                    <div class="review-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div class="reviewer-info" style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                <span style="color: white; font-weight: 600; font-size: 16px;">{{ strtoupper(substr($review->user->name ?? 'S', 0, 2)) }}</span>
                            </div>
                            <div>
                                <h4 style="font-size: 16px; font-weight: 600; color: #333; margin: 0 0 4px 0;">{{ $review->user->name ?? 'Student' }}</h4>
                                <p style="font-size: 13px; color: #828282; margin: 0;">{{ $review->user->email ?? 'N/A' }}</p>
                                <small style="font-size: 12px; color: #999;">{{ $review->created_at->format('d M Y, H:i') }}</small>
                            </div>
                        </div>
                        <div class="review-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star" style="font-size: 14px; color: {{ $i <= $review->rating ? '#ffc107' : '#e0e0e0' }};"></i>
                            @endfor
                            <span style="font-size: 12px; color: #666; margin-left: 8px;">({{ $review->rating }}/5)</span>
                        </div>
                    </div>
                    @if(!empty($review->comment))
                        <div class="review-comment">
                            <p style="font-size: 14px; color: #333; line-height: 1.6; margin: 0;">{{ $review->comment }}</p>
                        </div>
                    @else
                        <div class="review-comment">
                            <p style="font-size: 14px; color: #999; font-style: italic; margin: 0;">No comment provided</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="pagination-container">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="empty-state" style="text-align: center; padding: 48px 24px; background: #f8f9fa; border-radius: 12px;">
            <i class="fas fa-star" style="font-size: 48px; color: #e0e0e0; margin-bottom: 16px; display: block;"></i>
            <h4 style="color: #828282; font-size: 16px; font-weight: 600; margin-bottom: 8px;">No Reviews Yet</h4>
            <p style="color: #828282; font-size: 14px; margin: 0;">This class hasn't received any reviews yet.</p>
        </div>
    @endif
</div>

<style>
.card-content { 
    background: white; 
    border-radius: 12px; 
    padding: 24px; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
    margin-bottom: 20px;
}

.panel-title { 
    font-size: 18px; 
    font-weight: 700; 
    color: #333; 
    margin-bottom: 20px; 
}

.class-info p {
    margin-bottom: 8px;
    font-size: 14px;
}

.rating-number {
    font-size: 36px;
    font-weight: 700;
    color: #333;
}

.rating-stars {
    display: flex;
    justify-content: center;
    gap: 2px;
    margin: 6px 0;
}

.review-card {
    transition: all 0.2s ease;
}

.review-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.empty-state {
    text-align: center;
    padding: 48px 24px;
    background: #f8f9fa;
    border-radius: 12px;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border: 1px solid #6c757d;
}

.btn-secondary:hover {
    background: #5a6268;
    color: white;
    text-decoration: none;
}

.review-actions a:hover {
    color: white;
    text-decoration: none;
}
</style>
@endsection
