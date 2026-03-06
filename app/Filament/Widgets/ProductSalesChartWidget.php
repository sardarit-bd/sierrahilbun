<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatsService;
use Filament\Widgets\ChartWidget;

class ProductSalesChartWidget extends ChartWidget
{
    protected static ?string $title = 'Product Sales — Monthly Comparison';
    protected ?string $description = 'Revenue comparison between months and year.';
    protected static ?int $sort = 2;

    public string | int | array $columnSpan = 'full';

    protected ?string $pollingInterval = '360s';

    protected ?string $maxHeight = '300px';

    /**
     * Allow the user to toggle between chart types from the UI.
     */
    public string|null $filter = 'line';

    protected function getType(): string
    {
        // Respect the user-selected filter
        return $this->filter;
    }

    protected function getData(): array
    {
        $service = app(DashboardStatsService::class);
        $sales   = $service->monthlySalesCurrentYear();

        return [
            'labels'   => $sales['labels'],
            'datasets' => [
                [
                    'label'           => 'Products Sold — ' . now()->year,
                    'data'            => $sales['data'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor'     => 'rgba(59, 130, 246, 1)',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
        ];
    }

    /**
     * Chart.js options passed directly to the canvas.
     */
    protected function getOptions(): array
    {
        return [
            'responsive'          => true,
            'maintainAspectRatio' => false,
            'plugins'             => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode'      => 'index',
                    'intersect' => false,
                    'callbacks' => [
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text'    => 'Products Sold',
                        'color'   => '#6b7280',
                        'font'    => [
                            'size'   => 12,
                            'weight' => '500',
                        ],
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode'      => 'index',
            ],
        ];
    }
}