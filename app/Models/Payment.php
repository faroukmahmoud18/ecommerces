
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'amount',
        'payment_method',
        'payment_status',
        'transaction_id',
        'gateway_response',
        'fee',
        'currency',
        'paid_at',
        'notes',
        'is_active',
        'gateway',
        'type',
        'refunded_amount',
        'failure_reason',
        'gateway_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'gateway_data' => 'array',
        'paid_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the vendor that owns the payment.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Payment status labels
     */
    public $statusLabels = [
        'pending' => 'قيد الانتظار',
        'paid' => 'تم الدفع',
        'partially_paid' => 'تم الدفع جزئياً',
        'refunded' => 'تم الإرجاع',
        'partially_refunded' => 'تم الإرجاع جزئياً',
        'failed' => 'فشل الدفع',
        'cancelled' => 'ملغي',
    ];

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute()
    {
        return $this->statusLabels[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Check if the payment is successful.
     */
    public function isSuccess()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if the payment is pending.
     */
    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if the payment has failed.
     */
    public function isFailed()
    {
        return $this->payment_status === 'failed';
    }

    /**
     * Scope a query to only include active payments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include refunded payments.
     */
    public function scopeRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }

    /**
     * Scope a query to only include partially refunded payments.
     */
    public function scopePartiallyRefunded($query)
    {
        return $query->where('payment_status', 'partially_refunded');
    }

    /**
     * Scope a query to only include cancelled payments.
     */
    public function scopeCancelled($query)
    {
        return $query->where('payment_status', 'cancelled');
    }

    /**
     * Scope a query to only include payments by vendor.
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope a query to only include payments by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the payment status in Arabic.
     */
    public function getPaymentStatusInArabicAttribute()
    {
        return $this->statusLabels[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Check if the payment can be refunded.
     */
    public function canBeRefunded()
    {
        return in_array($this->payment_status, ['paid', 'partially_paid']);
    }

    /**
     * Calculate total payments for a vendor.
     */
    public static function getTotalPaymentsByVendor($vendorId)
    {
        return self::where('vendor_id', $vendorId)
            ->where('payment_status', 'paid')
            ->sum('amount');
    }

    /**
     * Calculate total fees for a vendor.
     */
    public static function getTotalFeesByVendor($vendorId)
    {
        return self::where('vendor_id', $vendorId)
            ->where('payment_status', 'paid')
            ->sum('fee');
    }

    /**
     * Calculate total payments in a date range.
     */
    public static function getTotalPaymentsByDateRange($startDate, $endDate)
    {
        return self::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->sum('amount');
    }

    /**
     * Calculate total fees in a date range.
     */
    public static function getTotalFeesByDateRange($startDate, $endDate)
    {
        return self::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->sum('fee');
    }

    /**
     * Calculate total refunds in a date range.
     */
    public static function getTotalRefundsByDateRange($startDate, $endDate)
    {
        return self::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'refunded')
            ->sum('amount');
    }
}
