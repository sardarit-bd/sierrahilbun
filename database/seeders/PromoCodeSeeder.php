<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        PromoCode::insert([
            [
                'code'          => 'SAVE10',
                'type'          => 'percentage',
                'value'         => 10.00,
                'min_purchase'  => 0.00,
                'max_discount'  => null,
                'usage_limit'   => null,
                'usage_count'   => 0,
                'starts_at'     => null,
                'expires_at'    => null,
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'code'          => 'SAVE20',
                'type'          => 'percentage',
                'value'         => 20.00,
                'min_purchase'  => 50.00,
                'max_discount'  => null,
                'usage_limit'   => 100,
                'usage_count'   => 0,
                'starts_at'     => null,
                'expires_at'    => now()->addDays(30),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'code'          => 'SPRINGREADY20',
                'type'          => 'percentage',
                'value'         => 20.00,
                'min_purchase'  => 0.00,
                'max_discount'  => 20.00,
                'usage_limit'   => null,
                'usage_count'   => 0,
                'starts_at'     => null,
                'expires_at'    => now()->addMonths(3),
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'code'          => 'FLAT5',
                'type'          => 'fixed',
                'value'         => 5.00,
                'min_purchase'  => 0.00,
                'max_discount'  => null,
                'usage_limit'   => null,
                'usage_count'   => 0,
                'starts_at'     => null,
                'expires_at'    => null,
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}