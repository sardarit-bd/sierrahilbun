<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $plans = DB::table('plans')->pluck('id', 'slug');

        $features = [
            // -------------------------------------------------------
            // Lawn Bronze — Basic Care
            // -------------------------------------------------------
            [
                'plan_id'    => $plans['lawn-bronze'],
                'title'      => 'Custom fertilizers',
                'subtitle'   => 'Grow a green, lush lawn with custom fertilizers.',
                'sort_order' => 1,
            ],
            [
                'plan_id'    => $plans['lawn-bronze'],
                'title'      => 'Advanced soil test',
                'subtitle'   => 'Unlock your lawn\'s potential with an advanced soil test.',
                'sort_order' => 2,
            ],
            [
                'plan_id'    => $plans['lawn-bronze'],
                'title'      => 'Surprise gift',
                'subtitle'   => 'A little something extra for you. (It\'s a surprise! You\'ll see it in your cart.)',
                'sort_order' => 3,
            ],

            // -------------------------------------------------------
            // Lawn Silver — Keep & Protect (Bronze features + extra)
            // -------------------------------------------------------
            [
                'plan_id'    => $plans['lawn-silver'],
                'title'      => 'Custom fertilizers',
                'subtitle'   => 'Grow a green, lush lawn with custom fertilizers.',
                'sort_order' => 1,
            ],
            [
                'plan_id'    => $plans['lawn-silver'],
                'title'      => 'Targeted weed control',
                'subtitle'   => 'Targeted control that works with nature, not against it.',
                'sort_order' => 2,
            ],
            [
                'plan_id'    => $plans['lawn-silver'],
                'title'      => 'Advanced soil test',
                'subtitle'   => 'Unlock your lawn\'s potential with an advanced soil test.',
                'sort_order' => 3,
            ],
            [
                'plan_id'    => $plans['lawn-silver'],
                'title'      => 'Surprise gift',
                'subtitle'   => 'A little something extra for you. (It\'s a surprise! You\'ll see it in your cart.)',
                'sort_order' => 4,
            ],

            // -------------------------------------------------------
            // Lawn Gold — Grow & Renew (Silver features + extra)
            // -------------------------------------------------------
            [
                'plan_id'    => $plans['lawn-gold'],
                'title'      => 'Custom fertilizers',
                'subtitle'   => 'Grow a green, lush lawn with custom fertilizers.',
                'sort_order' => 1,
            ],
            [
                'plan_id'    => $plans['lawn-gold'],
                'title'      => 'Targeted weed control',
                'subtitle'   => 'Targeted control that works with nature, not against it.',
                'sort_order' => 2,
            ],
            [
                'plan_id'    => $plans['lawn-gold'],
                'title'      => 'Patch repair',
                'subtitle'   => 'Fill patches and thinning grass.',
                'sort_order' => 3,
            ],
            [
                'plan_id'    => $plans['lawn-gold'],
                'title'      => 'Pet spot repair',
                'subtitle'   => 'Repair brown spots from pet urine.',
                'sort_order' => 4,
            ],
            [
                'plan_id'    => $plans['lawn-gold'],
                'title'      => 'Advanced soil test',
                'subtitle'   => 'Unlock your lawn\'s potential with an advanced soil test.',
                'sort_order' => 5,
            ],
            [
                'plan_id'    => $plans['lawn-gold'],
                'title'      => 'Surprise gift',
                'subtitle'   => 'A little something extra for you. (It\'s a surprise! You\'ll see it in your cart.)',
                'sort_order' => 6,
            ],

            // -------------------------------------------------------
            // Pest Bronze — Base Plan
            // -------------------------------------------------------
            [
                'plan_id'    => $plans['pest-bronze'],
                'title'      => 'Home barrier treatment',
                'subtitle'   => 'Create a bug-free zone with home barrier treatment.',
                'sort_order' => 1,
            ],

            // -------------------------------------------------------
            // Pest Silver — Custom Plan (Bronze feature + extra)
            // -------------------------------------------------------
            [
                'plan_id'    => $plans['pest-silver'],
                'title'      => 'Home barrier treatment',
                'subtitle'   => 'Create a bug-free zone with home barrier treatment.',
                'sort_order' => 1,
            ],
            [
                'plan_id'    => $plans['pest-silver'],
                'title'      => 'Yard treatment',
                'subtitle'   => 'Enjoy your yard again.',
                'sort_order' => 2,
            ],
        ];

        foreach ($features as $feature) {
            DB::table('plan_features')->insertOrIgnore($feature);
        }
    }
}