<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        /** @var DashboardStatsService $service */
        $service = app(DashboardStatsService::class);

        $newUsersThisMonth = $service->newUsersThisMonth();
        $activeProducts    = $service->activeProducts();
        $publishedBlogs    = $service->publishedBlogs();
        $totalRevenue      = $service->totalRevenue();

        return [
            // ── Total Users ──────────────────────────────────────────────
            Stat::make('Total Users', number_format($service->totalUsers()))
                ->description($newUsersThisMonth . ' new this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-users'),

            // ── Total Products ────────────────────────────────────────────
            Stat::make('Total Products', number_format($service->totalProducts()))
                ->description($activeProducts . ' active products')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->icon('heroicon-o-shopping-bag'),

            // ── Total Blogs ───────────────────────────────────────────────
            Stat::make('Total Blogs', number_format($service->totalBlogs()))
                ->description($publishedBlogs . ' published')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->icon('heroicon-o-newspaper'),

            // ── Total Revenue ─────────────────────────────────────────────
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('Completed orders (all time)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}