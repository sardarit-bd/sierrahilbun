<?php

namespace App\Services\Payment\DTO;

final class PaymentResponseDTO
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $transactionId,
        public readonly string $status,
        public readonly float  $amount,
        public readonly string $currency,
        public readonly ?string $errorMessage = null,
        public readonly array  $raw = [],
    ) {}
}