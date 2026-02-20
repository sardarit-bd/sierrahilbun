<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Services\Lawn\LawnPlanCalculatorService;
use App\Services\Lawn\SessionFlowService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function __construct(
        private SessionFlowService       $sessionFlow,
        private LawnPlanCalculatorService $calculator,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        // If plan already generated, serve it directly
        if ($assessment->isCompleted() && $assessment->generated_products) {
            return $this->renderPlan($assessment->toArray());
        }

        // Generate plan wrapped in a transaction (ACID)
        $assessment = DB::transaction(function () use ($assessment) {

            $result = $this->calculator->calculate(
                $assessment->resolved_tier,
                $assessment->square_feet,
                $assessment->soil_snapshot,
                $assessment->quiz_answers,
            );

            $assessment->update([
                'generated_products' => $result['products'],
                'total_base_price'   => $result['base_price'],
                'total_addons_price' => $result['addons_total'],
                'total_price'        => $result['total_price'],
                'status'             => 'completed',
                'current_step'       => 6,
                'completed_at'       => now(),
            ]);

            return $assessment->fresh();
        });

        return $this->renderPlan($assessment->toArray());
    }

    private function renderPlan(array $assessment): Response
    {
        return Inertia::render('yard/plan', [
            'assessment' => [
                'id'               => $assessment['id'],
                'zip_code'         => $assessment['zip_code'],
                'square_feet'      => $assessment['square_feet'],
                'tier'             => $assessment['resolved_tier'],
                'selected_services'=> $assessment['selected_services'],
                'quiz_answers'     => $assessment['quiz_answers'],
                'soil'             => $assessment['soil_snapshot'],
                'products'         => $assessment['generated_products'],
                'total_base_price' => $assessment['total_base_price'],
                'total_addons'     => $assessment['total_addons_price'],
                'total_price'      => $assessment['total_price'],
            ],
        ]);
    }
}