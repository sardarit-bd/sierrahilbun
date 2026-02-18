<?php

namespace App\Services\Payment\Webhooks;

use App\Models\CheckoutSession;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Services\Payment\Contracts\WebhookHandlerInterface;
use App\Services\Payment\Factory\PaymentGatewayFactory;
use App\Services\Payment\Gateways\StripeGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Webhook;

class StripeWebhookHandler implements WebhookHandlerInterface
{
    private string $webhookSecret;

    public function __construct(PaymentGatewayFactory $factory)
    {
        /** @var StripeGateway $gateway */
        $gateway = $factory->make('stripe');
        $this->webhookSecret = $gateway->getWebhookSecret();
    }

    public function verify(Request $request): bool
    {
        try {
            Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                $this->webhookSecret,
            );

            return true;
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function handle(Request $request): void
    {
        $event = Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            $this->webhookSecret,
        );

        match($event->type) {
            'payment_intent.succeeded'              => $this->onPaymentSucceeded($event),
            'payment_intent.payment_failed'         => $this->onPaymentFailed($event),
            'charge.refunded'                       => $this->onRefunded($event),
            default                                 => $this->onUnhandled($event),
        };
    }

    // private function onPaymentSucceeded(Event $event): void
    // {
    //     $paymentIntent = $event->data->object;

    //     DB::transaction(function () use ($paymentIntent) {
    //         $transaction = Transaction::where('transaction_id', $paymentIntent->id)
    //             ->lockForUpdate()
    //             ->first();

    //         if (!$transaction) {
    //             Log::warning('Stripe webhook: transaction not found', [
    //                 'transaction_id' => $paymentIntent->id,
    //             ]);
    //             return;
    //         }

    //         if ($transaction->status === 'succeeded') {
    //             return;
    //         }

    //         $transaction->update([
    //             'status'       => 'succeeded',
    //             'raw_response' => $paymentIntent->toArray(),
    //         ]);

    //         // ✅ Complete checkout session when payment succeeds
    //         if ($transaction->checkout_session_id) {
    //             \App\Models\CheckoutSession::where('session_id', $transaction->checkout_session_id)
    //                 ->update(['status' => 'completed']);
    //         }
    //     });
    // }

    private function onPaymentSucceeded(Event $event): void
    {
        $paymentIntent = $event->data->object;

        DB::transaction(function () use ($paymentIntent) {
            $transaction = Transaction::where('transaction_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                Log::warning('Stripe webhook: transaction not found', [
                    'transaction_id' => $paymentIntent->id,
                ]);
                return;
            }

            if ($transaction->status === 'succeeded') {
                return;
            }

            $transaction->update([
                'status'       => 'succeeded',
                'raw_response' => $paymentIntent->toArray(),
            ]);

            if ($transaction->checkout_session_id) {
                $session = CheckoutSession::where('session_id', $transaction->checkout_session_id)
                    ->first();

                if ($session) {
                    $session->update(['status' => 'completed']);

                    if ($session->promo_code) {
                        PromoCode::where('code', $session->promo_code)
                            ->increment('usage_count');
                    }
                }
            }
        });
    }

    private function onPaymentFailed(Event $event): void
    {
        $paymentIntent = $event->data->object;

        DB::transaction(function () use ($paymentIntent) {
            $transaction = Transaction::where('transaction_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                Log::warning('Stripe webhook: transaction not found for failed payment', [
                    'transaction_id' => $paymentIntent->id,
                ]);
                return;
            }

            if (in_array($transaction->status, ['succeeded', 'refunded'])) {
                return;
            }

            $transaction->update([
                'status'        => 'failed',
                'error_message' => $paymentIntent->last_payment_error?->message,
                'raw_response'  => $paymentIntent->toArray(),
            ]);
        });
    }

    private function onRefunded(Event $event): void
    {
        $charge = $event->data->object;

        DB::transaction(function () use ($charge) {
            $transaction = Transaction::where('transaction_id', $charge->payment_intent)
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                Log::warning('Stripe webhook: transaction not found for refund', [
                    'payment_intent' => $charge->payment_intent,
                ]);
                return;
            }

            if ($transaction->status === 'refunded') {
                return;
            }

            $transaction->update([
                'status'       => 'refunded',
                'raw_response' => $charge->toArray(),
            ]);
        });
    }

    private function onUnhandled(Event $event): void
    {
        Log::info('Stripe webhook: unhandled event type', [
            'type' => $event->type,
        ]);
    }
}