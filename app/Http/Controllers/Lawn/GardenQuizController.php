<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lawn\GardenQuizRequest;
use App\Services\Lawn\GardenQuizCalculatorService;
use App\Services\Lawn\SessionFlowService;
use Illuminate\Http\RedirectResponse;

class GardenQuizController extends Controller
{
    public function __construct(
        private SessionFlowService        $sessionFlow,
        private GardenQuizCalculatorService $calculator,
    ) {}

    public function store(GardenQuizRequest $request): RedirectResponse
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        $gardenTypes = $request->validated('garden_types');
        $gardenSize  = $request->validated('garden_size');

        $gardenProducts = $this->calculator->calculate($gardenTypes, $gardenSize);

        $assessment->update([
            'garden_types'    => $gardenTypes,
            'garden_size'     => $gardenSize,
            'garden_products' => $gardenProducts,
        ]);

        return redirect()->route('yard.plan');
    }
}