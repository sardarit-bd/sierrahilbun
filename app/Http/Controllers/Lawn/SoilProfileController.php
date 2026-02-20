<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Services\Lawn\SessionFlowService;
use App\Services\Lawn\SoilProfileService;
use Inertia\Inertia;
use Inertia\Response;

class SoilProfileController extends Controller
{
    public function __construct(
        private SessionFlowService $sessionFlow,
        private SoilProfileService $soilProfile,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();
        $soil       = $this->soilProfile->getByZip($assessment->zip_code);

        if (!$assessment->soil_snapshot) {
            $this->sessionFlow->updateAssessment([
                'soil_snapshot' => $soil,
                'current_step'  => 4,
            ]);
        }

        return Inertia::render('lawns/post', [
            'soil'        => $soil,
            'zip_code'    => $assessment->zip_code,
            'square_feet' => $assessment->square_feet,
        ]);
    }
}