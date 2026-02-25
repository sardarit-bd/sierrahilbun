<?php

namespace App\Services\Lawn;

use App\DTOs\Lawn\GeocodeResult;
use App\DTOs\Lawn\LawnAreaResult;
use App\Services\Area\LawnAreaCalculator;
use App\Services\Contracts\GeocoderInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class LawnSizeService
{
    public function __construct(
        private readonly GeocoderInterface  $geocoder,
        private readonly LawnAreaCalculator $areaCalculator,
    ) {}

    /**
     * Full pipeline: address → geocode → detect area → return result.
     *
     * Returns an array shaped for the Inertia response.
     * Never throws — all exceptions are caught and returned as failure arrays.
     */
    public function calculate(string $address): array
    {
        try {
            // ── Step 1: Geocode ───────────────────────────────────
            $geocodeResult = $this->geocoder->geocode($address);

            if ($geocodeResult === null) {
                return $this->failure(
                    message: 'Address could not be found. Please check the address and try again.',
                    reason : 'geocode_failed',
                );
            }

            // For low confidence, still show the map but skip auto-detection.
            // User will need to draw their lawn manually.
            // Only reject if geocoder returned nothing at all (null above).

            // ── Step 2: Area Detection ────────────────────────────
            // Only attempt auto-detection for high confidence matches.
            // Medium/low confidence → skip detection, go straight to map for user to draw.
            $areaResult = $geocodeResult->isHighConfidence()
                ? $this->areaCalculator->calculate($geocodeResult->lat, $geocodeResult->lon)
                : $this->defaultEstimate();

            Log::info('LawnSizeService calculated', [
                'address'     => $address,
                'confidence'  => $geocodeResult->confidence,
                'source'      => $areaResult->source,
                'square_feet' => $areaResult->squareFeet,
                'estimated'   => $areaResult->estimated,
            ]);

            return $this->success($geocodeResult, $areaResult);

        } catch (Throwable $e) {
            Log::error('LawnSizeService error', [
                'address' => $address,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return $this->failure(
                message: 'An unexpected error occurred. Please enter your square footage manually.',
                reason : 'exception',
            );
        }
    }

    // -------------------------------------------------------
    // Private
    // -------------------------------------------------------

    /**
     * Default estimate when geocode confidence is too low for area detection.
     */
    private function defaultEstimate(): LawnAreaResult
    {
        return new LawnAreaResult(
            squareFeet : 5000,
            lawnPolygon: [],
            source     : 'default_estimate',
            estimated  : true,
        );
    }

    /**
     * Build success response array for Inertia.
     */
    private function success(GeocodeResult $geocode, LawnAreaResult $area): array
    {
        return [
            'success'         => true,
            'square_feet'     => $area->squareFeet,
            'lawn_polygon'    => $area->lawnPolygon,
            'lot_polygon'     => $area->lotPolygon,
            'building_polygon'=> $area->buildingPolygon,
            'source'          => $area->source,
            'estimated'       => $area->estimated,
            'latitude'        => $geocode->lat,
            'longitude'       => $geocode->lon,
            'matched_address' => $geocode->fullAddress,
            'confidence'      => $geocode->confidence,
            'error'           => null,
        ];
    }

    /**
     * Build failure response array for Inertia.
     */
    private function failure(string $message, string $reason = 'unknown'): array
    {
        return [
            'success'         => false,
            'square_feet'     => null,
            'lawn_polygon'    => null,
            'lot_polygon'     => null,
            'building_polygon'=> null,
            'source'          => null,
            'estimated'       => false,
            'latitude'        => null,
            'longitude'       => null,
            'matched_address' => null,
            'confidence'      => null,
            'error'           => $message,
            'reason'          => $reason,
        ];
    }
}