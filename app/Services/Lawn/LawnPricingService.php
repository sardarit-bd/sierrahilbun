<?php

namespace App\Services\Lawn;

use App\Services\CacheService;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the scaled base price for a given lawn plan tier.
 *
 * Price = plan.base_price_yearly × sqft_band_multiplier
 *
 * Uses current_price_yearly if set (promotional/discounted),
 * otherwise falls back to base_price_yearly.
 *
 * Multiplier table (up to 1 acre / 43,560 sq ft):
 *   0–4,000      × 1.0
 *   4,001–8,000  × 1.4
 *   8,001–12,000 × 1.8
 *   12,001–20,000× 2.2
 *   20,001–32,000× 3.0
 *   32,001–43,560× 4.0
 *
 * Over 1 acre (> 43,560 sq ft):
 *   Base multiplier = 4.0
 *   +1.0 per additional acre (rounded up)
 *   e.g. 2.2 acres → 4.0 + ceil(1.2) = 4.0 + 2 = 6.0
 */
final class LawnPricingService
{
    private const SQFT_BANDS = [
        [0,     4000,  1.0],
        [4001,  8000,  1.4],
        [8001,  12000, 1.8],
        [12001, 20000, 2.2],
        [20001, 32000, 3.0],
        [32001, 43560, 4.0],
    ];

    private const ONE_ACRE_SQFT = 43560;

    private const BASE_MULTIPLIER_AT_ONE_ACRE = 4.0;

    private const FALLBACK_PRICES = [
        'bronze' => 179.00,
        'silver' => 249.00,
        'gold'   => 289.00,
    ];

    private const CACHE_TTL  = 3600 * 24;
    private const CACHE_TAGS = ['lawn_pricing'];

    public function __construct(
        private readonly CacheService $cache,
    ) {}

    // -------------------------------------------------------
    // Public API
    // -------------------------------------------------------

    /**
     * Returns the scaled price for a given tier and lawn size.
     *
     * This is the base price passed into PackagingService —
     * add-on products are then added on top of this.
     */
    public function scaledPrice(string $tier, int $squareFeet): float
    {
        $base = $this->basePriceForTier($tier);

        return round($base * $this->multiplierForSqft($squareFeet), 2);
    }

    /**
     * Returns scaled prices for all three tiers at once.
     * Used when building all plans for display.
     *
     * @return array<string, float> ['bronze' => 179.00, 'silver' => ...]
     */
    public function allScaledPrices(int $squareFeet): array
    {
        $multiplier = $this->multiplierForSqft($squareFeet);

        return array_map(
            fn (float $base) => round($base * $multiplier, 2),
            $this->allBasePrices(),
        );
    }

    // -------------------------------------------------------
    // Internals
    // -------------------------------------------------------

    private function basePriceForTier(string $tier): float
    {
        return $this->allBasePrices()[$tier]
            ?? self::FALLBACK_PRICES[$tier]
            ?? 179.00;
    }

    /**
     * Loads base prices for all lawn plans from DB.
     * Uses current_price_yearly if set, otherwise base_price_yearly.
     *
     * @return array<string, float>
     */
    private function allBasePrices(): array
    {
        return $this->cache->remember(
            'lawn_plan_base_prices',
            self::CACHE_TTL,
            function () {
                $plans = DB::table('plans')
                    ->join('services', 'services.id', '=', 'plans.service_id')
                    ->where('services.slug', 'lawn')
                    ->select('plans.slug', 'plans.base_price_yearly', 'plans.current_price_yearly')
                    ->get();

                $prices = [];

                foreach ($plans as $plan) {
                    $tier          = last(explode('-', $plan->slug));
                    $prices[$tier] = (float) ($plan->current_price_yearly ?? $plan->base_price_yearly);
                }

                return $prices;
            },
            self::CACHE_TAGS,
        );
    }

    /**
     * Returns the size multiplier for a given square footage.
     *
     * For lawns up to 1 acre (43,560 sq ft): uses the band table.
     * For lawns over 1 acre: starts at 4.0 and adds +1.0 per
     * additional acre (rounded up).
     *
     * Examples:
     *   43,560 sq ft (1.0 acre)  → 4.0
     *   87,120 sq ft (2.0 acres) → 4.0 + 1 = 5.0
     *   94,090 sq ft (2.16 acres)→ 4.0 + ceil(1.16) = 4.0 + 2 = 6.0
     */
    private function multiplierForSqft(int $sqft): float
    {
        foreach (self::SQFT_BANDS as [$min, $max, $multiplier]) {
            if ($sqft >= $min && $sqft <= $max) {
                return $multiplier;
            }
        }

        // Over 1 acre: +1.0 per additional acre (rounded up)
        $additionalAcres = ($sqft - self::ONE_ACRE_SQFT) / self::ONE_ACRE_SQFT;

        return self::BASE_MULTIPLIER_AT_ONE_ACRE + ceil($additionalAcres);
    }
}