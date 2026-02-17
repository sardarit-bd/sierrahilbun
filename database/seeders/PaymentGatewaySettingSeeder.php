<?php

namespace Database\Seeders;

use App\Models\PaymentGatewaySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewaySettingSeeder extends Seeder
{
    public function run(): void
    {
        PaymentGatewaySetting::updateOrCreate(
            ['gateway' => 'stripe'],
            [
                'secret_key'     => Crypt::encryptString(env('STRIPE_SECRET_KEY')),
                'public_key'     => Crypt::encryptString(env('STRIPE_PUBLIC_KEY')),
                'webhook_secret' => Crypt::encryptString(env('STRIPE_WEBHOOK_SECRET')),
                'is_active'      => true,
            ]
        );
    }
}