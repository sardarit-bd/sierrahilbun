<?php

namespace App\Observers;

use App\Models\Question;
use App\Repositories\QuestionRepository;

class QuestionObserver
{
    public function __construct(
        private QuestionRepository $repository,
    ) {}

    public function saved(Question $question): void
    {
        $this->repository->invalidateCache();
    }

    public function deleted(Question $question): void
    {
        $this->repository->invalidateCache();
    }
}