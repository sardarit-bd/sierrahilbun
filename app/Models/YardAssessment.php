<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class YardAssessment extends Model
{
    use HasUuids;

    protected $table = 'yard_assessments';

    protected $fillable = [
        'user_id',
        'quiz_floor_tier',
        'packaging_by_tier',
        'recommended_plan_ids',
        'session_id',
        'zip_code',
        'selected_services',
        'square_feet',
        'sqft_source',
        'address_input',
        'soil_snapshot',
        'quiz_answers',
        'resolved_tier',
        'generated_products',
        'total_base_price',
        'total_addons_price',
        'total_price',
        'current_step',
        'status',
        'completed_at',
        // Garden
        'garden_types',
        'garden_size',
        'garden_products',
    ];

    protected $casts = [
        'selected_services'    => 'array',
        'soil_snapshot'        => 'array',
        'quiz_answers'         => 'array',
        'generated_products'   => 'array',
        'packaging_by_tier'    => 'array',
        'recommended_plan_ids' => 'array',
        'total_base_price'     => 'decimal:2',
        'total_addons_price'   => 'decimal:2',
        'total_price'          => 'decimal:2',
        'current_step'         => 'integer',
        'completed_at'         => 'datetime',
        // Garden
        'garden_types'         => 'array',
        'garden_products'      => 'array',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function soilReference(): BelongsTo
    {
        return $this->belongsTo(GeoSoilReference::class, 'zip_code', 'zip_code');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeAtStep($query, int $step)
    {
        return $query->where('current_step', $step);
    }

    // -------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function advanceStep(int $step): void
    {
        $this->update(['current_step' => $step]);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status'       => 'completed',
            'current_step' => 6,
            'completed_at' => now(),
        ]);
    }

    public function markAbandoned(): void
    {
        $this->update(['status' => 'abandoned']);
    }

    public function hasLawnService(): bool
    {
        return in_array('lawn', $this->selected_services ?? []);
    }

    public function hasGardenService(): bool
    {
        return in_array('garden', $this->selected_services ?? []);
    }

    public function hasGardenProducts(): bool
    {
        return !empty($this->garden_products);
    }
}