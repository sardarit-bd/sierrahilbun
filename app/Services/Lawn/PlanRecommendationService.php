<?php

namespace App\Services\Lawn;

use Illuminate\Support\Facades\DB;

class PlanRecommendationService
{
    // Maps service slug + tier → plan slug
    private const PLAN_SLUG_MAP = [
        'lawn' => [
            'bronze' => 'lawn-bronze',
            'silver' => 'lawn-silver',
            'gold'   => 'lawn-gold',
        ],
        'weeds' => [
            'bronze' => 'weeds-bronze',
            'silver' => 'weeds-silver',
            'gold'   => 'weeds-gold',
        ],
    ];

    public function recommend(array $tiers): array
    {
        $result = [];

        foreach ($tiers as $service => $tier) {
            $planSlug = self::PLAN_SLUG_MAP[$service][$tier] ?? null;

            if (!$planSlug) {
                continue;
            }

            $plan = $this->fetchPlan($planSlug);

            if ($plan) {
                $result[$service] = $plan;
            }
        }

        return $result;
    }

    // -------------------------------------------------------
    // Fetch all tiers for a given service
    // Used to display all plan options on the result page
    // with the recommended one highlighted.
    //
    // Input:  ['lawn', 'weeds']
    // Output: ['lawn' => [bronze => [...], silver => [...], gold => [...]], ...]
    // -------------------------------------------------------

    public function allPlansForServices(array $services): array
    {
        $result = [];

        foreach ($services as $service) {
            if (!isset(self::PLAN_SLUG_MAP[$service])) {
                continue;
            }

            $slugs = array_unique(array_values(self::PLAN_SLUG_MAP[$service]));

            $plans = DB::table('plans')
                ->join('services', 'plans.service_id', '=', 'services.id')
                ->whereIn('plans.slug', $slugs)
                ->where('services.slug', $service)
                ->select(
                    'plans.id',
                    'plans.name',
                    'plans.slug',
                    'plans.description',
                    'plans.base_price_yearly',
                    'plans.current_price_yearly',
                    'plans.is_recommended',
                    'plans.target_audience',
                )
                ->get();

            $plansWithFeatures = $plans->map(function ($plan) {
                $plan->features = DB::table('plan_features')
                    ->where('plan_id', $plan->id)
                    ->orderBy('sort_order')
                    ->select('title', 'subtitle', 'icon_url')
                    ->get()
                    ->toArray();

                return $plan;
            });

            $result[$service] = $plansWithFeatures->keyBy('target_audience')->toArray();
        }

        return $result;
    }

    public function resolveIds(array $tiers): array
    {
        $ids = [];

        foreach ($tiers as $service => $tier) {
            $planSlug = self::PLAN_SLUG_MAP[$service][$tier] ?? null;

            if (!$planSlug) {
                continue;
            }

            $id = DB::table('plans')->where('slug', $planSlug)->value('id');

            if ($id) {
                $ids[$service] = $id;
            }
        }

        return $ids;
    }

    // -------------------------------------------------------
    // Private: fetch single plan with features
    // -------------------------------------------------------

    private function fetchPlan(string $slug): ?object
    {
        $plan = DB::table('plans')
            ->where('slug', $slug)
            ->select(
                'id',
                'name',
                'slug',
                'description',
                'base_price_yearly',
                'current_price_yearly',
                'is_recommended',
                'target_audience',
            )
            ->first();

        if (!$plan) {
            return null;
        }

        $plan->features = DB::table('plan_features')
            ->where('plan_id', $plan->id)
            ->orderBy('sort_order')
            ->select('title', 'subtitle', 'icon_url')
            ->get()
            ->toArray();

        return $plan;
    }
}