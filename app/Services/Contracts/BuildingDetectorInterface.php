<?php

namespace App\Services\Contracts;

/**
 * Detects building footprint polygon at a given coordinate.
 *
 * @return array<int, array{lat: float, lon: float}>|null
 */
interface BuildingDetectorInterface
{
    /**
     * Returns the building footprint polygon as an array of lat/lon points.
     * Returns null if no building found or detection fails.
     */
    public function detect(float $lat, float $lon): ?array;
}