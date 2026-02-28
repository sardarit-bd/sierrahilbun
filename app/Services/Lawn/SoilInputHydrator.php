<?php

namespace App\Services\Lawn;

use App\DTOs\Lawn\SoilInputDTO;
use App\Models\GeoSoilReference;

/**
 * Maps soil data into a SoilInputDTO.
 *
 * Two hydration paths:
 *   1. hydrate()             — from GeoSoilReference Eloquent model (flat structure)
 *   2. hydrateFromSnapshot() — from soil_snapshot JSON (nested structure)
 *
 * If geo lookup fails, the service falls back to soil_snapshot.
 * Both paths produce an identical SoilInputDTO — the engine never knows the difference.
 *
 * soil_snapshot nested structure:
 *   soil.avg_ph, soil.cec_meq_per_100g, soil.organic_matter_pct
 *   soil.soil_texture_class, soil.drainage_class
 *   risks.compaction_risk, risks.n_leaching_risk, risks.drought_stress_risk
 *   climate.avg_july_high_f, climate.frost_free_days, climate.avg_jan_low_f
 */
final class SoilInputHydrator
{
    // -------------------------------------------------------
    // GeoSoilReference model
    // -------------------------------------------------------

    public function hydrate(
        GeoSoilReference $geo,
        int              $lawnSqft,
        array            $quizAnswers = [],
    ): SoilInputDTO {
        return new SoilInputDTO(
            lawnSqft:          $lawnSqft,
            avgPh:             $this->toFloat($geo->avg_ph),
            cecMeqPer100g:     $this->toFloat($geo->cec_meq_per_100g),
            organicMatterPct:  $this->toFloat($geo->organic_matter_pct),
            textureClass:      $geo->soil_texture_class,
            drainageClass:     $geo->drainage_class,
            compactionRisk:    $geo->compaction_risk,
            nLeachingRisk:     $geo->n_leaching_risk,
            droughtStressRisk: $geo->drought_stress_risk,
            avgJulyHighF:      $this->toInt($geo->avg_july_high_F),
            frostFreeDays:     $this->toInt($geo->frost_free_days),
            avgJanLowF:        $this->toInt($geo->avg_jan_low_F),
            pets:              $quizAnswers['pets']  ?? null,
            weeds:             $quizAnswers['weeds'] ?? null,
        );
    }

    // -------------------------------------------------------
    // soil_snapshot array
    // -------------------------------------------------------

    public function hydrateFromSnapshot(
        array $snapshot,
        int   $lawnSqft,
        array $quizAnswers = [],
    ): SoilInputDTO {
        $soil    = $snapshot['soil']    ?? [];
        $risks   = $snapshot['risks']   ?? [];
        $climate = $snapshot['climate'] ?? [];

        return new SoilInputDTO(
            lawnSqft:          $lawnSqft,
            avgPh:             $this->toFloat($soil['avg_ph']             ?? null),
            cecMeqPer100g:     $this->toFloat($soil['cec_meq_per_100g']   ?? null),
            organicMatterPct:  $this->toFloat($soil['organic_matter_pct'] ?? null),
            textureClass:      $soil['soil_texture_class'] ?? null,
            drainageClass:     $soil['drainage_class']     ?? null,
            compactionRisk:    $risks['compaction_risk']    ?? null,
            nLeachingRisk:     $risks['n_leaching_risk']    ?? null,
            droughtStressRisk: $risks['drought_stress_risk'] ?? null,
            avgJulyHighF:      $this->toInt($climate['avg_july_high_f'] ?? null),
            frostFreeDays:     $this->toInt($climate['frost_free_days'] ?? null),
            avgJanLowF:        $this->toInt($climate['avg_jan_low_f']   ?? null),
            pets:              $quizAnswers['pets']  ?? null,
            weeds:             $quizAnswers['weeds'] ?? null,
        );
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function toFloat(mixed $value): ?float
    {
        return $value !== null ? (float) $value : null;
    }

    private function toInt(mixed $value): ?int
    {
        return $value !== null ? (int) $value : null;
    }
}