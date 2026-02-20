<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Services\Lawn\SessionFlowService;
use App\Services\Lawn\SquareFootageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LawnSizeController extends Controller
{
    public function __construct(
        private SessionFlowService  $sessionFlow,
        private SquareFootageService $squareFootage,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        return Inertia::render('lawn-size', [
            'zip_code' => $assessment->zip_code,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'source'      => ['required', 'in:manual,address'],
            'square_feet' => ['required_if:source,manual', 'nullable', 'integer', 'min:100', 'max:43560'],
            'address'     => ['required_if:source,address', 'nullable', 'string', 'max:500'],
        ]);

        if ($request->input('source') === 'manual') {
            $this->sessionFlow->updateAssessment([
                'square_feet'  => $request->input('square_feet'),
                'sqft_source'  => 'manual',
                'current_step' => 3,
            ]);

            return redirect()->route('yard.soil');
        }

        // Address path
        $result = $this->squareFootage->calculate($request->input('address'));

        if (!$result['success']) {
            return back()->withErrors([
                'address' => $result['error'],
            ]);
        }

        $this->sessionFlow->updateAssessment([
            'square_feet'   => $result['square_feet'],
            'sqft_source'   => 'calculated',
            'estimated'     => $result['estimated'],
            'address_input' => $request->input('address'),
            'current_step'  => 3,
        ]);

        return Inertia::render('lawn-size', [
            'zip_code'    => $this->sessionFlow->getAssessmentOrFail()->zip_code,
            'square_feet' => $result['square_feet'],
            'estimated'   => $result['estimated'],
            'latitude'    => $result['latitude'],
            'longitude'   => $result['longitude'],
        ]);
    }

    public function confirm(Request $request)
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