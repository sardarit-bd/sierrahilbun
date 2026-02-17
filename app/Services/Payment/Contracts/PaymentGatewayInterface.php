<?php

namespace App\Services\Payment\Contracts;

use App\Services\Payment\DTO\PaymentRequestDTO;
use App\Services\Payment\DTO\PaymentResponseDTO;

interface PaymentGatewayInterface
{
    public function charge(PaymentRequestDTO $request, string $idempotencyKey): PaymentResponseDTO;
    public function refund(string $transactionId, float $amount): PaymentResponseDTO;
}