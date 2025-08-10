<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'order_id',
        'amount',
        'fee',
        'net_amount',
        'payment_method',
        'status',
        'transaction_id',
        'payout_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payout_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the vendor that owns the payout.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the order that owns the payout.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Status labels
     */
    public $statusLabels = [
        'pending' => 'قيد الانتظار',
        'processing' => 'قيد المعالجة',
        'completed' => 'مكتمل',
        'failed' => 'فشل',
        'cancelled' => 'ملغي',
    ];

    /**
     * Scope a query to only include pending payouts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed payouts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed payouts.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute()
    {
        return $this->statusLabels[$this->status] ?? $this->status;
    }

    /**
     * Scope a query to only include active payouts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include cancelled payouts.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include payouts by vendor.
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope a query to only include payouts by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payout_date', [$startDate, $endDate]);
    }

    /**
     * Get the status in Arabic.
     */
    public function getStatusInArabicAttribute()
    {
        return $this->statusLabels[$this->status] ?? $this->status;
    }

    /**
     * Check if the payout can be cancelled.
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Calculate total payouts for a vendor.
     */
    public static function getTotalPayoutsByVendor($vendorId)
    {
        return self::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Calculate pending payouts for a vendor.
     */
    public static function getPendingPayoutsByVendor($vendorId)
    {
        return self::where('vendor_id', $vendorId)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');
    }

    /**
     * Calculate total payouts in a date range.
     */
    public static function getTotalPayoutsByDateRange($startDate, $endDate)
    {
        return self::whereBetween('payout_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Calculate total fees in a date range.
     */
    public static function getTotalFeesByDateRange($startDate, $endDate)
    {
        return self::whereBetween('payout_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('fee');
    }
}
