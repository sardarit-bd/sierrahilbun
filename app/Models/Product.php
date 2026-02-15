<?php

namespace App\Models;

use App\Observers\ProductObserver;
use App\Services\ProductImageService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'subtitle',
        'slug',
        'description',
        'benefits', 
        'ingredients', 
        'usage_instructions', 
        'coverage_sqft',
        'application_rate_oz_per_1k',
        'base_price',
        'discount_price',
        'rating_avg',
        'reviews_count', 
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'coverage_sqft' => 'integer',
        'application_rate_oz_per_1k' => 'decimal:2',
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'rating_avg' => 'decimal:2', 
        'reviews_count' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function ($product) {
            foreach ($product->images as $image) {
                app(ProductImageService::class)->delete($image->image_url);
            }
            $product->images()->delete();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function planDeliverables(): HasMany
    {
        return $this->hasMany(PlanDeliverable::class, 'product_sku', 'sku');
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderItem::class,
            ProductVariant::class,
            'product_id',      
            'product_variant_id',
        );
    }
    
    public function isPurchasedBy(int $userId): bool
    {
        return $this->orderItems()
            ->whereHas('order', fn($q) => $q->where('user_id', $userId))
            ->exists();
    }
}