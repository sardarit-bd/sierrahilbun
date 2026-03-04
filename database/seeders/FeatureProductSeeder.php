<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds feature → product mappings and plan → feature mappings
 * for lawn plans according to the new spec.
 *
 * Feature structure (lawn service only):
 *   Feature 1 — Custom fertilizers     → core products (kickstart, turf-fuel-n, neutralyze)
 *   Feature 2 — Soil & Climate Protection → modifiers (aerate, heatguard)
 *   Feature 3 — Patch repair           → patchpro
 *
 * Plan → feature mapping:
 *   Bronze → Feature 1 (core only)
 *   Silver → Feature 1 + Feature 3 (core + patch repair)
 *   Gold   → Feature 1 + Feature 2 + Feature 3 (core + soil/climate + patch repair)
 *
 * Plan IDs are resolved dynamically by slug — safe across re-seeds.
 * Feature IDs are resolved dynamically by title — safe across re-seeds.
 *
 * Note: Features 4 (Pet spot repair) and 5 (Advanced soil test) are
 * intentionally excluded — they are not part of the new recommendation logic.
 */
class FeatureProductSeeder extends Seeder
{
    /**
     * Feature title → product slugs.
     * Only lawn engine products are mapped here.
     */
    private const FEATURE_PRODUCT_MAP = [
        'Custom fertilizers'        => ['kickstart', 'turf-fuel-n', 'neutralyze'],
        'Soil & Climate Protection' => ['aerate', 'heatguard'],
        'Patch repair'              => ['patchpro'],
    ];

    /**
     * Plan slug → [feature titles in sort order].
     * Bronze ⊆ Silver ⊆ Gold — each tier is a superset of the one below.
     */
    private const PLAN_FEATURE_MAP = [
        'lawn-bronze' => [
            'Custom fertilizers',
        ],
        'lawn-silver' => [
            'Custom fertilizers',
            'Patch repair',
        ],
        'lawn-gold' => [
            'Custom fertilizers',
            'Soil & Climate Protection',
            'Patch repair',
        ],
    ];

    // -------------------------------------------------------

    public function run(): void
    {
        DB::transaction(function () {
            $this->updateFeatureTitles();
            $this->seedFeatureProducts();
            $this->seedPlanFeatures();
        });
    }

    // -------------------------------------------------------
    // Internals
    // -------------------------------------------------------

    /**
     * Rename Feature 2 from old "Targeted weed control" title to
     * "Soil & Climate Protection" to match the new modifier logic.
     * All other feature titles remain unchanged.
     */
    private function updateFeatureTitles(): void
    {
        DB::table('features')
            ->where('title', 'Targeted weed control')
            ->update(['title' => 'Soil & Climate Protection']);
    }

    /**
     * Wipe and re-seed feature → product mappings for lawn features only.
     * Resolves feature IDs dynamically by title.
     */
    private function seedFeatureProducts(): void
    {
        $featureIds = DB::table('features')
            ->whereIn('title', array_keys(self::FEATURE_PRODUCT_MAP))
            ->pluck('id', 'title');

        // Remove existing mappings for these features only
        DB::table('feature_product')
            ->whereIn('feature_id', $featureIds->values()->all())
            ->delete();

        $rows = [];

        foreach (self::FEATURE_PRODUCT_MAP as $featureTitle => $slugs) {
            $featureId = $featureIds->get($featureTitle);

            if (! $featureId) {
                continue;
            }

            foreach ($slugs as $slug) {
                $rows[] = [
                    'feature_id'  => $featureId,
                    'product_sku' => $slug,
                ];
            }
        }

        if (! empty($rows)) {
            DB::table('feature_product')->insert($rows);
        }
    }

    /**
     * Wipe and re-seed plan → feature mappings for lawn plans only.
     * Resolves plan IDs and feature IDs dynamically — no hardcoded IDs.
     */
    private function seedPlanFeatures(): void
    {
        $planIds = DB::table('plans')
            ->whereIn('slug', array_keys(self::PLAN_FEATURE_MAP))
            ->pluck('id', 'slug');

        $featureIds = DB::table('features')
            ->whereIn('title', array_unique(array_merge(...array_values(self::PLAN_FEATURE_MAP))))
            ->pluck('id', 'title');

        // Remove existing plan_feature rows for lawn plans only
        DB::table('plan_feature')
            ->whereIn('plan_id', $planIds->values()->all())
            ->delete();

        $rows = [];

        foreach (self::PLAN_FEATURE_MAP as $planSlug => $featureTitles) {
            $planId = $planIds->get($planSlug);

            if (! $planId) {
                continue;
            }

            foreach ($featureTitles as $sortOrder => $featureTitle) {
                $featureId = $featureIds->get($featureTitle);

                if (! $featureId) {
                    continue;
                }

                $rows[] = [
                    'plan_id'    => $planId,
                    'feature_id' => $featureId,
                    'sort_order' => $sortOrder + 1,
                ];
            }
        }

        if (! empty($rows)) {
            DB::table('plan_feature')->insert($rows);
        }
    }
}