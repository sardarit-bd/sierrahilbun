<?php

namespace App\Services\Contracts;

interface ApiConfigInterface
{
    /**
     * Get a single API config value by key.
     * Returns null if not found or not set.
     */
    public function get(string $key): ?string;

    /**
     * Get all config values for a given group.
     * Returns key => value array.
     */
    public function getGroup(string $group): array;

    /**
     * Check if a key exists and has a non-empty value.
     */
    public function has(string $key): bool;

    /**
     * Get a value or throw if missing.
     * Useful for required keys like API tokens.
     *
     * @throws \RuntimeException
     */
    public function getOrFail(string $key): string;
}