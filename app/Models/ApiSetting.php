<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ApiSetting extends Model
{
    protected $table = 'api_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Cache tag for all api_settings entries
    private const CACHE_TAG = 'api_settings';

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeGroup($query, string $group): mixed
    {
        return $query->where('group', $group);
    }

    // -------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------

    /**
     * Get a single setting value by key.
     * Returns null if key does not exist or value is empty.
     */
    public static function get(string $key): ?string
    {
        return app(CacheService::class)->remember(
            key     : "api_setting:{$key}",
            ttl     : now()->addHour(),
            callback: fn () => static::where('key', $key)->value('value'),
            tags    : [self::CACHE_TAG],
        );
    }

    /**
     * Set a value by key (upsert).
     * Clears only the affected key cache.
     */
    public static function set(string $key, ?string $value): void
    {
        static::where('key', $key)->update(['value' => $value]);

        app(CacheService::class)->forget(
            key : "api_setting:{$key}",
            tags: [self::CACHE_TAG],
        );
    }

    /**
     * Get all settings for a group as key => value array.
     */
    public static function getGroup(string $group): array
    {
        return app(CacheService::class)->remember(
            key     : "api_setting_group:{$group}",
            ttl     : now()->addHour(),
            callback: fn () => static::where('group', $group)
                ->pluck('value', 'key')
                ->toArray(),
            tags    : [self::CACHE_TAG],
        );
    }

    /**
     * Flush all api_settings cache entries at once.
     * Call this after bulk updates in Filament.
     */
    public static function clearCache(): void
    {
        app(CacheService::class)->flush(tags: [self::CACHE_TAG]);
    }

    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------

    /**
     * Return masked version for safe display in UI/logs only.
     * Real value is always stored and retrievable via ::get().
     */
    public function getMaskedValueAttribute(): ?string
    {
        if ($this->type === 'secret' && $this->value) {
            $visible = substr($this->value, 0, 6);
            return $visible . str_repeat('*', max(0, strlen($this->value) - 6));
        }

        return $this->value;
    }
}