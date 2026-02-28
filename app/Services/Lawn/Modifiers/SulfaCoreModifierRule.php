<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class SulfaCoreModifierRule implements ModifierRuleInterface
{
    private const WEED_TRIGGERS = ['everywhere', 'leafy', 'pre'];

    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO
    {
        if ($input->weeds === null) {
            return null;
        }

        if (!in_array($input->weeds, self::WEED_TRIGGERS, true)) {
            return null;
        }

        $reason = match ($input->weeds) {
            'everywhere' => 'Weeds present everywhere — aggressive pre/post emergent needed',
            'leafy'      => 'Broadleaf weeds present',
            'pre'        => 'Pre-emergent weed control indicated',
            default      => 'Weed presence detected',
        };

        return new ModifierRecommendationDTO(
            slug:      'sulfacore',
            ozPer1000: 0.41,
            reason:    $reason,
        );
    }
}