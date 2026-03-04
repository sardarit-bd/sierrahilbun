<?php

namespace App\Services\Lawn;

class GardenQuizCalculatorService
{
    private const PRICE_PER_QUART = 30.00;

    private const SIZE_SQFT_MAP = [
        'xs' => 250,
        'sm' => 750,
        'l'  => 1500,
    ];

    /**
     * Base products — always included regardless of garden type.
     */
    private const BASE_PRODUCTS = [
        [
            'slug' => 'garden-boost',
            'name' => 'Garden Boost',
        ],
        [
            'slug' => 'garden-revive',
            'name' => 'Garden Revive',
        ],
    ];

    /**
     * Conditional products — keyed by the garden_type that triggers them.
     */
    private const CONDITIONAL_PRODUCTS = [
        'vegetables' => [
            'slug' => 'tomato-fuel',
            'name' => 'TomatoFuel',
        ],
    ];

    // -------------------------------------------------------
    // Public Entry Point
    // -------------------------------------------------------

    public function calculate(array $gardenTypes, string $gardenSize): array
    {
        $sqft     = $this->resolveRepresentativeSqft($gardenSize);
        $quarts   = $this->calculateQuarts($sqft);
        $products = $this->resolveProducts($gardenTypes);
        $items    = $this->buildLineItems($products, $quarts);

        return [
            'garden_types'        => $gardenTypes,
            'garden_size'         => $gardenSize,
            'representative_sqft' => $sqft,
            'items'               => $items,
            'total_price'         => $this->sumTotal($items),
        ];
    }

    // -------------------------------------------------------
    // Product Resolution
    // -------------------------------------------------------

    /**
     * Returns base products + any conditional products triggered
     * by the selected garden types.
     */
    private function resolveProducts(array $gardenTypes): array
    {
        $products = self::BASE_PRODUCTS;

        foreach (self::CONDITIONAL_PRODUCTS as $triggerType => $product) {
            if (in_array($triggerType, $gardenTypes, true)) {
                $products[] = $product;
            }
        }

        return $products;
    }

    // -------------------------------------------------------
    // Sqft Resolution
    // -------------------------------------------------------

    private function resolveRepresentativeSqft(string $gardenSize): int
    {
        return self::SIZE_SQFT_MAP[$gardenSize] ?? self::SIZE_SQFT_MAP['xs'];
    }

    // -------------------------------------------------------
    // Quart Calculation
    // 1 quart per 500 sqft, rounded up
    // -------------------------------------------------------

    private function calculateQuarts(int $sqft): int
    {
        return (int) ceil($sqft / 500);
    }

    // -------------------------------------------------------
    // Line Item Builder
    // -------------------------------------------------------

    private function buildLineItems(array $products, int $quarts): array
    {
        return array_map(
            fn(array $product) => $this->buildLineItem($product, $quarts),
            $products,
        );
    }

    private function buildLineItem(array $product, int $quarts): array
    {
        $total = round($quarts * self::PRICE_PER_QUART, 2);

        return [
            'slug'            => $product['slug'],
            'name'            => $product['name'],
            'quarts'          => $quarts,
            'price_per_quart' => self::PRICE_PER_QUART,
            'total'           => $total,
        ];
    }

    // -------------------------------------------------------
    // Total
    // -------------------------------------------------------

    private function sumTotal(array $items): float
    {
        return round(array_sum(array_column($items, 'total')), 2);
    }
}