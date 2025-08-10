<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor that owns the review.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the product that owns the review.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order that owns the review.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Status labels
     */
    public $statusLabels = [
        'pending' => 'قيد الانتظار',
        'approved' => 'مُوَقَّع',
        'rejected' => 'مرفوض',
    ];

    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include reviews with a specific rating.
     */
    public function scopeRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope a query to only include reviews for a specific vendor.
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope a query to only include reviews for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute()
    {
        return $this->statusLabels[$this->status] ?? $this->status;
    }

    /**
     * Check if the review is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the review is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Approve the review.
     */
    public function approve()
    {
        $this->status = 'approved';
        $this->approved_at = now();
        $this->save();
    }

    /**
     * Reject the review.
     */
    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
    }
}
