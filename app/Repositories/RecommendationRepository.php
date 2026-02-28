<?php

namespace App\Repositories;

use App\DTOs\Lawn\RecommendationResultDTO;
use App\Models\YardAssessment;

/**
 * Handles persistence of recommendation results on the assessment.
 * Single responsibility: storage only — no calculation logic here.
 */
final class RecommendationRepository
{
    public function store(YardAssessment $assessment, RecommendationResultDTO $result): void
    {
        $assessment->update([
            'generated_products' => $result->toArray(),
        ]);
    }

    public function getStoredResult(YardAssessment $assessment): ?array
    {
        return $assessment->generated_products;
    }

    public function hasResult(YardAssessment $assessment): bool
    {
        return !empty($assessment->generated_products);
    }
}