<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'vendor_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_state',
        'customer_country',
        'customer_postal_code',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'transaction_id',
        'shipping_method',
        'shipping_tracking_number',
        'status',
        'notes',
        'processed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancelled_reason',
        'is_active',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor that owns the order.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the order items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for the order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the shipments for the order.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get the reviews for the order.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute()
    {
        return $this->statusLabels[$this->status] ?? $this->status;
    }

    /**
     * Get the payment status label.
     */
    public function getPaymentStatusLabelAttribute()
    {
        return $this->paymentStatusLabels[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Status labels
     */
    public $statusLabels = [
        'pending' => 'قيد الانتظار',
        'confirmed' => 'تم التأكيد',
        'processing' => 'قيد المعالجة',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغي',
        'refunded' => 'تم الإرجاع',
    ];

    /**
     * Payment status labels
     */
    public $paymentStatusLabels = [
        'pending' => 'قيد الانتظار',
        'paid' => 'تم الدفع',
        'partially_paid' => 'تم الدفع جزئياً',
        'refunded' => 'تم الإرجاع',
        'partially_refunded' => 'تم الإرجاع جزئياً',
        'failed' => 'فشل الدفع',
        'cancelled' => 'ملغي',
    ];

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing orders.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include shipped orders.
     */
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    /**
     * Scope a query to only include delivered orders.
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope a query to only include cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include paid orders.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include unpaid orders.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['pending', 'partially_paid']);
    }

    /**
     * Check if the order can be cancelled.
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']);
    }

    /**
     * Check if the order can be refunded.
     */
    public function canBeRefunded()
    {
        return in_array($this->status, ['delivered', 'shipped']);
    }

    /**
     * Check if the order has been paid.
     */
    public function isPaid()
    {
        return in_array($this->payment_status, ['paid', 'partially_paid']);
    }

    /**
     * Get the total quantity of items in the order.
     */
    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get the total amount of items in the order.
     */
    public function getTotalItemsAmountAttribute()
    {
        return $this->items->sum('subtotal');
    }

    /**
     * Scope a query to only include active orders.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include confirmed orders.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include refunded orders.
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope a query to only include orders with pending payment.
     */
    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope a query to only include orders with paid payment.
     */
    public function scopePaymentPaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include orders with failed payment.
     */
    public function scopePaymentFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Get the status in Arabic.
     */
    public function getStatusInArabicAttribute()
    {
        return $this->statusLabels[$this->status] ?? $this->status;
    }

    /**
     * Get the payment status in Arabic.
     */
    public function getPaymentStatusInArabicAttribute()
    {
        return $this->paymentStatusLabels[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Get the latest shipment for the order.
     */
    public function getLatestShipmentAttribute()
    {
        return $this->shipments()->latest()->first();
    }

    /**
     * Get the tracking number for the order.
     */
    public function getTrackingNumberAttribute()
    {
        $latestShipment = $this->latest_shipment;
        return $latestShipment ? $latestShipment->tracking_number : null;
    }

    /**
     * Get the shipping provider for the order.
     */
    public function getShippingProviderAttribute()
    {
        $latestShipment = $this->latest_shipment;
        return $latestShipment ? $latestShipment->carrier : null;
    }

    /**
     * Calculate commission amount for the vendor.
     */
    public function getCommissionAmountAttribute()
    {
        if (!$this->vendor) {
            return 0;
        }

        return $this->total_amount * ($this->vendor->commission_rate / 100);
    }

    /**
     * Calculate net amount for the vendor (after commission).
     */
    public function getNetAmountAttribute()
    {
        return $this->total_amount - $this->commission_amount;
    }

    /**
     * Check if the order belongs to multiple vendors.
     */
    public function hasMultipleVendors()
    {
        $vendorIds = $this->items->pluck('vendor_id')->unique();
        return $vendorIds->count() > 1;
    }

    /**
     * Get vendors for this order.
     */
    public function getVendorsAttribute()
    {
        $vendorIds = $this->items->pluck('vendor_id')->unique();
        return Vendor::whereIn('id', $vendorIds)->get();
    }
}
