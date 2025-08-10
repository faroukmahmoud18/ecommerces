<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
        'vendor_id',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the vendor that owns the coupon.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the orders that use the coupon.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_coupon');
    }

    /**
     * Get the images for the coupon.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Scope a query to only include active coupons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope a query to only include coupons for a specific vendor.
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope a query to only include coupons that can be applied to an order.
     */
    public function scopeApplicable($query, $orderAmount = 0)
    {
        return $query->active()
            ->where(function ($query) use ($orderAmount) {
                $query->whereNull('min_order_amount')
                    ->orWhere('min_order_amount', '<=', $orderAmount);
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhere('used_count', '<', 'max_uses');
            });
    }

    /**
     * Check if the coupon is valid for an order.
     */
    public function isValidForOrder($orderAmount = 0)
    {
        return $this->is_active
            && ($this->start_date === null || $this->start_date <= now())
            && ($this->end_date === null || $this->end_date >= now())
            && ($this->min_order_amount === null || $this->min_order_amount <= $orderAmount)
            && ($this->max_uses === null || $this->used_count < $this->max_uses);
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsage()
    {
        $this->used_count++;
        $this->save();
    }

    /**
     * Decrement the usage count.
     */
    public function decrementUsage()
    {
        if ($this->used_count > 0) {
            $this->used_count--;
            $this->save();
        }
    }
}
