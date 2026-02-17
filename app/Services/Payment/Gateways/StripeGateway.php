<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTO\PaymentRequestDTO;
use App\Services\Payment\DTO\PaymentResponseDTO;
use Stripe\StripeClient;

class StripeGateway implements PaymentGatewayInterface
{
    private StripeClient $client;

    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
    ) {
        $this->client = new StripeClient($this->secretKey);
    }

    public function charge(PaymentRequestDTO $request, string $idempotencyKey): PaymentResponseDTO
    {
        try {
            $intent = $this->client->paymentIntents->create(
                [
                    'amount'   => (int) ($request->amount * 100),
                    'currency' => $request->currency,
                    'payment_method' => $request->paymentMethodId,
                    'description'    => $request->description,
                    'metadata'       => $request->metadata,
                    'automatic_payment_methods' => [
                        'enabled'         => true,
                        'allow_redirects' => 'never',
                    ],
                    'confirm' => true,
                ],
                ['idempotency_key' => $idempotencyKey]
            );

            return new PaymentResponseDTO(
                success:       true,
                transactionId: $intent->id,
                status:        'pending',
                amount:        $request->amount,
                currency:      $request->currency,
                raw:           $intent->toArray(),
            );

        } catch (\Stripe\Exception\CardException $e) {
            return new PaymentResponseDTO(
                success:       false,
                transactionId: '',
                status:        'failed',
                amount:        $request->amount,
                currency:      $request->currency,
                errorMessage:  $e->getMessage(),
            );
        }
    }

    public function refund(string $transactionId, float $amount): PaymentResponseDTO
    {
        try {
            $refund = $this->client->refunds->create([
                'payment_intent' => $transactionId,
                'amount'         => (int) ($amount * 100),
            ]);

            return new PaymentResponseDTO(
                success:       true,
                transactionId: $refund->id,
                status:        $refund->status,
                amount:        $amount,
                currency:      $refund->currency,
                raw:           $refund->toArray(),
            );

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return new PaymentResponseDTO(
                success:       false,
                transactionId: '',
                status:        'failed',
                amount:        $amount,
                currency:      '',
                errorMessage:  $e->getMessage(),
            );
        }
    }

    public function getWebhookSecret(): string
    {
        return $this->webhookSecret;
    }
}