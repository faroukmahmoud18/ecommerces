
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'price',
        'total',
        'tax_amount',
        'discount_amount',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the vendor that owns the item.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the subtotal for this item (price * quantity).
     */
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the total for this item (subtotal + tax - discount).
     */
    public function getTotalAttribute()
    {
        return ($this->price * $this->quantity) + $this->tax_amount - $this->discount_amount;
    }
}
