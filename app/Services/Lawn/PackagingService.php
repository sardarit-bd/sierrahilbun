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
 * For each recommended product:
 *   1. Calculate total oz needed (oz_per_1000 × sqft / 1000)
 *   2. Select optimal variant combination (never under-ship)
 *   3. Determine pricing type from TierInclusionMap (included vs addon)
 *   4. Calculate line price (included = $0, addon = unit_price × qty)
 */
final class PackagingService
{
    public function __construct(
        private readonly VariantSelectorService $variantSelector,
        private readonly TierInclusionMap       $tierInclusionMap,
    ) {}

    public function package(
        RecommendationResultDTO $recommendation,
        int                     $squareFeet,
        string                  $tier,
        float                   $basePrice,
    ): PackagingResultDTO {
        $sqftFactor = $squareFeet / 1000.0;

        // Build full product list: core + modifiers
        $products = $this->buildProductList($recommendation);

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
                ]);
                continue;
            }

            $ozShipped  = (float) array_sum(array_map(
                fn ($u) => $u['size_volume_oz'] * $u['qty'],
                $units,
            ));

            [$unitPrice, $lineTotal] = $this->calculatePrice($units, $isIncluded);

            $addonsTotal += $lineTotal;

            // Fetch product name for the line
            $name = $this->resolveProductName($slug);

            $lines[] = new PackagingLineDTO(
                slug:        $slug,
                name:        $name,
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

        // Core products
        foreach ($recommendation->core->toArray() as $slug => $ozPer1000) {
            $products[$slug] = $ozPer1000;
        }

        // Modifier products
        foreach ($recommendation->modifiers as $modifier) {
            $products[$modifier->slug] = $modifier->ozPer1000;
        }

        return $products;
    }

    /**
     * For included products: price is $0.
     * For add-on products: price = unit_price of default (first) variant × qty.
     * Uses the largest shipped variant's unit price as the reference price.
     */
    private function calculatePrice(array $units, bool $isIncluded): array
    {
        if ($isIncluded) {
            return [0.0, 0.0];
        }

        // Use the unit price of the largest container shipped
        $largestUnit = collect($units)->sortByDesc('size_volume_oz')->first();
        $unitPrice   = (float) ($largestUnit['unit_price'] ?? 0.0);

        $lineTotal = array_sum(array_map(
            fn ($u) => $u['unit_price'] * $u['qty'],
            $units,
        ));

        return [$unitPrice, $lineTotal];
    }

    /**
     * Resolve product display name from DB.
     * In-memory cached per service instance to avoid repeated queries.
     */
    private array $nameCache = [];

    private function resolveProductName(string $slug): string
    {
        if (!isset($this->nameCache[$slug])) {
            $this->nameCache[$slug] = DB::table('products')
                ->where('slug', $slug)
                ->value('name') ?? $slug;
        }

        return $this->nameCache[$slug];
    }
}