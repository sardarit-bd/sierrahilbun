<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class FallSupportModifierRule implements ModifierRuleInterface
{
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        $reasons = [];

        if ($input->frostFreeDays !== null && $input->frostFreeDays < 150) {
            $reasons[] = "Only {$input->frostFreeDays} frost-free days — short growing season";
        }

        if ($input->avgJanLowF !== null && $input->avgJanLowF < 20) {
            $reasons[] = "Avg January low {$input->avgJanLowF}°F — severe winter stress";
        }

        if (empty($reasons)) {
            return null;
        }

        return new ModifierRecommendationDTO(
            slug:      'fall-support',
            ozPer1000: 5.0,
            reason:    implode('; ', $reasons),
        );
    }
}