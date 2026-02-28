<?php

namespace App\DTOs\Lawn;

final class ModifierRecommendationDTO
{
    public function __construct(
        public readonly string $slug,
        public readonly float  $ozPer1000,
        public readonly string $reason,
    ) {}

    public function toArray(): array
    {
        return [
            'slug'        => $this->slug,
            'oz_per_1000' => $this->ozPer1000,
            'reason'      => $this->reason,
        ];
    }
}