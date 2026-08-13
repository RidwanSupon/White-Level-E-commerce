<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku', 'price', 'compare_price',
        'cost_price', 'description', 'short_description', 'featured_image',
        'is_active', 'is_featured', 'stock_quantity', 'low_stock_threshold',
        'track_inventory', 'weight', 'dimensions', 'rating_cache', 'reviews_count',
        'meta_title', 'meta_description', 'tax_rate_id', 'is_tax_exempt'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'rating_cache' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_inventory' => 'boolean',
        'is_tax_exempt' => 'boolean',
    ];

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return null;
    }

    public function getFeaturedImageUrlAttribute(): string
    {
        if (!empty($this->featured_image)) {
            // Check if file exists locally or is external URL
            if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
                return $this->featured_image;
            }
            if (file_exists(public_path(ltrim($this->featured_image, '/')))) {
                return asset(ltrim($this->featured_image, '/'));
            }
        }

        // Fallback to first primary/gallery image if featured_image path is broken
        $primary = $this->images()->orderBy('is_primary', 'desc')->orderBy('sort_order', 'asc')->first();
        if ($primary && !empty($primary->image_path) && file_exists(public_path(ltrim($primary->image_path, '/')))) {
            return asset(ltrim($primary->image_path, '/'));
        }

        return asset('images/placeholder.png');
    }
}
