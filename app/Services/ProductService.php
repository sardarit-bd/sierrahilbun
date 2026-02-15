<?php

namespace App\Services;

use App\Models\ProductReview;
use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

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
            'id'          => $product->id,
            'slug'        => $product->slug,
            'title'       => $product->name,
            'subtitle'    => $product->subtitle,
            'description' => $product->description,
            'benefits'    => $product->benefits ?? [],
            'ingredients' => $product->ingredients,
            'howToUse'    => $product->usage_instructions,
            'rating'      => (float) $product->rating_avg,
            'reviewCount' => (int) $product->reviews_count,
            'category'    => $product->category?->name,

            'images' => $product->images
                ->sortByDesc('is_primary')
                ->map(fn($img) => Storage::url($img->image_url))
                ->toArray(),


            'variants' => $product->variants
                ->map(fn($v) => [
                    'id'            => $v->id,
                    'sku'           => $v->sku,
                    'size'          => $v->size_label,
                    'price'         => (float) $v->price,
                    'originalPrice' => (float) ($v->compare_at_price ?? $v->price),
                    'inStock'       => $v->stock_quantity > 0,
                    'isDefault'     => (bool) $v->is_default,
                ])
                ->toArray(),
        ];
    }

    /**
     * Get approved reviews for the product detail page.
     */
    public function getProductReviews(string $slug): array
    {
        $product = $this->repository->findBySlug($slug);

        return ProductReview::where('product_id', $product->id)
            ->where('is_approved', true)
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn($review) => [
                'id'       => $review->id,
                'author'   => $review->user?->name ?? 'Anonymous',
                'verified' => $review->is_verified_purchase,
                'rating'   => $review->rating,
                'date'     => $review->created_at->format('F j, Y'),
                'title'    => $review->title ?? '',
                'content'  => $review->content ?? '',
                'helpful'  => $review->helpful_count,
                'images'   => $review->images_json ?? [],
            ])
            ->toArray();
    }
}