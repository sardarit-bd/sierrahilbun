<?php

namespace App\Services\Order;

use App\Models\CheckoutSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
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
                'user_id'               => $session->user_id,
                'transaction_id'        => $transaction->id,
                'total_amount'          => $session->total,
                'status'                => 'paid',
                'delivery_status'       => 'pending',
                'shipping_address_json' => $this->resolveShippingAddress($session),
            ]);

            // ── Create Order Items ────────────────────────────────
            $items = $session->items ?? [];

            foreach ($items as $item) {
                $type = $item['type'] ?? 'product';

                match ($type) {
                    'product' => $this->createProductOrderItem($order, $item),
                    'plan'    => $this->createPlanOrderItems($order, $item),
                    'garden'  => $this->createGardenOrderItem($order, $item),
                    default   => Log::warning('OrderService: unknown item type', [
                        'type'     => $type,
                        'order_id' => $order->id,
                    ]),
                };
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
    // Item creators
    // -------------------------------------------------------

    private function createProductOrderItem(Order $order, array $item): void
    {
        $variant = $this->resolveDefaultVariant((int) $item['product_id']);

        if (!$variant) {
            Log::warning('OrderService: no default variant for product', [
                'product_id' => $item['product_id'],
                'order_id'   => $order->id,
            ]);
            return;
        }

        OrderItem::create([
            'order_id'           => $order->id,
            'item_type'          => 'product',
            'item_id'            => $variant->id,
            'product_variant_id' => $variant->id,
            'quantity'           => (int) $item['quantity'],
            'price_at_purchase'  => (float) $item['unit_price'],
            'display_name'       => $item['name'] ?? null,
            'display_image'      => null, // resolved via variant → product → images relationship
        ]);
    }

    /**
     * Creates one OrderItem row per product found across all features of the plan.
     *
     * A plan has many features (plan_feature pivot), each feature has many
     * products (feature_product pivot, keyed on product_sku <-> sku).
     * Each product gets its own row so the dashboard can show the full
     * product list with individual names and images.
     *
     * The plan's total unit_price is split evenly across all products so
     * that line totals still sum to the correct plan price.
     */
    private function createPlanOrderItems(Order $order, array $item): void
    {
        $plan = Plan::find($item['plan_id'] ?? null);

        if (!$plan) {
            Log::warning('OrderService: plan not found', [
                'plan_id'  => $item['plan_id'] ?? null,
                'order_id' => $order->id,
            ]);
            return;
        }

        // Use the exact slugs from the assessment packaging — these are the
        // products the user actually saw and purchased, not all plan features.
        $productSlugs = $item['product_slugs'] ?? [];

        if (empty($productSlugs)) {
            // Fallback: load all unique products from plan features
            $plan->loadMissing('features.products.images');
            $products = $plan->features
                ->flatMap(fn($f) => $f->products)
                ->unique('sku');
        } else {
            $products = Product::with('images')
                ->whereIn('sku', $productSlugs)
                ->get();
        }

        if ($products->isEmpty()) {
            Log::warning('OrderService: plan has no products, creating summary row', [
                'plan_id'  => $plan->id,
                'order_id' => $order->id,
            ]);

            OrderItem::create([
                'order_id'           => $order->id,
                'item_type'          => 'plan',
                'item_id'            => $plan->id,
                'product_variant_id' => null,
                'quantity'           => (int) $item['quantity'],
                'price_at_purchase'  => (float) $item['unit_price'],
                'display_name'       => $plan->name,
                'display_image'      => null,
            ]);

            return;
        }

        $productCount    = $products->count();
        $pricePerProduct = round((float) $item['unit_price'] / $productCount, 2);

        foreach ($products as $product) {
            $imageUrl = $product->images->firstWhere('is_primary', true)?->image_url
                    ?? $product->images->first()?->image_url;

            if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = '/storage/' . ltrim($imageUrl, '/');
            }

            OrderItem::create([
                'order_id'           => $order->id,
                'item_type'          => 'plan',
                'item_id'            => $plan->id,
                'product_variant_id' => null,
                'quantity'           => (int) $item['quantity'],
                'price_at_purchase'  => $pricePerProduct,
                'display_name'       => $product->name,
                'display_image'      => $imageUrl,
            ]);
        }
    }

    private function createGardenOrderItem(Order $order, array $item): void
    {
        foreach ($item['items'] as $subItem) {
            OrderItem::create([
                'order_id'           => $order->id,
                'item_type'          => 'garden',
                'item_id'            => null,
                'product_variant_id' => null,
                'quantity'           => (int) $subItem['quarts'],
                'price_at_purchase'  => (float) $subItem['price_per_quart'],
                'display_name'       => $subItem['name'] ?? 'Garden Care',
                'display_image'      => null,
            ]);
        }
    }

    // -------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------

    private function resolveDefaultVariant(int $productId): ?ProductVariant
    {
        return ProductVariant::where('product_id', $productId)
            ->where('is_default', true)
            ->first()
            ?? ProductVariant::where('product_id', $productId)
                ->orderBy('sort_order')
                ->first();
    }

    private function resolveShippingAddress(CheckoutSession $session): ?array
    {
        $addressId = $session->shipping_address_id;

        $address = $addressId
            ? ShippingAddress::find($addressId)
            : ShippingAddress::where('user_id', $session->user_id)
                ->where('is_default', true)
                ->first();

        if (!$address) {
            Log::warning('OrderService: no shipping address found', [
                'user_id'             => $session->user_id,
                'shipping_address_id' => $addressId,
            ]);
            return null;
        }

        return [
            'first_name'    => $address->first_name,
            'last_name'     => $address->last_name,
            'phone'         => $address->phone,
            'address_line1' => $address->address_line1,
            'address_line2' => $address->address_line2,
            'city'          => $address->city,
            'state'         => $address->state,
            'zip_code'      => $address->zip_code,
        ];
    }
}