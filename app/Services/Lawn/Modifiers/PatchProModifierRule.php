<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class PatchProModifierRule implements ModifierRuleInterface
{
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        if ($input->organicMatterPct === null) {
            return null;
        }

        if ($input->organicMatterPct < 2.0) {
            return new ModifierRecommendationDTO(
                slug:       'patchpro',
                ozPer1000:  2.0,
                reason:     'Organic matter below 2% — severe deficiency',
            );
        }

        if ($input->organicMatterPct < 3.0) {
            return new ModifierRecommendationDTO(
                slug:       'patchpro',
                ozPer1000:  1.0,
                reason:     'Organic matter between 2–3% — moderate deficiency',
            );
        }

        return null;
    }
}