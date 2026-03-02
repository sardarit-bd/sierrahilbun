<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureProductSeeder extends Seeder
{
    /**
     * Feature IDs (lawn service_id = 1)
     *
     * 1 = Custom fertilizers   → Bronze (core products)
     * 2 = Targeted weed control → Gold   (Aerate, HeatGuard)
     * 3 = Patch repair          → Silver (PatchPro+)
     * 4 = Pet spot repair       → Gold   (quiz-driven, not soil-driven)
     * 5 = Advanced soil test    → All plans
     *
     * Plan IDs (lawn service_id = 1)
     * 1 = Bronze, 2 = Silver, 3 = Gold
     */

    // -------------------------------------------------------
    // Feature → Product mappings (soil engine driven)
    // -------------------------------------------------------

    private const FEATURE_PRODUCTS = [
        1 => ['kickstart', 'turf-fuel-n', 'neutralyze'], // Custom fertilizers → Bronze
        3 => ['patchpro'],                                // Patch repair       → Silver
        2 => ['aerate', 'heatguard'],                    // Targeted weed ctrl → Gold
    ];

    // -------------------------------------------------------
    // Correct plan_feature rows to match Python tier logic:
    //
    // Bronze → 1 (Custom fertilizers), 5 (Advanced soil test)
    // Silver → 1, 3 (Patch repair),    5
    // Gold   → 1, 2 (Weed control), 3, 4 (Pet spot repair), 5
    // -------------------------------------------------------

    private const PLAN_FEATURES = [
        1 => [                          // Bronze
            ['feature_id' => 1, 'sort_order' => 1],
            ['feature_id' => 5, 'sort_order' => 2],
        ],
        2 => [                          // Silver
            ['feature_id' => 1, 'sort_order' => 1],
            ['feature_id' => 3, 'sort_order' => 2],
            ['feature_id' => 5, 'sort_order' => 3],
        ],
        3 => [                          // Gold
            ['feature_id' => 1, 'sort_order' => 1],
            ['feature_id' => 2, 'sort_order' => 2],
            ['feature_id' => 3, 'sort_order' => 3],
            ['feature_id' => 4, 'sort_order' => 4],
            ['feature_id' => 5, 'sort_order' => 5],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedFeatureProducts();
            $this->correctPlanFeatures();
        });
    }

    // -------------------------------------------------------
    // Internals
    // -------------------------------------------------------

    private function seedFeatureProducts(): void
    {
        // Wipe and re-seed cleanly (idempotent)
        DB::table('feature_product')->whereIn('feature_id', array_keys(self::FEATURE_PRODUCTS))->delete();

        $rows = [];

        foreach (self::FEATURE_PRODUCTS as $featureId => $skus) {
            foreach ($skus as $sku) {
                $rows[] = [
                    'feature_id'  => $featureId,
                    'product_sku' => $sku,
                ];
            }
        }

        DB::table('feature_product')->insert($rows);
    }

    private function correctPlanFeatures(): void
    {
        // Only correct lawn plans (IDs 1, 2, 3) — leave weeds/garden untouched
        DB::table('plan_feature')->whereIn('plan_id', array_keys(self::PLAN_FEATURES))->delete();

        $rows = [];

        foreach (self::PLAN_FEATURES as $planId => $features) {
            foreach ($features as $feature) {
                $rows[] = [
                    'plan_id'    => $planId,
                    'feature_id' => $feature['feature_id'],
                    'sort_order' => $feature['sort_order'],
                ];
            }
        }

        DB::table('plan_feature')->insert($rows);
    }
}