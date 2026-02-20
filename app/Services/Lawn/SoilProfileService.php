<?php

namespace App\Services\Lawn;

use App\Models\GeoSoilReference;

class SoilProfileService
{
    public function getByZip(string $zipCode): ?array
    {
        $soil = GeoSoilReference::active()
            ->where('zip_code', $zipCode)
            ->first();

        if (!$soil) {
            return null;
        }

        return $this->format($soil);
    }

    public function zipExists(string $zipCode): bool
    {
        return GeoSoilReference::active()
            ->where('zip_code', $zipCode)
            ->exists();
    }

    private function format(GeoSoilReference $soil): array
    {
        return [
            'location'      => $this->formatLocation($soil),
            'climate'       => $this->formatClimate($soil),
            'soil'          => $this->formatSoil($soil),
            'risks'         => $this->formatRisks($soil),
            'charts'        => $this->formatCharts($soil),
            'predictions'   => $this->formatPredictions($soil),
        ];
    }

    private function formatLocation(GeoSoilReference $soil): array
    {
        return [
            'zip_code'     => $soil->zip_code,
            'city'         => $soil->primary_city,
            'state'        => $soil->state,
            'county'       => $soil->county,
            'latitude'     => $soil->latitude,
            'longitude'    => $soil->longitude,
        ];
    }

    private function formatClimate(GeoSoilReference $soil): array
    {
        return [
            'climate_zone'       => $soil->climate_zone,
            'avg_annual_rain_in' => $soil->avg_annual_rain_in,
            'avg_july_high_f'    => $soil->avg_july_high_F,
            'avg_jan_low_f'      => $soil->avg_jan_low_F,
            'frost_free_days'    => $soil->frost_free_days,
        ];
    }

    private function formatSoil(GeoSoilReference $soil): array
    {
        return [
            'avg_ph'                   => $soil->avg_ph,
            'avg_ph_class'             => $soil->avg_ph_class,
            'soil_ph_max'              => $soil->soil_ph_max,
            'soil_ph_class'            => $soil->soil_ph_class,
            'cec_meq_per_100g'         => $soil->cec_meq_per_100g,
            'cec_class'                => $soil->cec_class,
            'organic_matter_pct'       => $soil->organic_matter_pct,
            'organic_matter_class'     => $soil->organic_matter_class,
            'calcium_saturation_class' => $soil->calcium_saturation_class,
            'soil_texture_class'       => $soil->soil_texture_class,
            'drainage_class'           => $soil->drainage_class,
            'composition'              => $this->parseSoilComposition($soil->soil_texture_class),
        ];
    }

    private function formatRisks(GeoSoilReference $soil): array
    {
        return [
            'compaction_risk'    => $soil->compaction_risk,
            'n_leaching_risk'    => $soil->n_leaching_risk,
            'disease_pressure'   => $soil->disease_pressure,
            'drought_stress_risk'=> $soil->drought_stress_risk,
        ];
    }

    private function formatCharts(GeoSoilReference $soil): array
    {
        return [
            'monthly_temp'     => $soil->monthly_temp_data,
            'monthly_rainfall' => $soil->monthly_rainfall_data,
            'growth_potential' => $soil->growth_potential_data,
        ];
    }

    private function formatPredictions(GeoSoilReference $soil): array
    {
        return [
            'organic_matter_slider' => $this->toSliderValue(
                $soil->organic_matter_pct,
                0,
                10
            ),
            'ph_slider' => $this->toSliderValue(
                $soil->avg_ph,
                4.0,
                8.0
            ),
            'nutrients' => $this->resolveNutrients($soil),
        ];
    }

    /**
     * Normalize a value to a 0–100 slider scale
     * given its expected min/max range.
     */
    private function toSliderValue(float $value, float $min, float $max): int
    {
        if ($max === $min) {
            return 50;
        }

        $normalized = (($value - $min) / ($max - $min)) * 100;

        return (int) max(0, min(100, round($normalized)));
    }

    /**
     * Derive silt/sand/clay percentages from soil_texture_class.
     * These are approximate midpoint values per USDA texture class.
     */
    private function parseSoilComposition(?string $textureClass): array
    {
        $map = [
            'sand'          => ['sand' => 92, 'silt' => 5,  'clay' => 3],
            'loamy sand'    => ['sand' => 82, 'silt' => 12, 'clay' => 6],
            'sandy loam'    => ['sand' => 65, 'silt' => 25, 'clay' => 10],
            'loam'          => ['sand' => 40, 'silt' => 40, 'clay' => 20],
            'silt loam'     => ['sand' => 20, 'silt' => 65, 'clay' => 15],
            'silt'          => ['sand' => 7,  'silt' => 88, 'clay' => 5],
            'sandy clay loam'=> ['sand' => 58, 'silt' => 17, 'clay' => 25],
            'clay loam'     => ['sand' => 32, 'silt' => 34, 'clay' => 34],
            'silty clay loam'=> ['sand' => 10, 'silt' => 57, 'clay' => 33],
            'sandy clay'    => ['sand' => 52, 'silt' => 7,  'clay' => 41],
            'silty clay'    => ['sand' => 7,  'silt' => 47, 'clay' => 46],
            'clay'          => ['sand' => 22, 'silt' => 20, 'clay' => 58],
        ];

        $key = strtolower(trim($textureClass ?? ''));

        return $map[$key] ?? ['sand' => 33, 'silt' => 34, 'clay' => 33];
    }

    /**
     * Resolve projected nutrient statuses from soil risk fields.
     */
    private function resolveNutrients(GeoSoilReference $soil): array
    {
        $ph         = (float) $soil->avg_ph;
        $leaching   = strtolower($soil->n_leaching_risk ?? 'low');
        $cec        = strtolower($soil->cec_class ?? '');
        $drainage   = strtolower($soil->drainage_class ?? '');

        return [
            [
                'symbol'  => 'N',
                'name'    => 'Nitrogen',
                'desc'    => 'Primary driver of green growth and blade density',
                'status'  => $leaching === 'high' ? 'Likely needs more' : 'Soil is likely sufficient',
                'is_good' => $leaching !== 'high',
            ],
            [
                'symbol'  => 'P',
                'name'    => 'Phosphorus',
                'desc'    => 'An energy source in plant metabolism',
                'status'  => $ph < 6.0 ? 'Likely needs more' : 'Soil is likely sufficient',
                'is_good' => $ph >= 6.0,
            ],
            [
                'symbol'  => 'K',
                'name'    => 'Potassium',
                'desc'    => "Vital to grass' ability to endure stress",
                'status'  => in_array($cec, ['low', 'very low']) ? 'Likely needs more' : 'Soil is likely sufficient',
                'is_good' => !in_array($cec, ['low', 'very low']),
            ],
            [
                'symbol'  => 'Ca',
                'name'    => 'Calcium',
                'desc'    => 'Supports cell wall structure and root development',
                'status'  => $ph < 5.5 ? 'Likely needs more' : 'Soil is likely sufficient',
                'is_good' => $ph >= 5.5,
            ],
        ];
    }
}