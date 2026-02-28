<?php

namespace App\Repositories;

use App\DTOs\Lawn\PackagingResultDTO;
use App\DTOs\Lawn\RecommendationResultDTO;
use App\Models\YardAssessment;

/**
 * Persists the full plan result (engine recommendation + packaging) to the assessment.
 */
final class PackagingRepository
{
    public function store(
        YardAssessment          $assessment,
        RecommendationResultDTO $recommendation,
        PackagingResultDTO      $packaging,
    ): void {
        $generated = array_merge(
            $recommendation->toArray(),
            $packaging->toArray(),
        );

        $assessment->update([
            'generated_products' => $generated,
            'total_base_price'   => $packaging->basePrice,
            'total_addons_price' => $packaging->addonsTotal,
            'total_price'        => $packaging->totalPrice,
        ]);
    }
}