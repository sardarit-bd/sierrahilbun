<?php

namespace App\Services\Area;

use App\Services\Contracts\LotDetectorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OverpassLotDetector implements LotDetectorInterface
{
    private const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';
    private const USER_AGENT   = 'LawnTurfApp/2.0';
    private const RADIUS       = 50;   // meters around the coordinate
    private const TIMEOUT      = 15;

    /**
     * Detect lot/parcel boundary using OpenStreetMap Overpass API.
     * Tries residential landuse first, then plot tag as fallback.
     * Returns array of {lat, lon} points or null if not found.
     */
    public function detect(float $lat, float $lon): ?array
    {
        // Try residential landuse boundary first
        $polygon = $this->query($lat, $lon, ['landuse' => 'residential']);

        if ($polygon !== null) {
            return $polygon;
        }

        // Fallback: explicit plot tag
        return $this->query($lat, $lon, ['plot' => '*']);
    }

    // -------------------------------------------------------
    // Private
    // -------------------------------------------------------

    /**
     * Query Overpass for a way/relation with given tags near the coordinate.
     *
     * @param  array<string, string>                           $tags
     * @return array<int, array{lat: float, lon: float}>|null
     */
    private function query(float $lat, float $lon, array $tags): ?array
    {
        $query    = $this->buildQuery($lat, $lon, $tags);
        $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
            ->timeout(self::TIMEOUT)
            ->post(self::OVERPASS_URL, ['data' => $query]);

        Log::debug('Overpass Lot query', [
            'lat'    => $lat,
            'lon'    => $lon,
            'tags'   => $tags,
            'status' => $response->status(),
        ]);

        if (!$response->ok()) {
            Log::warning('Overpass Lot query failed', [
                'status' => $response->status(),
            ]);
            return null;
        }

        $elements = $response->json('elements', []);

        if (empty($elements)) {
            return null;
        }

        return $this->resolvePolygon($elements, $lat, $lon);
    }

    /**
     * Build Overpass QL query string.
     *
     * @param array<string, string> $tags
     */
    private function buildQuery(float $lat, float $lon, array $tags): string
    {
        $tagFilters = '';
        $radius     = self::RADIUS;

        foreach ($tags as $key => $value) {
            $tagFilters .= $value === '*'
                ? "[\"$key\"]"
                : "[\"$key\"=\"$value\"]";
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

    /**
     * From returned elements, find the polygon that contains our point.
     * Falls back to the first element if none contain the point.
     *
     * @param  array<int, array<string, mixed>>               $elements
     * @return array<int, array{lat: float, lon: float}>|null
     */
    private function resolvePolygon(array $elements, float $lat, float $lon): ?array
    {
        foreach ($elements as $element) {
            if (empty($element['geometry'])) {
                continue;
            }

            $polygon = $this->geometryToLatLon($element['geometry']);

            if ($this->pointInPolygon($lat, $lon, $polygon)) {
                return $polygon;
            }
        }

        // No polygon contained the point — return the closest one
        $first = $elements[0];

        if (!empty($first['geometry'])) {
            return $this->geometryToLatLon($first['geometry']);
        }

        return null;
    }

    /**
     * Convert Overpass geometry nodes to our {lat, lon} format.
     *
     * @param  array<int, array{lat: float, lon: float}>  $geometry
     * @return array<int, array{lat: float, lon: float}>
     */
    private function geometryToLatLon(array $geometry): array
    {
        return array_map(
            fn (array $node) => [
                'lat' => (float) $node['lat'],
                'lon' => (float) $node['lon'],
            ],
            $geometry
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