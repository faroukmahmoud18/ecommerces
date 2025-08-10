<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'bio',
        'logo',
        'cover_image',
        'featured_image',
        'status',
        'commission_rate',
        'wallet_balance',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'bank_iban',
        'tax_number',
        'address',
        'phone',
        'email',
        'website',
        'social_links',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'wallet_balance' => 'decimal:2',
        'social_links' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the vendor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products for the vendor.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the orders for the vendor.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the payouts for the vendor.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(VendorPayout::class);
    }

    /**
     * Get the images for the vendor.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Scope a query to only include active vendors.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    /**
     * Scope a query to only include pending vendors.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include suspended vendors.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Calculate total earnings for the vendor.
     */
    public function getTotalEarningsAttribute()
    {
        return $this->orders()->where('status', 'delivered')->sum('total_amount');
    }

    /**
     * Calculate pending payouts for the vendor.
     */
    public function getPendingPayoutsAttribute()
    {
        return $this->wallet_balance - $this->payouts()->sum('amount');
    }

    /**
     * Get the vendor's storefront URL.
     */
    public function getStorefrontUrlAttribute()
    {
        return route('vendor.show', $this->slug);
    }

    /**
     * Get the average rating of the vendor.
     */
    public function getAverageRatingAttribute()
    {
        return $this->products()->withAvg('reviews', 'rating')->avg('reviews_avg_rating') ?? 0;
    }

    /**
     * Get the total number of reviews for the vendor.
     */
    public function getTotalReviewsAttribute()
    {
        return $this->products()->withCount('reviews')->sum('reviews_count');
    }
}
