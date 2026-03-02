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

final class QuestionnaireController extends Controller
{
    public function __construct(
        private readonly SessionFlowService  $sessionFlow,
        private readonly TierResolverService $tierResolver,
        private readonly QuestionRepository  $questions,
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
        $validated = $request->validate($this->buildValidationRules());

        $assessment = $this->sessionFlow->getAssessmentOrFail();

        $floorTier = $this->tierResolver->resolveLawnFloor($validated);

        $this->sessionFlow->updateAssessment([
            'quiz_answers'     => $validated,
            'quiz_floor_tier'  => $floorTier,
            'current_step'     => 5,
        ]);

        return redirect()->route('yard.plan');
    }

    // -------------------------------------------------------
    // Internals
    // -------------------------------------------------------

    private function buildValidationRules(): array
    {
        return collect($this->questions->getValidationMap())
            ->mapWithKeys(
                fn (array $slugs, string $questionSlug) => [
                    $questionSlug => ['required', 'string', 'in:' . implode(',', $slugs)],
                ]
            )
            ->all();
    }
}