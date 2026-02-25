<?php

namespace App\Services\Geocoding;

use App\DTOs\Lawn\GeocodeResult;
use App\Services\Contracts\ApiConfigInterface;
use App\Services\Contracts\GeocoderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapboxGeocoder implements GeocoderInterface
{
    private const BASE_URL     = 'https://api.mapbox.com/search/geocode/v6/forward';
    private const TIMEOUT      = 10;

    // Only these feature types are precise enough for property-level work
    private const ACCEPTED_FEATURE_TYPES = ['address'];

    public function __construct(
        private readonly ApiConfigInterface $config,
    ) {}

    /**
     * Geocode an address using Mapbox Geocoding API v6.
     * Returns null if address cannot be resolved to a precise location.
     */
    public function geocode(string $address): ?GeocodeResult
    {
        $token = $this->config->getOrFail('mapbox_token');

        $response = Http::timeout(self::TIMEOUT)
            ->get(self::BASE_URL, [
                'q'           => $address,
                'access_token'=> $token,
                'limit'       => (int) ($this->config->get('mapbox_geocoding_limit') ?? 1),
                'types'       => 'address',   // Only return address-level results
                'language'    => 'en',
            ]);

        Log::debug('Mapbox Geocoding request', [
            'address' => $address,
            'status'  => $response->status(),
        ]);

        if (!$response->ok()) {
            Log::warning('Mapbox Geocoding failed', [
                'address' => $address,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            return null;
        }

        $features = $response->json('features', []);

        if (empty($features)) {
            return null;
        }

        return $this->parseFeature($features[0]);
    }

    // -------------------------------------------------------
    // Private
    // -------------------------------------------------------

    private function parseFeature(array $feature): ?GeocodeResult
    {
        $featureType = $feature['properties']['feature_type'] ?? '';
        $confidence  = $feature['properties']['match_code']['confidence'] ?? 'unknown';

        Log::debug('Mapbox Geocoding parseFeature', [
            'feature_type' => $featureType,
            'confidence'   => $confidence,
            'full_address' => $feature['properties']['full_address'] ?? '',
        ]);

        // Reject anything that isn't a precise address
        if (!in_array($featureType, self::ACCEPTED_FEATURE_TYPES, true)) {
            Log::warning('Mapbox Geocoding: feature type not accepted', [
                'feature_type' => $featureType,
            ]);
            return null;
        }

        $coordinates = $feature['geometry']['coordinates'] ?? null;

        if (!$coordinates || count($coordinates) < 2) {
            return null;
        }

        [$lon, $lat] = $coordinates; // GeoJSON is [lon, lat]

        $confidence  = $this->resolveConfidence($feature);
        $fullAddress = $feature['properties']['full_address']
            ?? $feature['properties']['name']
            ?? '';

        return new GeocodeResult(
            lat        : (float) $lat,
            lon        : (float) $lon,
            fullAddress: $fullAddress,
            confidence : $confidence,
            featureType: $featureType,
        );
    }

    /**
     * Map Mapbox match_code confidence to our simplified scale.
     * high   → safe for auto boundary detection
     * medium → show map but prompt user to draw
     * low    → reject, ask user for a better address
     */
    private function resolveConfidence(array $feature): string
    {
        $matchCode  = $feature['properties']['match_code'] ?? [];
        $confidence = $matchCode['confidence'] ?? 'low';

        return match ($confidence) {
            'high'        => 'high',
            'medium'      => 'medium',
            default       => 'low',
        };
    }
}