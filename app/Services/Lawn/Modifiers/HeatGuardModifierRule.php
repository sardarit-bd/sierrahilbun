<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class HeatGuardModifierRule implements ModifierRuleInterface
{
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        $reasons = [];

        if ($input->droughtStressRisk === 'high') {
            $reasons[] = 'Drought stress risk high';
        }

        if ($input->avgJulyHighF !== null && $input->avgJulyHighF >= 95) {
            $reasons[] = "Avg July high {$input->avgJulyHighF}°F >= 95°F";
        }

        if (empty($reasons)) {
            return null;
        }

        return new ModifierRecommendationDTO(
            slug:      'heatguard',
            ozPer1000: 1.0,
            reason:    implode('; ', $reasons),
        );
    }
}
