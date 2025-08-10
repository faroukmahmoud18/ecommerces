
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'tax_amount',
        'discount_amount',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the cart that owns the item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant that owns the item.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Calculate the subtotal for this item.
     */
    public function calculateSubtotal()
    {
        $this->subtotal = ($this->price * $this->quantity) + $this->tax_amount - $this->discount_amount;
        $this->save();
    }

    /**
     * Update the price and recalculate totals.
     */
    public function updatePrice($price)
    {
        $this->price = $price;
        $this->calculateSubtotal();
    }

    /**
     * Update the quantity and recalculate totals.
     */
    public function updateQuantity($quantity)
    {
        $this->quantity = $quantity;
        $this->calculateSubtotal();
    }

    /**
     * Get the product name with variant options if applicable.
     */
    public function getProductNameWithVariantAttribute()
    {
        $name = $this->product->name;

        if ($this->variant && !empty($this->variant->options)) {
            $optionsText = collect($this->variant->options)
                ->map(fn($value, $key) => $key . ': ' . $value)
                ->join(', ');

            $name .= ' (' . $optionsText . ')';
        }

        return $name;
    }
}
