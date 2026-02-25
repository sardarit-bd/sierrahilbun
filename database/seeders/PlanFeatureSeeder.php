<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('plan_feature')->insert([
            ['plan_id' => 1, 'feature_id' => 1, 'sort_order' => 1],
            ['plan_id' => 1, 'feature_id' => 5, 'sort_order' => 2],
            ['plan_id' => 1, 'feature_id' => 6, 'sort_order' => 3],

            ['plan_id' => 2, 'feature_id' => 1, 'sort_order' => 1],
            ['plan_id' => 2, 'feature_id' => 2, 'sort_order' => 2],
            ['plan_id' => 2, 'feature_id' => 5, 'sort_order' => 3],
            ['plan_id' => 2, 'feature_id' => 6, 'sort_order' => 4],

            ['plan_id' => 3, 'feature_id' => 1, 'sort_order' => 1],
            ['plan_id' => 3, 'feature_id' => 2, 'sort_order' => 2],
            ['plan_id' => 3, 'feature_id' => 3, 'sort_order' => 3],
            ['plan_id' => 3, 'feature_id' => 4, 'sort_order' => 4],
            ['plan_id' => 3, 'feature_id' => 5, 'sort_order' => 5],
            ['plan_id' => 3, 'feature_id' => 6, 'sort_order' => 6],

            ['plan_id' => 4, 'feature_id' => 7, 'sort_order' => 1],

            ['plan_id' => 5, 'feature_id' => 7, 'sort_order' => 1],
            ['plan_id' => 5, 'feature_id' => 8, 'sort_order' => 2],
        ]);
    }
}