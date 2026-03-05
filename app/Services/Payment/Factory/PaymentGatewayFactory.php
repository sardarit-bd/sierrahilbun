<?php

namespace App\Services\Payment\Factory;

use App\Models\PaymentGatewaySetting;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\StripeGateway;
use Illuminate\Support\Facades\Cache;

class PaymentGatewayFactory
{
    public function make(string $gateway): PaymentGatewayInterface
    {
        $settingId = Cache::remember("payment_gateway_id_{$gateway}", 3600, function () use ($gateway) {
            return PaymentGatewaySetting::where('gateway', $gateway)
                ->where('is_active', true)
                ->firstOrFail()
                ->id;
        });

        $setting = PaymentGatewaySetting::findOrFail($settingId);

        return match($gateway) {
            'stripe' => new StripeGateway(
                secretKey:     $setting->secret_key,  
                webhookSecret: $setting->webhook_secret,  
            ),
            default => throw new \InvalidArgumentException("Unsupported gateway: [{$gateway}]"),
        };
    }
}