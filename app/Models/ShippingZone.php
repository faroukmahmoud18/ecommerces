<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'country',
        'state',
        'city',
        'zip_from',
        'zip_to',
        'estimated_delivery_days',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_delivery_days' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Get the provider that owns the zone.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class, 'provider_id');
    }

    /**
     * Get the rates for this zone.
     */
    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'zone_id');
    }

    /**
     * Get the formatted name with country
     */
    public function getFormattedNameAttribute(): string
    {
        $parts = [$this->name];

        if ($this->city) {
            $parts[] = $this->city;
        }

        if ($this->state) {
            $parts[] = $this->state;
        }

        if ($this->country) {
            $parts[] = $this->country;
        }

        return implode(', ', array_filter($parts));
    }

    /**
     * Get the scope description
     */
    public function getScopeDescriptionAttribute(): string
    {
        if ($this->zip_from && $this->zip_to) {
            return "الرموز البريدية من {$this->zip_from} إلى {$this->zip_to}, {$this->country}";
        } elseif ($this->city) {
            return "مدينة {$this->city}, {$this->state}, {$this->country}";
        } elseif ($this->state) {
            return "منطقة {$this->state}, {$this->country}";
        } else {
            return "دولة {$this->country}";
        }
    }

    /**
     * Get shipping zones for a specific country
     */
    public static function getZonesForCountry(string $country, $providerId = null)
    {
        $query = self::where('country', $country)
                     ->where('is_active', true);

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        return $query->orderBy('priority', 'desc')
                    ->get();
    }

    /**
     * Get default shipping zone
     */
    public static function getDefaultZone($providerId)
    {
        return self::where('provider_id', $providerId)
                 ->where('is_active', true)
                 ->whereNull('state')
                 ->whereNull('city')
                 ->whereNull('zip_from')
                 ->whereNull('zip_to')
                 ->first();
    }
}