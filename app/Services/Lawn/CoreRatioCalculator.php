<?php

namespace App\Services\Lawn;

use App\DTOs\Lawn\CoreRatioDTO;
use App\DTOs\Lawn\SoilInputDTO;

/**
 * Calculates core product oz/1,000 sq ft ratios.
 *
 * Three products always ship. Total is locked at 10 oz / 1,000 sq ft.
 * Ratios shift based on soil conditions — same logic as the Python recommend() function.
 *
 * Skipped rules (fields not in geo_soil_references):
 *   - phosphorus_class (would bump KickStart+)
 *   - potassium_class  (would bump Neutralyze)
 *
 * DB value notes:
 *   - compaction_risk / drought_stress_risk / n_leaching_risk: 'high', 'moderate', 'low'
 *   - soil_texture_class: compound e.g. 'sandy loam', 'clay loam' — matched via str_contains
 *   - drainage_class: 'well drained', 'moderately well drained', 'somewhat poorly drained'
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
        [$k, $n, $z] = $this->applyDrainageAdjustment($k, $n, $z, $input);

        [$k, $n, $z] = $this->applyGuardrails($k, $n, $z);
        [$k, $n, $z] = $this->normalizeToTotal($k, $n, $z);
        [$k, $n, $z] = $this->roundAndClose($k, $n, $z);

        return new CoreRatioDTO(
            kickstartOzPer1000:  $k,
            turfFuelNOzPer1000:  $n,
            neutralyzeOzPer1000: $z,
        );
    }

    private function applyPhAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        if ($input->avgPh === null) {
            return [$k, $n, $z];
        }

        if ($input->avgPh < 6.2) {
            $z += 0.8; $k -= 0.4; $n -= 0.4;
        } elseif ($input->avgPh > 7.4) {
            $k += 0.6; $z -= 0.3; $n -= 0.3;
        }

        return [$k, $n, $z];
    }

    private function applyCecLeachingAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        if ($input->cecMeqPer100g !== null) {
            if ($input->cecMeqPer100g < 8.0) {
                $n -= 0.7; $z += 0.4; $k += 0.3;
            }
            return [$k, $n, $z];
        }

        // DB values: 'high', 'moderate', 'low'
        if ($input->nLeachingRisk === 'high') {
            $n -= 0.6; $z += 0.3; $k += 0.3;
        } elseif ($input->nLeachingRisk === 'moderate') {
            $n -= 0.3; $z += 0.15; $k += 0.15;
        }

        return [$k, $n, $z];
    }

    private function applyOrganicMatterAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        if ($input->organicMatterPct === null) {
            return [$k, $n, $z];
        }

        if ($input->organicMatterPct < 2.0) {
            $z += 0.4; $n -= 0.2; $k -= 0.2;
        } elseif ($input->organicMatterPct > 5.0) {
            $k += 0.2; $n += 0.2; $z -= 0.4;
        }

        return [$k, $n, $z];
    }

    private function applyTextureAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        // DB uses compound values: 'sandy loam', 'clay loam', 'silt loam', 'loam'
        $texture = strtolower($input->textureClass ?? '');

        if (str_contains($texture, 'sand')) {
            $n -= 0.4; $k += 0.2; $z += 0.2;
        } elseif (str_contains($texture, 'clay')) {
            $z += 0.3; $k -= 0.15; $n -= 0.15;
        }

        return [$k, $n, $z];
    }

    private function applyDrainageAdjustment(float $k, float $n, float $z, SoilInputDTO $input): array
    {
        // DB values: 'well drained', 'moderately well drained',
        //            'somewhat poorly drained', 'excessively drained'
        $drainage = strtolower($input->drainageClass ?? '');

        if (str_contains($drainage, 'well') && !str_contains($drainage, 'poor')) {
            $n -= 0.2; $k += 0.1; $z += 0.1;
        } elseif (str_contains($drainage, 'poor')) {
            $z += 0.3; $k -= 0.15; $n -= 0.15;
        }

        return [$k, $n, $z];
    }

    private function applyGuardrails(float $k, float $n, float $z): array
    {
        return [
            $this->clamp($k, self::MIN_KICKSTART,  self::MAX_KICKSTART),
            $this->clamp($n, self::MIN_TURFFUELN,  self::MAX_TURFFUELN),
            $this->clamp($z, self::MIN_NEUTRALYZE, self::MAX_NEUTRALYZE),
        ];
    }

    private function normalizeToTotal(float $k, float $n, float $z): array
    {
        $total = $k + $n + $z;

        if ($total == 0) {
            return [self::BASE_KICKSTART, self::BASE_TURFFUELN, self::BASE_NEUTRALYZE];
        }

        $scale = self::TOTAL_OZ / $total;
        return [$k * $scale, $n * $scale, $z * $scale];
    }

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

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}