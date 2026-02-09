<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
        'images_json',
        'is_verified_purchase',
    ];

    protected $casts = [
        'rating' => 'integer',
        'images_json' => 'array',
        'is_verified_purchase' => 'boolean',
        'created_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}