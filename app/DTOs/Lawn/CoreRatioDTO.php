<?php

namespace App\DTOs\Lawn;

final class CoreRatioDTO
{
    public function __construct(
        public readonly float $kickstartOzPer1000,
        public readonly float $turfFuelNOzPer1000,
        public readonly float $neutralyzeOzPer1000,
    ) {}

    public function toArray(): array
    {
        return [
            'kickstart'    => $this->kickstartOzPer1000,
            'turf-fuel-n'  => $this->turfFuelNOzPer1000,
            'neutralyze'   => $this->neutralyzeOzPer1000,
        ];
    }
}