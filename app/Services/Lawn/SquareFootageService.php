<?php

namespace App\Services\Lawn;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SquareFootageService
{
    private const GEOCODIO_URL  = 'https://api.geocod.io/v1.7/geocode';
    private const OVERPASS_URL  = 'https://overpass-api.de/api/interpreter';
    private const SQFT_PER_SQM  = 10.7639;
    private const USER_AGENT    = 'LawnTurfApp/1.0 (your@email.com)';

    // Typical US suburban lot sizes by density (sq ft)
    // Used as last-resort estimate when boundary data unavailable
    private const DEFAULT_LOT_SQFT = 7500;

    // -------------------------------------------------------
    // Public Entry Point
    // -------------------------------------------------------

    public function calculate(string $address): array
    {
        try {
            $coordinates = $this->geocodeAddress($address);

            if (!$coordinates) {
                return $this->failure(
                    'Address could not be found. Please check the address and try again.',
                    'geocode_failed'
                );
            }

            ['lat' => $lat, 'lon' => $lon] = $coordinates;

            // Only bother querying Overpass if geocode was high confidence
            // Low confidence = wrong street/city, Overpass result would be meaningless
            if ($coordinates['accuracy'] >= 0.95) {
                $lotSqm      = $this->fetchLotArea($lat, $lon);
                $buildingSqm = $this->fetchBuildingArea($lat, $lon);

                if ($lotSqm !== null) {
                    $lawnSqm  = max(0, $lotSqm - ($buildingSqm ?? 0));
                    $lawnSqft = (int) round($lawnSqm * self::SQFT_PER_SQM);
                    return $this->success($lawnSqft, $lotSqm, $buildingSqm, $lat, $lon, 'boundary', $coordinates['matched_address']);
                }

                if ($buildingSqm !== null) {
                    $estimatedLotSqm = $buildingSqm * 4;
                    $lawnSqm         = $estimatedLotSqm - $buildingSqm;
                    $lawnSqft        = (int) round($lawnSqm * self::SQFT_PER_SQM);
                    return $this->success($lawnSqft, $estimatedLotSqm, $buildingSqm, $lat, $lon, 'building_estimate', $coordinates['matched_address']);
                }
            }

            // Low confidence match OR Overpass found nothing — return default estimate
            // Address was recognised, just not precisely enough for boundary lookup
            return $this->success(
                self::DEFAULT_LOT_SQFT,
                null, null, $lat, $lon,
                'default_estimate',
                $coordinates['matched_address']
            );

        } catch (\Throwable $e) {
            Log::error('SquareFootageService error', [
                'address' => $address,
                'message' => $e->getMessage(),
            ]);

            return $this->failure('An unexpected error occurred. Please enter square footage manually.', 'exception');
        }
    }

    // -------------------------------------------------------
    // Geocode
    // -------------------------------------------------------

    private function geocodeAddress(string $address): ?array
    {
        $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
            ->timeout(10)
            ->get(self::GEOCODIO_URL, [
                'q'       => $address,
                'api_key' => config('services.geocodio.key'),
                'limit'   => 1,
            ]);

        Log::debug('Geocodio request', [
            'address' => $address,
            'status'  => $response->status(),
        ]);

        if (!$response->ok()) return null;

        $results = $response->json('results', []);
        if (empty($results)) return null;

        $result   = $results[0];
        $location = $result['location'] ?? null;
        if (!$location) return null;

        $accuracyType  = $result['accuracy_type'] ?? '';
        $acceptedTypes = ['rooftop', 'point', 'range_interpolation', 'nearest_rooftop_match'];

        // Reject only completely unusable types (intersections, cities, states)
        if (!in_array($accuracyType, $acceptedTypes)) {
            Log::warning('Geocodio unusable accuracy type', [
                'input'         => $address,
                'accuracy_type' => $accuracyType,
            ]);
            return null;
        }

        return [
            'lat'             => (float) $location['lat'],
            'lon'             => (float) $location['lng'],
            'matched_address' => $result['formatted_address'] ?? $address,
            'accuracy'        => (float) ($result['accuracy'] ?? 0),
        ];
    }

    // -------------------------------------------------------
    // Overpass: Lot + Building (unchanged)
    // -------------------------------------------------------

    private function fetchLotArea(float $lat, float $lon): ?float
    {
        $query   = $this->buildOverpassQuery($lat, $lon, ['landuse' => 'residential']);
        $areaSqm = $this->queryOverpass($query, $lat, $lon);

        if ($areaSqm !== null) return $areaSqm;

        $query = $this->buildOverpassQuery($lat, $lon, ['plot' => '*']);
        return $this->queryOverpass($query, $lat, $lon);
    }

    private function fetchBuildingArea(float $lat, float $lon): ?float
    {
        $query = $this->buildOverpassQuery($lat, $lon, ['building' => '*']);
        return $this->queryOverpass($query, $lat, $lon);
    }

    private function buildOverpassQuery(float $lat, float $lon, array $tags): string
    {
        $radius     = 50;
        $tagFilters = '';

        foreach ($tags as $key => $value) {
            $tagFilters .= $value === '*' ? "[\"$key\"]" : "[\"$key\"=\"$value\"]";
        }

        return <<<OSM
        [out:json][timeout:15];
        (
          way{$tagFilters}(around:{$radius},{$lat},{$lon});
          relation{$tagFilters}(around:{$radius},{$lat},{$lon});
        );
        out geom;
        OSM;
    }

    private function queryOverpass(string $query, float $lat, float $lon): ?float
    {
        $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
            ->timeout(15)
            ->post(self::OVERPASS_URL, ['data' => $query]);

        if (!$response->ok()) return null;

        $elements = $response->json('elements', []);

        if (empty($elements)) return null;

        foreach ($elements as $element) {
            if (empty($element['geometry'])) continue;

            $polygon = array_map(
                fn($node) => [$node['lon'], $node['lat']],
                $element['geometry']
            );

            if ($this->pointInPolygon($lat, $lon, $polygon)) {
                return $this->polygonAreaSqm($polygon);
            }
        }

        $first = $elements[0];
        if (!empty($first['geometry'])) {
            $polygon = array_map(
                fn($node) => [$node['lon'], $node['lat']],
                $first['geometry']
            );
            return $this->polygonAreaSqm($polygon);
        }

        return null;
    }

    // -------------------------------------------------------
    // Geometry helpers (unchanged)
    // -------------------------------------------------------

    private function polygonAreaSqm(array $polygon): float
    {
        $n = count($polygon);
        if ($n < 3) return 0.0;

        $avgLat             = array_sum(array_column($polygon, 1)) / $n;
        $metersPerDegreeLat = 111320;
        $metersPerDegreeLon = 111320 * cos(deg2rad($avgLat));
        $area               = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $j  = ($i + 1) % $n;
            $xi = $polygon[$i][0] * $metersPerDegreeLon;
            $yi = $polygon[$i][1] * $metersPerDegreeLat;
            $xj = $polygon[$j][0] * $metersPerDegreeLon;
            $yj = $polygon[$j][1] * $metersPerDegreeLat;
            $area += $xi * $yj;
            $area -= $xj * $yi;
        }

        return abs($area) / 2.0;
    }

    private function pointInPolygon(float $lat, float $lon, array $polygon): bool
    {
        $inside = false;
        $n      = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][0]; $yi = $polygon[$i][1];
            $xj = $polygon[$j][0]; $yj = $polygon[$j][1];

            $intersects = (($yi > $lat) !== ($yj > $lat))
                && ($lon < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);

            if ($intersects) $inside = !$inside;
        }

        return $inside;
    }

    // -------------------------------------------------------
    // Response builders — added 'source' and 'estimated' flag
    // -------------------------------------------------------

    private function success(
        int $lawnSqft,
        ?float $lotSqm,
        ?float $buildingSqm,
        float $lat,
        float $lon,
        string $source = 'boundary'
    ): array {
        return [
            'success'      => true,
            'square_feet'  => $lawnSqft,
            'lot_sqm'      => $lotSqm ? round($lotSqm, 2) : null,
            'building_sqm' => $buildingSqm ? round($buildingSqm, 2) : null,
            'latitude'     => $lat,
            'longitude'    => $lon,
            'source'       => $source,         // 'boundary' | 'building_estimate' | 'default_estimate'
            'estimated'    => $source !== 'boundary',
            'error'        => null,
        ];
    }

    private function failure(string $message, string $reason = 'unknown'): array
    {
        return [
            'success'      => false,
            'square_feet'  => null,
            'lot_sqm'      => null,
            'building_sqm' => null,
            'latitude'     => null,
            'longitude'    => null,
            'source'       => null,
            'estimated'    => false,
            'error'        => $message,
            'reason'       => $reason,
        ];
    }
}