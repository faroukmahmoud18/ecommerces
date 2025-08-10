<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'name',
        'description',
        'min_weight',
        'max_weight',
        'min_order_amount',
        'max_order_amount',
        'rate',
        'is_active',
        'priority',
        'free_shipping_threshold',
        'handling_fee',
        'tax_rate',
        'is_default',
    ];

    protected $casts = [
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'handling_fee' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the zone that owns the rate.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    /**
     * Calculate final rate with taxes and handling
     */
    public function calculateFinalRate($orderTotal = 0): float
    {
        // If order amount meets free shipping threshold
        if ($this->free_shipping_threshold && $orderTotal >= $this->free_shipping_threshold) {
            return 0;
        }

        $baseRate = $this->rate;

        // Add handling fee if any
        if ($this->handling_fee > 0) {
            $baseRate += $this->handling_fee;
        }

        // Add tax if any
        if ($this->tax_rate > 0) {
            $baseRate += ($baseRate * $this->tax_rate / 100);
        }

        return round($baseRate, 2);
    }

    /**
     * Get the formatted weight range
     */
    public function getWeightRangeAttribute(): string
    {
        return "{$this->min_weight} كجم - {$this->max_weight} كجم";
    }

    /**
     * Get the formatted order amount range
     */
    public function getOrderAmountRangeAttribute(): string
    {
        if ($this->min_order_amount === null && $this->max_order_amount === null) {
            return "جميع الطلبات";
        }

        if ($this->min_order_amount !== null && $this->max_order_amount === null) {
            return "أكثر من {$this->min_order_amount} ر.س";
        }

        if ($this->min_order_amount === null && $this->max_order_amount !== null) {
            return "حتى {$this->max_order_amount} ر.س";
        }

        return "{$this->min_order_amount} - {$this->max_order_amount} ر.س";
    }

    /**
     * Get appropriate shipping rate for weight and amount
     */
    public static function findRateForWeightAndAmount($zoneId, $weight, $orderAmount)
    {
        return self::where('zone_id', $zoneId)
            ->where('is_active', true)
            ->where(function($query) use ($weight) {
                $query->where('min_weight', '<=', $weight)
                    ->where('max_weight', '>=', $weight);
            })
            ->where(function($query) use ($orderAmount) {
                $query->where(function($q) {
                    $q->whereNull('min_order_amount')
                      ->whereNull('max_order_amount');
                })
                ->orWhere(function($q) use ($orderAmount) {
                    $q->where('min_order_amount', '<=', $orderAmount)
                      ->whereNull('max_order_amount');
                })
                ->orWhere(function($q) use ($orderAmount) {
                    $q->whereNull('min_order_amount')
                      ->where('max_order_amount', '>=', $orderAmount);
                })
                ->orWhere(function($q) use ($orderAmount) {
                    $q->where('min_order_amount', '<=', $orderAmount)
                      ->where('max_order_amount', '>=', $orderAmount);
                });
            })
            ->orderBy('priority', 'desc')
            ->first();
    }
}