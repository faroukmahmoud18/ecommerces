<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'status',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the products associated with the offer.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'offer_products');
    }

    /**
     * Get the categories associated with the offer.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'offer_categories');
    }

    /**
     * Get the vendors associated with the offer.
     */
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'offer_vendors');
    }

    /**
     * Check if the offer is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' && 
               now()->between($this->start_date, $this->end_date);
    }

    /**
     * Check if the offer has expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->status === 'expired' || now()->gt($this->end_date);
    }

    /**
     * Check if the offer is upcoming.
     *
     * @return bool
     */
    public function isUpcoming()
    {
        return $this->status === 'upcoming' || now()->lt($this->start_date);
    }

    /**
     * Scope a query to only include active offers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include expired offers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere('end_date', '<', now());
    }

    /**
     * Scope a query to only include upcoming offers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
                    ->orWhere('start_date', '>', now());
    }
}
