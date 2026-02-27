<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = [
        'user_id',
        'author_name',
        'author_title',
        'author_avatar_url',
        'body',
        'rating',
        'status',
        'is_featured',
        'approved_at',
        'source',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
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

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    public function approve(): static
    {
        $this->update([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        return $this;
    }

    public function reject(): static
    {
        $this->update([
            'status'      => 'rejected',
            'approved_at' => null,
        ]);

        return $this;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}