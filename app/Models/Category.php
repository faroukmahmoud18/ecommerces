<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'icon',
        'display_order',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }

    /**
     * Get the images for the category.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get all categories in a hierarchical structure.
     */
    public static function getHierarchical()
    {
        $categories = self::whereNull('parent_id')
            ->with('children')
            ->orderBy('display_order')
            ->get();

        return $categories;
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the full category path (for breadcrumbs).
     */
    public function getPathAttribute()
    {
        $path = [];
        $category = $this;

        while ($category) {
            array_unshift($path, $category);
            $category = $category->parent;
        }

        return $path;
    }
}
