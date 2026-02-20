<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Services\Lawn\SessionFlowService;
use App\Services\Lawn\TierResolverService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuestionnaireController extends Controller
{
    public function __construct(
        private SessionFlowService $sessionFlow,
        private TierResolverService $tierResolver,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        return Inertia::render('lawns/questions/post', [
            'zip_code'    => $assessment->zip_code,
            'square_feet' => $assessment->square_feet,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'goals'      => ['required', 'in:looks,health,safety,all'],
            'pets'       => ['required', 'in:lot,not_much'],
            'knowledge'  => ['required', 'in:expert,hobbyist,amateur,rookie'],
            'grass'      => ['required', 'string'],
            'patches'    => ['required', 'in:none,few,moderate,lots'],
            'weeds'      => ['required', 'in:none,leafy,stubborn,everywhere,pre'],
            'care'       => ['required', 'in:service,fert_high,fert_low,mow'],
            'preference' => ['required', 'in:liquid,granular'],
        ]);

        $answers = $request->only([
            'goals', 'pets', 'knowledge', 'grass',
            'patches', 'weeds', 'care', 'preference',
        ]);

        $tier = $this->tierResolver->resolve($answers);

        $this->sessionFlow->updateAssessment([
            'quiz_answers'  => $answers,
            'resolved_tier' => $tier,
            'current_step'  => 5,
        ]);

        return redirect()->route('yard.plan');
    }
}