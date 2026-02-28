<?php

namespace App\Services\Lawn;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Determines whether a product is included in a given tier or charged as an add-on.
 *
 * Rules are driven by the products.tier_inclusion column — not hardcoded.
 * To change a product's tier: UPDATE products SET tier_inclusion = 'silver' WHERE slug = 'microbe-boost'
 * No code changes needed.
 *
 * Tier inheritance:
 *   bronze → products with tier_inclusion = 'bronze'
 *   silver → bronze + silver
 *   gold   → bronze + silver + gold
 *   addon  → always charged separately, regardless of tier
 */
final class TierInclusionMap
{
    private const CACHE_KEY = 'tier_inclusion_map';
    private const CACHE_TTL = 3600 * 24; // 24 hours

    private const TIER_HIERARCHY = [
        'bronze' => ['bronze'],
        'silver' => ['bronze', 'silver'],
        'gold'   => ['bronze', 'silver', 'gold'],
    ];

    /**
     * Returns true if the product slug is included in the given tier.
     * Returns false if it is an add-on for this tier.
     */
    public function isIncluded(string $slug, string $tier): bool
    {
        $map              = $this->loadMap();
        $productInclusion = $map[$slug] ?? 'addon';

        if ($productInclusion === 'addon') {
            return false;
        }

        $includedLevels = self::TIER_HIERARCHY[strtolower($tier)] ?? ['bronze'];

        return in_array($productInclusion, $includedLevels, true);
    }

    public function invalidate(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // -------------------------------------------------------
    // Internals
    // -------------------------------------------------------

    /**
     * Returns slug => tier_inclusion map for all active products.
     * e.g. ['kickstart' => 'bronze', 'patchpro' => 'silver', 'sulfacore' => 'addon']
     */
    private function loadMap(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return DB::table('products')
                ->where('is_active', true)
                ->pluck('tier_inclusion', 'slug')
                ->all();
        });
    }
}