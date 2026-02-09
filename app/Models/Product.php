<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'coverage_sqft',
        'application_rate_oz_per_1k',
        'base_price',
        'is_active',
    ];

    protected $casts = [
        'coverage_sqft' => 'integer',
        'application_rate_oz_per_1k' => 'decimal:2',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function planDeliverables(): HasMany
    {
        return $this->hasMany(PlanDeliverable::class, 'product_sku', 'sku');
    }
}