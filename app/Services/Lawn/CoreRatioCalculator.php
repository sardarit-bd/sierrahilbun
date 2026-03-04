<?php

namespace App\Services\Lawn;

use App\DTOs\Lawn\CoreRatioDTO;
use App\DTOs\Lawn\SoilInputDTO;

/**
 * Calculates core product oz/1,000 sq ft ratios.
 *
 * Three products always ship. Total is locked at 10 oz / 1,000 sq ft.
 * Ratios shift based on soil conditions only — drainage class is
 * intentionally excluded here; it only drives the Aerate modifier.
 *
 * Adjustment triggers (spec-defined):
 *   pH       < 6.2  → Increase Neutralyze; decrease TurfFuel N and KickStart
 *   pH       > 7.4  → Increase KickStart; slight decrease to others
 *   CEC      < 8    → Reduce TurfFuel N; increase Neutralyze and KickStart
 *   Leaching high   → Reduce TurfFuel N (redistribute to Neutralyze + KickStart)
 *   Leaching moderate → Reduce TurfFuel N partially
 *   OM       < 2%   → Slight Neutralyze increase
 *   Sand texture    → Reduce TurfFuel N
 *   Clay texture    → Increase Neutralyze
 *
 * Skipped (no DB field available):
 *   - Low Phosphorus → would increase KickStart
 *   - Low Potassium  → would increase Neutralyze
 *
 * DB value notes:
 *   - n_leaching_risk: 'high', 'moderate', 'low'
 *   - soil_texture_class: compound e.g. 'sandy loam', 'clay loam' — matched via str_contains
 *   - drainage_class: NOT used here — handled exclusively by AerateModifierRule
 */
final class CoreRatioCalculator
{
    private const BASE_KICKSTART  = 4.0;
    private const BASE_TURFFUELN  = 3.0;
    private const BASE_NEUTRALYZE = 3.0;

    private const MIN_KICKSTART  = 2.0;
    private const MAX_KICKSTART  = 6.5;
    private const MIN_TURFFUELN  = 1.5;
    private const MAX_TURFFUELN  = 5.5;
    private const MIN_NEUTRALYZE = 2.0;
    private const MAX_NEUTRALYZE = 6.5;

    private const TOTAL_OZ = 10.0;

    public function calculate(SoilInputDTO $input): CoreRatioDTO
    {
        $k = self::BASE_KICKSTART;
        $n = self::BASE_TURFFUELN;
        $z = self::BASE_NEUTRALYZE;

        [$k, $n, $z] = $this->applyPhAdjustment($k, $n, $z, $input);
        [$k, $n, $z] = $this->applyCecLeachingAdjustment($k, $n, $z, $input);
        [$k, $n, $z] = $this->applyOrganicMatterAdjustment($k, $n, $z, $input);
        [$k, $n, $z] = $this->applyTextureAdjustment($k, $n, $z, $input);

        [$k, $n, $z] = $this->applyGuardrails($k, $n, $z);
        [$k, $n, $z] = $this->normalizeToTotal($k, $n, $z);
        [$k, $n, $z] = $this->roundAndClose($k, $n, $z);

        return new CoreRatioDTO(
            kickstartOzPer1000:  $k,
            turfFuelNOzPer1000:  $n,
            neutralyzeOzPer1000: $z,
        );
    }

    // -------------------------------------------------------
    // Adjustment methods
    // -------------------------------------------------------

    /**
     * Low pH  → soil is acidic → Neutralyze corrects structure
     * High pH → soil is alkaline → KickStart drives green-up
     */
    private function applyPhAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        if ($input->avgPh === null) {
            return [$k, $n, $z];
        }

        if ($input->avgPh < 6.2) {
            $z += 0.8;
            $k -= 0.4;
            $n -= 0.4;
        } elseif ($input->avgPh > 7.4) {
            $k += 0.6;
            $z -= 0.3;
            $n -= 0.3;
        }

        return [$k, $n, $z];
    }

    /**
     * Low CEC → soil cannot hold nutrients well → reduce nitrogen to prevent waste.
     *
     * If CEC value is available, use it directly (more precise).
     * Otherwise fall back to n_leaching_risk class from DB:
     *   high     → aggressive N reduction (nitrogen washes away quickly)
     *   moderate → partial N reduction
     *   low/null → no adjustment
     *
     * Redistributed oz always go to Neutralyze and KickStart to keep total at 10 oz.
     */
    private function applyCecLeachingAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        if ($input->cecMeqPer100g !== null) {
            if ($input->cecMeqPer100g < 8.0) {
                $n -= 0.7;
                $z += 0.4;
                $k += 0.3;
            }

            return [$k, $n, $z];
        }

        // Fallback: use n_leaching_risk when CEC is not available
        if ($input->nLeachingRisk === 'high') {
            $n -= 0.6;
            $z += 0.3;
            $k += 0.3;
        } elseif ($input->nLeachingRisk === 'moderate') {
            $n -= 0.3;
            $z += 0.15;
            $k += 0.15;
        }

        return [$k, $n, $z];
    }

    /**
     * Low OM → soil lacks biological activity and structure.
     * Slight Neutralyze increase to help build soil health.
     *
     * High OM (> 5%) has no defined adjustment — soil is already healthy,
     * base ratios are sufficient.
     */
    private function applyOrganicMatterAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        if ($input->organicMatterPct === null) {
            return [$k, $n, $z];
        }

        if ($input->organicMatterPct < 2.0) {
            $z += 0.4;
            $n -= 0.2;
            $k -= 0.2;
        }

        return [$k, $n, $z];
    }

    /**
     * Sandy soil → nitrogen leaches quickly → reduce TurfFuel N.
     * Clay soil  → poor structure, water retention → increase Neutralyze.
     *
     * DB uses compound values: 'sandy loam', 'clay loam', 'silt loam', 'loam'
     * Matched via str_contains to cover all variants.
     *
     * Note: drainage_class is intentionally NOT used here.
     * Poor drainage triggers the Aerate modifier — see AerateModifierRule.
     */
    private function applyTextureAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        $texture = strtolower($input->textureClass ?? '');

        if (str_contains($texture, 'sand')) {
            $n -= 0.4;
            $k += 0.2;
            $z += 0.2;
        } elseif (str_contains($texture, 'clay')) {
            $z += 0.3;
            $k -= 0.15;
            $n -= 0.15;
        }

        return [$k, $n, $z];
    }

    // -------------------------------------------------------
    // Guardrails, normalization, rounding
    // -------------------------------------------------------

    /**
     * Clamp each product to its min/max guardrail before normalization.
     * Prevents any product from going dangerously low or high.
     */
    private function applyGuardrails(float $k, float $n, float $z): array
    {
        return [
            $this->clamp($k, self::MIN_KICKSTART,  self::MAX_KICKSTART),
            $this->clamp($n, self::MIN_TURFFUELN,  self::MAX_TURFFUELN),
            $this->clamp($z, self::MIN_NEUTRALYZE, self::MAX_NEUTRALYZE),
        ];
    }

    /**
     * Normalize the three values so they always sum to exactly 10 oz.
     * Preserves the relative ratios after all adjustments.
     */
    private function normalizeToTotal(float $k, float $n, float $z): array
    {
        $total = $k + $n + $z;

        if ($total == 0) {
            return [self::BASE_KICKSTART, self::BASE_TURFFUELN, self::BASE_NEUTRALYZE];
        }

        $scale = self::TOTAL_OZ / $total;

        return [$k * $scale, $n * $scale, $z * $scale];
    }

    /**
     * Round K and N to nearest 0.5 oz.
     * Derive Z as the remainder to guarantee total = 10.0 oz exactly.
     * Re-clamp Z in case rounding pushed it out of bounds,
     * then re-scale if the sum still drifts.
     */
    private function roundAndClose(float $k, float $n, float $z): array
    {
        $k = round($k * 2) / 2;
        $n = round($n * 2) / 2;
        $z = self::TOTAL_OZ - ($k + $n);
        $z = $this->clamp($z, self::MIN_NEUTRALYZE, self::MAX_NEUTRALYZE);

        $sum = $k + $n + $z;

        if (abs($sum - self::TOTAL_OZ) > 1e-6) {
            $scale = self::TOTAL_OZ / $sum;
            $k    *= $scale;
            $n    *= $scale;
            $z     = self::TOTAL_OZ - ($k + $n);
        }

        return [$k, $n, $z];
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}