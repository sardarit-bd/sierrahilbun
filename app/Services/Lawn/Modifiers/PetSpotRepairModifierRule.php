<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class PetSpotRepairModifierRule implements ModifierRuleInterface
{
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        if ($input->pets !== 'lot') {
            return null;
        }

        return new ModifierRecommendationDTO(
            slug:      'pet-spot-repair',
            ozPer1000: 16.5,
            reason:    'Heavy pet activity — preventative coverage for urine salt damage',
        );
    }
}