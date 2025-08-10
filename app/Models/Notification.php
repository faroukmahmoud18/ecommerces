<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return $this;
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        if ($this->is_read) {
            $this->update([
                'is_read' => false,
                'read_at' => null,
            ]);
        }

        return $this;
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to only include notifications of a given type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include notifications of given types.
     */
    public function scopeInTypes($query, array $types)
    {
        return $query->whereIn('type', $types);
    }

    /**
     * Get notification type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'order_placed' => 'تم إنشاء طلب',
            'order_shipped' => 'تم شحن الطلب',
            'order_delivered' => 'تم توصيل الطلب',
            'payment_completed' => 'تم تأكيد الدفع',
            'payment_failed' => 'فشل الدفع',
            'refund_processed' => 'تمت معالجة الإرجاع',
            'vendor_approved' => 'تم قبول البائع',
            'vendor_rejected' => 'تم رفض البائع',
            'password_reset' => 'إعادة تعيين كلمة المرور',
            'account_verification' => 'تحقق الحساب',
            'new_message' => 'رسالة جديدة',
            'promotion' => 'عروض خاصة',
            'system_alert' => 'تنبيه نظام',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute()
    {
        $icons = [
            'order_placed' => 'fa-shopping-cart',
            'order_shipped' => 'fa-truck',
            'order_delivered' => 'fa-check-circle',
            'payment_completed' => 'fa-credit-card',
            'payment_failed' => 'fa-times-circle',
            'refund_processed' => 'fa-undo',
            'vendor_approved' => 'fa-check',
            'vendor_rejected' => 'fa-times',
            'password_reset' => 'fa-key',
            'account_verification' => 'fa-user-check',
            'new_message' => 'fa-envelope',
            'promotion' => 'fa-tag',
            'system_alert' => 'fa-exclamation-triangle',
        ];

        return $icons[$this->type] ?? 'fa-bell';
    }

    /**
     * Get notification color based on type
     */
    public function getColorAttribute()
    {
        $colors = [
            'order_placed' => 'primary',
            'order_shipped' => 'info',
            'order_delivered' => 'success',
            'payment_completed' => 'primary',
            'payment_failed' => 'danger',
            'refund_processed' => 'warning',
            'vendor_approved' => 'success',
            'vendor_rejected' => 'danger',
            'password_reset' => 'info',
            'account_verification' => 'info',
            'new_message' => 'primary',
            'promotion' => 'warning',
            'system_alert' => 'danger',
        ];

        return $colors[$this->type] ?? 'secondary';
    }

    /**
     * Get URL for notification action
     */
    public function getActionUrlAttribute()
    {
        if (!$this->data || !isset($this->data['action_url'])) {
            return null;
        }

        return $this->data['action_url'];
    }

    /**
     * Check if notification has an action
     */
    public function hasAction()
    {
        return !empty($this->action_url);
    }

    /**
     * Get formatted notification data
     */
    public function getFormattedDataAttribute()
    {
        if (!$this->data) {
            return [];
        }

        $formatted = [];

        foreach ($this->data as $key => $value) {
            if (is_array($value)) {
                $formatted[$key] = $value;
            } else {
                $formatted[$key] = htmlspecialchars($value);
            }
        }

        return $formatted;
    }

    /**
     * Get notification time in a human-readable format
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notifications by user with pagination
     */
    public static function getUserNotifications($userId, $limit = 10, $type = null)
    {
        $query = static::where('user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->latest()->paginate($limit);
    }

    /**
     * Get unread notifications count for user
     */
    public static function getUnreadCount($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Delete old notifications
     */
    public static function cleanupOldNotifications($days = 30)
    {
        $cutoffDate = now()->subDays($days);

        return static::where('created_at', '<', $cutoffDate)
            ->delete();
    }
}