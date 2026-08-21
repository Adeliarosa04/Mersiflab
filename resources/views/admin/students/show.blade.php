@extends('layouts.admin')

@section('title', 'Student Detail - ' . ($student->name ?? 'N/A'))

@section('content')
@php
    $isBanned = $student->isBanned();
    $isOnline = $student->last_login_at ? $student->last_login_at->diffInMinutes(now()) <= 15 : false;
    $enrolled = $enrolled ?? collect();
    $totalModulesCompleted = $totalModulesCompleted ?? 0;
    $activities = $activities ?? collect();
    $completions = $completions ?? collect();
    
    // Subscription status
    $isSubscriber = $student->is_subscriber;
    $subscriptionPlan = strtolower($student->subscription_plan ?? 'none');
    $isExpired = $student->subscription_expires_at && $student->subscription_expires_at->isPast();
    
    $activityItems = collect();
    foreach ($activities as $log) {
        $activityItems->push((object)['type' => 'log', 'action' => $log->action ?? '', 'desc' => $log->description ?? '', 'at' => $log->created_at]);
    }
    foreach ($enrolled as $c) {
        $enrolledAt = $c->pivot->enrolled_at ?? null;
        if ($enrolledAt) {
            $activityItems->push((object)['type' => 'enroll', 'action' => 'Enrolled in class', 'desc' => $c->name, 'at' => \Carbon\Carbon::parse($enrolledAt)]);
        }
    }
    foreach ($completions as $comp) {
        $activityItems->push((object)['type' => 'complete', 'action' => 'Completed module', 'desc' => ($comp->module_title ?? 'Module') . ' in ' . ($comp->class_name ?? 'Class'), 'at' => \Carbon\Carbon::parse($comp->completed_at)]);
    }
    $activityItems = $activityItems->sortByDesc('at')->values()->take(30);
@endphp

<div class="page-title">
    <div>
        <h1>Student Detail</h1>
        <p style="color: #828282; margin: 5px 0 0 0; font-size: 14px;">Student information, enrolled classes, progress & activities</p>
    </div>
</div>

<!-- Back to Students Button -->
<div style="margin-bottom: 20px; text-align: right;">
    <a href="{{ back_url(route('admin.students.index')) }}" 
       style="background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; white-space: nowrap;"
       onmouseover="this.style.background='#545b62'; this.style.transform='translateY(-1px)';" 
       onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>
</div>

<!-- Student Header -->
<div class="card-content mb-4 student-header-card">
    <div class="student-header-inner">
        <div class="student-header-main">
            <div class="student-avatar-lg">
                @if($student->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->avatar))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($student->avatar) }}" alt="{{ $student->name }}" class="student-avatar-img-lg" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <i class="fas fa-user-graduate student-avatar-fallback-lg" style="display: none;"></i>
                @else
                    <i class="fas fa-user-graduate"></i>
                @endif
            </div>
            <div>
                <h2 class="student-title">{{ $student->name }}</h2>
                <div class="student-meta">
                    <span>ID: #{{ $student->id }}</span>
                    <span>{{ $student->email }}</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($isOnline)
                            <span class="badge" style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i> Online
                            </span>
                        @else
                            <span class="badge" style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i> Offline
                            </span>
                        @endif
                        <span class="student-status-badge {{ $isBanned ? 'status-banned' : 'status-active' }}">{{ $isBanned ? 'Banned' : 'Active' }}</span>

                        {{-- Subscription badge (students only) --}}
                        @if($isSubscriber && $subscriptionPlan !== 'none')
                            @if($subscriptionPlan === 'premium')
                                <!-- Premium Subscriber -->
                                <div style="display:inline-block; margin-left:6px;">
                                    <span class="badge" style="background: linear-gradient(135deg, #6a1b9a, #8e24aa); color: white; padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; box-shadow: 0 2px 4px rgba(106, 27, 154, 0.3);">
                                        <i class="fas fa-crown" style="margin-right:6px;"></i>
                                        Premium Subscriber
                                        <small style="color: rgba(255,255,255,0.9); font-weight: 500; margin-left:8px;">{{ $student->subscription_expires_at ? $student->subscription_expires_at->format('d M Y') : 'Unlimited' }}</small>
                                    </span>
                                    @if($isExpired)
                                        <div style="margin-top:6px;">
                                            <span class="badge" style="background: #ff9800; color: white; padding: 3px 8px; border-radius: 8px; font-size: 10px; font-weight: 500;">
                                                <i class="fas fa-exclamation-triangle" style="margin-right:3px;"></i>Expired
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div style="font-size:13px; color:#6a1b9a; font-weight:600; margin-top:8px;">
                                    <i class="fas fa-infinity" style="margin-right:4px;"></i>Access to ALL courses (Standard + Premium)
                                </div>
                            @else
                                <!-- Standard Subscriber -->
                                <div style="display:inline-block; margin-left:6px;">
                                    <span class="badge" style="background: linear-gradient(135deg, #2e7d32, #43a047); color: white; padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; box-shadow: 0 2px 4px rgba(46, 125, 50, 0.3);">
                                        <i class="fas fa-star" style="margin-right:6px;"></i>
                                        Standard Subscriber
                                        <small style="color: rgba(255,255,255,0.9); font-weight: 500; margin-left:8px;">{{ $student->subscription_expires_at ? $student->subscription_expires_at->format('d M Y') : 'Unlimited' }}</small>
                                    </span>
                                    @if($isExpired)
                                        <div style="margin-top:6px;">
                                            <span class="badge" style="background: #ff9800; color: white; padding: 3px 8px; border-radius: 8px; font-size: 10px; font-weight: 500;">
                                                <i class="fas fa-exclamation-triangle" style="margin-right:3px;"></i>Expired
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div style="font-size:13px; color:#2e7d32; font-weight:600; margin-top:8px;">
                                    <i class="fas fa-graduation-cap" style="margin-right:4px;"></i>Access to Standard courses only
                                </div>
                            @endif
                        @else
                            <!-- Not Subscribed -->
                            <span class="badge" style="background: linear-gradient(135deg, #64748b, #475569); color: white; padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-left: 6px;">
                                <i class="fas fa-user" style="margin-right:6px;"></i>
                                Free User
                            </span>
                            <div style="font-size:13px; color:#64748b; font-weight:500; margin-top:8px; margin-left:6px;">
                                <i class="fas fa-lock" style="margin-right:4px;"></i>No subscription access
                            </div>
                        @endif
                    </div>
                </div>
                <div class="student-meta">
                    <span>Joined: {{ $student->created_at ? $student->created_at->format('M d, Y') : '—' }}</span>
                    <span>Enrolled: {{ $enrolled->count() }} classes</span>
                    <span>Completed: {{ $totalModulesCompleted }} modules</span>
                </div>
            </div>
        </div>
        <div class="student-actions-header" style="margin-left:auto; display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                <label style="font-size:13px; margin:0; font-weight:600;">Quick Subscription:</label>
                <div style="display:flex; gap:6px;">
                    <form action="{{ route('admin.students.updateSubscription', $student->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="plan" value="standard">
                        <input type="hidden" name="expires_at" value="{{ \Carbon\Carbon::now()->addMonth()->format('Y-m-d') }}">
                        <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, #2e7d32, #43a047); color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 500;">
                            <i class="fas fa-star"></i> Set Standard
                        </button>
                    </form>
                    <form action="{{ route('admin.students.updateSubscription', $student->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="plan" value="premium">
                        <input type="hidden" name="expires_at" value="{{ \Carbon\Carbon::now()->addMonth()->format('Y-m-d') }}">
                        <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, #6a1b9a, #8e24aa); color: white; border: none; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 500;">
                            <i class="fas fa-crown"></i> Set Premium
                        </button>
                    </form>
                    <form action="{{ route('admin.students.updateSubscription', $student->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="plan" value="none">
                        <button type="submit" class="btn btn-sm btn-secondary" style="padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 500;">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </form>
                </div>
            </div>
            <form action="{{ route('admin.students.updateSubscription', $student->id) }}" method="POST" style="display:flex; gap:8px; align-items:center; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                @csrf
                <label style="font-size:13px; margin:0; font-weight:600;">Custom:</label>
                <select name="plan" class="form-select form-select-sm" style="width:140px;">
                    <option value="none" {{ !$isSubscriber || $subscriptionPlan === 'none' ? 'selected' : '' }}>None</option>
                    <option value="standard" {{ $subscriptionPlan === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="premium" {{ $subscriptionPlan === 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
                <input type="date" name="expires_at" value="{{ $student->subscription_expires_at ? $student->subscription_expires_at->format('Y-m-d') : '' }}" class="form-control form-control-sm" style="width:140px;" placeholder="Expires (optional)">
                <button class="btn btn-sm btn-primary" type="submit">Apply</button>
            </form>
        </div>
    </div>
</div>

<!-- Student Details -->
<div class="card-content mb-4">
    <h3 class="panel-title">Student Information</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Phone</span>
            <strong>{{ $student->telephone ?? 'Not set' }}</strong>
        </div>
        @if($student->bio || $student->biography)
            <div class="detail-item detail-full">
                <span class="detail-label">Bio</span>
                <p class="detail-bio">{{ $student->bio ?? $student->biography }}</p>
            </div>
        @endif
    </div>
</div>

<!-- Enrolled Classes -->
<div class="card-content mb-4">
    <h3 class="panel-title">Enrolled Classes</h3>
    @if($enrolled->count() > 0)
        <div class="enrolled-list">
            @foreach($enrolled as $c)
                @php
                    $pivot = $c->pivot;
                    $progress = (float) ($pivot->progress ?? 0);
                    $enrolledAt = $pivot->enrolled_at ?? null;
                    $completedAt = $pivot->completed_at ?? null;
                @endphp
                <div class="enrolled-item">
                    <div class="enrolled-main">
                        <div class="enrolled-icon"><i class="fas fa-book"></i></div>
                        <div>
                            <div class="enrolled-name">{{ $c->name }}</div>
                            <div class="enrolled-meta">
                                <span>Instructor: {{ $c->teacher->name ?? 'N/A' }}</span>
                                <span>Enrolled: {{ $enrolledAt ? \Carbon\Carbon::parse($enrolledAt)->format('M d, Y') : '—' }}</span>
                                @if($completedAt)
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Completed {{ \Carbon\Carbon::parse($completedAt)->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="enrolled-progress-wrap">
                        <div class="enrolled-progress-bar"><div class="enrolled-progress-fill" style="width: {{ min(100, max(0, (float)$progress)) }}%;"></div></div>
                        <span class="enrolled-progress-pct">{{ number_format((float)$progress, 1) }}%</span>
                    </div>
                    <a href="{{ route('admin.courses.approval', $c->id) }}" class="btn-enrolled-link" title="View in Course Approval"><i class="fas fa-external-link-alt"></i></a>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <p>This student hasn't purchased/enrolled in any classes yet</p>
        </div>
    @endif
</div>

<!-- Purchase History -->
<div class="card-content mb-4">
    <h3 class="panel-title">Purchase History</h3>
    @if(isset($purchases) && $purchases->count() > 0)
        <div class="purchase-list">
            @foreach($purchases as $purchase)
                <div class="purchase-item">
                    <div class="purchase-main">
                        <div class="purchase-icon">
                            @if($purchase->status === 'success')
                                <i class="fas fa-check-circle" style="color: #27AE60;"></i>
                            @elseif($purchase->status === 'pending')
                                <i class="fas fa-clock" style="color: #ff9800;"></i>
                            @else
                                <i class="fas fa-times-circle" style="color: #e53935;"></i>
                            @endif
                        </div>
                        <div class="purchase-details">
                            <div class="purchase-name">{{ $purchase->course->name }}</div>
                            <div class="purchase-meta">
                                <span>Code: {{ $purchase->purchase_code }}</span>
                                <span>Amount: Rp{{ number_format($purchase->amount, 0, ',', '.') }}</span>
                                <span>Method: {{ $purchase->payment_method ?? 'N/A' }}</span>
                                <span>Created: {{ $purchase->created_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="purchase-status">
                        <span class="badge bg-{{ $purchase->status_badge }}">
                            @if($purchase->status === 'success')
                                Success
                            @elseif($purchase->status === 'pending')
                                Pending
                            @elseif($purchase->status === 'expired')
                                Expired
                            @else
                                Cancelled
                            @endif
                        </span>
                        @if($purchase->status === 'pending')
                            <form action="{{ route('admin.students.unlock-course', [$student->id, $purchase->id]) }}" method="POST" style="margin-top: 8px;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to unlock this course?')">
                                    <i class="fas fa-unlock"></i> Unlock
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <p>No purchase history found</p>
        </div>
    @endif
</div>

<!-- Subscription Purchases -->
<div class="card-content mb-4" id="subscriptions">
    <h3 class="panel-title">Subscription Purchases</h3>
    @if(isset($subscriptionPurchases) && $subscriptionPurchases->count() > 0)
        <div class="purchase-list">
            @foreach($subscriptionPurchases as $subscription)
                <div class="purchase-item">
                    <div class="purchase-main">
                        <div class="purchase-icon">
                            @if($subscription->status === 'success')
                                <i class="fas fa-check-circle" style="color: #27AE60;"></i>
                            @elseif($subscription->status === 'pending')
                                <i class="fas fa-clock" style="color: #ff9800;"></i>
                            @else
                                <i class="fas fa-times-circle" style="color: #e53935;"></i>
                            @endif
                        </div>
                        <div class="purchase-details">
                            <div class="purchase-name">{{ $subscription->formatted_plan }} Subscription</div>
                            <div class="purchase-meta">
                                <span>Code: {{ $subscription->purchase_code }}</span>
                                <span>Plan: {{ $subscription->formatted_plan }}</span>
                                <span>Amount: {{ $subscription->formatted_final_amount }}</span>
                                <span>Method: {{ $subscription->payment_method ?? 'N/A' }}</span>
                                <span>Created: {{ $subscription->created_at->format('M d, Y H:i') }}</span>
                                @if($subscription->paid_at)
                                    <span>Paid: {{ $subscription->paid_at->format('M d, Y H:i') }}</span>
                                @endif
                                @if($subscription->expires_at)
                                    <span>Expires: {{ $subscription->expires_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="purchase-status">
                        <span class="badge bg-{{ $subscription->status_badge }}">
                            @if($subscription->status === 'success')
                                Active
                            @elseif($subscription->status === 'pending')
                                Pending
                            @elseif($subscription->status === 'expired')
                                Expired
                            @else
                                Cancelled
                            @endif
                        </span>
                        @if($subscription->status === 'pending')
                            <div style="margin-top: 8px; display: flex; gap: 4px;">
                                <form action="{{ route('admin.students.approve-subscription', [$student->id, $subscription->id]) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this subscription?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.students.reject-subscription', [$student->id, $subscription->id]) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this subscription?')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-crown"></i>
            <p>No subscription purchases found</p>
        </div>
    @endif
</div>

<!-- Recent Activities -->
<div class="card-content mb-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 class="panel-title" style="margin: 0;">Recent Activities</h3>
        <a href="{{ route('admin.activities.user', $student->id) }}" 
           class="btn btn-sm" 
           style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 6px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;"
           onmouseover="this.style.background='#1976d2'; this.style.color='white'; this.style.borderColor='#1976d2';" 
           onmouseout="this.style.background='#e3f2fd'; this.style.color='#1976d2'; this.style.borderColor='#90caf9';">
            <i class="fas fa-list"></i> View All Activities
        </a>
    </div>
    @if($activityItems->count() > 0)
        <ul class="activity-list">
            @foreach($activityItems->take(10) as $a)
                <li>
                    <span class="activity-action">
                        <i class="fas fa-circle text-secondary me-2"></i>
                        {{ $a->action }}
                    </span>
                    <span class="activity-desc">{{ $a->desc }}</span>
                    <small class="activity-time">{{ $a->at->format('d M Y, H:i') }}</small>
                </li>
            @endforeach
        </ul>
    @else
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <p>No activities recorded yet</p>
        </div>
    @endif
</div>

<script>
// Auto-refresh for real-time status updates
let refreshInterval;

function refreshStudentStatus() {
    fetch('{{ route("admin.students.show", $student->id) }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse the HTML to extract status information
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Find the status badges in the parsed HTML
        const statusContainer = doc.querySelector('.student-meta div[style*="flex"]');
        const currentStatusContainer = document.querySelector('.student-meta div[style*="flex"]');
        
        if (statusContainer && currentStatusContainer) {
            currentStatusContainer.innerHTML = statusContainer.innerHTML;
        }
    })
    .catch(error => {
        console.log('Status refresh failed:', error);
    });
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Refresh status every 30 seconds
    refreshInterval = setInterval(refreshStudentStatus, 30000);
    
    // Stop refresh when page is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(refreshInterval);
        } else {
            refreshInterval = setInterval(refreshStudentStatus, 30000);
        }
    });
});

// Clean up interval when page unloads
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style>
.student-header-card, .card-content { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.student-header-inner { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; }
.student-header-main { display: flex; align-items: flex-start; gap: 20px; flex-wrap: wrap; }
.student-avatar-lg { width: 64px; height: 64px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.student-avatar-lg i { color: #2e7d32; font-size: 28px; }
.student-avatar-lg .student-avatar-img-lg { width: 100%; height: 100%; object-fit: cover; }
.student-avatar-lg .student-avatar-fallback-lg { display: none; color: #2e7d32; font-size: 28px; }
.student-avatar-lg .student-avatar-fallback-lg[style*="display: flex"] { display: flex !important; }
.student-title { font-size: 24px; font-weight: 700; color: #333; margin: 0 0 8px 0; }
.student-meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 12px; }
.student-meta span { color: #666; font-size: 14px; }
.student-status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.student-status-badge.status-active { background: #d4edda; color: #155724; }
.student-status-badge.status-banned { background: #f8d7da; color: #721c24; }
.student-actions-header .btn { padding: 6px 14px; font-size: 13px; border-radius: 6px; border: none; }
.student-actions-header .btn-ban { background: #fff3e0; color: #f57c00; }
.student-actions-header .btn-unban { background: #e8f5e9; color: #2e7d32; }

.panel-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px; }
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; }
.detail-label { display: block; color: #828282; font-size: 13px; margin-bottom: 4px; }
.detail-item strong { color: #333; font-size: 16px; }
.detail-full { grid-column: 1 / -1; }
.detail-bio { color: #333; font-size: 14px; line-height: 1.6; margin: 8px 0 0 0; }

.enrolled-list { display: flex; flex-direction: column; gap: 12px; }
.enrolled-item { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; padding: 16px; border: 1px solid #e8e8e8; border-radius: 8px; background: #fafafa; }
.enrolled-main { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
.enrolled-icon { width: 44px; height: 44px; background: #e8f5e9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #2e7d32; }
.enrolled-name { font-weight: 600; color: #333; margin-bottom: 4px; }
.enrolled-meta { font-size: 12px; color: #666; display: flex; flex-wrap: wrap; gap: 12px; }
.enrolled-progress-wrap { display: flex; align-items: center; gap: 10px; min-width: 120px; }
.enrolled-progress-bar { flex: 1; height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden; }
.enrolled-progress-fill { height: 100%; background: #2e7d32; border-radius: 4px; transition: width 0.3s; }
.enrolled-progress-pct { font-size: 12px; font-weight: 600; color: #333; white-space: nowrap; }
.btn-enrolled-link { background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; padding: 8px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s; }
.btn-enrolled-link:hover { background: #1976d2; color: white; border-color: #1976d2; }

.activity-list { list-style: none; padding: 0; margin: 0; }
.activity-list li { padding: 12px 0; border-bottom: 1px solid #f0f0f0; display: flex; flex-wrap: wrap; gap: 8px; align-items: baseline; }
.activity-list li:last-child { border-bottom: none; }
.activity-action { font-weight: 600; color: #333; font-size: 13px; }
.activity-desc { color: #666; font-size: 13px; flex: 1; min-width: 0; }
.activity-time { color: #999; font-size: 11px; }

.empty-state { text-align: center; padding: 40px; color: #828282; }
.empty-state i { font-size: 48px; color: #e0e0e0; display: block; margin-bottom: 12px; }

.student-footer-actions { display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; margin-top: 24px; margin-bottom: 24px; }
.student-footer-actions a { padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
.btn-back { background: #6c757d; color: white; }
.btn-back:hover { color: white; opacity: 0.9; }
.btn-dashboard { background: #2F80ED; color: white; }
.btn-dashboard:hover { color: white; opacity: 0.9; }

/* Purchase History Styles */
.purchase-list { display: flex; flex-direction: column; gap: 12px; }
.purchase-item { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; padding: 16px; border: 1px solid #e8e8e8; border-radius: 8px; background: #fafafa; }
.purchase-main { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
.purchase-icon { width: 44px; height: 44px; background: #f5f5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.purchase-details { flex: 1; min-width: 0; }
.purchase-name { font-weight: 600; color: #333; margin-bottom: 4px; }
.purchase-meta { font-size: 12px; color: #666; display: flex; flex-wrap: wrap; gap: 12px; }
.purchase-status { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; flex-shrink: 0; }

@media (max-width: 768px) {
    .student-header-card, .card-content { padding: 16px; }
    .student-header-main { flex-direction: column; align-items: flex-start; }
    .student-title { font-size: 20px; }
    .detail-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .enrolled-item { flex-direction: column; align-items: flex-start; }
    .enrolled-progress-wrap { width: 100%; }
}
@media (max-width: 480px) {
    .detail-grid { grid-template-columns: 1fr; }
    .student-footer-actions { flex-direction: column; }
    .student-footer-actions a { justify-content: center; }
}
</style>
@endsection
