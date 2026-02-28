<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Repositories\PackagingRepository;
use App\Services\Lawn\PackagingService;
use App\Services\Lawn\PlanRecommendationService;
use App\Services\Lawn\ProductRecommendationService;
use App\Services\Lawn\SessionFlowService;
use App\Services\Lawn\TierResolverService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function __construct(
        private SessionFlowService           $sessionFlow,
        private PlanRecommendationService    $planRecommendation,
        private TierResolverService          $tierResolver,
        private ProductRecommendationService $recommendationService,
        private PackagingService             $packagingService,
        private PackagingRepository          $packagingRepository,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        // Serve already-completed plan directly
        if ($assessment->isCompleted() && $assessment->generated_products) {
            return $this->renderPlan($assessment);
        }

        $assessment = DB::transaction(function () use ($assessment) {

            $quizAnswers      = $assessment->quiz_answers ?? [];
            $selectedServices = $assessment->selected_services ?? ['lawn'];
            $squareFeet       = (int) ($assessment->square_feet ?? 0);

            $tiers    = $this->tierResolver->resolve($quizAnswers, $selectedServices);
            $lawnTier = $tiers['lawn'] ?? 'bronze';

            $recommendedPlanIds = $this->planRecommendation->resolveIds($tiers);

            // -------------------------------------------------------
            // Step 1 — Product Recommendation Engine (Part A)
            // Determines which products and oz/1000 ratios
            // -------------------------------------------------------
            $engineResult = $this->recommendationService->recommendForAssessment($assessment);

            if ($engineResult === null) {
                Log::error('PlanController: recommendation engine returned null', [
                    'assessment_id' => $assessment->id,
                    'zip_code'      => $assessment->zip_code,
                ]);

                // Fallback: mark complete without products
                $assessment->update([
                    'resolved_tier'        => $lawnTier,
                    'recommended_plan_ids' => $recommendedPlanIds,
                    'status'               => 'completed',
                    'current_step'         => 6,
                    'completed_at'         => now(),
                ]);

                return $assessment->fresh();
            }

            // -------------------------------------------------------
            // Step 2 — Packaging Service (Part B)
            // Resolves variant combinations + pricing
            // -------------------------------------------------------

            // Re-fetch fresh assessment so generated_products from Step 1 is available
            $freshAssessment = $assessment->fresh();

            // Resolve base price from plans table
            $basePrice = $this->resolveBasePrice($lawnTier, $squareFeet);

            // Get the RecommendationResultDTO equivalent from stored result
            // We need the DTO — re-run engine (it's pure/fast, no DB side effects)
            $recommendation = $this->recommendationService->getRecommendationDTO($freshAssessment);

            $packagingResult = $this->packagingService->package(
                recommendation: $recommendation,
                squareFeet:     $squareFeet,
                tier:           $lawnTier,
                basePrice:      $basePrice,
            );

            // -------------------------------------------------------
            // Step 3 — Persist full result (replaces generated_products)
            // -------------------------------------------------------
            $this->packagingRepository->store(
                $freshAssessment,
                $recommendation,
                $packagingResult,
            );

            $freshAssessment->update([
                'resolved_tier'        => $lawnTier,
                'recommended_plan_ids' => $recommendedPlanIds,
                'status'               => 'completed',
                'current_step'         => 6,
                'completed_at'         => now(),
            ]);

            return $freshAssessment->fresh();
        });

        return $this->renderPlan($assessment);
    }

    private function renderPlan($assessment): Response
    {
        $selectedServices = $assessment->selected_services ?? ['lawn'];
        $quizAnswers      = $assessment->quiz_answers ?? [];

        $tiers       = $this->tierResolver->resolve($quizAnswers, $selectedServices);
        $allPlans    = $this->planRecommendation->allPlansForServices($selectedServices);
        $recommended = $this->planRecommendation->recommend($tiers);

        return Inertia::render('yard/plan', [
            'assessment' => [
                'id'                   => $assessment->id,
                'zip_code'             => $assessment->zip_code,
                'square_feet'          => $assessment->square_feet,
                'resolved_tier'        => $assessment->resolved_tier,
                'selected_services'    => $selectedServices,
                'quiz_answers'         => $quizAnswers,
                'soil'                 => $assessment->soil_snapshot,
                'products'             => $assessment->generated_products,
                'total_base_price'     => $assessment->total_base_price,
                'total_addons'         => $assessment->total_addons_price,
                'total_price'          => $assessment->total_price,
                'recommended_plan_ids' => $assessment->recommended_plan_ids,
                'garden_products'      => $assessment->garden_products,
                'garden_types'         => $assessment->garden_types,
                'garden_size'          => $assessment->garden_size,
            ],
            'recommended_plans' => $recommended,
            'all_plans'         => $allPlans,
            'tiers'             => $tiers,
        ]);
    }

    /**
     * Resolve scaled base price from plans table.
     * Applies size multiplier based on square footage bands.
     */
    private function resolveBasePrice(string $tier, int $squareFeet): float
    {
        $planSlug = "lawn-{$tier}";

        $base = DB::table('plans')
            ->where('slug', $planSlug)
            ->value('base_price_yearly');

        $fallback = ['bronze' => 179.00, 'silver' => 249.00, 'gold' => 289.00];
        $base     = (float) ($base ?? $fallback[$tier] ?? 179.00);

        return $base * $this->sizeMultiplier($squareFeet);
    }

    private function sizeMultiplier(int $sqft): float
    {
        $bands = [
            [0,     4000,  1.0],
            [4001,  8000,  1.4],
            [8001,  12000, 1.8],
            [12001, 20000, 2.2],
            [20001, 32000, 3.0],
            [32001, 43560, 4.0],
        ];

        foreach ($bands as [$min, $max, $mult]) {
            if ($sqft >= $min && $sqft <= $max) {
                return $mult;
            }
        }

        return 4.0;
    }
}