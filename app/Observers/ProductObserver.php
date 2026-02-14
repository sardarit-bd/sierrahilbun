<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Clear all product-related cache when any product is updated, 
     * deleted, or created.
     */
    public function saved(Product $product): void
    {
        Cache::tags('products')->flush();
    }

    public function deleted(Product $product): void
    {
        Cache::tags('products')->flush();
    }
}