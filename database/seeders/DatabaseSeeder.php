<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            ServiceSeeder::class,
            PlanSeeder::class,
            PlanFeatureSeeder::class,
            PaymentGatewaySettingSeeder::class,
            BlogCategorySeeder::class,
            QuestionnaireSeeder::class
        ]);
    }
}
