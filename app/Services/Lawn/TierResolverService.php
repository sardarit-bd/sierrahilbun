<?php

namespace App\Services\Lawn;

class TierResolverService
{
    // -------------------------------------------------------
    // Base tier from goals answer
    // -------------------------------------------------------

    private const GOAL_TIER_MAP = [
        'looks'  => 'bronze',
        'health' => 'silver',
        'safety' => 'silver',
        'all'    => 'gold',
    ];

    // -------------------------------------------------------
    // Tier rank for upgrade comparisons
    // -------------------------------------------------------

    private const TIER_RANK = [
        'bronze' => 1,
        'silver' => 2,
        'gold'   => 3,
    ];

    // -------------------------------------------------------
    // Public Entry Point
    // -------------------------------------------------------

    public function resolve(array $answers): string
    {
        $tier = $this->baseFromGoals($answers['goals'] ?? 'looks');
        $tier = $this->applyUpgradeRules($tier, $answers);

        return $tier;
    }

    // -------------------------------------------------------
    // Base Tier
    // -------------------------------------------------------

    private function baseFromGoals(string $goal): string
    {
        return self::GOAL_TIER_MAP[$goal] ?? 'bronze';
    }

    // -------------------------------------------------------
    // Upgrade Rules
    // Each rule can only upgrade, never downgrade.
    // -------------------------------------------------------

    private function applyUpgradeRules(string $tier, array $answers): string
    {
        $tier = $this->applyPetsRule($tier, $answers);
        $tier = $this->applyWeedsRule($tier, $answers);
        $tier = $this->applyCareRule($tier, $answers);
        $tier = $this->applyPatchesRule($tier, $answers);
        $tier = $this->applyKnowledgeRule($tier, $answers);

        return $tier;
    }

    // Pets on lawn "a lot" + bronze → upgrade to silver
    private function applyPetsRule(string $tier, array $answers): string
    {
        if (($answers['pets'] ?? '') === 'lot' && $tier === 'bronze') {
            return 'silver';
        }

        return $tier;
    }

    // Weeds everywhere → upgrade to gold
    // Leafy or pre-emergent needed → upgrade bronze to silver
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

    // High-frequency care (3–5x/year) + bronze → upgrade to silver
    // Lawn service does it all → upgrade to gold
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

    // Lots of bare patches → upgrade bronze to silver
    private function applyPatchesRule(string $tier, array $answers): string
    {
        if (($answers['patches'] ?? 'none') === 'lots' && $tier === 'bronze') {
            return 'silver';
        }

        return $tier;
    }

    // Expert knowledge + bronze → upgrade to silver
    // (Experts expect more from their plan)
    private function applyKnowledgeRule(string $tier, array $answers): string
    {
        if (($answers['knowledge'] ?? '') === 'expert' && $tier === 'bronze') {
            return 'silver';
        }

        return $tier;
    }

    // -------------------------------------------------------
    // Upgrade Helper
    // Only moves tier up, never down.
    // -------------------------------------------------------

    private function upgrade(string $current, string $target): string
    {
        $currentRank = self::TIER_RANK[$current] ?? 1;
        $targetRank  = self::TIER_RANK[$target] ?? 1;

        return $targetRank > $currentRank ? $target : $current;
    }
}