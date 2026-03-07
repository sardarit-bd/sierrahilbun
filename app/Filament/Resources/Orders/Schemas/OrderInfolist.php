<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
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

                // ── Order Items (full width) ───────────────────────
                // Items are grouped by type:
                //   plan    → grouped under plan name, shows product image + name + qty
                //   garden  → grouped under "Garden Care", shows name + qty + price
                //   product → shown individually with image, name, variant, qty, price
                Section::make('Order Items')
                    ->columnSpanFull()
                    ->schema([

                        // ── Plan items ────────────────────────────
                        // All plan rows share the same item_id (plan_id).
                        // We group them visually under the plan name header.
                        RepeatableEntry::make('items')
                            ->label('Lawn Plan Products')
                            ->visible(fn ($record) => $record->items->where('item_type', 'plan')->isNotEmpty())
                            ->getStateUsing(fn ($record) => $record->items->where('item_type', 'plan')->values())
                            ->schema([
                                // Product image — from display_image (denormalized at order time)
                                ImageEntry::make('display_image')
                                    ->label('Image')
                                    ->disk('public')
                                    ->width(56)
                                    ->height(56)
                                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                                    ->placeholder('—')
                                    ->getStateUsing(function ($record) {
                                        $url = $record->display_image;
                                        if (!$url) return null;
                                        // Strip /storage/ prefix — ImageEntry with disk('public') adds it
                                        return ltrim(str_replace('/storage/', '', $url), '/');
                                    }),

                                TextEntry::make('display_name')
                                    ->label('Product')
                                    ->placeholder('—')
                                    ->weight('bold'),

                                TextEntry::make('quantity')
                                    ->label('Qty'),

                                // Plan name from the Plan model via item_id
                                TextEntry::make('plan_name')
                                    ->label('Plan')
                                    ->placeholder('—')
                                    ->getStateUsing(fn ($record) =>
                                        \App\Models\Plan::find($record->item_id)?->name ?? '—'
                                    )
                                    ->badge()
                                    ->color('info'),
                            ])
                            ->columns(4),

                        // ── Garden items ──────────────────────────
                        RepeatableEntry::make('garden_items')
                            ->label('Garden Care Products')
                            ->visible(fn ($record) => $record->items->where('item_type', 'garden')->isNotEmpty())
                            ->getStateUsing(fn ($record) => $record->items->where('item_type', 'garden')->values())
                            ->schema([
                                TextEntry::make('display_name')
                                    ->label('Product')
                                    ->placeholder('—')
                                    ->weight('bold'),

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
                            ->columns(4),

                        // ── Regular product items ─────────────────
                        RepeatableEntry::make('product_items')
                            ->label('Products')
                            ->visible(fn ($record) => $record->items->where('item_type', 'product')->isNotEmpty())
                            ->getStateUsing(fn ($record) => $record->items->where('item_type', 'product')->values())
                            ->schema([
                                ImageEntry::make('image')
                                    ->label('Image')
                                    ->disk('public')
                                    ->width(56)
                                    ->height(56)
                                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                                    ->placeholder('—')
                                    ->getStateUsing(function ($record) {
                                        $primary = $record->variant?->product?->images
                                            ->firstWhere('is_primary', true)
                                            ?? $record->variant?->product?->images->first();
                                        $url = $primary?->image_url;
                                        if (!$url) return null;
                                        return ltrim(str_replace('/storage/', '', $url), '/');
                                    }),

                                TextEntry::make('variant.product.name')
                                    ->label('Product')
                                    ->placeholder('—')
                                    ->weight('bold'),

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
                            ->columns(6),
                    ]),

                // ── Delivery + Customer stacked in right column ───
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Delivery')
                            ->columns(2)
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

                        Section::make('Customer')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Name'),

                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->copyable(),
                            ]),
                    ]),
            ]);
    }
}