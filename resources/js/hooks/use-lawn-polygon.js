import { useState, useCallback } from 'react';

const SQFT_PER_SQM = 10.7639;

/**
 * Shoelace formula for polygon area in square metres.
 * Coordinates projected via equirectangular approximation.
 *
 * @param {Array<{lat: number, lon: number}>} polygon
 * @returns {number} area in sq ft
 */
function polygonToSqft(polygon) {
    const n = polygon.length;
    if (n < 3) return 0;

    const avgLat = polygon.reduce((s, p) => s + p.lat, 0) / n;
    const mPerLat = 111320;
    const mPerLon = 111320 * Math.cos((avgLat * Math.PI) / 180);

    let area = 0;
    for (let i = 0; i < n; i++) {
        const j  = (i + 1) % n;
        const xi = polygon[i].lon * mPerLon;
        const yi = polygon[i].lat * mPerLat;
        const xj = polygon[j].lon * mPerLon;
        const yj = polygon[j].lat * mPerLat;
        area += xi * yj - xj * yi;
    }

    const sqm = Math.abs(area) / 2;
    return Math.round(sqm * SQFT_PER_SQM);
}

/**
 * Convert GeoJSON feature coordinates to our {lat, lon} polygon format.
 * Handles both Polygon and MultiPolygon.
 *
 * @param {Object} feature  GeoJSON feature from Mapbox Draw
 * @returns {Array<{lat: number, lon: number}>}
 */
function featureToPolygon(feature) {
    const { type, coordinates } = feature.geometry;

    if (type === 'Polygon') {
        return coordinates[0].map(([lon, lat]) => ({ lat, lon }));
    }

    if (type === 'MultiPolygon') {
        // Flatten all rings — treat as one combined polygon
        return coordinates.flatMap((poly) =>
            poly[0].map(([lon, lat]) => ({ lat, lon }))
        );
    }

    return [];
}

/**
 * Convert our {lat, lon} polygon format to GeoJSON coordinates.
 * Used when pre-populating the map with a server-provided polygon.
 *
 * @param {Array<{lat: number, lon: number}>} polygon
 * @returns {Array<[number, number]>}  [lon, lat] pairs
 */
export function polygonToGeoJSON(polygon) {
    return polygon.map(({ lat, lon }) => [lon, lat]);
}

/**
 * useLawnPolygon
 *
 * Manages the active lawn polygon and derived square footage.
 *
 * Priority order for square footage:
 *   1. User-drawn polygon (most accurate)
 *   2. Server-calculated value (auto-detected)
 *   3. Manual text input override
 *
 * @param {number|null}  serverSqft     Square footage from server calculation
 * @param {Array|null}   serverPolygon  Polygon from server detection
 */
export default function useLawnPolygon(serverSqft = null, serverPolygon = null) {
    const [drawnPolygon, setDrawnPolygon] = useState(null);  // user drew on map
    const [manualSqft,   setManualSqft]   = useState(null);  // user typed manually

    // ── Derived state ─────────────────────────────────────────────
    const drawnSqft = drawnPolygon ? polygonToSqft(drawnPolygon) : null;

    // Priority: drawn > server > manual
    const activeSqft    = drawnSqft ?? serverSqft ?? manualSqft ?? 0;
    const activePolygon = drawnPolygon ?? serverPolygon ?? [];

    const isDrawn   = drawnPolygon !== null;
    const isManual  = !isDrawn && manualSqft !== null;
    const isServer  = !isDrawn && !isManual && serverSqft !== null;

    const sourceLabel = isDrawn  ? 'drawn'
                      : isServer ? 'calculated'
                      : isManual ? 'manual'
                      : 'unknown';

    // ── Actions ───────────────────────────────────────────────────

    /**
     * Called by MapboxLawnMap when user finishes drawing/editing a polygon.
     * Accepts a GeoJSON feature from Mapbox Draw.
     */
    const onPolygonDrawn = useCallback((feature) => {
        if (!feature) {
            setDrawnPolygon(null);
            return;
        }
        const polygon = featureToPolygon(feature);
        setDrawnPolygon(polygon.length >= 3 ? polygon : null);
    }, []);

    /**
     * Called when user clears/resets the drawn polygon.
     */
    const clearDrawn = useCallback(() => {
        setDrawnPolygon(null);
    }, []);

    /**
     * Called when user types in the manual override input.
     * Clears drawn polygon so manual value takes priority.
     */
    const onManualInput = useCallback((value) => {
        setDrawnPolygon(null);
        setManualSqft(value ? parseInt(value, 10) : null);
    }, []);

    return {
        // Values
        activeSqft,
        activePolygon,
        drawnPolygon,
        drawnSqft,
        manualSqft,
        sourceLabel,

        // State flags
        isDrawn,
        isManual,
        isServer,

        // Actions
        onPolygonDrawn,
        clearDrawn,
        onManualInput,
    };
}