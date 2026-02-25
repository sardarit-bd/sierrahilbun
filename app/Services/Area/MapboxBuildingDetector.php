<?php

namespace App\Services\Area;

use App\Services\Contracts\ApiConfigInterface;
use App\Services\Contracts\BuildingDetectorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapboxBuildingDetector implements BuildingDetectorInterface
{
    // Mapbox Tilequery — queries vector tiles for features at a point
    private const TILEQUERY_URL = 'https://api.mapbox.com/v4/{tileset}/tilequery/{lon},{lat}.json';

    // OSM building footprints tileset hosted by Mapbox
    private const BUILDING_TILESET = 'mapbox.mapbox-terrain-v2';

    // Radius in meters to search around the coordinate
    private const RADIUS = 50;

    private const TIMEOUT = 10;

    public function __construct(
        private readonly ApiConfigInterface $config,
    ) {}

    /**
     * Detect building footprint polygon using Mapbox Tilequery API.
     * Returns array of {lat, lon} points or null if not found.
     */
    public function detect(float $lat, float $lon): ?array
    {
        $token = $this->config->getOrFail('mapbox_token');

        $url = strtr(self::TILEQUERY_URL, [
            '{tileset}' => self::BUILDING_TILESET,
            '{lon}'     => $lon,
            '{lat}'     => $lat,
        ]);

        $response = Http::timeout(self::TIMEOUT)
            ->get($url, [
                'radius'       => self::RADIUS,
                'limit'        => 10,
                'geometry'     => 'polygon',
                'access_token' => $token,
            ]);

        Log::debug('Mapbox Tilequery request', [
            'lat'    => $lat,
            'lon'    => $lon,
            'status' => $response->status(),
        ]);

        if (!$response->ok()) {
            Log::warning('Mapbox Tilequery failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        $features = $response->json('features', []);

        if (empty($features)) {
            return null;
        }

        // Find the first polygon feature that contains our point
        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? [];
            $type     = $geometry['type'] ?? '';

            if (!in_array($type, ['Polygon', 'MultiPolygon'], true)) {
                continue;
            }

            $polygon = $this->extractPolygon($geometry, $lat, $lon);

            if ($polygon !== null) {
                return $polygon;
            }
        }

        return null;
    }

    // -------------------------------------------------------
    // Private
    // -------------------------------------------------------

    /**
     * Extract the relevant polygon ring from GeoJSON geometry.
     * For MultiPolygon, returns the ring that contains the point.
     *
     * @return array<int, array{lat: float, lon: float}>|null
     */
    private function extractPolygon(array $geometry, float $lat, float $lon): ?array
    {
        $type        = $geometry['type'];
        $coordinates = $geometry['coordinates'];

        if ($type === 'Polygon') {
            // coordinates[0] is the outer ring
            return $this->ringToLatLon($coordinates[0]);
        }

        if ($type === 'MultiPolygon') {
            foreach ($coordinates as $polygonCoords) {
                $ring = $this->ringToLatLon($polygonCoords[0]);
                if ($this->pointInPolygon($lat, $lon, $ring)) {
                    return $ring;
                }
            }
        }

        return null;
    }

    /**
     * Convert GeoJSON ring [lon, lat] pairs to our {lat, lon} format.
     *
     * @param  array<int, array{float, float}>            $ring
     * @return array<int, array{lat: float, lon: float}>
     */
    private function ringToLatLon(array $ring): array
    {
        return array_map(
            fn (array $point) => ['lat' => (float) $point[1], 'lon' => (float) $point[0]],
            $ring
        );
    }

    /**
     * Ray-casting point-in-polygon check.
     *
     * @param array<int, array{lat: float, lon: float}> $polygon
     */
    private function pointInPolygon(float $lat, float $lon, array $polygon): bool
    {
        $inside = false;
        $n      = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i]['lon']; $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lon']; $yj = $polygon[$j]['lat'];

            $intersects = (($yi > $lat) !== ($yj > $lat))
                && ($lon < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);

            if ($intersects) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}