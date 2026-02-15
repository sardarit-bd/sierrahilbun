<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{
    protected int $cacheTTL = 86400;

    public function getShopProducts(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $cacheKey = "shop_index_cat_" . ($filters['category'] ?? 'all') . "_sort_" . ($filters['sort'] ?? 'newest') . "_page_" . $page;

        $cache = Cache::supportsTags() ? Cache::tags(['products']) : Cache::store();

        return $cache->remember($cacheKey, $this->cacheTTL, function () use ($filters, $perPage) {
            return Product::query()
                ->select(['id', 'name', 'slug', 'subtitle', 'category_id', 'base_price', 'rating_avg', 'reviews_count'])
                ->withMin('variants as price_min', 'price')
                ->withMax('variants as price_max', 'price')
                ->where('is_active', true)
                ->with([
                    'category:id,name,slug',
                    'images' => fn($q) => $q->where('is_primary', true)->select('id', 'product_id', 'image_url')
                ])
                ->when($filters['category'] ?? null, fn($q, $slug) => $q->whereHas('category', fn($c) => $c->where('slug', $slug)))
                ->when($filters['sort'] ?? 'newest', function ($query, $sort) {
                    match ($sort) {
                        'price_low' => $query->orderBy('price_min', 'asc'),
                        'price_high' => $query->orderBy('price_max', 'desc'),
                        'top_rated' => $query->orderBy('rating_avg', 'desc'),
                        default => $query->latest(),
                    };
                })
                ->paginate($perPage);
        });
    }

    public function findBySlugForDetail(string $slug): Product
    {
        return Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category:id,name,slug',
                
                'images' => fn($q) => $q
                    ->select('id', 'product_id', 'image_url', 'is_primary')
                    ->orderByDesc('is_primary')
                    ->orderBy('id'),

                'variants' => fn($q) => $q
                    ->select(['id', 'product_id', 'sku', 'size_label', 'price', 'compare_at_price', 'stock_quantity', 'is_default'])
                    ->orderBy('is_default', 'desc')
                    ->orderBy('price', 'asc'),
            ])
            ->withAvg('reviews as rating_avg', 'rating')
            ->withCount('reviews as reviews_count')
            ->firstOrFail();
    }
}