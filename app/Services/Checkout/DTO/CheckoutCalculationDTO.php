<?php

namespace App\Services\Checkout\DTO;

final class CheckoutCalculationDTO
{
    public function __construct(
        public readonly float  $subtotal,
        public readonly float  $discountAmount,
        public readonly float  $shippingCost,
        public readonly float  $taxAmount,
        public readonly float  $total,
        public readonly string $currency,
        public readonly array  $items,
        public readonly ?string $promoCode = null,
    ) {}
}