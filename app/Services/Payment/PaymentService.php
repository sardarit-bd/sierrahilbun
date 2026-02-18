<?php

namespace App\Services\Payment;

use App\Models\Transaction;
use App\Services\Payment\DTO\PaymentRequestDTO;
use App\Services\Payment\DTO\PaymentResponseDTO;
use App\Services\Payment\Factory\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayFactory $factory,
    ) {}

    public function charge(string $gateway, PaymentRequestDTO $request, ?object $payable = null): PaymentResponseDTO
    {
        $idempotencyKey  = Str::uuid()->toString();
        $gatewayInstance = $this->factory->make($gateway);

        // Persist pending transaction before hitting gateway
        $transaction = DB::transaction(function () use ($gateway, $request, $idempotencyKey, $payable) {
            return Transaction::create([
                'transaction_id'    => $idempotencyKey,
                'gateway'           => $gateway,
                'amount'            => $request->amount,
                'currency'          => $request->currency,
                'status'            => 'pending',
                'payment_method_id' => $request->paymentMethodId,
                'payable_id'        => $payable?->id,
                'payable_type'      => $payable ? get_class($payable) : null,
            ]);
        });

        // Call gateway with idempotency key
        try {
            $response = $gatewayInstance->charge($request, $idempotencyKey);
        } catch (\Throwable $e) {
            $transaction->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Payment gateway exception', [
                'gateway'         => $gateway,
                'idempotency_key' => $idempotencyKey,
                'error'           => $e->getMessage(),
            ]);

            return new PaymentResponseDTO(
                success:       false,
                transactionId: $idempotencyKey,
                status:        'failed',
                amount:        $request->amount,
                currency:      $request->currency,
                errorMessage:  $e->getMessage(),
            );
        }

        // Update transaction — status stays pending until webhook confirms
        DB::transaction(function () use ($transaction, $response) {
            $transaction->update([
                'transaction_id' => $response->transactionId ?: $transaction->transaction_id,
                'error_message'  => $response->errorMessage,
                'raw_response'   => $response->raw,
            ]);
        });

        return $response;
    }

    public function refund(string $gateway, string $transactionId, float $amount): PaymentResponseDTO
    {
        $gatewayInstance = $this->factory->make($gateway);

        $existingTransaction = DB::transaction(function () use ($transactionId) {
            return Transaction::where('transaction_id', $transactionId)
                ->lockForUpdate()
                ->firstOrFail();
        });

        if ($existingTransaction->status === 'refunded') {
            return new PaymentResponseDTO(
                success:       false,
                transactionId: $transactionId,
                status:        'refunded',
                amount:        $amount,
                currency:      $existingTransaction->currency,
                errorMessage:  'Transaction already refunded.',
            );
        }

        try {
            $response = $gatewayInstance->refund($transactionId, $amount);
        } catch (\Throwable $e) {
            Log::error('Refund gateway exception', [
                'gateway'        => $gateway,
                'transaction_id' => $transactionId,
                'error'          => $e->getMessage(),
            ]);

            return new PaymentResponseDTO(
                success:       false,
                transactionId: $transactionId,
                status:        'refund_failed',
                amount:        $amount,
                currency:      $existingTransaction->currency,
                errorMessage:  $e->getMessage(),
            );
        }

        DB::transaction(function () use ($existingTransaction, $response) {
            $existingTransaction->update([
                'status'        => $response->success ? 'refunded' : 'refund_failed',
                'error_message' => $response->errorMessage,
                'raw_response'  => $response->raw,
            ]);
        });

        return $response;
    }
}