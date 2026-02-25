<?php

namespace App\Services\Payment\Webhooks;

use App\Models\CheckoutSession;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Services\Order\OrderService;
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

    public function __construct(
        private readonly PaymentGatewayFactory $factory,
        private readonly OrderService          $orderService,
    ) {
        /** @var StripeGateway $gateway */
        $gateway             = $this->factory->make('stripe');
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
            'payment_intent.succeeded'      => $this->onPaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->onPaymentFailed($event),
            'charge.refunded'               => $this->onRefunded($event),
            default                         => $this->onUnhandled($event),
        };
    }

    // -------------------------------------------------------
    // Event Handlers
    // -------------------------------------------------------

    private function onPaymentSucceeded(Event $event): void
    {
        $paymentIntent = $event->data->object;

        DB::transaction(function () use ($paymentIntent) {

            // ── Step 1: Find and lock transaction ─────────────────
            $transaction = Transaction::where('transaction_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                Log::warning('Stripe webhook: transaction not found', [
                    'transaction_id' => $paymentIntent->id,
                ]);
                return;
            }

            // ── Step 2: Idempotency guard ─────────────────────────
            if ($transaction->status === 'succeeded') {
                return;
            }

            // ── Step 3: Update transaction status ─────────────────
            $transaction->update([
                'status'       => 'succeeded',
                'raw_response' => $paymentIntent->toArray(),
            ]);

            // ── Step 4: Find checkout session ─────────────────────
            if (!$transaction->checkout_session_id) {
                Log::warning('Stripe webhook: no checkout session linked to transaction', [
                    'transaction_id' => $paymentIntent->id,
                ]);
                return;
            }

            $session = CheckoutSession::where('session_id', $transaction->checkout_session_id)
                ->first();

            if (!$session) {
                Log::warning('Stripe webhook: checkout session not found', [
                    'session_id' => $transaction->checkout_session_id,
                ]);
                return;
            }

            // ── Step 5: Complete checkout session ─────────────────
            $session->update(['status' => 'completed']);

            // ── Step 6: Increment promo code usage ────────────────
            if ($session->promo_code) {
                PromoCode::where('code', $session->promo_code)
                    ->increment('usage_count');
            }

            // ── Step 7: Create Order + OrderItems ─────────────────
            // OrderService handles its own idempotency guard internally
            $this->orderService->createFromCheckoutSession($transaction, $session);
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