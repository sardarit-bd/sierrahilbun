<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fertilizers & Nutrition',
                'image_url' => 'categories/fertilizer-placeholder.png',
                'description' => 'Promote vigorous growth and deep green color.'
            ],
            [
                'name' => 'Soil Improvement',
                'image_url' => 'categories/soil-placeholder.png',
                'description' => 'Conditioners, aerators, and pH balancers for healthy soil.'
            ],
            [
                'name' => 'Stress Defense',
                'image_url' => 'categories/stress-defense-placeholder.png',
                'description' => 'Protect turf from heat, drought, and seasonal stress.'
            ],
            [
                'name' => 'Lawn Repair',
                'image_url' => 'categories/repair-placeholder.png',
                'description' => 'Solutions for bare spots, thinning, and pet damage.'
            ],
        ];

        foreach ($categories as $cat) {
            ProductCategory::firstOrCreate(
                ['name' => $cat['name']], 
                [
                    'slug' => Str::slug($cat['name']),
                    'image_url' => $cat['image_url'],
                    'parent_id' => null, 
                ]
            );
        }
    }
}