<?php

namespace App\Services\Payment\DTO;

final class PaymentRequestDTO
{
    public function __construct(
        public readonly float  $amount,
        public readonly string $currency,
        public readonly string $paymentMethodId,
        public readonly string $description = '',
        public readonly array  $metadata = [],
    ) {}
}