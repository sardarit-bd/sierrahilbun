<?php

namespace App\DTOs\Lawn;

final class GeocodeResult
{
    public function __construct(
        public readonly float  $lat,
        public readonly float  $lon,
        public readonly string $fullAddress,
        public readonly string $confidence,   // 'high' | 'medium' | 'low'
        public readonly string $featureType,  // 'address' | 'street' | 'place' etc.
    ) {}

    /**
     * Any address-level feature type is precise enough to show on the map.
     * Confidence level determines whether we attempt auto-detection — not
     * whether we show the map at all.
     */
    public function isPreciseEnough(): bool
    {
        return $this->featureType === 'address';
    }

    /**
     * High confidence rooftop-level match.
     * Safe to attempt automatic boundary detection.
     */
    public function isHighConfidence(): bool
    {
        return $this->confidence === 'high'
            && $this->featureType === 'address';
    }
}