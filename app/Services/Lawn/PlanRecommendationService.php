<?php

namespace App\Services\Lawn;

use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class PlanRecommendationService
{
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

    public function allPlansForServices(array $services): array
    {
        $result = [];

        foreach ($services as $service) {
            if (!isset(self::PLAN_SLUG_MAP[$service])) {
                continue;
            }

            $slugs = array_values(self::PLAN_SLUG_MAP[$service]);

            $plans = Plan::with(['features' => function ($query) {
                            $query->orderBy('plan_feature.sort_order');
                        }])
                        ->whereIn('slug', $slugs)
                        ->whereHas('service', fn($q) => $q->where('slug', $service))
                        ->get();

            $result[$service] = $plans
                ->map(fn($plan) => $this->formatPlan($plan))
                ->keyBy('target_audience')
                ->toArray();
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

    private function fetchPlan(string $slug): ?array
    {
        $plan = Plan::with(['features' => function ($query) {
                        $query->orderBy('plan_feature.sort_order');
                    }])
                    ->where('slug', $slug)
                    ->first();

        return $plan ? $this->formatPlan($plan) : null;
    }

    private function formatPlan(Plan $plan): array
    {
        return [
            'id'                   => $plan->id,
            'name'                 => $plan->name,
            'slug'                 => $plan->slug,
            'description'          => $plan->description,
            'base_price_yearly'    => $plan->base_price_yearly,
            'current_price_yearly' => $plan->current_price_yearly,
            'is_recommended'       => $plan->is_recommended,
            'target_audience'      => $plan->target_audience,
            'features'             => $plan->features->map(fn($f) => [
                'title'     => $f->title,
                'subtitle'  => $f->subtitle,
                'icon_url'  => $this->resolveStorageUrl($f->icon_url),
                'image_url' => array_map(
                    fn($path) => $this->resolveStorageUrl($path),
                    $f->image_url ?? []
                ),
            ])->values()->toArray(),
        ];
    }

    private function resolveStorageUrl(?string $path): ?string
    {
        if (!$path) return null;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = preg_replace('#^/?storage/#', '', $path);

        return asset('storage/' . $normalized);
    }
}