<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function remember(
        string $key,
        $ttl,
        callable $callback,
        array $tags = []
    ) {
        if ($this->supportsTags() && !empty($tags)) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    public function forget(string $key, array $tags = []): void
    {
        if ($this->supportsTags() && !empty($tags)) {
            Cache::tags($tags)->forget($key);
            return;
        }

        Cache::forget($key);
    }

    public function flush(array $tags = []): void
    {
        if ($this->supportsTags() && !empty($tags)) {
            Cache::tags($tags)->flush();
            return;
        }

        Cache::flush();
    }

    protected function supportsTags(): bool
    {
        return method_exists(Cache::getStore(), 'tags');
    }
}
