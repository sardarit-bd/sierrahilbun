<?php

namespace App\Services\Area;

use App\DTOs\Lawn\LawnAreaResult;
use App\Services\Contracts\BuildingDetectorInterface;
use App\Services\Contracts\LotDetectorInterface;
use Illuminate\Support\Facades\Log;

class LawnAreaCalculator
{
    private const SQFT_PER_SQM      = 10.7639;
    private const DEFAULT_LAWN_SQFT = 5000;

    public function __construct(
        private readonly LotDetectorInterface      $lotDetector,
        private readonly BuildingDetectorInterface $buildingDetector,
    ) {}

    /**
     * Calculate the estimated lawn area at the given coordinates.
     *
     * Strategy:
     *  1. Detect lot boundary (Overpass)
     *  2. Detect building footprint (Mapbox Tilequery)
     *  3. Subtract building area from lot area → lawn polygon approximation
     *  4. If only building found → estimate lot as 4x building
     *  5. If nothing found → return default estimate
     */
    public function calculate(float $lat, float $lon): LawnAreaResult
    {
        $lotPolygon      = $this->lotDetector->detect($lat, $lon);
        $buildingPolygon = $this->buildingDetector->detect($lat, $lon);

        Log::debug('LawnAreaCalculator results', [
            'has_lot'      => $lotPolygon !== null,
            'has_building' => $buildingPolygon !== null,
        ]);

        // ── Strategy 1: Lot + Building → subtract ────────────────
        if ($lotPolygon !== null && $buildingPolygon !== null) {
            $lotSqm      = $this->polygonAreaSqm($lotPolygon);
            $buildingSqm = $this->polygonAreaSqm($buildingPolygon);
            $lawnSqm     = max(0, $lotSqm - $buildingSqm);
            $lawnSqft    = (int) round($lawnSqm * self::SQFT_PER_SQM);

            // Approximate lawn polygon = lot polygon (user can refine on map)
            return new LawnAreaResult(
                squareFeet     : $lawnSqft,
                lawnPolygon    : $lotPolygon,
                source         : 'calculated',
                estimated      : false,
                lotPolygon     : $lotPolygon,
                buildingPolygon: $buildingPolygon,
            );
        }

        // ── Strategy 2: Lot only → show lot, no subtraction ──────
        if ($lotPolygon !== null) {
            $lotSqm   = $this->polygonAreaSqm($lotPolygon);
            $lawnSqft = (int) round($lotSqm * self::SQFT_PER_SQM);

            return new LawnAreaResult(
                squareFeet : $lawnSqft,
                lawnPolygon: $lotPolygon,
                source     : 'lot_only',
                estimated  : true,
                lotPolygon : $lotPolygon,
            );
        }

        // ── Strategy 3: Building only → estimate lot as 4x ───────
        if ($buildingPolygon !== null) {
            $buildingSqm     = $this->polygonAreaSqm($buildingPolygon);
            $estimatedLotSqm = $buildingSqm * 4;
            $lawnSqm         = $estimatedLotSqm - $buildingSqm;
            $lawnSqft        = (int) round($lawnSqm * self::SQFT_PER_SQM);

            return new LawnAreaResult(
                squareFeet     : $lawnSqft,
                lawnPolygon    : $buildingPolygon, // best we have — user must redraw
                source         : 'calculated',
                estimated      : true,
                buildingPolygon: $buildingPolygon,
            );
        }

        // ── Strategy 4: Nothing found → default estimate ─────────
        return new LawnAreaResult(
            squareFeet : self::DEFAULT_LAWN_SQFT,
            lawnPolygon: [],
            source     : 'default_estimate',
            estimated  : true,
        );
    }

    // -------------------------------------------------------
    // Geometry Helpers
    // -------------------------------------------------------

    /**
     * Calculate polygon area in square metres using the Shoelace formula.
     * Coordinates are projected to metres using equirectangular approximation.
     *
     * @param array<int, array{lat: float, lon: float}> $polygon
     */
    private function polygonAreaSqm(array $polygon): float
    {
        $n = count($polygon);

        if ($n < 3) {
            return 0.0;
        }

        $avgLat             = array_sum(array_column($polygon, 'lat')) / $n;
        $metersPerDegreeLat = 111320;
        $metersPerDegreeLon = 111320 * cos(deg2rad($avgLat));
        $area               = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $j  = ($i + 1) % $n;
            $xi = $polygon[$i]['lon'] * $metersPerDegreeLon;
            $yi = $polygon[$i]['lat'] * $metersPerDegreeLat;
            $xj = $polygon[$j]['lon'] * $metersPerDegreeLon;
            $yj = $polygon[$j]['lat'] * $metersPerDegreeLat;
            $area += $xi * $yj;
            $area -= $xj * $yi;
        }

        return abs($area) / 2.0;
    }
}