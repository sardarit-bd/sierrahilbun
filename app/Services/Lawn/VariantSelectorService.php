<?php

namespace App\Services\Lawn;

use Illuminate\Support\Facades\DB;

/**
 * Selects the optimal product_variants combination for a required oz amount.
 *
 * Rules (in priority order):
 *   1. Never under-ship — always ship >= oz needed
 *   2. Minimize overage first
 *   3. Minimize container count second
 *   4. Prefer larger containers (tertiary tie-breaker)
 *
 * Large Volume Shortcut:
 *   If the requirement is >= 40 gallons OR the naive selection produces
 *   > 12 small jugs, bypass the combination search and round up directly
 *   to the largest available container size. This avoids absurd results
 *   like shipping 20 quart bottles for a large lawn.
 *
 * Loads all variants for requested slugs in a single query (no N+1).
 * Variants are cached per service instance lifetime (per request).
 */
final class VariantSelectorService
{
    /**
     * Gallons threshold that triggers the large volume shortcut.
     * 40 gal × 128 oz/gal = 5,120 oz
     */
    private const LARGE_VOLUME_THRESHOLD_GAL = 40;

    /**
     * Maximum small-jug count before the large volume shortcut kicks in.
     */
    private const MAX_SMALL_JUG_COUNT = 12;

    /**
     * Containers at or below this size (oz) are considered "small jugs"
     * for the purposes of the jug-count shortcut.
     * 1 gal = 128 oz — anything quart (32 oz) or pint (16 oz) is small.
     */
    private const SMALL_JUG_MAX_OZ = 128;

    /**
     * In-memory cache of variants per slug for this request.
     * @var array<string, array>
     */
    private array $variantCache = [];

    // -------------------------------------------------------
    // Public API
    // -------------------------------------------------------

    /**
     * Select optimal variants for a product slug given oz needed.
     *
     * Returns array of unit lines:
     * [
     *   ['variant_id' => 14, 'sku' => 'KICKSTART-1-GALLON', 'size_label' => '1 Gallon',
     *    'size_volume_oz' => 128, 'qty' => 1, 'unit_price' => 89.99],
     *   ...
     * ]
     */
    public function select(string $slug, float $ozNeeded): array
    {
        $variants = $this->getVariants($slug);

        if (empty($variants)) {
            return [];
        }

        return $this->optimize($variants, $ozNeeded);
    }

    /**
     * Pre-load variants for multiple slugs in one query.
     * Call this before a batch of select() calls to avoid repeated DB hits.
     */
    public function preload(array $slugs): void
    {
        $missing = array_filter(
            $slugs,
            fn (string $slug) => ! isset($this->variantCache[$slug])
        );

        if (empty($missing)) {
            return;
        }

        $rows = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->whereIn('p.slug', $missing)
            ->where('p.is_active', true)
            ->select(
                'p.slug',
                'pv.id as variant_id',
                'pv.sku',
                'pv.size_label',
                'pv.size_volume_oz',
                'pv.price',
                'pv.sort_order',
            )
            ->orderBy('p.slug')
            ->orderBy('pv.sort_order')
            ->get();

        foreach ($rows as $row) {
            $this->variantCache[$row->slug][] = (array) $row;
        }

        // Ensure missing slugs have an empty array so we don't re-query
        foreach ($missing as $slug) {
            $this->variantCache[$slug] ??= [];
        }
    }

    // -------------------------------------------------------
    // Optimization entry point
    // -------------------------------------------------------

    /**
     * Decide whether to use the large volume shortcut or the
     * standard combination optimizer.
     *
     * Large volume shortcut triggers when:
     *   - ozNeeded >= 40 gal (5,120 oz), OR
     *   - naive small-jug fill would require > 12 containers
     *
     * In both cases we round up directly to the largest container.
     */
    private function optimize(array $variants, float $ozNeeded): array
    {
        // Sort variants descending by size — prefer larger containers
        usort($variants, fn ($a, $b) => $b['size_volume_oz'] <=> $a['size_volume_oz']);

        if ($this->shouldUseLargeVolumeShortcut($variants, $ozNeeded)) {
            return $this->largeVolumeShortcut($variants, $ozNeeded);
        }

        return $this->optimizeStandard($variants, $ozNeeded);
    }

    // -------------------------------------------------------
    // Large volume shortcut
    // -------------------------------------------------------

    /**
     * Determines whether the large volume shortcut should be applied.
     *
     * Triggers if EITHER condition is true:
     *   1. ozNeeded >= 40 gallons (5,120 oz)
     *   2. Filling with small jugs alone would need > 12 containers
     */
    private function shouldUseLargeVolumeShortcut(array $variants, float $ozNeeded): bool
    {
        // Condition 1: volume threshold
        $thresholdOz = self::LARGE_VOLUME_THRESHOLD_GAL * 128;

        if ($ozNeeded >= $thresholdOz) {
            return true;
        }

        // Condition 2: small-jug count threshold
        $smallJugs = array_filter(
            $variants,
            fn ($v) => $v['size_volume_oz'] <= self::SMALL_JUG_MAX_OZ
        );

        if (empty($smallJugs)) {
            return false;
        }

        // Use the largest small jug for the count estimate
        $largestSmall   = reset($smallJugs); // already sorted desc
        $estimatedCount = (int) ceil($ozNeeded / $largestSmall['size_volume_oz']);

        return $estimatedCount > self::MAX_SMALL_JUG_COUNT;
    }

    /**
     * Large volume shortcut: round up to the largest available container.
     *
     * Strategy:
     *   Fill as many of the largest container as needed to meet ozNeeded.
     *   This minimizes container count and prefers large jugs — both goals
     *   align when volume is high.
     *
     * Example:
     *   ozNeeded = 6,400 oz, largest variant = 55 gal (7,040 oz)
     *   → 1 × 55 gal jug (ships 7,040 oz, overage = 640 oz)
     *
     *   ozNeeded = 15,000 oz, largest variant = 55 gal (7,040 oz)
     *   → ceil(15000 / 7040) = 3 × 55 gal jugs (ships 21,120 oz)
     */
    private function largeVolumeShortcut(array $variants, float $ozNeeded): array
    {
        $largest = $variants[0]; // already sorted desc
        $qty     = (int) ceil($ozNeeded / $largest['size_volume_oz']);

        return $this->formatUnits([['variant' => $largest, 'qty' => $qty]]);
    }

    // -------------------------------------------------------
    // Standard combination optimizer
    // -------------------------------------------------------

    private function optimizeStandard(array $variants, float $ozNeeded): array
    {
        $best = null;

        foreach ($this->generateCombinations($variants, $ozNeeded) as $combination) {
            $shipped = $this->totalOz($combination);

            if ($shipped < $ozNeeded) {
                continue; // never under-ship
            }

            $overage = $shipped - $ozNeeded;
            $count   = array_sum(array_column($combination, 'qty'));

            if ($best === null) {
                $best = ['combination' => $combination, 'overage' => $overage, 'count' => $count];
                continue;
            }

            // Minimize overage first, then container count
            if ($overage < $best['overage']
                || ($overage === $best['overage'] && $count < $best['count'])
            ) {
                $best = ['combination' => $combination, 'overage' => $overage, 'count' => $count];
            }
        }

        if ($best === null) {
            return $this->fallback($variants, $ozNeeded);
        }

        return $this->formatUnits($best['combination']);
    }

    /**
     * Generate candidate combinations.
     *
     * Strategy:
     *   For each variant size as the "primary" container:
     *     - Fill as many of that size as possible without going over
     *     - Then cover remainder with next smaller sizes
     *   Also try: just enough of the largest size to cover entirely
     */
    private function generateCombinations(array $variants, float $ozNeeded): array
    {
        $combinations = [];

        // Strategy 1: greedy from largest to smallest
        $combinations[] = $this->greedyFill($variants, $ozNeeded);

        // Strategy 2: for each size, try using just enough of that size alone
        foreach ($variants as $variant) {
            $qty            = (int) ceil($ozNeeded / $variant['size_volume_oz']);
            $combinations[] = [['variant' => $variant, 'qty' => $qty]];
        }

        // Strategy 3: greedy fill stopping one short on largest, fill rest with smaller
        if (count($variants) > 1) {
            $largest = $variants[0];
            $maxQty  = (int) floor($ozNeeded / $largest['size_volume_oz']);

            if ($maxQty > 0) {
                $remainder      = $ozNeeded - ($maxQty * $largest['size_volume_oz']);
                $rest           = $this->greedyFill(array_slice($variants, 1), $remainder);
                $combo          = array_merge([['variant' => $largest, 'qty' => $maxQty]], $rest);
                $combinations[] = $combo;
            }
        }

        return $combinations;
    }

    private function greedyFill(array $variants, float $ozNeeded): array
    {
        $result    = [];
        $remaining = $ozNeeded;

        foreach ($variants as $variant) {
            if ($remaining <= 0) {
                break;
            }

            $qty = (int) floor($remaining / $variant['size_volume_oz']);

            if ($qty > 0) {
                $result[]   = ['variant' => $variant, 'qty' => $qty];
                $remaining -= $qty * $variant['size_volume_oz'];
            }
        }

        // Cover any remaining oz with the smallest available variant
        if ($remaining > 0.001) {
            $smallest  = end($variants);
            $lastIndex = count($result) - 1;

            if ($lastIndex >= 0
                && $result[$lastIndex]['variant']['variant_id'] === $smallest['variant_id']
            ) {
                $result[$lastIndex]['qty']++;
            } else {
                $result[] = ['variant' => $smallest, 'qty' => 1];
            }
        }

        return $result;
    }

    private function fallback(array $variants, float $ozNeeded): array
    {
        // Pick the smallest variant that covers ozNeeded, or largest available
        foreach (array_reverse($variants) as $variant) {
            if ($variant['size_volume_oz'] >= $ozNeeded) {
                return $this->formatUnits([['variant' => $variant, 'qty' => 1]]);
            }
        }

        $largest = $variants[0];
        $qty     = (int) ceil($ozNeeded / $largest['size_volume_oz']);

        return $this->formatUnits([['variant' => $largest, 'qty' => $qty]]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function totalOz(array $combination): float
    {
        return array_sum(array_map(
            fn ($item) => $item['variant']['size_volume_oz'] * $item['qty'],
            $combination,
        ));
    }

    private function formatUnits(array $combination): array
    {
        return array_values(array_map(
            fn ($item) => [
                'variant_id'     => $item['variant']['variant_id'],
                'sku'            => $item['variant']['sku'],
                'size_label'     => $item['variant']['size_label'],
                'size_volume_oz' => $item['variant']['size_volume_oz'],
                'qty'            => $item['qty'],
                'unit_price'     => (float) $item['variant']['price'],
            ],
            array_filter($combination, fn ($item) => $item['qty'] > 0),
        ));
    }

    // -------------------------------------------------------
    // Variant loading
    // -------------------------------------------------------

    private function getVariants(string $slug): array
    {
        if (! isset($this->variantCache[$slug])) {
            $this->preload([$slug]);
        }

        return $this->variantCache[$slug] ?? [];
    }
}