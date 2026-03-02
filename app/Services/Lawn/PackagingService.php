<?php

namespace App\Services\Lawn;

use App\DTOs\Lawn\PackagingLineDTO;
use App\DTOs\Lawn\PackagingResultDTO;
use App\DTOs\Lawn\RecommendationResultDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Converts a RecommendationResultDTO into a PackagingResultDTO.
 *
 * For each product in the tier's product set:
 *   1. Calculate total oz needed (oz_per_1000 × sqft / 1000)
 *   2. Select optimal variant combination (never under-ship)
 *   3. Determine pricing type from TierInclusionMap (included vs addon)
 *   4. Calculate line price (included = $0, addon = unit_price × qty)
 *
 * The $productSlugs parameter scopes packaging to a specific tier's
 * product set — bronze gets fewer products than gold.
 */
final class PackagingService
{
    public function __construct(
        private readonly VariantSelectorService $variantSelector,
        private readonly TierInclusionMap       $tierInclusionMap,
    ) {}

    /**
     * @param string[] $productSlugs  SKUs for this specific tier's product set.
     *                                Passed by PlanResolverService — scopes which
     *                                products are packaged for bronze/silver/gold.
     */
    public function package(
        RecommendationResultDTO $recommendation,
        int                     $squareFeet,
        string                  $tier,
        float                   $basePrice,
        array                   $productSlugs = [],
    ): PackagingResultDTO {
        $sqftFactor = $squareFeet / 1000.0;

        // Build full product list from engine output (slug => oz_per_1000)
        $allProducts = $this->buildProductList($recommendation);

        // Scope to this tier's product set — empty means include all
        $products = empty($productSlugs)
            ? $allProducts
            : array_intersect_key($allProducts, array_flip($productSlugs));

        // Pre-load all variants in one query
        $this->variantSelector->preload(array_keys($products));

        $lines       = [];
        $addonsTotal = 0.0;

        foreach ($products as $slug => $ozPer1000) {
            $ozNeeded   = $ozPer1000 * $sqftFactor;
            $units      = $this->variantSelector->select($slug, $ozNeeded);
            $isIncluded = $this->tierInclusionMap->isIncluded($slug, $tier);

            if (empty($units)) {
                Log::warning('PackagingService: no variants found for product', [
                    'slug' => $slug,
                    'tier' => $tier,
                ]);
                continue;
            }

            $ozShipped = (float) array_sum(array_map(
                fn ($u) => $u['size_volume_oz'] * $u['qty'],
                $units,
            ));

            [$unitPrice, $lineTotal] = $this->calculatePrice($units, $isIncluded);

            $addonsTotal += $lineTotal;

            $lines[] = new PackagingLineDTO(
                slug:        $slug,
                name:        $this->resolveProductName($slug),
                pricingType: $isIncluded ? 'included' : 'addon',
                ozNeeded:    $ozNeeded,
                ozShipped:   $ozShipped,
                units:       $units,
                unitPrice:   $unitPrice,
                totalPrice:  $lineTotal,
            );
        }

        $totalPrice = $basePrice + $addonsTotal;

        return new PackagingResultDTO(
            lines:       $lines,
            addonsTotal: $addonsTotal,
            basePrice:   $basePrice,
            totalPrice:  $totalPrice,
        );
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Merge core + modifiers into a single slug => oz_per_1000 map.
     */
    private function buildProductList(RecommendationResultDTO $recommendation): array
    {
        $products = [];

        foreach ($recommendation->core->toArray() as $slug => $ozPer1000) {
            $products[$slug] = $ozPer1000;
        }

        foreach ($recommendation->modifiers as $modifier) {
            $products[$modifier->slug] = $modifier->ozPer1000;
        }

        return $products;
    }

    /**
     * For included products: price is $0.
     * For add-on products: price = unit_price × qty per unit shipped.
     */
    private function calculatePrice(array $units, bool $isIncluded): array
    {
        if ($isIncluded) {
            return [0.0, 0.0];
        }

        $largestUnit = collect($units)->sortByDesc('size_volume_oz')->first();
        $unitPrice   = (float) ($largestUnit['unit_price'] ?? 0.0);

        $lineTotal = array_sum(array_map(
            fn ($u) => $u['unit_price'] * $u['qty'],
            $units,
        ));

        return [$unitPrice, $lineTotal];
    }

    private array $nameCache = [];

    private function resolveProductName(string $slug): string
    {
        if (! isset($this->nameCache[$slug])) {
            $this->nameCache[$slug] = DB::table('products')
                ->where('slug', $slug)
                ->value('name') ?? $slug;
        }

        return $this->nameCache[$slug];
    }
}