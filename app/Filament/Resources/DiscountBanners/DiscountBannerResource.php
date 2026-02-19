<?php

namespace App\Filament\Resources\DiscountBanners;

use App\Filament\Resources\DiscountBanners\Pages\CreateDiscountBanner;
use App\Filament\Resources\DiscountBanners\Pages\EditDiscountBanner;
use App\Filament\Resources\DiscountBanners\Pages\ListDiscountBanners;
use App\Filament\Resources\DiscountBanners\Schemas\DiscountBannerForm;
use App\Filament\Resources\DiscountBanners\Tables\DiscountBannersTable;
use App\Models\DiscountBanner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DiscountBannerResource extends Resource
{
    protected static ?string $model = DiscountBanner::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DiscountBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscountBannersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscountBanners::route('/'),
            'create' => CreateDiscountBanner::route('/create'),
            'edit' => EditDiscountBanner::route('/{record}/edit'),
        ];
    }
}
