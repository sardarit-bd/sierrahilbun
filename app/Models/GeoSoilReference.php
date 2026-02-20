<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoSoilReference extends Model
{
    protected $table = 'geo_soil_references';
    protected $primaryKey = 'zip_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'zip_code',
        'type',
        'decommissioned',
        'primary_city',
        'acceptable_cities',
        'unacceptable_cities',
        'state',
        'county',
        'timezone',
        'area_codes',
        'world_region',
        'country',
        'latitude',
        'longitude',
        'climate_zone',
        'avg_annual_rain_in',
        'avg_july_high_F',
        'avg_jan_low_F',
        'frost_free_days',
        'avg_ph',
        'soil_ph_max',
        'avg_ph_class',
        'soil_ph_class',
        'cec_meq_per_100g',
        'cec_class',
        'organic_matter_class',
        'calcium_saturation_class',
        'soil_texture_class',
        'drainage_class',
        'compaction_risk',
        'n_leaching_risk',
        'disease_pressure',
        'drought_stress_risk',
        'organic_matter_pct',
        'monthly_temp_data',
        'monthly_rainfall_data',
        'growth_potential_data',
    ];

    protected $casts = [
        'decommissioned'       => 'boolean',
        'latitude'             => 'decimal:6',
        'longitude'            => 'decimal:6',
        'avg_annual_rain_in'   => 'decimal:2',
        'avg_ph'               => 'decimal:2',
        'soil_ph_max'          => 'decimal:2',
        'cec_meq_per_100g'     => 'decimal:1',
        'organic_matter_pct'   => 'decimal:2',
        'monthly_temp_data'    => 'array',
        'monthly_rainfall_data'=> 'array',
        'growth_potential_data'=> 'array',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    public function assessments(): HasMany
    {
        return $this->hasMany(YardAssessment::class, 'zip_code', 'zip_code');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('decommissioned', false);
    }

    public function scopeInState($query, string $state)
    {
        return $query->where('state', strtoupper($state));
    }
}