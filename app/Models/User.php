<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_banned',
        'is_subscriber',
        'subscription_expires_at',
        'subscription_plan',
        'subscription_started_at',
        'subscription_cancelled_at',
        'google_id',
        'telephone',
        'biography',
        'phone',
        'address',
        'bio',
        'created_by',
        'last_login_at',
        'is_active',
        'avatar',
        'email_verification_token',
        'email_verification_sent_at',
        'email_verified_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'subscription_started_at' => 'datetime',
        'subscription_cancelled_at' => 'datetime',
        'is_banned' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verification_sent_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isSubscriber(): bool
    {
        return $this->is_subscriber &&
               ($this->subscription_expires_at === null ||
                $this->subscription_expires_at > now());
    }

    /**
     * Cek apakah user di-banned oleh admin (untuk teacher).
     */
    public function isBanned(): bool
    {
        return (bool) ($this->is_banned ?? false);
    }

    /**
     * Get classes yang dibuat oleh teacher ini
     */
    public function classes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClassModel::class, 'teacher_id');
    }

    /**
     * Get courses yang di-enroll oleh student ini (classes / class_student)
     */
    public function enrolledClasses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'class_student', 'user_id', 'class_id')
            ->withPivot('progress', 'enrolled_at', 'completed_at')
            ->withTimestamps();
    }

    /**
     * Get notifications untuk user ini
     */
    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    /**
     * Get teacher balance for this teacher
     */
    public function teacherBalance()
    {
        return $this->hasOne(TeacherBalance::class, 'teacher_id');
    }

    /**
     * Get commission settings for this teacher
     */
    public function commissionSettings()
    {
        return $this->hasMany(CommissionSetting::class, 'teacher_id');
    }

    /**
     * Get withdrawal requests for this teacher
     */
    public function withdrawals()
    {
        return $this->hasMany(TeacherWithdrawal::class, 'teacher_id');
    }

    /**
     * Get subscription purchases for this user
     */
    public function subscriptionPurchases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SubscriptionPurchase::class);
    }

    /**
     * Get invoices for this user
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Invoice::class);
    }

    /**
     * Get unread notifications count
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    /**
     * Get notification preferences untuk user ini
     */
    public function notificationPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Get or create notification preferences
     */
    public function getNotificationPreference(): NotificationPreference
    {
        return $this->notificationPreference ?? NotificationPreference::create([
            'user_id' => $this->id,
        ]);
    }

    /**
     * Check if user wants to receive notification of specific type
     */
    public function wantsNotification(string $type): bool
    {
        $preference = $this->getNotificationPreference();
        return $preference->wantsNotification($type);
    }

    /**
     * Get user who created this user
     */
    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get users created by this user
     */
    public function createdUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Get activity logs for this user
     */
    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get formatted last login
     */
    public function getLastLoginAttribute(): string
    {
        return $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Never';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

    /**
     * Get admin role label
     */
    public function getAdminRoleLabel(): string
    {
        if ($this->role !== 'admin') {
            return $this->role;
        }

        return $this->isSuperAdmin() ? 'Super Admin' : 'Admin';
    }

    /**
     * Log activity for this user
     */
    public function logActivity(string $action, string $description): void
    {
        ActivityLog::create([
            'user_id' => $this->id,
            'action' => $action,
            'description' => $description,
        ]);
    }

    /**
     * Get admin permissions for this user
     */
    public function adminPermissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdminPermission::class);
    }

    /**
     * Check if user has specific admin permission
     */
    public function hasAdminPermission(string $permission): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }

        return AdminPermission::hasPermission($this->id, $permission);
    }

    /**
     * Grant admin permission to this user
     */
    public function grantAdminPermission(string $permission, int $grantedBy): void
    {
        AdminPermission::grantPermission($this->id, $permission, $grantedBy);
    }

    /**
     * Revoke admin permission from this user
     */
    public function revokeAdminPermission(string $permission): void
    {
        AdminPermission::revokePermission($this->id, $permission);
    }

    /**
     * Check if user is currently online (based on cache)
     * User is considered online if they have activity within the last 2 minutes
     * 
     * @return bool
     */
    public function getIsOnlineAttribute(): bool
    {
        return \Illuminate\Support\Facades\Cache::has('user-is-online-' . $this->id);
    }

    /**
     * Get all admin permissions for this user
     */
    public function getAdminPermissions(): array
    {
        if (!$this->isAdmin()) {
            return [];
        }

        return AdminPermission::getUserPermissions($this->id);
    }

    /**
     * Get teacher application for this user
     */
    public function teacherApplication(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TeacherApplication::class);
    }

    /**
     * Check if user has pending teacher application
     */
    public function hasPendingTeacherApplication(): bool
    {
        return $this->teacherApplication()->where('status', 'pending')->exists();
    }

    /**
     * Check if user has rejected teacher application
     */
    public function hasRejectedTeacherApplication(): bool
    {
        return $this->teacherApplication()->where('status', 'rejected')->exists();
    }

    /**
     * Check if user is super admin (first admin)
     */
    public function isSuperAdmin(): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }

        $firstAdmin = User::where('role', 'admin')->oldest()->first();
        return $firstAdmin && $firstAdmin->id === $this->id;
    }

    /**
     * Toggle admin active status
     */
    public function toggleActiveStatus(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->is_subscriber && $this->subscription_expires_at && $this->subscription_expires_at > now();
    }

    /**
     * Check if user can access a course via subscription
     * @param string $courseTier The tier of the course ('standard' or 'premium')
     */
    public function canAccessViaPlanTier(string $courseTier): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $subscriptionPlan = strtolower($this->subscription_plan ?? 'standard');
        $courseTier = strtolower($courseTier ?? 'standard');

        // Premium subscribers can access all courses
        if ($subscriptionPlan === 'premium') {
            return true;
        }

        // Standard subscribers can only access standard tier courses
        if ($subscriptionPlan === 'standard' && $courseTier === 'standard') {
            return true;
        }

        return false;
    }

    /**
     * Check if subscription is expiring soon (within 7 days)
     */
    public function isSubscriptionExpiringSoon(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        return $this->subscription_expires_at->diffInDays(now()) <= 7;
    }
}