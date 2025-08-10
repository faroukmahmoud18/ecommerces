<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'vendor_id',
        'is_active',
        'items_count',
        'items_quantity',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'discount_amount',
        'total_amount',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'items_count' => 'integer',
        'items_quantity' => 'integer',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor that owns the cart.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the items for the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Scope a query to only include active carts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get carts by session ID.
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope a query to get carts by user ID.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get carts by vendor ID.
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Calculate cart totals based on items.
     */
    public function calculateTotals()
    {
        $this->items_count = $this->items()->count();
        $this->items_quantity = $this->items()->sum('quantity');
        $this->subtotal = $this->items()->sum('subtotal');
        $this->tax_amount = $this->items()->sum('tax_amount');
        $this->discount_amount = $this->items()->sum('discount_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_cost - $this->discount_amount;

        $this->save();
    }

    /**
     * Add an item to the cart.
     */
    public function addItem($productId, $quantity = 1, $variantId = null)
    {
        $cartItem = $this->items()->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($cartItem) {
            // Item already exists, update quantity
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            // Create new cart item
            $cartItem = new CartItem([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
            $this->items()->save($cartItem);
        }

        $this->calculateTotals();
        return $cartItem;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem($cartItemId)
    {
        $this->items()->where('id', $cartItemId)->delete();
        $this->calculateTotals();
    }

    /**
     * Update item quantity.
     */
    public function updateItemQuantity($cartItemId, $quantity)
    {
        $cartItem = $this->items()->find($cartItemId);

        if ($cartItem) {
            if ($quantity <= 0) {
                $this->removeItem($cartItemId);
            } else {
                $cartItem->quantity = $quantity;
                $cartItem->save();
                $this->calculateTotals();
            }
        }
    }

    /**
     * Clear all items from the cart.
     */
    public function clear()
    {
        $this->items()->delete();
        $this->calculateTotals();
    }
}
