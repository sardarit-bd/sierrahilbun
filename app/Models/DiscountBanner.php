<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountBanner extends Model
{
    protected $fillable = [
        'text',
        'promo_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getActive(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }
}