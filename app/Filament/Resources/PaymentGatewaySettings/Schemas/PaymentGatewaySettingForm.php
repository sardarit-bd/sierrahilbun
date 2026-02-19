<?php

namespace App\Filament\Resources\PaymentGatewaySettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentGatewaySettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('gateway')
                    ->required(),
                Textarea::make('secret_key')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('public_key')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('webhook_secret')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
