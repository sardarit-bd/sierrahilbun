<?php

namespace App\Services\Checkout;

use App\Models\CheckoutSession;
use App\Models\Product;
use App\Models\Plan;
use App\Models\PromoCode;
use App\Services\Checkout\DTO\CheckoutCalculationDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutService
{
    private const SHIPPING_THRESHOLD = 75.00;
    private const SHIPPING_COST      = 9.99;
    private const TAX_RATE           = 0.08;
    private const SESSION_TTL        = 30; // minutes

    // Server-side garden pricing constants — never trusted from frontend
    private const GARDEN_PRICE_PER_QUART = 30.00;
    private const GARDEN_SQFT_PER_QUART  = 500;

    private const GARDEN_SIZE_SQFT_MAP = [
        'xs' => 250,
        'sm' => 750,
        'l'  => 1500,
    ];

    private const GARDEN_PRODUCTS = [
        ['slug' => 'garden-boost',  'name' => 'Garden Boost'],
        ['slug' => 'garden-revive', 'name' => 'Garden Revive'],
    ];

    private const PROMO_CODES = [
        'SAVE10' => ['type' => 'percentage', 'value' => 10],
        'SAVE20' => ['type' => 'percentage', 'value' => 20],
        'FLAT5'  => ['type' => 'fixed',      'value' => 5],
    ];

    public function createSession(
        int     $userId,
        array   $cartItems,
        ?string $promoCode = null,
        string  $currency  = 'USD',
    ): CheckoutSession {

        $calculation = $this->calculate($cartItems, $promoCode, $currency);

        return DB::transaction(function () use ($userId, $calculation, $promoCode) {

            CheckoutSession::where('user_id', $userId)
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            return CheckoutSession::create([
                'session_id'      => Str::uuid()->toString(),
                'user_id'         => $userId,
                'subtotal'        => $calculation->subtotal,
                'discount_amount' => $calculation->discountAmount,
                'shipping_cost'   => $calculation->shippingCost,
                'tax_amount'      => $calculation->taxAmount,
                'total'           => $calculation->total,
                'currency'        => $calculation->currency,
                'promo_code'      => $promoCode,
                'items'           => $calculation->items,
                'status'          => 'pending',
                'expires_at'      => now()->addMinutes(self::SESSION_TTL),
            ]);
        });
    }

    public function calculate(
        array   $cartItems,
        ?string $promoCode = null,
        string  $currency  = 'USD',
    ): CheckoutCalculationDTO {

        // Step 1: Collect IDs for batch DB fetches
        $productIds = collect($cartItems)
            ->where('type', 'product')
            ->pluck('product_id')
            ->unique()
            ->toArray();

        $planIds = collect($cartItems)
            ->where('type', 'plan')
            ->pluck('plan_id')
            ->unique()
            ->toArray();

        $products = !empty($productIds)
            ? Product::whereIn('id', $productIds)->where('is_active', true)->get()->keyBy('id')
            : collect();

        $plans = !empty($planIds)
            ? Plan::whereIn('id', $planIds)->get()->keyBy('id')
            : collect();

        // Step 2: Build verified items with server-side prices
        $verifiedItems = [];
        $subtotal      = 0.00;

        foreach ($cartItems as $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));

            // ── Plan item ──────────────────────────────────────────
            if ($item['type'] === 'plan') {
                $plan = $plans->get($item['plan_id']);

                if (!$plan) {
                    throw new \InvalidArgumentException(
                        "Plan [{$item['plan_id']}] not found or unavailable."
                    );
                }

                $yearlyPrice = (float) ($plan->current_price_yearly ?? $plan->base_price_yearly);
                $unitPrice   = round($yearlyPrice / 12, 2);
                $lineTotal   = round($unitPrice * $quantity, 2);
                $subtotal   += $lineTotal;

                $verifiedItems[] = [
                    'type'       => 'plan',
                    'plan_id'    => $plan->id,
                    'name'       => $plan->name,
                    'unit_price' => $unitPrice,
                    'quantity'   => $quantity,
                    'line_total' => $lineTotal,
                ];

                continue;
            }

            // ── Garden item ────────────────────────────────────────
            // Price is fully recomputed server-side from garden_size.
            // Frontend garden_products payload is used only for garden_size —
            // quarts and totals are recalculated here; never trusted from client.
            if ($item['type'] === 'garden') {
                $gardenProducts = $item['garden_products'] ?? null;
                $gardenSize     = $gardenProducts['garden_size'] ?? null;

                if (!$gardenSize || !isset(self::GARDEN_SIZE_SQFT_MAP[$gardenSize])) {
                    throw new \InvalidArgumentException(
                        "Invalid or missing garden size in garden_products payload."
                    );
                }

                $sqft   = self::GARDEN_SIZE_SQFT_MAP[$gardenSize];
                $quarts = (int) ceil($sqft / self::GARDEN_SQFT_PER_QUART);

                $gardenLineItems = [];
                $gardenTotal     = 0.00;

                foreach (self::GARDEN_PRODUCTS as $product) {
                    $lineTotal        = round($quarts * self::GARDEN_PRICE_PER_QUART, 2);
                    $gardenTotal     += $lineTotal;
                    $gardenLineItems[] = [
                        'slug'            => $product['slug'],
                        'name'            => $product['name'],
                        'quarts'          => $quarts,
                        'price_per_quart' => self::GARDEN_PRICE_PER_QUART,
                        'line_total'      => $lineTotal,
                    ];
                }

                $gardenTotal = round($gardenTotal, 2);
                $subtotal   += $gardenTotal;

                $verifiedItems[] = [
                    'type'        => 'garden',
                    'name'        => 'Garden Care',
                    'garden_size' => $gardenSize,
                    'items'       => $gardenLineItems,
                    'unit_price'  => $gardenTotal,
                    'quantity'    => $quantity,
                    'line_total'  => round($gardenTotal * $quantity, 2),
                ];

                continue;
            }

            // ── Product item ───────────────────────────────────────
            $product = $products->get($item['product_id']);

            if (!$product) {
                throw new \InvalidArgumentException(
                    "Product [{$item['product_id']}] not found or unavailable."
                );
            }

            $unitPrice  = (float) $product->base_price;
            $lineTotal  = round($unitPrice * $quantity, 2);
            $subtotal  += $lineTotal;

            $verifiedItems[] = [
                'type'       => 'product',
                'product_id' => $product->id,
                'name'       => $product->name,
                'unit_price' => $unitPrice,
                'quantity'   => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        // Step 3: Apply promo code
        $discountAmount = 0.00;
        $validatedPromo = null;

        if ($promoCode) {
            $promo = PromoCode::where('code', strtoupper(trim($promoCode)))->first();

            if (!$promo) {
                throw new \InvalidArgumentException("Invalid promo code: [{$promoCode}]");
            }

            if (!$promo->isValid()) {
                throw new \InvalidArgumentException("Promo code has expired or is no longer valid.");
            }

            if ($subtotal < $promo->min_purchase) {
                throw new \InvalidArgumentException(
                    "Minimum purchase of \${$promo->min_purchase} required for this promo code."
                );
            }

            $discountAmount = match($promo->type) {
                'percentage' => round($subtotal * ($promo->value / 100), 2),
                'fixed'      => min((float) $promo->value, $subtotal),
            };

            if ($promo->max_discount) {
                $discountAmount = min($discountAmount, (float) $promo->max_discount);
            }

            $validatedPromo = strtoupper(trim($promoCode));
        }

        // Step 4: Shipping
        $taxableAmount = $subtotal - $discountAmount;
        $shippingCost  = $taxableAmount >= self::SHIPPING_THRESHOLD ? 0.00 : self::SHIPPING_COST;

        // Step 5: Tax
        $taxAmount = round($taxableAmount * self::TAX_RATE, 2);

        // Step 6: Total
        $total = round($taxableAmount + $shippingCost + $taxAmount, 2);

        return new CheckoutCalculationDTO(
            subtotal:       $subtotal,
            discountAmount: $discountAmount,
            shippingCost:   $shippingCost,
            taxAmount:      $taxAmount,
            total:          $total,
            currency:       $currency,
            items:          $verifiedItems,
            promoCode:      $validatedPromo,
        );
    }

    public function getValidSession(string $sessionId, int $userId): CheckoutSession
    {
        $session = CheckoutSession::where('session_id', $sessionId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($session->isExpired()) {
            $session->update(['status' => 'expired']);
            throw new \RuntimeException('Checkout session has expired. Please try again.');
        }

        return $session;
    }

    public function completeSession(string $sessionId): void
    {
        CheckoutSession::where('session_id', $sessionId)
            ->update(['status' => 'completed']);
    }
}