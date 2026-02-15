<?php

namespace App\Filament\Resources\ProductReviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('rating')
                    ->required()
                    ->numeric(),
                TextInput::make('title'),
                Textarea::make('content')
                    ->columnSpanFull(),
                TextInput::make('images_json'),
                TextInput::make('helpful_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_verified_purchase')
                    ->required(),
                Toggle::make('is_approved')
                    ->required(),
            ]);
    }
}
