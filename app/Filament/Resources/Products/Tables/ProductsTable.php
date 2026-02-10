<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images.image_url')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->size(50)
                    ->getStateUsing(function ($record) {
                        $imageUrl = $record->images()->where('is_primary', true)->first()?->image_url 
                            ?? $record->images()->first()?->image_url;
                        
                        return $imageUrl ? Storage::url($imageUrl) : null;
                    }),
                
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('SKU copied')
                    ->weight('bold')
                    ->description(fn ($record) => $record->name),
                
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->default('Uncategorized'),
                
                TextColumn::make('discount_price')
                    ->label('Discount Price')
                    ->money('USD')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('base_price')
                    ->label('Base Price')
                    ->money('USD')
                    ->sortable()
                    ->alignEnd(),
                
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->default(0),
                
                TextColumn::make('coverage_sqft')
                    ->label('Coverage')
                    ->numeric()
                    ->sortable()
                    ->suffix(' sq ft')
                    ->alignEnd()
                    ->toggleable()
                    ->placeholder('—'),
                
                TextColumn::make('application_rate_oz_per_1k')
                    ->label('App Rate')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->suffix(' oz/1k')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All products')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                
                SelectFilter::make('has_variants')
                    ->label('Variants')
                    ->options([
                        'yes' => 'With Variants',
                        'no' => 'Without Variants',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'yes') {
                            return $query->has('variants');
                        } elseif ($data['value'] === 'no') {
                            return $query->doesntHave('variants');
                        }
                        return $query;
                    }),
                
                SelectFilter::make('price_range')
                    ->label('Price Range')
                    ->options([
                        '0-25' => 'Under $25',
                        '25-50' => '$25 - $50',
                        '50-100' => '$50 - $100',
                        '100+' => 'Over $100',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            '0-25' => $query->where('base_price', '<', 25),
                            '25-50' => $query->whereBetween('base_price', [25, 50]),
                            '50-100' => $query->whereBetween('base_price', [50, 100]),
                            '100+' => $query->where('base_price', '>', 100),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Create your first product to get started.')
            ->emptyStateIcon('heroicon-o-cube');
    }
}