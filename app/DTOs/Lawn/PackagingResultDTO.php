<?php

namespace App\DTOs\Lawn;

final class PackagingResultDTO
{
    /**
     * @param PackagingLineDTO[] $lines
     */
    public function __construct(
        public readonly array $lines,
        public readonly float $addonsTotal,
        public readonly float $basePrice,
        public readonly float $totalPrice,
    ) {}

    public function toArray(): array
    {
        return [
            'packaging' => array_map(
                fn (PackagingLineDTO $line) => $line->toArray(),
                $this->lines,
            ),
            'pricing' => [
                'base_price'   => round($this->basePrice, 2),
                'addons_total' => round($this->addonsTotal, 2),
                'total_price'  => round($this->totalPrice, 2),
            ],
        ];
    }
}