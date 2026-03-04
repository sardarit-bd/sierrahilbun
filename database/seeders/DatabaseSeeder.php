<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Run order is intentional — dependencies flow top to bottom:
     *   ProductSeeder       → creates products + variants (required by FeatureProductSeeder)
     *   ServiceSeeder       → creates services (required by PlanSeeder)
     *   PlanSeeder          → creates plans (required by FeatureProductSeeder)
     *   FeatureProductSeeder→ maps features→products and plans→features
     *                         (replaces old PlanFeatureSeeder — do not re-add)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            ServiceSeeder::class,
            PlanSeeder::class,
            FeatureSeeder::class,
            FeatureProductSeeder::class,   
            PaymentGatewaySettingSeeder::class,
            BlogCategorySeeder::class,
            QuestionnaireSeeder::class,
        ]);
    }
}