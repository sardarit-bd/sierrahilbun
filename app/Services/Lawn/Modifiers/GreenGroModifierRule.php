<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class GreenGroModifierRule implements ModifierRuleInterface
{
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        $lowOm = $input->organicMatterPct !== null && $input->organicMatterPct < 3.0;

        $poorNutrientRetention = ($input->cecMeqPer100g !== null && $input->cecMeqPer100g < 8.0)
            || $input->nLeachingRisk === 'high';

        if (!$lowOm || !$poorNutrientRetention) {
            return null;
        }

        $reasons = ["Organic matter {$input->organicMatterPct}% below 3%"];

        if ($input->cecMeqPer100g !== null && $input->cecMeqPer100g < 8.0) {
            $reasons[] = "CEC {$input->cecMeqPer100g} meq/100g — low nutrient holding capacity";
        }

        if ($input->nLeachingRisk === 'high') {
            $reasons[] = 'High nitrogen leaching risk — nutrients not retained';
        }

        return new ModifierRecommendationDTO(
            slug:      'green-gro',
            ozPer1000: 4.0,
            reason:    implode('; ', $reasons),
        );
    }
}
