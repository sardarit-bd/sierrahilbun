<?php

namespace App\DTOs\Lawn;

final class RecommendationResultDTO
{
    /**
     * @param ModifierRecommendationDTO[] $modifiers
     * @param string[]                    $notes
     */
    public function __construct(
        public readonly CoreRatioDTO $core,
        public readonly array        $modifiers,
        public readonly array        $notes,
    ) {}

    public function allSlugs(): array
    {
        $core = array_keys($this->core->toArray());

        $modifiers = array_map(
            fn (ModifierRecommendationDTO $m) => $m->slug,
            $this->modifiers
        );

        return array_values(array_unique(array_merge($core, $modifiers)));
    }

    public function toArray(): array
    {
        return [
            'core'      => $this->core->toArray(),
            'modifiers' => array_map(
                fn (ModifierRecommendationDTO $m) => $m->toArray(),
                $this->modifiers
            ),
            'notes'     => $this->notes,
        ];
    }
}