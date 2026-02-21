<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('services')->insertOrIgnore([
            [
                'name'       => 'Lawn',
                'slug'       => 'lawn',
                'is_active'  => true,
                'created_at' => now(),
            ],
            [
                'name'       => 'Pest',
                'slug'       => 'pest',
                'is_active'  => true,
                'created_at' => now(),
            ],
            [
                'name'       => 'Garden',
                'slug'       => 'garden',
                'is_active'  => true,
                'created_at' => now(),
            ],
        ]);
    }
}