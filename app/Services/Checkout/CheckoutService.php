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

    private const PROMO_CODES = [
        'SAVE10' => ['type' => 'percentage', 'value' => 10],
        'SAVE20' => ['type' => 'percentage', 'value' => 20],
        'FLAT5'  => ['type' => 'fixed',      'value' => 5],
    ];

    public function createSession(
        int     $userId,
        array   $cartItems,  // [{ product_id, quantity }]
        ?string $promoCode = null,
        string  $currency  = 'USD',
    ): CheckoutSession {

        $calculation = $this->calculate($cartItems, $promoCode, $currency);

        return DB::transaction(function () use ($userId, $calculation, $promoCode) {

            // Expire any existing pending sessions for this user
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

        // Step 1: Fetch real prices from DB — never trust frontend prices
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
            ? \App\Models\Plan::whereIn('id', $planIds)->get()->keyBy('id')
            : collect();

        // Step 2: Build verified items with server prices
        $verifiedItems = [];
        $subtotal      = 0.00;

        foreach ($cartItems as $item) {
            $quantity = max(1, (int) $item['quantity']);

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

        // Apply max discount cap if set
        if ($promo->max_discount) {
            $discountAmount = min($discountAmount, (float) $promo->max_discount);
        }

        $validatedPromo = strtoupper(trim($promoCode));
    }


        // Step 4: Calculate shipping
        $taxableAmount = $subtotal - $discountAmount;
        $shippingCost  = $taxableAmount >= self::SHIPPING_THRESHOLD ? 0.00 : self::SHIPPING_COST;

        // Step 5: Calculate tax on (subtotal - discount)
        $taxAmount = round($taxableAmount * self::TAX_RATE, 2);

        // Step 6: Final total
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