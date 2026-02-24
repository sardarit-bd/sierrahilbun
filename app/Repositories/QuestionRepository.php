<?php

namespace App\Repositories;

use App\Models\Question;
use App\Services\CacheService;
use Illuminate\Support\Collection;

class QuestionRepository
{
    private const CACHE_KEY = 'questionnaire:all';
    private const CACHE_TTL = 3600*24; // 24 hours
    private const CACHE_TAGS = ['questionnaire'];

    public function __construct(
        private CacheService $cache,
    ) {}

    /**
     * Returns all active questions with their active options, ordered.
     * Result is cached.
     */
    public function getAllActive(): Collection
    {
        return $this->cache->remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn () => Question::active()
                ->with(['activeOptions'])
                ->orderBy('sort_order')
                ->get(),
            self::CACHE_TAGS,
        );
    }

    /**
     * Returns a flat map of question_slug => [allowed option slugs]
     * Used for dynamic validation rules.
     */
    public function getValidationMap(): array
    {
        return $this->getAllActive()
            ->mapWithKeys(fn (Question $q) => [
                $q->slug => $q->activeOptions->pluck('slug')->all(),
            ])
            ->all();
    }

    public function invalidateCache(): void
    {
        $this->cache->forget(self::CACHE_KEY, self::CACHE_TAGS);
    }
}