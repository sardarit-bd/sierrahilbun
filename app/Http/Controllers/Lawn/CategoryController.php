<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Lawn\SessionFlowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private SessionFlowService $sessionFlow,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        $services = Service::active()
            ->orderByRaw("slug = 'lawn' DESC")
            ->get(['name', 'slug']);

        return Inertia::render('yard-issue', [
            'zip_code' => $assessment->zip_code,
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $activeSlugs = Service::active()->pluck('slug')->toArray();

        $request->validate([
            'selected_services'   => ['required', 'array', 'min:1'],
            'selected_services.*' => ['required', 'string', 'in:' . implode(',', $activeSlugs)],
        ]);

        $services = $request->input('selected_services');

        if (!in_array('lawn', $services)) {
            $services = array_merge(['lawn'], $services);
        }

        $this->sessionFlow->updateAssessment([
            'selected_services' => array_values(array_unique($services)),
            'current_step'      => 2,
        ]);

        return redirect()->route('yard.size');
    }
}