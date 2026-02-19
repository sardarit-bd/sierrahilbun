<?php

namespace App\Filament\Resources\DiscountBanners\Pages;

use App\Filament\Resources\DiscountBanners\DiscountBannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscountBanner extends CreateRecord
{
    protected static string $resource = DiscountBannerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
