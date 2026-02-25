<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Services\Lawn\LawnSizeService;
use App\Services\Lawn\SessionFlowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LawnSizeController extends Controller
{
    public function __construct(
        private readonly SessionFlowService $sessionFlow,
        private readonly LawnSizeService    $lawnSizeService,
    ) {}

    // -------------------------------------------------------
    // Show
    // -------------------------------------------------------

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        return Inertia::render('lawn-size', [
            'zip_code' => $assessment->zip_code,
        ]);
    }

    // -------------------------------------------------------
    // Store
    // -------------------------------------------------------

    public function store(Request $request): mixed
    {
        $request->validate([
            'source'      => ['required', 'in:manual,address'],
            'square_feet' => ['required_if:source,manual', 'nullable', 'integer', 'min:100', 'max:43560'],
            'address'     => ['required_if:source,address', 'nullable', 'string', 'max:500'],
        ]);

        // ── Manual path ───────────────────────────────────────────
        if ($request->input('source') === 'manual') {
            $this->sessionFlow->updateAssessment([
                'square_feet'  => $request->input('square_feet'),
                'sqft_source'  => 'manual',
                'current_step' => 3,
            ]);

            return redirect()->route('yard.soil');
        }

        // ── Address path ──────────────────────────────────────────
        $result = $this->lawnSizeService->calculate($request->input('address'));

        if (!$result['success']) {
            return back()->withErrors(['address' => $result['error']]);
        }

        $this->sessionFlow->updateAssessment([
            'square_feet'   => $result['square_feet'],
            'sqft_source'   => 'calculated',
            'estimated'     => $result['estimated'],
            'address_input' => $request->input('address'),
            'current_step'  => 3,
        ]);

        return Inertia::render('lawn-size', [
            'zip_code'         => $this->sessionFlow->getAssessmentOrFail()->zip_code,

            // Coordinates & address
            'latitude'         => $result['latitude'],
            'longitude'        => $result['longitude'],
            'matched_address'  => $result['matched_address'],
            'confidence'       => $result['confidence'],

            // Area data
            'square_feet'      => $result['square_feet'],
            'estimated'        => $result['estimated'],
            'source'           => $result['source'],

            // Polygons for the map — frontend draws these automatically
            'lawn_polygon'     => $result['lawn_polygon'],
            'lot_polygon'      => $result['lot_polygon'],
            'building_polygon' => $result['building_polygon'],
        ]);
    }

    // -------------------------------------------------------
    // Confirm
    // -------------------------------------------------------

    public function confirm(Request $request): mixed
    {
        $request->validate([
            'square_feet' => ['required', 'integer', 'min:100', 'max:43560'],
        ]);

        $this->sessionFlow->updateAssessment([
            'square_feet'  => $request->input('square_feet'),
            'sqft_source'  => 'confirmed',
            'current_step' => 3,
        ]);

        return redirect()->route('yard.soil');
    }
}