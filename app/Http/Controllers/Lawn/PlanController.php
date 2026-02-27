<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Services\Lawn\LawnPlanCalculatorService;
use App\Services\Lawn\PlanRecommendationService;
use App\Services\Lawn\SessionFlowService;
use App\Services\Lawn\TierResolverService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function __construct(
        private SessionFlowService        $sessionFlow,
        private LawnPlanCalculatorService $calculator,
        private PlanRecommendationService $planRecommendation,
        private TierResolverService       $tierResolver,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        // Serve already-completed plan directly
        if ($assessment->isCompleted() && $assessment->generated_products) {
            return $this->renderPlan($assessment);
        }

        // Generate and save plan wrapped in a DB transaction (ACID)
        $assessment = DB::transaction(function () use ($assessment) {

            $quizAnswers      = $assessment->quiz_answers;
            $selectedServices = $assessment->selected_services ?? ['lawn'];
            $soilSnapshot     = $assessment->soil_snapshot;
            $squareFeet       = $assessment->square_feet;

            $tiers = $this->tierResolver->resolve($quizAnswers, $selectedServices);

            $recommendedPlanIds = $this->planRecommendation->resolveIds($tiers);

            $lawnTier   = $tiers['lawn'] ?? 'bronze';
            $calcResult = $this->calculator->calculate(
                $lawnTier,
                $squareFeet,
                $soilSnapshot,
                $quizAnswers
            );

            $assessment->update([
                'resolved_tier'        => $lawnTier,
                'recommended_plan_ids' => $recommendedPlanIds,
                'generated_products'   => $calcResult['products'],
                'total_base_price'     => $calcResult['base_price'],
                'total_addons_price'   => $calcResult['addons_total'],
                'total_price'          => $calcResult['total_price'],
                'status'               => 'completed',
                'current_step'         => 6,
                'completed_at'         => now(),
            ]);

            return $assessment->fresh();
        });

        return $this->renderPlan($assessment);
    }

    private function renderPlan($assessment): Response
    {
        $selectedServices = $assessment->selected_services ?? ['lawn'];
        $quizAnswers      = $assessment->quiz_answers;

        $tiers    = $this->tierResolver->resolve($quizAnswers, $selectedServices);
        $allPlans = $this->planRecommendation->allPlansForServices($selectedServices);
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
                'garden_products' => $assessment->garden_products,
                'garden_types'    => $assessment->garden_types,
                'garden_size'     => $assessment->garden_size,
            ],
            'recommended_plans' => $recommended,
            'all_plans'         => $allPlans,
            'tiers'             => $tiers,
        ]);
    }
}