<?php

namespace App\Services\Lawn;

use App\Models\Plan;
use App\Services\CacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds all three lawn plans (bronze, silver, gold) with their
 * respective product sets, and flags which plan is recommended.
 *
 * Responsibilities:
 *   1. Build each plan's product set progressively (bronze ⊆ silver ⊆ gold)
 *   2. Determine the recommended plan — minimum plan that covers
 *      ALL products the soil engine said the customer needs
 *   3. Respect the quiz floor tier — recommended plan can only
 *      go UP from the quiz result, never down
 */
final class PlanResolverService
{
    private const TIER_RANK = [
        'bronze' => 1,
        'silver' => 2,
        'gold'   => 3,
    ];

    private const TIERS = ['bronze', 'silver', 'gold'];

    private const SERVICE_SLUG = 'lawn';

    private const CACHE_TTL = 3600 * 24; // 24 hours

    private const CACHE_TAGS = ['plan_resolver'];

    public function __construct(
        private readonly CacheService $cache,
    ) {}

    // -------------------------------------------------------
    // Public API
    // -------------------------------------------------------

    /**
     * Build all three plans with their product sets and flag
     * the recommended one.
     *
     * Returns:
     * [
     *   'plans' => [
     *     'bronze' => ['plan' => Plan, 'products' => [...], 'is_recommended' => false],
     *     'silver' => [...],
     *     'gold'   => [...],
     *   ],
     *   'recommended_tier' => 'gold',
     * ]
     *
     * @param  string   $quizFloorTier  Tier from TierResolverService
     * @param  string[] $productSlugs   SKUs from soil engine
     */
    public function buildPlans(string $quizFloorTier, array $productSlugs): array
    {
        $lawnPlans       = $this->getLawnPlans();
        $recommendedTier = $this->resolveRecommendedTier($quizFloorTier, $productSlugs);
        $planProductSets = $this->buildPlanProductSets($productSlugs);

        $plans = [];

        foreach (self::TIERS as $tier) {
            $plan = $lawnPlans->first(
                fn (Plan $p) => $this->extractTier($p->slug) === $tier
            );

            if (! $plan) {
                continue;
            }

            $plans[$tier] = [
                'plan'           => $plan,
                'products'       => $planProductSets[$tier] ?? [],
                'is_recommended' => $tier === $recommendedTier,
            ];
        }

        return [
            'plans'            => $plans,
            'recommended_tier' => $recommendedTier,
        ];
    }

    /**
     * Returns only the recommended tier slug.
     * Lightweight — used when only the tier string is needed.
     */
    public function resolveRecommendedTier(string $quizFloorTier, array $productSlugs): string
    {
        $productMappedTier = $this->resolveTierFromProducts($productSlugs);

        return $this->higher($quizFloorTier, $productMappedTier);
    }

    /**
     * Returns all lawn plans ordered bronze → silver → gold.
     */
    public function allPlans(): Collection
    {
        return $this->getLawnPlans();
    }

    // -------------------------------------------------------
    // Plan Product Set Builder
    // -------------------------------------------------------

    /**
     * Builds the product set for each tier progressively.
     * Only includes products the soil engine recommended.
     * Each tier is a superset of the tier below it.
     *
     * @param  string[] $productSlugs
     * @return array<string, string[]>
     */
    private function buildPlanProductSets(array $productSlugs): array
    {
        if (empty($productSlugs)) {
            return array_fill_keys(self::TIERS, []);
        }

        $productTierMap = $this->mapProductsToTiers($productSlugs);

        $sets = array_fill_keys(self::TIERS, []);

        foreach ($productSlugs as $slug) {
            $productTier = $productTierMap[$slug] ?? 'gold';
            $productRank = self::TIER_RANK[$productTier] ?? 3;

            // Add product to all plans at or above its tier rank
            foreach (self::TIERS as $tier) {
                if (self::TIER_RANK[$tier] >= $productRank) {
                    $sets[$tier][] = $slug;
                }
            }
        }

        return $sets;
    }

    /**
     * Maps each product slug to the lowest tier plan
     * whose features contain that product.
     *
     * e.g. 'kickstart' → 'bronze', 'patchpro' → 'silver', 'aerate' → 'gold'
     *
     * @param  string[] $productSlugs
     * @return array<string, string>
     */
    private function mapProductsToTiers(array $productSlugs): array
    {
        $rows = DB::table('feature_product')
            ->whereIn('product_sku', $productSlugs)
            ->get();

        $productTierMap = [];

        foreach ($rows as $row) {
            $lowestTier = $this->getLowestTierForFeature((int) $row->feature_id);

            $productTierMap[$row->product_sku] = $lowestTier ?? 'gold';
        }

        // Any product not found in feature_product defaults to gold
        foreach ($productSlugs as $slug) {
            if (! isset($productTierMap[$slug])) {
                $productTierMap[$slug] = 'gold';
            }
        }

        return $productTierMap;
    }

    // -------------------------------------------------------
    // Tier Resolution
    // -------------------------------------------------------

    /**
     * Find the minimum tier whose plan covers ALL required features.
     */
    private function resolveTierFromProducts(array $productSlugs): string
    {
        if (empty($productSlugs)) {
            return 'bronze';
        }

        $requiredFeatureIds = $this->getFeatureIdsForProducts($productSlugs);

        if ($requiredFeatureIds->isEmpty()) {
            return 'bronze';
        }

        foreach ($this->getLawnPlans() as $plan) {
            $planFeatureIds = $this->getFeatureIdsForPlan($plan->id);

            if ($requiredFeatureIds->diff($planFeatureIds)->isEmpty()) {
                return $this->extractTier($plan->slug);
            }
        }

        return 'gold';
    }

    // -------------------------------------------------------
    // Data Access (cached via CacheService)
    // -------------------------------------------------------

    private function getFeatureIdsForProducts(array $productSlugs): Collection
    {
        return $this->cache->remember(
            'product_features:' . $this->hashSlugs($productSlugs),
            self::CACHE_TTL,
            fn () => DB::table('feature_product')
                ->whereIn('product_sku', $productSlugs)
                ->pluck('feature_id')
                ->unique()
                ->values(),
            self::CACHE_TAGS,
        );
    }

    private function getFeatureIdsForPlan(int $planId): Collection
    {
        return $this->cache->remember(
            'plan_features:' . $planId,
            self::CACHE_TTL,
            fn () => DB::table('plan_feature')
                ->where('plan_id', $planId)
                ->pluck('feature_id')
                ->unique()
                ->values(),
            self::CACHE_TAGS,
        );
    }

    /**
     * Find the lowest tier plan that contains the given feature.
     */
    private function getLowestTierForFeature(int $featureId): ?string
    {
        return $this->cache->remember(
            'feature_lowest_tier:' . $featureId,
            self::CACHE_TTL,
            function () use ($featureId) {
                $planIds = DB::table('plan_feature')
                    ->where('feature_id', $featureId)
                    ->pluck('plan_id')
                    ->all();

                if (empty($planIds)) {
                    return null;
                }

                // Load lawn plans from cache, filter to matching IDs,
                // sort by tier rank and return the lowest
                $matched = $this->getLawnPlans()
                    ->filter(fn (Plan $p) => in_array($p->id, $planIds))
                    ->sortBy(fn (Plan $p) => self::TIER_RANK[$this->extractTier($p->slug)] ?? 99);

                if ($matched->isEmpty()) {
                    return null;
                }

                return $this->extractTier($matched->first()->slug);
            },
            self::CACHE_TAGS,
        );
    }

    /**
     * Returns all lawn plans ordered bronze → silver → gold.
     * Cached — plan structure rarely changes.
     */
    private function getLawnPlans(): Collection
    {
        return $this->cache->remember(
            'lawn_plans',
            self::CACHE_TTL,
            fn () => Plan::whereHas(
                        'service',
                        fn ($q) => $q->where('slug', self::SERVICE_SLUG)
                    )
                    ->get()
                    ->sortBy(fn (Plan $p) => self::TIER_RANK[$this->extractTier($p->slug)] ?? 99)
                    ->values(),
            self::CACHE_TAGS,
        );
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function higher(string $a, string $b): string
    {
        return (self::TIER_RANK[$a] ?? 1) >= (self::TIER_RANK[$b] ?? 1) ? $a : $b;
    }

    private function extractTier(string $slug): string
    {
        return last(explode('-', $slug));
    }

    private function hashSlugs(array $slugs): string
    {
        sort($slugs);
        return md5(implode(',', $slugs));
    }
}