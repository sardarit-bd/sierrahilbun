<?php

namespace App\Filament\Resources\ProductReviews\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Product Info with Thumbnail
                TextColumn::make('product.name')
                    ->label('Product')
                    ->description(fn ($record) => $record->product->subtitle ?? '')
                    ->searchable()
                    ->sortable(),

                // User with Avatar
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->description(fn ($record) => $record->user->email)
                    ->searchable(),

                // Visual Star Rating
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->icon('heroicon-m-star')
                    ->alignCenter()
                    ->sortable(),

                // Review Content Preview
                TextColumn::make('title')
                    ->label('Review')
                    ->description(fn ($record) => str($record->content)->limit(40))
                    ->wrap()
                    ->searchable(),

                // Status Toggle (The "Premium" instant action)
                ToggleColumn::make('is_approved')
                    ->label('Approved')
                    ->onColor('success')
                    ->offColor('danger'),

                // Verified Purchase Badge
                IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('info')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Approval Status')
                    ->placeholder('All Reviews')
                    ->trueLabel('Approved Only')
                    ->falseLabel('Pending Only'),

                SelectFilter::make('rating')
                    ->options([
                        '5' => '5 Stars',
                        '4' => '4 Stars',
                        '3' => '3 Stars',
                        '2' => '2 Stars',
                        '1' => '1 Star',
                    ]),
            ])
            ->actions([
                // High-visibility Approval Actions
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->hidden(fn ($record) => $record->is_approved)
                    ->action(fn ($record) => $record->update(['is_approved' => true]))
                    ->requiresConfirmation(),

                ActionGroup::make([
                    ViewAction::make(),
                    DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_approved' => true])),
                ]),
            ])
            ->emptyStateHeading('No reviews yet')
            ->emptyStateDescription('Customer reviews will appear here once submitted.')
            ->emptyStateIcon('heroicon-o-chat-bubble-bottom-center-text')
            ->defaultSort('created_at', 'desc');
    }
}