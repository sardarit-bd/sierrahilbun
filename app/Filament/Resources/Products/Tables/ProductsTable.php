<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('Image')
                    ->disk('public') 
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->getStateUsing(function (Product $record) {
                        $image = $record->images->firstWhere('is_primary', true) 
                            ?? $record->images->first();

                        return $image?->image_url; 
                    }),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $record) => $record->subtitle ?? $record->sku)
                    ->wrap(),


                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('base_price')
                    ->label('Price')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(function (Product $record) {
                        if ($record->price_min != $record->price_max && $record->price_max > 0) {
                            return '$' . number_format($record->price_min, 2) . ' - $' . number_format($record->price_max, 2);
                        }
                        return '$' . number_format($record->base_price, 2);
                    }),


                TextColumn::make('rating_avg')
                    ->label('Rating')
                    ->sortable()
                    ->badge()
                    ->icon('heroicon-m-star')
                    ->color(fn (string $state): string => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.0 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (string $state) => number_format($state, 1)),

 
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

 
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('reviews_count')
                    ->label('Reviews')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('coverage_sqft')
                    ->label('Coverage')
                    ->numeric()
                    ->suffix(' sq ft')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->multiple(),
                
                TernaryFilter::make('is_active')
                    ->label('Status'),

                SelectFilter::make('stock_status')
                    ->label('Stock Status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'low_stock' => 'Low Stock (< 10)',
                        'out_of_stock' => 'Out of Stock',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'out_of_stock') {
                            return $query->whereHas('variants', fn ($q) => $q->where('stock_quantity', 0));
                        }
                        return $query;
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}