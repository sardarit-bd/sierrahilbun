<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class AerateModifierRule implements ModifierRuleInterface
{
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        $reasons = [];

        if ($input->compactionRisk === 'high') {
            $reasons[] = 'Compaction risk high';
        }

        if (str_contains(strtolower($input->textureClass ?? ''), 'clay')) {
            $reasons[] = 'Clay soil texture';
        }

        if (str_contains(strtolower($input->drainageClass ?? ''), 'poor')) {
            $reasons[] = 'Poor drainage';
        }

        if (empty($reasons)) {
            return null;
        }

        return new ModifierRecommendationDTO(
            slug:      'aerate',
            ozPer1000: 0.5,
            reason:    implode('; ', $reasons),
        );
    }
}
