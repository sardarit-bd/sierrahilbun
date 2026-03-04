<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the features table for all services.
 *
 * Features are the plan card display rows — each feature groups
 * related products under a heading on the plan page.
 *
 * Lawn features (must match FeatureProductSeeder title keys exactly):
 *   1 — Custom fertilizers        → core products
 *   2 — Soil & Climate Protection → aerate, heatguard
 *   3 — Patch repair              → patchpro
 *
 * Weeds + garden features are preserved as-is from original data.
 */
class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $lawnId   = DB::table('services')->where('slug', 'lawn')->value('id');
        $weedsId  = DB::table('services')->where('slug', 'weeds')->value('id');

        // -------------------------------------------------------
        // Lawn features — 3 features matching new spec
        // -------------------------------------------------------
        $lawnFeatures = [
            [
                'service_id' => $lawnId,
                'title'      => 'Custom fertilizers',
                'subtitle'   => 'Grow a green, lush lawn with custom fertilizers.',
                'icon_url'   => null,
                'image_url'  => json_encode(['features/01KJQ54D4SVR1Q4H32BJGY4H73.png']),
                'sort_order' => 1,
            ],
            [
                'service_id' => $lawnId,
                'title'      => 'Soil & Climate Protection',
                'subtitle'   => 'Improve soil structure and protect against heat and drought stress.',
                'icon_url'   => null,
                'image_url'  => json_encode([]),
                'sort_order' => 2,
            ],
            [
                'service_id' => $lawnId,
                'title'      => 'Patch repair',
                'subtitle'   => 'Fill patches and thinning grass.',
                'icon_url'   => null,
                'image_url'  => json_encode([]),
                'sort_order' => 3,
            ],
        ];

        // -------------------------------------------------------
        // Weeds features — preserved from original
        // -------------------------------------------------------
        $weedsFeatures = [
            [
                'service_id' => $weedsId,
                'title'      => 'Home barrier treatment',
                'subtitle'   => 'Create a bug-free zone with home barrier treatment.',
                'icon_url'   => null,
                'image_url'  => json_encode([]),
                'sort_order' => 1,
            ],
            [
                'service_id' => $weedsId,
                'title'      => 'Yard treatment',
                'subtitle'   => 'Enjoy your yard again.',
                'icon_url'   => null,
                'image_url'  => json_encode([]),
                'sort_order' => 2,
            ],
        ];

        $allFeatures = array_merge($lawnFeatures, $weedsFeatures);

        foreach ($allFeatures as $feature) {
            DB::table('features')->updateOrInsert(
                [
                    'service_id' => $feature['service_id'],
                    'title'      => $feature['title'],
                ]
            );
        }
    }
}