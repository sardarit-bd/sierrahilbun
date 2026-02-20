<?php

namespace App\Services\Lawn;

use App\Models\YardAssessment;
use Illuminate\Support\Facades\Session;

class SessionFlowService
{
    private const SESSION_KEY = 'yard_assessment_id';

    public function createAssessment(string $zipCode): YardAssessment
    {
        $assessment = YardAssessment::create([
            'zip_code'     => $zipCode,
            'session_id'   => Session::getId(),
            'user_id'      => auth()->id(),
            'status'       => 'draft',
            'current_step' => 1,
        ]);

        Session::put(self::SESSION_KEY, $assessment->id);

        return $assessment;
    }

    public function getAssessment(): ?YardAssessment
    {
        $id = Session::get(self::SESSION_KEY);

        if (!$id) {
            return null;
        }

        return YardAssessment::find($id);
    }

    public function getAssessmentOrFail(): YardAssessment
    {
        $assessment = $this->getAssessment();

        if (!$assessment) {
            abort(404, 'No active assessment found.');
        }

        return $assessment;
    }

    public function updateAssessment(array $data): YardAssessment
    {
        $assessment = $this->getAssessmentOrFail();
        $assessment->update($data);

        return $assessment->fresh();
    }

    public function advanceStep(int $step): void
    {
        $this->updateAssessment(['current_step' => $step]);
    }

    public function clearSession(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function hasActiveAssessment(): bool
    {
        return $this->getAssessment() !== null;
    }

    public function getCurrentStep(): int
    {
        $assessment = $this->getAssessment();

        return $assessment?->current_step ?? 1;
    }

    public function getAssessmentId(): ?string
    {
        return Session::get(self::SESSION_KEY);
    }
}