<?php

namespace App\Filament\Resources\PaymentGatewaySettings\Pages;

use App\Filament\Resources\PaymentGatewaySettings\PaymentGatewaySettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentGatewaySetting extends CreateRecord
{
    protected static string $resource = PaymentGatewaySettingResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
