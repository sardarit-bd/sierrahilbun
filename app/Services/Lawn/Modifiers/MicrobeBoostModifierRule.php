<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class MicrobeBoostModifierRule implements ModifierRuleInterface
{
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        $reasons = [];

        if ($input->organicMatterPct !== null && $input->organicMatterPct < 2.0) {
            $reasons[] = 'Organic matter below 2% — low biological activity';
        }

        if ($input->compactionRisk === 'high') {
            $reasons[] = 'Compaction risk high — inhibits microbial activity';
        }

        if ($input->avgPh !== null && $input->avgPh > 7.4) {
            $reasons[] = "Soil pH {$input->avgPh} — high pH locks out micronutrients";
        }

        if (empty($reasons)) {
            return null;
        }

        return new ModifierRecommendationDTO(
            slug:      'microbe-boost',
            ozPer1000: 4.5,
            reason:    implode('; ', $reasons),
        );
    }
}
