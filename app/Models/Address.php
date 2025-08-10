<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'is_default',
        'is_billing',
        'is_shipping',
        'additional_info',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_billing' => 'boolean',
        'is_shipping' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted full address
     */
    public function getFormattedAttribute(): string
    {
        $parts = [
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Set address as default
     */
    public function setAsDefault()
    {
        // First remove default from all user addresses
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this one as default
        $this->is_default = true;
        $this->save();

        return $this;
    }

    /**
     * Get default address for user
     */
    public static function getDefaultForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get shipping addresses for user
     */
    public static function getShippingAddressesForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_shipping', true)
            ->orderBy('is_default', 'desc')
            ->get();
    }

    /**
     * Get billing addresses for user
     */
    public static function getBillingAddressesForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_billing', true)
            ->orderBy('is_default', 'desc')
            ->get();
    }
}