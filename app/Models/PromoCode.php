<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'usage_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value'        => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
        'is_active'    => 'boolean',
    ];

    /**
     * Check if the promo code is valid for a given subtotal.
     * Returns ['valid' => bool, 'message' => string]
     */
    public function isValid(float $subtotal = 0): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'This promo code is inactive.'];
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return ['valid' => false, 'message' => 'This promo code is not yet active.'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'This promo code has expired.'];
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'This promo code has reached its usage limit.'];
        }

        if ($subtotal < (float) $this->min_purchase) {
            return [
                'valid'   => false,
                'message' => 'A minimum purchase of $' . number_format($this->min_purchase, 2) . ' is required.',
            ];
        }

        return ['valid' => true, 'message' => 'Promo code applied successfully!'];
    }

    /**
     * Calculate the discount amount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        $discount = $this->type === 'percentage'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round($discount, 2);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}