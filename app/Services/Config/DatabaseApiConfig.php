<?php

namespace App\Services\Config;

use App\Models\ApiSetting;
use App\Services\Contracts\ApiConfigInterface;
use RuntimeException;

class DatabaseApiConfig implements ApiConfigInterface
{
    /**
     * Get a single API config value by key.
     * Cache is handled inside ApiSetting::get() via CacheService.
     */
    public function get(string $key): ?string
    {
        $value = ApiSetting::get($key);

        return ($value !== null && $value !== '') ? $value : null;
    }

    /**
     * Get all config values for a given group as key => value array.
     */
    public function getGroup(string $group): array
    {
        return ApiSetting::getGroup($group);
    }

    /**
     * Check if a key exists and has a non-empty value.
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get a value or throw a RuntimeException if missing or empty.
     *
     * @throws RuntimeException
     */
    public function getOrFail(string $key): string
    {
        $value = $this->get($key);

        if ($value === null) {
            throw new RuntimeException(
                "API configuration key [{$key}] is not set. Please configure it in the admin panel."
            );
        }

        return $value;
    }
}