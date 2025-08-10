
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'tracking_number',
        'carrier',
        'status',
        'shipping_method',
        'shipping_cost',
        'weight',
        'dimensions',
        'from_address',
        'to_address',
        'shipped_at',
        'delivered_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'from_address' => 'array',
        'to_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the order that owns the shipment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the vendor that owns the shipment.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the tracking events for the shipment.
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }

    /**
     * Status labels
     */
    public $statusLabels = [
        'pending' => 'قيد الانتظار',
        'picked' => 'تم الاستلام',
        'in_transit' => 'في طريقه',
        'out_for_delivery' => 'في طريق التسليم',
        'delivered' => 'تم التسليم',
        'exception' => 'استثناء',
        'returned' => 'تم الإرجاع',
    ];

    /**
     * Scope a query to only include pending shipments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in transit shipments.
     */
    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    /**
     * Scope a query to only include delivered shipments.
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute()
    {
        return $this->statusLabels[$this->status] ?? $this->status;
    }

    /**
     * Check if the shipment has been delivered.
     */
    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if the shipment is in transit.
     */
    public function isInTransit()
    {
        return in_array($this->status, ['picked', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Add a tracking event to the shipment.
     */
    public function addTrackingEvent($event)
    {
        return $this->trackingEvents()->create($event);
    }

    /**
     * Scope a query to only include active shipments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include picked shipments.
     */
    public function scopePicked($query)
    {
        return $query->where('status', 'picked');
    }

    /**
     * Scope a query to only include out-for-delivery shipments.
     */
    public function scopeOutForDelivery($query)
    {
        return $query->where('status', 'out_for_delivery');
    }

    /**
     * Scope a query to only include exception shipments.
     */
    public function scopeException($query)
    {
        return $query->where('status', 'exception');
    }

    /**
     * Scope a query to only include returned shipments.
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    /**
     * Scope a query to only include shipments by vendor.
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope a query to only include shipments by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the status in Arabic.
     */
    public function getStatusInArabicAttribute()
    {
        return $this->statusLabels[$this->status] ?? $this->status;
    }

    /**
     * Check if the shipment can be tracked.
     */
    public function canBeTracked()
    {
        return !empty($this->tracking_number);
    }

    /**
     * Get the tracking URL for the shipment.
     */
    public function getTrackingUrlAttribute()
    {
        if (!$this->canBeTracked()) {
            return null;
        }

        // This would typically use a mapping of carriers to tracking URLs
        // For now, we'll return a placeholder URL
        return "https://tracking.example.com/?number=" . urlencode($this->tracking_number);
    }

    /**
     * Mark the shipment as delivered.
     */
    public function markAsDelivered($notes = null)
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'notes' => $notes,
        ]);

        // Add a delivered tracking event
        $this->addTrackingEvent([
            'event_code' => 'delivered',
            'event_description' => 'تم التسليم للعميل',
            'event_location' => $this->to_address['city'] ?? null,
            'event_date' => now(),
            'event_time' => now(),
        ]);

        return $this;
    }

    /**
     * Calculate total shipping cost for a vendor.
     */
    public static function getTotalShippingCostByVendor($vendorId)
    {
        return self::where('vendor_id', $vendorId)
            ->where('status', 'delivered')
            ->sum('shipping_cost');
    }

    /**
     * Calculate total shipping cost in a date range.
     */
    public static function getTotalShippingCostByDateRange($startDate, $endDate)
    {
        return self::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->sum('shipping_cost');
    }
}
