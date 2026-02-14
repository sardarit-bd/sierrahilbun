<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'size_label',
        'size_volume_oz',
        'sort_order',      
        'price',
        'compare_at_price', 
        'stock_quantity',
        'is_default',      
    ];

    protected $casts = [
        'size_volume_oz' => 'integer',
        'sort_order' => 'integer',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2', 
        'stock_quantity' => 'integer',
        'is_default' => 'boolean',         
        'created_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}