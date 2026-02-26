<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductSearchService
{
    public function __construct(
        protected CacheService $cache
    ) {}

    public function search(string $query, int $limit = 8): array
    {
        $cacheKey = 'search_' . md5(strtolower(trim($query)));

        return $this->cache->remember(
            $cacheKey,
            300,
            function () use ($query, $limit) {
                return Product::query()
                    ->select([
                        'id', 'name', 'slug', 'subtitle',
                        'category_id', 'base_price',
                        'rating_avg', 'reviews_count',
                    ])
                    ->withMin('variants as price_min', 'price')
                    ->where('is_active', true)
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('subtitle', 'like', "%{$query}%")
                          ->orWhere('description', 'like', "%{$query}%");
                    })
                    ->with([
                        'images' => fn($q) => $q
                            ->where('is_primary', true)
                            ->select('id', 'product_id', 'image_url'),
                    ])
                    ->orderByDesc('rating_avg')
                    ->limit($limit)
                    ->get()
                    ->map(fn($product) => [
                        'id'          => $product->id,
                        'name'        => $product->name,
                        'slug'        => $product->slug,
                        'description' => $product->subtitle,
                        'price'       => (float) ($product->price_min ?: $product->base_price),
                        'rating'      => (float) $product->rating_avg,
                        'reviews'     => (int) $product->reviews_count,
                        'image'       => $product->images->first()
                            ? Storage::url($product->images->first()->image_url)
                            : '/images/placeholder.png',
                        'href'        => "/products/{$product->slug}",
                    ])
                    ->toArray();
            },
            ['products']
        );
    }
}