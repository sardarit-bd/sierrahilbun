<?php

namespace App\Services\Lawn;

class TierResolverService
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

    // Weeds has no gold tier — caps at silver
    private const WEEDS_MAX_TIER = 'silver';

    // -------------------------------------------------------
    // Public Entry Point
    // Returns per-service tier map e.g:
    // ['lawn' => 'gold', 'weeds' => 'silver']
    // Only resolves tiers for selected services.
    // -------------------------------------------------------

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

    // -------------------------------------------------------
    // Lawn Tier Resolution
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

    // -------------------------------------------------------
    // Weeds Tier Resolution — same rules, caps at silver
    // -------------------------------------------------------

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
    // Base Tier from Goals
    // -------------------------------------------------------

    private function baseFromGoals(string $goal): string
    {
        return self::GOAL_TIER_MAP[$goal] ?? 'bronze';
    }

    // -------------------------------------------------------
    // Upgrade Rules
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

    // Only upgrades, never downgrades
    private function upgrade(string $current, string $target): string
    {
        $currentRank = self::TIER_RANK[$current] ?? 1;
        $targetRank  = self::TIER_RANK[$target] ?? 1;

        return $targetRank > $currentRank ? $target : $current;
    }

    // Caps tier at a maximum allowed tier
    private function capAt(string $tier, string $max): string
    {
        $tierRank = self::TIER_RANK[$tier] ?? 1;
        $maxRank  = self::TIER_RANK[$max] ?? 1;

        return $tierRank > $maxRank ? $max : $tier;
    }
}