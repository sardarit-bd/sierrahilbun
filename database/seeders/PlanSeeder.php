<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $lawnId = DB::table('services')->where('slug', 'lawn')->value('id');
        $pestId = DB::table('services')->where('slug', 'pest')->value('id');

        DB::table('plans')->insertOrIgnore([
            // -------------------------------------------------------
            // Lawn Plans
            // -------------------------------------------------------
            [
                'service_id'           => $lawnId,
                'name'                 => 'Basic Care',
                'slug'                 => 'lawn-bronze',
                'description'          => 'Essential lawn nutrition to grow a green, lush lawn.',
                'base_price_yearly'    => 179.00,
                'current_price_yearly' => 179.00,
                'is_recommended'       => false,
                'target_audience'      => 'bronze',
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
            [
                'service_id'           => $lawnId,
                'name'                 => 'Keep & Protect',
                'slug'                 => 'lawn-silver',
                'description'          => 'Nutrition plus targeted weed control to keep your lawn thriving.',
                'base_price_yearly'    => 249.00,
                'current_price_yearly' => 249.00,
                'is_recommended'       => true,
                'target_audience'      => 'silver',
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
            [
                'service_id'           => $lawnId,
                'name'                 => 'Grow & Renew',
                'slug'                 => 'lawn-gold',
                'description'          => 'Complete lawn care with patch repair and full weed control.',
                'base_price_yearly'    => 289.00,
                'current_price_yearly' => 289.00,
                'is_recommended'       => false,
                'target_audience'      => 'gold',
                'created_at'           => now(),
                'updated_at'           => now(),
            ],

            // -------------------------------------------------------
            // Pest Plans
            // -------------------------------------------------------
            [
                'service_id'           => $pestId,
                'name'                 => 'Base Plan',
                'slug'                 => 'pest-bronze',
                'description'          => 'Create a bug-free zone with home barrier treatment.',
                'base_price_yearly'    => 115.00,
                'current_price_yearly' => 115.00,
                'is_recommended'       => false,
                'target_audience'      => 'bronze',
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
            [
                'service_id'           => $pestId,
                'name'                 => 'Custom Plan',
                'slug'                 => 'pest-silver',
                'description'          => 'Full barrier treatment plus yard pest control.',
                'base_price_yearly'    => 169.00,
                'current_price_yearly' => 169.00,
                'is_recommended'       => true,
                'target_audience'      => 'silver',
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
        ]);
    }
}