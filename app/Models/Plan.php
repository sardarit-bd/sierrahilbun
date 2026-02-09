<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'slug',
        'description',
        'base_price_yearly',
        'current_price_yearly',
        'is_recommended',
        'target_audience',
    ];

    protected $casts = [
        'base_price_yearly' => 'decimal:2',
        'current_price_yearly' => 'decimal:2',
        'is_recommended' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(PlanDeliverable::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}