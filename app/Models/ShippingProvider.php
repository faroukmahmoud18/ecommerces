<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'logo',
        'is_active',
        'config',
        'settings',
        'api_url',
        'api_key',
        'api_secret',
        'supports_tracking',
        'supports_label_generation',
        'supports_scheduling',
        'priority'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'settings' => 'array',
        'supports_tracking' => 'boolean',
        'supports_label_generation' => 'boolean',
        'supports_scheduling' => 'boolean',
    ];

    /**
     * Get the zones for the shipping provider.
     */
    public function zones(): HasMany
    {
        return $this->hasMany(ShippingZone::class, 'provider_id');
    }

    /**
     * Get the shipments for the shipping provider.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'provider_id');
    }

    /**
     * Check if provider is configured properly
     */
    public function isConfigured(): bool
    {
        switch ($this->code) {
            case 'aramex':
                return !empty($this->config['account_number']) && 
                       !empty($this->config['account_pin']) &&
                       !empty($this->config['username']) &&
                       !empty($this->config['password']);
            case 'dhl':
                return !empty($this->api_key) && !empty($this->api_secret);
            case 'smsa':
                return !empty($this->api_key);
            case 'local':
                return true;
            default:
                return false;
        }
    }
}