<?php

namespace App\Services\Payment\Webhooks;

use App\Models\CheckoutSession;
use App\Models\PaymentGatewaySetting;
use App\Models\PromoCode;
use App\Models\Transaction;
use App\Services\Order\OrderService;
use App\Services\Payment\Contracts\WebhookHandlerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookHandler implements WebhookHandlerInterface
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    // -------------------------------------------------------
    // WebhookHandlerInterface
    // -------------------------------------------------------

    public function verify(Request $request): bool
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (empty($payload)) {
            Log::warning('Stripe webhook: empty payload');
            return false;
        }

        if (!$sigHeader) {
            Log::warning('Stripe webhook: missing Stripe-Signature header');
            return false;
        }

        try {
            Webhook::constructEvent($payload, $sigHeader, $this->resolveWebhookSecret());
            return true;
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Stripe webhook unexpected verify error', [
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
            $this->resolveWebhookSecret(),
        );

        match ($event->type) {
            'payment_intent.succeeded'      => $this->onPaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->onPaymentFailed($event),
            'charge.refunded'               => $this->onRefunded($event),
            default                         => $this->onUnhandled($event),
        };
    }

    // -------------------------------------------------------
    // Secret resolution — lazy, cached, never in constructor
    // -------------------------------------------------------

    private function resolveWebhookSecret(): string
    {
        $settingId = Cache::remember('stripe_webhook_setting_id', 3600, function () {
            return PaymentGatewaySetting::where('gateway', 'stripe')
                ->where('is_active', true)
                ->value('id');
        });

        if (!$settingId) {
            throw new \RuntimeException('Stripe gateway not configured or inactive.');
        }

        // Fetch fresh — accessor auto-decrypts, no manual Crypt call needed
        $setting = PaymentGatewaySetting::find($settingId);

        return $setting->webhook_secret; // accessor returns plaintext
    }

    // -------------------------------------------------------
    // Event handlers (unchanged from your implementation)
    // -------------------------------------------------------

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
                return; // idempotency guard
            }

            $transaction->update([
                'status'       => 'succeeded',
                'raw_response' => $paymentIntent->toArray(),
            ]);

            if (!$transaction->checkout_session_id) {
                Log::warning('Stripe webhook: no checkout session linked', [
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

            $session->update(['status' => 'completed']);

            if ($session->promo_code) {
                PromoCode::where('code', $session->promo_code)->increment('usage_count');
            }

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

            if (!$transaction || in_array($transaction->status, ['succeeded', 'refunded'])) {
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

            if (!$transaction || $transaction->status === 'refunded') {
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
        Log::info('Stripe webhook: unhandled event type', ['type' => $event->type]);
    }
}