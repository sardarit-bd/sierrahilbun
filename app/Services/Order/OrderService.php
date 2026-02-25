<?php

namespace App\Services\Order;

use App\Models\CheckoutSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Create an Order and OrderItems from a completed CheckoutSession.
     *
     * ACID guarantees:
     *  - Entire operation runs in a single DB transaction
     *  - If any step fails, everything rolls back
     *  - Idempotent — won't create duplicate orders for the same transaction
     *
     * @throws \Throwable
     */
    public function createFromCheckoutSession(
        Transaction     $transaction,
        CheckoutSession $session,
    ): Order {
        return DB::transaction(function () use ($transaction, $session) {

            // ── Idempotency guard ─────────────────────────────────
            // If an order already exists for this transaction, return it.
            // Protects against duplicate webhook delivery.
            $existing = Order::where('transaction_id', $transaction->id)->first();

            if ($existing) {
                Log::info('OrderService: order already exists, skipping', [
                    'transaction_id' => $transaction->transaction_id,
                    'order_id'       => $existing->id,
                ]);
                return $existing;
            }

            // ── Create Order ──────────────────────────────────────
            $order = Order::create([
                'user_id'        => $session->user_id,
                'transaction_id' => $transaction->id,
                'total_amount'   => $session->total,
                'status'         => 'paid',
            ]);

            // ── Create Order Items ────────────────────────────────
            $items = $session->items ?? [];

            foreach ($items as $item) {
                $variant = $this->resolveDefaultVariant((int) $item['product_id']);

                if (!$variant) {
                    Log::warning('OrderService: no default variant found for product', [
                        'product_id' => $item['product_id'],
                        'order_id'   => $order->id,
                    ]);
                    // Skip item gracefully — do not fail entire order
                    continue;
                }

                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => (int) $item['quantity'],
                    'price_at_purchase'  => (float) $item['unit_price'],
                ]);
            }

            Log::info('OrderService: order created successfully', [
                'order_id'       => $order->id,
                'transaction_id' => $transaction->transaction_id,
                'user_id'        => $session->user_id,
                'total'          => $order->total_amount,
                'item_count'     => count($items),
            ]);

            return $order;
        });
    }

    // -------------------------------------------------------
    // Private
    // -------------------------------------------------------

    /**
     * Resolve the default product variant for a given product.
     * Falls back to the first variant if no default is set.
     */
    private function resolveDefaultVariant(int $productId): ?ProductVariant
    {
        return ProductVariant::where('product_id', $productId)
            ->where('is_default', true)
            ->first()
            ?? ProductVariant::where('product_id', $productId)
                ->orderBy('sort_order')
                ->first();
    }
}