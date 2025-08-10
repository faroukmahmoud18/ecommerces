<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        // 'category_id', // Added category_id to fillable
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'compare_price',
        'cost',
        'track_quantity',
        'quantity',
        'manage_inventory',
        'allow_backorder',
        'is_active',
        'featured',
        'new_arrival',
        'meta_title',
        'meta_description',
        'shipping_class_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'quantity' => 'integer',
        'featured' => 'boolean',
        'new_arrival' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the vendor that owns the product.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the categories for the product.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    /**
     * Get the offers for the product.
     */
    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offer_products');
    }

    /**
     * Get the images for the product.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the average rating of the product.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get the total number of reviews.
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Get the active offers for the product.
     */
    public function getActiveOffersAttribute()
    {
        return $this->offers()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    /**
     * Calculate the discounted price for the product.
     *
     * @return array
     */
    public function getDiscountInfoAttribute()
    {
        $offers = $this->active_offers;
        $discount = 0;
        $discountType = null;
        $offerName = null;
        $discountedPrice = $this->price;

        foreach ($offers as $offer) {
            if ($offer->discount_type === 'percentage') {
                $offerDiscount = $this->price * ($offer->discount_value / 100);
            } else {
                $offerDiscount = $offer->discount_value;
            }

            if ($offerDiscount > $discount) {
                $discount = $offerDiscount;
                $discountType = $offer->discount_type;
                $offerName = $offer->name;
            }
        }

        if ($discount > 0) {
            $discountedPrice = $this->price - $discount;
        }

        return [
            'has_discount' => $discount > 0,
            'discount' => $discount,
            'discount_type' => $discountType,
            'offer_name' => $offerName,
            'original_price' => $this->price,
            'discounted_price' => $discountedPrice,
        ];
    }

    /**
     * Get the discounted price for the product.
     *
     * @return float
     */
    public function getDiscountedPriceAttribute()
    {
        return $this->discount_info['discounted_price'];
    }

    /**
     * Check if the product has an active offer.
     *
     * @return bool
     */
    public function hasActiveOffer()
    {
        return $this->discount_info['has_discount'];
    }

    /**
     * Get the savings amount for the product.
     *
     * @return float
     */
    public function getSavingsAttribute()
    {
        return $this->discount_info['discount'];
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to only include new arrival products.
     */
    public function scopeNewArrival($query)
    {
        return $query->where('new_arrival', true);
    }

    /**
     * Scope a query to search products by name or description.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', '%' . $term . '%')
                  ->orWhere('description', 'like', '%' . $term . '%');
        });
    }
}
