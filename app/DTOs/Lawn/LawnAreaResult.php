<?php

namespace App\DTOs\Lawn;

final class LawnAreaResult
{
    /**
     * @param array<int, array{lat: float, lon: float}>      $lawnPolygon      Lawn boundary coordinates
     * @param array<int, array{lat: float, lon: float}>|null $lotPolygon       Full lot boundary (if found)
     * @param array<int, array{lat: float, lon: float}>|null $buildingPolygon  Building footprint (if found)
     */
    public function __construct(
        public readonly int    $squareFeet,
        public readonly array  $lawnPolygon,
        public readonly string $source,
        public readonly bool   $estimated,
        public readonly ?array $lotPolygon      = null,
        public readonly ?array $buildingPolygon = null,
    ) {}

    public function hasPolygon(): bool
    {
        return !empty($this->lawnPolygon);
    }

    public function wasAutoCalculated(): bool
    {
        return $this->source === 'calculated' && !$this->estimated;
    }
}