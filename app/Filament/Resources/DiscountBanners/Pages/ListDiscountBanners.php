<?php

namespace App\Filament\Resources\DiscountBanners\Pages;

use App\Filament\Resources\DiscountBanners\DiscountBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscountBanners extends ListRecords
{
    protected static string $resource = DiscountBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
