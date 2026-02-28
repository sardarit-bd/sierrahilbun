<?php

namespace App\DTOs\Lawn;

final class PackagingLineDTO
{
    /**
     * @param array<int, array{variant_id: int, sku: string, size_label: string, size_volume_oz: int, qty: int}> $units
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $pricingType,
        public readonly float  $ozNeeded,
        public readonly float  $ozShipped,
        public readonly array  $units,
        public readonly float  $unitPrice,
        public readonly float  $totalPrice,
    ) {}

    public function toArray(): array
    {
        return [
            'slug'         => $this->slug,
            'name'         => $this->name,
            'pricing_type' => $this->pricingType,
            'oz_needed'    => round($this->ozNeeded, 2),
            'oz_shipped'   => round($this->ozShipped, 2),
            'units'        => $this->units,
            'unit_price'   => $this->unitPrice,
            'total_price'  => round($this->totalPrice, 2),
        ];
    }
}