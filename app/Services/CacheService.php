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

        return Cache::remember($this->versionedKey($key, $tags), $ttl, $callback);
    }

    public function forget(string $key, array $tags = []): void
    {
        if ($this->supportsTags() && !empty($tags)) {
            Cache::tags($tags)->forget($key);
            return;
        }

        Cache::forget($this->versionedKey($key, $tags));
    }

    public function flush(array $tags = []): void
    {
        if ($this->supportsTags() && !empty($tags)) {
            Cache::tags($tags)->flush();
            return;
        }

        foreach ($tags as $tag) {
            $this->incrementVersion($tag);
        }
    }

    private function versionedKey(string $key, array $tags): string
    {
        if (empty($tags)) {
            return $key;
        }

        $versions = array_map(
            fn($tag) => $tag . '_v' . $this->getVersion($tag),
            $tags
        );

        return implode('_', $versions) . '_' . $key;
    }

    private function getVersion(string $tag): int
    {
        return (int) Cache::get($this->versionKey($tag), 1);
    }

    private function incrementVersion(string $tag): void
    {
        $key = $this->versionKey($tag);

        if (Cache::has($key)) {
            Cache::increment($key);
            return;
        }

        Cache::put($key, 2);
    }

    private function versionKey(string $tag): string
    {
        return 'cache_version_' . $tag;
    }

    protected function supportsTags(): bool
    {
        return method_exists(Cache::getStore(), 'tags');
    }
}