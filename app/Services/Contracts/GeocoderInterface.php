<?php

namespace App\Services\Contracts;

use App\DTOs\Lawn\GeocodeResult;

interface GeocoderInterface
{
    /**
     * Geocode a human-readable address into coordinates.
     *
     * Returns a GeocodeResult on success, null on failure.
     */
    public function geocode(string $address): ?GeocodeResult;
}