<?php

namespace App\Http\Controllers\Lawn;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Repositories\QuestionRepository;
use App\Services\Lawn\SessionFlowService;
use App\Services\Lawn\TierResolverService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuestionnaireController extends Controller
{
    public function __construct(
        private SessionFlowService   $sessionFlow,
        private TierResolverService  $tierResolver,
        private QuestionRepository   $questions,
    ) {}

    public function show(): Response
    {
        $assessment = $this->sessionFlow->getAssessmentOrFail();

        return Inertia::render('lawns/questions/post', [
            'zip_code'    => $assessment->zip_code,
            'square_feet' => $assessment->square_feet,
            'questions'   => QuestionResource::collection(
                                 $this->questions->getAllActive()
                             )->resolve(),
        ]);
    }

    public function store(Request $request)
    {
        // Build validation rules dynamically from DB (cached)
        $validationMap = $this->questions->getValidationMap();

        $rules = collect($validationMap)->mapWithKeys(
            fn (array $slugs, string $questionSlug) => [
                $questionSlug => ['required', 'string', 'in:' . implode(',', $slugs)],
            ]
        )->all();

        $validated = $request->validate($rules);

        $assessment = $this->sessionFlow->getAssessmentOrFail();

        $tiers = $this->tierResolver->resolve(
            $validated,
            $assessment->selected_services ?? ['lawn']
        );

        $this->sessionFlow->updateAssessment([
            'quiz_answers'  => $validated,
            'resolved_tier' => $tiers['lawn'] ?? 'bronze',
            'current_step'  => 5,
        ]);

        return redirect()->route('yard.plan');
    }
}