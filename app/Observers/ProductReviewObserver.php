<?php

namespace App\Observers;

use App\Models\ProductReview;
use Illuminate\Support\Facades\DB;

class ProductReviewObserver
{
    /**
     * Handle the ProductReview "created" event.
     */
    public function created(ProductReview $review): void
    {
        $this->updateProductRating($review);
    }

    /**
     * Handle the ProductReview "updated" event.
     */
    public function updated(ProductReview $review): void
    {
        if ($review->isDirty(['rating', 'is_approved'])) {
            $this->updateProductRating($review);
        }
    }

    /**
     * Handle the ProductReview "deleted" event.
     */
    public function deleted(ProductReview $review): void
    {
        $this->updateProductRating($review);
    }

    /**
     * Handle the ProductReview "restored" event.
     */
    public function restored(ProductReview $review): void
    {
        $this->updateProductRating($review);
    }

    /**
     * Calculate the new average and count, then update the Product.
     * * @param ProductReview $review
     */
    private function updateProductRating(ProductReview $review): void
    {
        $productId = $review->product_id;

        $stats = DB::table('product_reviews')
            ->where('product_id', $productId)
            ->where('is_approved', true) 
            ->selectRaw('count(*) as count, avg(rating) as avg')
            ->first();

        DB::table('products')
            ->where('id', $productId)
            ->update([
                'reviews_count' => $stats->count ?? 0,
                'rating_avg'    => $stats->avg ?? 0,
                'updated_at'    => now(),
            ]);
    }
}