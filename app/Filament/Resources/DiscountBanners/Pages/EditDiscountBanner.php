<?php

namespace App\Filament\Resources\DiscountBanners\Pages;

use App\Filament\Resources\DiscountBanners\DiscountBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscountBanner extends EditRecord
{
    protected static string $resource = DiscountBannerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
