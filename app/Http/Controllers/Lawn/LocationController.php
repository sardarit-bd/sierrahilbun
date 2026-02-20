<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Services\Lawn\SessionFlowService;
use App\Services\Lawn\SoilProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function __construct(
        private SessionFlowService $sessionFlow,
        private SoilProfileService $soilProfile,
    ) {}

    public function show(): Response
    {
        return Inertia::render('CustomLawnPlan');
    }

    public function store(Request $request)
    {
        $request->validate([
            'zip_code' => ['required', 'digits:5'],
        ]);

        $zip = $request->input('zip_code');

        if (!$this->soilProfile->zipExists($zip)) {
            return back()->withErrors([
                'zip_code' => 'We do not service this zip code yet.',
            ]);
        }

        $this->sessionFlow->createAssessment($zip);

        return redirect()->route('yard.category');
    }
}