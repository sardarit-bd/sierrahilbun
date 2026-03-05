<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── LEFT COLUMN ───────────────────────────────────

                // Shipping Address
                Section::make('Shipping Address')
                    ->columns(2)
                    ->columnSpan(1)
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

                // ── RIGHT COLUMN ──────────────────────────────────

                // Order Summary
                Section::make('Order Summary')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order ID')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('status')
                            ->label('Payment Status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'paid'     => 'success',
                                'failed'   => 'danger',
                                'refunded' => 'warning',
                                default    => 'gray',
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
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make('transaction.gateway')
                            ->label('Payment Gateway')
                            ->placeholder('—'),
                    ]),

                // Order Items (full width)
                Section::make('Order Items')
                    ->columnSpan(1)
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                ImageEntry::make('variant.product.images')
                                    ->label('Image')
                                    ->getStateUsing(function ($record) {
                                        $primary = $record->variant?->product?->images
                                            ->firstWhere('is_primary', true)
                                            ?? $record->variant?->product?->images->first();
                                        return $primary?->image_url;
                                    })
                                    ->disk('public')
                                    ->width(64)
                                    ->height(64)
                                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                                    ->placeholder('—')
                                    ->visible(fn ($record) => $record->item_type === 'product'),

                                TextEntry::make('item_type')
                                    ->label('Type')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'product' => 'success',
                                        'plan'    => 'info',
                                        'garden'  => 'warning',
                                        default   => 'gray',
                                    }),

                                TextEntry::make('variant.product.name')
                                    ->label('Product')
                                    ->placeholder('—'),

                                TextEntry::make('variant.size_label')
                                    ->label('Variant')
                                    ->placeholder('—'),

                                TextEntry::make('quantity')
                                    ->label('Qty'),

                                TextEntry::make('price_at_purchase')
                                    ->label('Unit Price')
                                    ->money('USD'),

                                TextEntry::make('line_total')
                                    ->label('Line Total')
                                    ->money('USD')
                                    ->getStateUsing(fn ($record) => $record->quantity * $record->price_at_purchase),
                            ])
                            ->columns(7),
                    ]),

                // Delivery
                Section::make('Delivery')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('delivery_status')
                            ->label('Delivery Status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'pending'    => 'gray',
                                'processing' => 'info',
                                'shipped'    => 'warning',
                                'delivered'  => 'success',
                                default      => 'gray',
                            }),

                        TextEntry::make('tracking_number')
                            ->label('Tracking Number')
                            ->copyable()
                            ->placeholder('Not assigned yet'),
                    ]),

                // Customer
                Section::make('Customer')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Name'),

                        TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable(),
                    ]),
            ]);
    }
}