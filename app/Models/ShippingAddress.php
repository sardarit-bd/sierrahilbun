<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'first_name',
        'last_name',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    public function makeDefault(): static
    {
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);

        return $this;
    }

    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFormattedAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            "{$this->city}, {$this->state} {$this->zip_code}",
        ]);

        return implode(', ', $parts);
    }
}