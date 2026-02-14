<?php

namespace App\Models;

use App\Observers\ProductReviewObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ProductReviewObserver::class])]
class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'title', 
        'content',        
        'images_json',
        'is_verified_purchase',
        'helpful_count',  
        'is_approved',    
    ];

    protected $casts = [
        'rating' => 'integer',
        'images_json' => 'array',
        'is_verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
        'is_approved' => 'boolean',   
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