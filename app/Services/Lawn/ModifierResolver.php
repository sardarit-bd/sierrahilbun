<?php

namespace App\Services\Lawn;

use App\DTOs\Lawn\ModifierRecommendationDTO;
use App\DTOs\Lawn\SoilInputDTO;
use App\Services\Lawn\Modifiers\ModifierRuleInterface;

/**
 * Runs all registered modifier rules against the soil input.
 * Returns only the rules that triggered.
 *
 * To add a new modifier: implement ModifierRuleInterface,
 * register it in AppServiceProvider — nothing here changes.
 */
final class ModifierResolver
{
    /**
     * @param ModifierRuleInterface[] $rules
     */
    public function __construct(
        private readonly array $rules,
    ) {}

    /**
     * @return ModifierRecommendationDTO[]
     */
    public function resolve(SoilInputDTO $input): array
    {
        $triggered = [];

        foreach ($this->rules as $rule) {
            $result = $rule->evaluate($input);

            if ($result !== null) {
                $triggered[] = $result;
            }
        }

        return $triggered;
    }
}