<?php

namespace App\Services\Lawn;

use App\DTOs\Lawn\RecommendationResultDTO;
use App\Models\GeoSoilReference;
use App\Models\YardAssessment;
use App\Repositories\RecommendationRepository;
use Illuminate\Support\Facades\Log;

/**
 * Laravel-aware orchestrator for Part A.
 *
 * Hydration priority:
 *   1. GeoSoilReference by zip_code (most accurate)
 *   2. soil_snapshot on assessment (fallback)
 */
final class ProductRecommendationService
{
    public function __construct(
        private readonly SoilInputHydrator           $hydrator,
        private readonly ProductRecommendationEngine  $engine,
        private readonly RecommendationRepository     $repository,
    ) {}

    /**
     * Run the engine and persist the result.
     * Returns the stored array or null if no soil data available.
     */
    public function recommendForAssessment(YardAssessment $assessment): ?array
    {
        $dto = $this->buildDTO($assessment);

        if ($dto === null) {
            return null;
        }

        $this->repository->store($assessment, $dto);

        return $dto->toArray();
    }

    /**
     * Run the engine and return the DTO directly (no persistence).
     * Used by PlanController to pass the DTO into PackagingService
     * without a redundant DB write.
     */
    public function getRecommendationDTO(YardAssessment $assessment): ?RecommendationResultDTO
    {
        return $this->buildDTO($assessment);
    }

    // -------------------------------------------------------
    // Internals
    // -------------------------------------------------------

    private function buildDTO(YardAssessment $assessment): ?RecommendationResultDTO
    {
        $quizAnswers = $assessment->quiz_answers ?? [];
        $lawnSqft    = (int) ($assessment->square_feet ?? 0);

        // Path 1 — GeoSoilReference (preferred)
        $geo = GeoSoilReference::find($assessment->zip_code);

        if ($geo) {
            $input = $this->hydrator->hydrate($geo, $lawnSqft, $quizAnswers);
        } elseif (!empty($assessment->soil_snapshot)) {
            // Path 2 — soil_snapshot fallback
            Log::info('ProductRecommendationService: geo not found, falling back to soil_snapshot', [
                'zip_code'      => $assessment->zip_code,
                'assessment_id' => $assessment->id,
            ]);

            $input = $this->hydrator->hydrateFromSnapshot(
                $assessment->soil_snapshot,
                $lawnSqft,
                $quizAnswers,
            );
        } else {
            Log::warning('ProductRecommendationService: no soil data available', [
                'zip_code'      => $assessment->zip_code,
                'assessment_id' => $assessment->id,
            ]);

            return null;
        }

        return $this->engine->recommend($input);
    }
}