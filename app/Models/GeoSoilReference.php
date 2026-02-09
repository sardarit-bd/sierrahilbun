<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoSoilReference extends Model
{
    protected $primaryKey = 'zip_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'zip_code',
        'state_code',
        'city',
        'hardiness_zone',
        'dominant_soil_texture',
        'ph_mean',
        'monthly_temp_data',
        'monthly_rainfall_data',
        'growth_potential_data',
    ];

    protected $casts = [
        'ph_mean' => 'decimal:1',
        'monthly_temp_data' => 'array',
        'monthly_rainfall_data' => 'array',
        'growth_potential_data' => 'array',
        'created_at' => 'datetime',
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'zip_code', 'zip_code');
    }
}