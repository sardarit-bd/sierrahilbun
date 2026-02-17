<?php

namespace App\Services\Payment\Factory;

use App\Models\PaymentGatewaySetting;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\StripeGateway;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class PaymentGatewayFactory
{
    public function make(string $gateway): PaymentGatewayInterface
    {
        $setting = Cache::remember("payment_gateway_{$gateway}", 3600, function () use ($gateway) {
            return PaymentGatewaySetting::where('gateway', $gateway)
                ->where('is_active', true)
                ->firstOrFail();
        });

        return match($gateway) {
            'stripe' => new StripeGateway(
                secretKey:     Crypt::decryptString($setting->secret_key),
                webhookSecret: Crypt::decryptString($setting->webhook_secret),
            ),
            default => throw new \InvalidArgumentException("Unsupported gateway: [{$gateway}]"),
        };
    }
}