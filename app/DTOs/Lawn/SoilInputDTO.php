<?php

namespace App\DTOs\Lawn;

final class SoilInputDTO
{
    public function __construct(
        public readonly int     $lawnSqft,
        public readonly ?float  $avgPh,
        public readonly ?float  $cecMeqPer100g,
        public readonly ?float  $organicMatterPct,
        public readonly ?string $textureClass,
        public readonly ?string $drainageClass,
        public readonly ?string $compactionRisk,
        public readonly ?string $nLeachingRisk,
        public readonly ?string $droughtStressRisk,
        public readonly ?int    $avgJulyHighF,
        public readonly ?int    $frostFreeDays,
        public readonly ?int    $avgJanLowF,
        // Quiz answers — passed alongside geo data
        public readonly ?string $pets,
        public readonly ?string $weeds,
    ) {}
}