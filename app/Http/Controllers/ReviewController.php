<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * Show the review creation form for a product.
     */
    public function create(string $slug): Response
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['images' => fn($q) => $q->where('is_primary', true)->select('id', 'product_id', 'image_url')])
            ->firstOrFail();

        return Inertia::render('review/post', [
            'product' => [
                'id'       => $product->id,
                'name'     => $product->name,
                'slug'     => $product->slug,
                'subtitle' => $product->subtitle,
                'image'    => $product->images->first()
                    ? Storage::url($product->images->first()->image_url)
                    : '/images/placeholder.png',
            ],
        ]);
    }

    /**
     * Store a new review.
     */
    public function store(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'rating'    => ['required', 'integer', 'min:1', 'max:5'],
            'title'     => ['nullable', 'string', 'max:255'],
            'content'   => ['nullable', 'string', 'max:5000'],
            'recommend' => ['nullable', 'boolean'],
            'name'      => ['required', 'string', 'max:100'],
            'images'    => ['nullable', 'array', 'max:4'],
            'images.*'  => ['image', 'max:2048'],
        ]);

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $uploadedImages[] = Storage::url($path);
            }
        }

        $isVerified = Auth::check() && $product->isPurchasedBy(Auth::id());

        ProductReview::create([
            'product_id'            => $product->id,
            'user_id'               => Auth::id(),
            'rating'                => $validated['rating'],
            'title'                 => $validated['title'] ?? null,
            'content'               => $validated['content'] ?? null,
            'images_json'           => !empty($uploadedImages) ? $uploadedImages : null,
            'is_verified_purchase'  => $isVerified,
            'is_approved'           => false,
        ]);

        $avg = ProductReview::where('product_id', $product->id)
            ->where('is_approved', true)
            ->avg('rating');

        $count = ProductReview::where('product_id', $product->id)
            ->where('is_approved', true)
            ->count();

        $product->update([
            'rating_avg'     => round($avg ?? 0, 2),
            'reviews_count'  => $count,
        ]);

        return redirect()
            ->route('products.show', $product->slug)
            ->with('success', 'Thank you! Your review has been submitted for approval.');
    }
}