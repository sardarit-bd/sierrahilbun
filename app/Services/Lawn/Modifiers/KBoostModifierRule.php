<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class KBoostModifierRule implements ModifierRuleInterface
{
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        $reasons = [];

        if ($input->droughtStressRisk === 'high') {
            $reasons[] = 'Drought stress high — potassium depletes under heat/drought';
        }

        if ($input->avgJulyHighF !== null && $input->avgJulyHighF >= 95) {
            $reasons[] = "Avg July high {$input->avgJulyHighF}°F — heat stress accelerates K loss";
        }

        if (empty($reasons)) {
            return null;
        }

        return new ModifierRecommendationDTO(
            slug:      'k-boost',
            ozPer1000: 3.0,
            reason:    implode('; ', $reasons),
        );
    }
}
