<?php

namespace App\Services\Lawn;

/**
 * Resolves the quiz-based floor tier for each selected service.
 *
 * Responsibility: translate quiz answers into a minimum starting tier.
 * This tier acts as a FLOOR — the final recommended plan can only
 * go UP from here, never down.
 *
 * Plan resolution (product mapping → final plan) is handled
 * exclusively by PlanResolverService.
 */
final class TierResolverService
{
    private const GOAL_TIER_MAP = [
        'looks'  => 'bronze',
        'health' => 'silver',
        'safety' => 'silver',
        'all'    => 'gold',
    ];

    private const TIER_RANK = [
        'bronze' => 1,
        'silver' => 2,
        'gold'   => 3,
    ];

    // Weeds service has no gold tier — caps at silver
    private const WEEDS_MAX_TIER = 'silver';

    // -------------------------------------------------------
    // Public API
    // -------------------------------------------------------

    /**
     * Resolve the floor tier per selected service.
     *
     * Returns a map of service slug → floor tier, e.g:
     * ['lawn' => 'silver', 'weeds' => 'silver']
     *
     * Only resolves tiers for services the customer selected.
     *
     * @param  array    $answers          Validated quiz answers
     * @param  string[] $selectedServices e.g. ['lawn', 'weeds']
     * @return array<string, string>
     */
    public function resolve(array $answers, array $selectedServices): array
    {
        $tiers = [];

        if (in_array('lawn', $selectedServices)) {
            $tiers['lawn'] = $this->resolveLawn($answers);
        }

        if (in_array('weeds', $selectedServices)) {
            $tiers['weeds'] = $this->resolveWeeds($answers);
        }

        return $tiers;
    }

    /**
     * Convenience method — returns only the lawn floor tier.
     */
    public function resolveLawnFloor(array $answers): string
    {
        return $this->resolveLawn($answers);
    }

    // -------------------------------------------------------
    // Per-service floor tier resolution
    // -------------------------------------------------------

    private function resolveLawn(array $answers): string
    {
        $tier = $this->baseFromGoals($answers['goals'] ?? 'looks');
        $tier = $this->applyPetsRule($tier, $answers);
        $tier = $this->applyWeedsRule($tier, $answers);
        $tier = $this->applyCareRule($tier, $answers);
        $tier = $this->applyPatchesRule($tier, $answers);
        $tier = $this->applyKnowledgeRule($tier, $answers);

        return $tier;
    }

    private function resolveWeeds(array $answers): string
    {
        $tier = $this->baseFromGoals($answers['goals'] ?? 'looks');
        $tier = $this->applyPetsRule($tier, $answers);
        $tier = $this->applyWeedsRule($tier, $answers);
        $tier = $this->applyCareRule($tier, $answers);
        $tier = $this->applyKnowledgeRule($tier, $answers);

        return $this->capAt($tier, self::WEEDS_MAX_TIER);
    }

    // -------------------------------------------------------
    // Base tier from goals answer
    // -------------------------------------------------------

    private function baseFromGoals(string $goal): string
    {
        return self::GOAL_TIER_MAP[$goal] ?? 'bronze';
    }

    // -------------------------------------------------------
    // Upgrade rules — can only upgrade, never downgrade
    // -------------------------------------------------------

    private function applyPetsRule(string $tier, array $answers): string
    {
        if (($answers['pets'] ?? '') === 'lot' && $tier === 'bronze') {
            return 'silver';
        }

        return $tier;
    }

    private function applyWeedsRule(string $tier, array $answers): string
    {
        $weeds = $answers['weeds'] ?? 'none';

        if ($weeds === 'everywhere') {
            return $this->upgrade($tier, 'gold');
        }

        if (in_array($weeds, ['leafy', 'pre']) && $tier === 'bronze') {
            return 'silver';
        }

        return $tier;
    }

    private function applyCareRule(string $tier, array $answers): string
    {
        $care = $answers['care'] ?? 'mow';

        if ($care === 'service') {
            return $this->upgrade($tier, 'gold');
        }

        if ($care === 'fert_high' && $tier === 'bronze') {
            return 'silver';
        }

        return $tier;
    }

    private function applyPatchesRule(string $tier, array $answers): string
    {
        if (($answers['patches'] ?? 'none') === 'lots' && $tier === 'bronze') {
            return 'silver';
        }

        return $tier;
    }

    private function applyKnowledgeRule(string $tier, array $answers): string
    {
        if (($answers['knowledge'] ?? '') === 'expert' && $tier === 'bronze') {
            return 'silver';
        }

        return $tier;
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function upgrade(string $current, string $target): string
    {
        return (self::TIER_RANK[$target] ?? 1) > (self::TIER_RANK[$current] ?? 1)
            ? $target
            : $current;
    }

    private function capAt(string $tier, string $max): string
    {
        return (self::TIER_RANK[$tier] ?? 1) > (self::TIER_RANK[$max] ?? 1)
            ? $max
            : $tier;
    }
}