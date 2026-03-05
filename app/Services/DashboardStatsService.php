<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    protected CacheService $cache;

    // Cache TTL constants
    const TTL_SHORT  = 300;   // 5 minutes  — for counts that change often
    const TTL_LONG   = 3600;  // 1 hour     — for monthly aggregates

    const TAGS = ['dashboard'];

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    public function totalUsers(): int
    {
        return $this->cache->remember(
            'dashboard_total_users',
            self::TTL_SHORT,
            fn () => User::count(),
            self::TAGS
        );
    }

    public function newUsersThisMonth(): int
    {
        return $this->cache->remember(
            'dashboard_new_users_' . now()->format('Y_m'),
            self::TTL_SHORT,
            fn () => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            self::TAGS
        );
    }

    public function totalProducts(): int
    {
        return $this->cache->remember(
            'dashboard_total_products',
            self::TTL_SHORT,
            fn () => Product::count(),
            self::TAGS
        );
    }

    public function activeProducts(): int
    {
        return $this->cache->remember(
            'dashboard_active_products',
            self::TTL_SHORT,
            fn () => Product::where('is_active', true)->count(),
            self::TAGS
        );
    }

    public function totalBlogs(): int
    {
        return $this->cache->remember(
            'dashboard_total_blogs',
            self::TTL_SHORT,
            fn () => BlogPost::count(),
            self::TAGS
        );
    }

    public function publishedBlogs(): int
    {
        return $this->cache->remember(
            'dashboard_published_blogs',
            self::TTL_SHORT,
            fn () => BlogPost::where('status', 'published')->count(),
            self::TAGS
        );
    }

    public function totalRevenue(): float
    {
        return $this->cache->remember(
            'dashboard_total_revenue',
            self::TTL_SHORT,
            fn () => (float) Order::where('status', 'paid')->sum('total_amount'),
            self::TAGS
        );
    }

    public function monthlySalesCurrentYear(): array
    {
        return $this->cache->remember(
            'dashboard_monthly_sales_' . now()->format('Y'),
            self::TTL_LONG,
            function () {
                $year = now()->year;

                $rows = DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereYear('orders.created_at', $year)
                    ->whereIn('orders.status', ['paid', 'processing', 'shipped'])
                    ->select(
                        DB::raw('MONTH(orders.created_at) as month'),
                        DB::raw('SUM(order_items.quantity) as total')
                    )
                    ->groupBy(DB::raw('MONTH(orders.created_at)'))
                    ->orderBy('month')
                    ->pluck('total', 'month')
                    ->toArray();

                $labels = [];
                $data   = [];

                for ($m = 1; $m <= 12; $m++) {
                    $labels[] = now()->setMonth($m)->format('M');
                    $data[]   = (int) ($rows[$m] ?? 0);
                }

                return [
                    'labels' => $labels,
                    'data'   => $data,
                ];
            },
            self::TAGS
        );
    }

    /**
     * Call this whenever an order is created/updated
     * to bust all dashboard cache entries.
     */
    public function clearCache(): void
    {
        $this->cache->flush(self::TAGS);
    }
}