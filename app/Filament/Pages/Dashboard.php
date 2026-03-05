<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductSalesChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    protected static ?int $navigationSort = -2;

    /**
     * Explicitly declare widget order and layout.
     * Remove this method to fall back to auto-discovery.
     */
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            ProductSalesChartWidget::class,
        ];
    }

    /**
     * Number of columns in the widget grid.
     * StatsOverviewWidget uses default column span.
     * ProductSalesChartWidget uses 'full' span (set on the widget itself).
     */
    public function getColumns(): int
    {
        return 2;
    }
}