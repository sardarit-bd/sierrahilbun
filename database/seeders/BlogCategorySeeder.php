<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Lawn Renovation',
                'slug' => 'lawn-renovation',
            ],
            [
                'name' => 'Pest Control',
                'slug' => 'pest-control',
            ],
            [
                'name' => 'Gardening',
                'slug' => 'gardening',
            ],
            [
                'name' => 'Seasonal Care',
                'slug' => 'seasonal-care',
            ],
            [
                'name' => 'Equipment',
                'slug' => 'equipment',
            ],
        ];

        foreach ($categories as $cat) {
            \App\Models\BlogCategory::create($cat);
        }
    }
}
