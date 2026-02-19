<?php

namespace App\Filament\Resources\DiscountBanners\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DiscountBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('text')
                    ->required(),
                TextInput::make('promo_code'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
