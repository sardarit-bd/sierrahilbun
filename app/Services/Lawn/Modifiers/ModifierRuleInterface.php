<?php

namespace App\Services\Lawn\Modifiers;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;

interface ModifierRuleInterface
{
    /**
     * Evaluate soil input against this rule.
     * Returns a recommendation DTO if the rule triggers, null otherwise.
     */
    public function evaluate(SoilInputDTO $input): ?ModifierRecommendationDTO;
}