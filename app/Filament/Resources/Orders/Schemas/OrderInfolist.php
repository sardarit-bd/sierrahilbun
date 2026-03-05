<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Order Summary ─────────────────────────────────
                Section::make('Order Summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order ID')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'paid'       => 'success',
                                'processing' => 'info',
                                'shipped'    => 'warning',
                                default      => 'gray',
                            }),

                        TextEntry::make('total_amount')
                            ->label('Total')
                            ->money('USD'),

                        TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime('M j, Y H:i'),

                        TextEntry::make('transaction.transaction_id')
                            ->label('Transaction ID')
                            ->copyable()
                            ->placeholder('—'),

                        TextEntry::make('transaction.gateway')
                            ->label('Payment Gateway')
                            ->placeholder('—'),
                    ]),

                // ── Customer ──────────────────────────────────────
                Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Name'),

                        TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable(),
                    ]),

                // ── Shipping Address ──────────────────────────────
                Section::make('Shipping Address')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('shipping_address_json.first_name')
                            ->label('First Name')
                            ->placeholder('—'),

                        TextEntry::make('shipping_address_json.last_name')
                            ->label('Last Name')
                            ->placeholder('—'),

                        TextEntry::make('shipping_address_json.phone')
                            ->label('Phone')
                            ->placeholder('—'),

                        TextEntry::make('shipping_address_json.address_line1')
                            ->label('Address Line 1')
                            ->placeholder('—'),

                        TextEntry::make('shipping_address_json.address_line2')
                            ->label('Address Line 2')
                            ->placeholder('—'),

                        TextEntry::make('shipping_address_json.city')
                            ->label('City')
                            ->placeholder('—'),

                        TextEntry::make('shipping_address_json.state')
                            ->label('State')
                            ->placeholder('—'),

                        TextEntry::make('shipping_address_json.zip_code')
                            ->label('ZIP Code')
                            ->placeholder('—'),
                    ]),

                // ── Order Items ───────────────────────────────────
                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('item_type')
                                    ->label('Type')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'product' => 'success',
                                        'plan'    => 'info',
                                        'garden'  => 'warning',
                                        default   => 'gray',
                                    }),

                                TextEntry::make('productVariant.product.name')
                                    ->label('Product')
                                    ->placeholder('—'),

                                TextEntry::make('quantity')
                                    ->label('Qty'),

                                TextEntry::make('price_at_purchase')
                                    ->label('Unit Price')
                                    ->money('USD'),
                            ]),
                    ]),
            ]);
    }
}