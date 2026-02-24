<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $plans    = DB::table('plans')->pluck('id', 'slug');
        $services = DB::table('services')->pluck('id', 'slug');

        $lawnServiceId  = $services['lawn'];
        $weedsServiceId = $services['weeds'];

        // -------------------------------------------------------
        // 1. Insert unique features once per service
        // -------------------------------------------------------

        $lawnFeatures = [
            'custom-fertilizers' => [
                'service_id' => $lawnServiceId,
                'title'      => 'Custom fertilizers',
                'subtitle'   => 'Grow a green, lush lawn with custom fertilizers.',
                'sort_order' => 0,
            ],
            'targeted-weed-control' => [
                'service_id' => $lawnServiceId,
                'title'      => 'Targeted weed control',
                'subtitle'   => 'Targeted control that works with nature, not against it.',
                'sort_order' => 0,
            ],
            'patch-repair' => [
                'service_id' => $lawnServiceId,
                'title'      => 'Patch repair',
                'subtitle'   => 'Fill patches and thinning grass.',
                'sort_order' => 0,
            ],
            'pet-spot-repair' => [
                'service_id' => $lawnServiceId,
                'title'      => 'Pet spot repair',
                'subtitle'   => 'Repair brown spots from pet urine.',
                'sort_order' => 0,
            ],
            'advanced-soil-test' => [
                'service_id' => $lawnServiceId,
                'title'      => 'Advanced soil test',
                'subtitle'   => 'Unlock your lawn\'s potential with an advanced soil test.',
                'sort_order' => 0,
            ],
            'surprise-gift' => [
                'service_id' => $lawnServiceId,
                'title'      => 'Surprise gift',
                'subtitle'   => 'A little something extra for you. (It\'s a surprise! You\'ll see it in your cart.)',
                'sort_order' => 0,
            ],
        ];

        $weedsFeatures = [
            'home-barrier-treatment' => [
                'service_id' => $weedsServiceId,
                'title'      => 'Home barrier treatment',
                'subtitle'   => 'Create a bug-free zone with home barrier treatment.',
                'sort_order' => 0,
            ],
            'yard-treatment' => [
                'service_id' => $weedsServiceId,
                'title'      => 'Yard treatment',
                'subtitle'   => 'Enjoy your yard again.',
                'sort_order' => 0,
            ],
        ];

        // Insert and collect IDs keyed by local slug
        $lawnFeatureIds = [];
        foreach ($lawnFeatures as $key => $data) {
            $lawnFeatureIds[$key] = DB::table('features')->insertGetId($data);
        }

        $weedsFeatureIds = [];
        foreach ($weedsFeatures as $key => $data) {
            $weedsFeatureIds[$key] = DB::table('features')->insertGetId($data);
        }

        // -------------------------------------------------------
        // 2. Attach features to plans via pivot
        // -------------------------------------------------------

        $pivotRows = [

            // --- Lawn Bronze: Basic Care ---
            ['plan_id' => $plans['lawn-bronze'], 'feature_id' => $lawnFeatureIds['custom-fertilizers'],    'sort_order' => 1],
            ['plan_id' => $plans['lawn-bronze'], 'feature_id' => $lawnFeatureIds['advanced-soil-test'],    'sort_order' => 2],
            ['plan_id' => $plans['lawn-bronze'], 'feature_id' => $lawnFeatureIds['surprise-gift'],         'sort_order' => 3],

            // --- Lawn Silver: Keep & Protect (bronze + weed control) ---
            ['plan_id' => $plans['lawn-silver'], 'feature_id' => $lawnFeatureIds['custom-fertilizers'],    'sort_order' => 1],
            ['plan_id' => $plans['lawn-silver'], 'feature_id' => $lawnFeatureIds['targeted-weed-control'], 'sort_order' => 2],
            ['plan_id' => $plans['lawn-silver'], 'feature_id' => $lawnFeatureIds['advanced-soil-test'],    'sort_order' => 3],
            ['plan_id' => $plans['lawn-silver'], 'feature_id' => $lawnFeatureIds['surprise-gift'],         'sort_order' => 4],

            // --- Lawn Gold: Grow & Renew (silver + patch + pet) ---
            ['plan_id' => $plans['lawn-gold'],   'feature_id' => $lawnFeatureIds['custom-fertilizers'],    'sort_order' => 1],
            ['plan_id' => $plans['lawn-gold'],   'feature_id' => $lawnFeatureIds['targeted-weed-control'], 'sort_order' => 2],
            ['plan_id' => $plans['lawn-gold'],   'feature_id' => $lawnFeatureIds['patch-repair'],          'sort_order' => 3],
            ['plan_id' => $plans['lawn-gold'],   'feature_id' => $lawnFeatureIds['pet-spot-repair'],       'sort_order' => 4],
            ['plan_id' => $plans['lawn-gold'],   'feature_id' => $lawnFeatureIds['advanced-soil-test'],    'sort_order' => 5],
            ['plan_id' => $plans['lawn-gold'],   'feature_id' => $lawnFeatureIds['surprise-gift'],         'sort_order' => 6],

            // --- Weeds Bronze: Base Plan ---
            ['plan_id' => $plans['weeds-bronze'], 'feature_id' => $weedsFeatureIds['home-barrier-treatment'], 'sort_order' => 1],

            // --- Weeds Silver: Custom Plan (bronze + yard) ---
            ['plan_id' => $plans['weeds-silver'], 'feature_id' => $weedsFeatureIds['home-barrier-treatment'], 'sort_order' => 1],
            ['plan_id' => $plans['weeds-silver'], 'feature_id' => $weedsFeatureIds['yard-treatment'],         'sort_order' => 2],
        ];

        DB::table('plan_feature')->insert($pivotRows);
    }
}