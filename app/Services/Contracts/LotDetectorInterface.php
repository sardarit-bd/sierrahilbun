<?php

namespace App\Services\Contracts;

/**
 * Detects the full lot/parcel boundary polygon at a given coordinate.
 *
 * @return array<int, array{lat: float, lon: float}>|null
 */
interface LotDetectorInterface
{
    /**
     * Returns the lot/parcel boundary polygon as an array of lat/lon points.
     * Returns null if no parcel boundary found or detection fails.
     */
    public function detect(float $lat, float $lon): ?array;
}