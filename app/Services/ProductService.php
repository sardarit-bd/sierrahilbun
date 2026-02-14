<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        protected ProductRepository $repository
    ) {}

    /**
     * Get formatted data for the Shop Index page.
     */
    public function getShopList(array $filters): LengthAwarePaginator
    {
        $products = $this->repository->getShopProducts($filters);

        // Transform the collection securely for Inertia
        $products->through(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'subtitle' => $product->subtitle,
            'category' => $product->category?->name,
            'image' => $product->images->first() 
                ? Storage::url($product->images->first()->image_url) 
                : '/images/placeholder.png',
            'price' => (float) $product->base_price,
            'min_price' => (float) ($product->price_min ?: $product->base_price),
            'max_price' => (float) ($product->price_max ?: $product->base_price),
            'rating' => (float) $product->rating_avg,
            'reviews_count' => $product->reviews_count,
        ]);

        return $products;
    }

    /**
     * Get formatted data for the Single Product Detail page.
     */
    public function getProductDetails(string $slug): array
    {
        $product = $this->repository->findBySlugForDetail($slug);

        return [
            'id' => $product->id,
            'title' => $product->name, // React expects 'title'
            'subtitle' => $product->subtitle,
            'description' => $product->description,
            'benefits' => $product->benefits ?? [],
            'ingredients' => $product->ingredients,
            'howToUse' => $product->usage_instructions,
            'rating' => (float) $product->rating_avg,
            'reviewCount' => $product->reviews_count,
            'category' => $product->category?->name,
            
            // Format Images
            'images' => $product->images->map(fn($img) => Storage::url($img->image_url))->toArray(),
            
            // Format Variants (Crucial for the selector)
            'variants' => $product->variants->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'size' => $v->size_label,
                'price' => (float) $v->price,
                'originalPrice' => (float) ($v->compare_at_price ?? $v->price),
                'inStock' => $v->stock_quantity > 0,
                'isDefault' => $v->is_default,
            ]),
        ];
    }
}