<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Pest\Support\View;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->description(fn ($record) => $record->user?->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->formatStateUsing(fn ($state) => $state . ' ' . str('item')->plural($state))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid'     => 'success',
                        'failed'   => 'danger',
                        'refunded' => 'warning',
                        default    => 'gray',
                    }),

                TextColumn::make('delivery_status')
                    ->label('Delivery')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending'    => 'gray',
                        'processing' => 'info',
                        'shipped'    => 'warning',
                        'delivered'  => 'success',
                        default      => 'gray',
                    }),

                TextColumn::make('tracking_number')
                    ->label('Tracking')
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('transaction.transaction_id')
                    ->label('Transaction')
                    ->description(fn ($record) => $record->transaction?->gateway)
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('delivery_status')
                    ->label('Delivery Status')
                    ->options([
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'shipped'    => 'Shipped',
                        'delivered'  => 'Delivered',
                    ]),

                Filter::make('created_at')
                    ->label('Date Range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}