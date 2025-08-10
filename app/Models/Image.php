<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'path',
        'alt_text',
        'title',
        'caption',
        'is_featured',
        'display_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the parent imageable model (product, category, etc.).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the URL for the image.
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    /**
     * Get the URL for the thumbnail image.
     */
    public function getThumbnailUrlAttribute()
    {
        $path = pathinfo($this->path);
        $thumbnailPath = $path['dirname'] . '/' . $path['filename'] . '_thumb.' . $path['extension'];
        return asset('storage/' . $thumbnailPath);
    }

    /**
     * Scope a query to only include featured images.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
