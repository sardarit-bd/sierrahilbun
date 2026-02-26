<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheService;

class ProductObserver
{
    public function __construct(
        protected CacheService $cache
    ) {}

    public function saved(Product $product): void
    {
        $this->cache->flush(['products']);
    }

    public function deleted(Product $product): void
    {
        $this->cache->flush(['products']);
    }
}