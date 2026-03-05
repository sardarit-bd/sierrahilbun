<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    // ── What to show on the view page ─────────────
    public function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    // ── Buttons in the top-right of the page ──────
    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateDelivery')
                ->label('Update Delivery')
                ->icon('heroicon-o-truck')

                ->form([
                    Select::make('delivery_status')
                        ->label('Delivery Status')
                        ->options([
                            'pending'    => 'Pending',
                            'processing' => 'Processing',
                            'shipped'    => 'Shipped',
                            'delivered'  => 'Delivered',
                        ])
                        ->default(fn () => $this->record->delivery_status)
                        ->required(),

                    TextInput::make('tracking_number')
                        ->label('Tracking Number')
                        ->default(fn () => $this->record->tracking_number)
                        ->placeholder('e.g. 1Z999AA10123456784')
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'delivery_status' => $data['delivery_status'],
                        'tracking_number' => $data['tracking_number'] ?? null,
                    ]);
                    $this->refreshFormData(['delivery_status', 'tracking_number']);

                    Notification::make()
                        ->title('Delivery status updated')
                        ->success()
                        ->send();
                }),
        ];
    }

    // ── Eager load relationships for the view ────────────
    protected function resolveRecord(int | string $key): \App\Models\Order
    {
        return \App\Models\Order::with([
            'user',
            'transaction',
            'items.variant.product.images',
        ])->findOrFail($key);
    }
}