<?php

namespace App\Observers;

use App\Models\QuestionOption;
use App\Repositories\QuestionRepository;

class QuestionOptionObserver
{
    public function __construct(
        private QuestionRepository $repository,
    ) {}

    public function saved(QuestionOption $option): void
    {
        $this->repository->invalidateCache();
    }

    public function deleted(QuestionOption $option): void
    {
        $this->repository->invalidateCache();
    }
}