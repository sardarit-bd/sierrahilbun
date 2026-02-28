<?php

namespace App\Services\Lawn;

use App\DTOs\Lawn\RecommendationResultDTO;
use App\DTOs\Lawn\SoilInputDTO;

final class ProductRecommendationEngine
{
    public function __construct(
        private readonly CoreRatioCalculator $coreCalculator,
        private readonly ModifierResolver    $modifierResolver,
    ) {}

    public function recommend(SoilInputDTO $input): RecommendationResultDTO
    {
        $core      = $this->coreCalculator->calculate($input);
        $modifiers = $this->modifierResolver->resolve($input);
        $notes     = $this->buildNotes($input);

        return new RecommendationResultDTO(
            core:      $core,
            modifiers: $modifiers,
            notes:     $notes,
        );
    }

    private function buildNotes(SoilInputDTO $input): array
    {
        $notes = [];

        if ($input->avgPh !== null) {
            if ($input->avgPh < 6.2) {
                $notes[] = "pH {$input->avgPh} is low — biased toward soil structure correction.";
            } elseif ($input->avgPh > 7.4) {
                $notes[] = "pH {$input->avgPh} is high — biased toward green-up products.";
            }
        }

        if ($input->cecMeqPer100g !== null && $input->cecMeqPer100g < 8.0) {
            $notes[] = "CEC {$input->cecMeqPer100g} meq/100g is low — reduced nitrogen to prevent leaching.";
        } elseif ($input->cecMeqPer100g === null && $input->nLeachingRisk === 'high') {
            $notes[] = "High nitrogen leaching risk — reduced nitrogen application rate.";
        }

        if ($input->organicMatterPct !== null) {
            if ($input->organicMatterPct < 2.0) {
                $notes[] = "Organic matter {$input->organicMatterPct}% is very low — soil health products recommended.";
            } elseif ($input->organicMatterPct > 5.0) {
                $notes[] = "Organic matter {$input->organicMatterPct}% is high — boosted green-up and nitrogen.";
            }
        }

        $texture = strtolower($input->textureClass ?? '');
        if (str_contains($texture, 'sand')) {
            $notes[] = "Sandy soil — nitrogen reduced to account for leaching.";
        } elseif (str_contains($texture, 'clay')) {
            $notes[] = "Clay soil — structure products emphasized.";
        }

        if ($input->droughtStressRisk === 'high') {
            $notes[] = "High drought stress risk — heat and drought protection products recommended.";
        }

        if ($input->frostFreeDays !== null && $input->frostFreeDays < 150) {
            $notes[] = "Short growing season ({$input->frostFreeDays} frost-free days) — fall preparation recommended.";
        }

        return $notes;
    }
}
