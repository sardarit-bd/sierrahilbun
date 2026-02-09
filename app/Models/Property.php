<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'user_id',
        'onboarding_session_id',
        'zip_code',
        'address_line_1',
        'city',
        'state',
        'measured_sqft',
        'sqft_source',
        'lot_polygon_coordinates',
        'satellite_snapshot_url',
    ];

    protected $casts = [
        'measured_sqft' => 'integer',
        'lot_polygon_coordinates' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function onboardingSession(): BelongsTo
    {
        return $this->belongsTo(OnboardingSession::class, 'onboarding_session_id');
    }

    public function geoSoilReference(): BelongsTo
    {
        return $this->belongsTo(GeoSoilReference::class, 'zip_code', 'zip_code');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}