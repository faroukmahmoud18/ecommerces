<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'compare_price',
        'cost',
        'track_quantity',
        'quantity',
        'manage_inventory',
        'allow_backorder',
        'is_active',
        'weight',
        'dimensions',
        'options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'quantity' => 'integer',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the images for the variant.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get the order items for the variant.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to only include active variants.
     */
    public function scopeActive($query)
    {
        return $this->query()->where('is_active', true);
    }

    /**
     * Scope a query to only include variants with available stock.
     */
    public function scopeInStock($query)
    {
        return $this->query()->where('quantity', '>', 0);
    }

    /**
     * Scope a query to search variants by name or SKU.
     */
    public function scopeSearch($query, $term)
    {
        return $this->query()->where(function ($query) use ($term) {
            $query->where('name', 'like', '%' . $term . '%')
                  ->orWhere('sku', 'like', '%' . $term . '%');
        });
    }

    /**
     * Get the option values as a string.
     */
    public function getOptionsTextAttribute()
    {
        if (empty($this->options)) {
            return '';
        }

        return collect($this->options)
            ->map(fn($value, $key) => $key . ': ' . $value)
            ->join(', ');
    }

    /**
     * Get the total number of order items for this variant.
     */
    public function getTotalSoldAttribute()
    {
        return $this->orderItems()->sum('quantity');
    }

    /**
     * Check if the variant is in stock.
     */
    public function isInStock()
    {
        return $this->quantity > 0;
    }

    /**
     * Check if the variant allows backorders.
     */
    public function allowsBackorder()
    {
        return $this->allow_backorder;
    }

    /**
     * Decrement the quantity of the variant.
     */
    public function decrementQuantity($quantity)
    {
        if ($this->manage_inventory) {
            $this->quantity -= $quantity;
            $this->save();
        }
    }

    /**
     * Increment the quantity of the variant.
     */
    public function incrementQuantity($quantity)
    {
        if ($this->manage_inventory) {
            $this->quantity += $quantity;
            $this->save();
        }
    }
}
