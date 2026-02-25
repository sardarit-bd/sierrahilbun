import { useEffect, useRef, useCallback } from 'react';
import mapboxgl from 'mapbox-gl';
import MapboxDraw from '@mapbox/mapbox-gl-draw';
import { polygonToGeoJSON } from '@/hooks/use-lawn-polygon';
import 'mapbox-gl/dist/mapbox-gl.css';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';

// ─── Draw styles ──────────────────────────────────────────────────────────────
// Custom Mapbox Draw styles — lawn green theme
const DRAW_STYLES = [
    // Polygon fill — active (being drawn)
    {
        id    : 'gl-draw-polygon-fill-active',
        type  : 'fill',
        filter: ['all', ['==', '$type', 'Polygon'], ['!=', 'mode', 'static']],
        paint : {
            'fill-color'  : '#2E7D32',
            'fill-opacity': 0.25,
        },
    },
    // Polygon fill — static (confirmed)
    {
        id    : 'gl-draw-polygon-fill-static',
        type  : 'fill',
        filter: ['all', ['==', '$type', 'Polygon'], ['==', 'mode', 'static']],
        paint : {
            'fill-color'  : '#2E7D32',
            'fill-opacity': 0.20,
        },
    },
    // Polygon stroke — active
    {
        id    : 'gl-draw-polygon-stroke-active',
        type  : 'line',
        filter: ['all', ['==', '$type', 'Polygon'], ['!=', 'mode', 'static']],
        paint : {
            'line-color' : '#43A047',
            'line-width' : 2.5,
            'line-dasharray': [2, 1],
        },
    },
    // Polygon stroke — static
    {
        id    : 'gl-draw-polygon-stroke-static',
        type  : 'line',
        filter: ['all', ['==', '$type', 'Polygon'], ['==', 'mode', 'static']],
        paint : {
            'line-color': '#2E7D32',
            'line-width': 2.5,
        },
    },
    // Vertex points
    {
        id    : 'gl-draw-polygon-and-line-vertex-active',
        type  : 'circle',
        filter: ['all', ['==', 'meta', 'vertex'], ['==', '$type', 'Point']],
        paint : {
            'circle-radius'      : 6,
            'circle-color'       : '#fff',
            'circle-stroke-color': '#2E7D32',
            'circle-stroke-width': 2,
        },
    },
    // Midpoint handles
    {
        id    : 'gl-draw-polygon-midpoint',
        type  : 'circle',
        filter: ['all', ['==', '$type', 'Point'], ['==', 'meta', 'midpoint']],
        paint : {
            'circle-radius'      : 4,
            'circle-color'       : '#fff',
            'circle-stroke-color': '#43A047',
            'circle-stroke-width': 2,
        },
    },
];

// ─── Building polygon layer styles ───────────────────────────────────────────
const BUILDING_LAYER = {
    id    : 'building-footprint',
    type  : 'fill',
    source: 'building',
    paint : {
        'fill-color'  : '#FF5722',
        'fill-opacity': 0.30,
    },
};

const BUILDING_OUTLINE_LAYER = {
    id    : 'building-footprint-outline',
    type  : 'line',
    source: 'building',
    paint : {
        'line-color': '#FF5722',
        'line-width': 1.5,
    },
};

// ─── Component ────────────────────────────────────────────────────────────────

/**
 * MapboxLawnMap
 *
 * Renders a satellite map with:
 *  - Auto-drawn lawn polygon (from server detection)
 *  - Building footprint overlay (orange, so user knows to exclude it)
 *  - Mapbox Draw toolbar for user to redraw/adjust
 *  - Calls onPolygonDrawn(feature) whenever polygon changes
 *
 * @param {string}  token            Mapbox public token
 * @param {string}  styleId          Mapbox style URL
 * @param {number}  lat              Center latitude
 * @param {number}  lon              Center longitude
 * @param {number}  zoom             Initial zoom level
 * @param {Array}   lawnPolygon      Server-detected lawn polygon [{lat,lon}]
 * @param {Array}   buildingPolygon  Building footprint [{lat,lon}]
 * @param {Function}onPolygonDrawn   Callback (feature) => void
 * @param {string}  className        Tailwind classes for container
 */
export default function MapboxLawnMap({
    token,
    styleId  = 'mapbox://styles/mapbox/satellite-streets-v12',
    lat,
    lon,
    zoom     = 19,
    lawnPolygon     = [],
    buildingPolygon = [],
    onPolygonDrawn,
    className = 'w-full h-full',
}) {
    const containerRef = useRef(null);
    const mapRef       = useRef(null);
    const drawRef      = useRef(null);
    const initialised  = useRef(false);

    // ── Initialise map ────────────────────────────────────────────
    useEffect(() => {
        if (initialised.current || !containerRef.current || !token) return;
        initialised.current = true;

        mapboxgl.accessToken = token;

        const map = new mapboxgl.Map({
            container : containerRef.current,
            style     : styleId,
            center    : [lon, lat],
            zoom,
            pitchWithRotate: false,
        });

        // Navigation controls (zoom +/-)
        map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');

        // Mapbox Draw
        const draw = new MapboxDraw({
            displayControlsDefault: false,
            controls: {
                polygon      : true,
                trash        : true,
            },
            styles        : DRAW_STYLES,
            defaultMode   : 'simple_select',
        });

        map.addControl(draw, 'top-right');

        mapRef.current  = map;
        drawRef.current = draw;

        // ── On map load ───────────────────────────────────────────
        map.on('load', () => {
            // Add building footprint source + layers
            if (buildingPolygon.length >= 3) {
                map.addSource('building', {
                    type: 'geojson',
                    data: {
                        type    : 'Feature',
                        geometry: {
                            type       : 'Polygon',
                            coordinates: [polygonToGeoJSON(buildingPolygon)],
                        },
                    },
                });
                map.addLayer(BUILDING_LAYER);
                map.addLayer(BUILDING_OUTLINE_LAYER);
            }

            // Pre-load server-detected lawn polygon into Draw
            if (lawnPolygon.length >= 3) {
                const feature = draw.add({
                    type    : 'Feature',
                    geometry: {
                        type       : 'Polygon',
                        coordinates: [polygonToGeoJSON(lawnPolygon)],
                    },
                });

                // Notify parent of the initial polygon
                if (onPolygonDrawn) {
                    const features = draw.getAll().features;
                    if (features.length > 0) {
                        onPolygonDrawn(features[0]);
                    }
                }
            }
        });

        // ── Draw event listeners ──────────────────────────────────
        const handleDrawUpdate = () => {
            if (!onPolygonDrawn) return;
            const features = draw.getAll().features;
            onPolygonDrawn(features.length > 0 ? features[features.length - 1] : null);
        };

        map.on('draw.create', handleDrawUpdate);
        map.on('draw.update', handleDrawUpdate);
        map.on('draw.delete', () => onPolygonDrawn?.(null));

        return () => {
            map.remove();
            initialised.current = false;
        };
    }, [token]); // Only re-init if token changes

    // ── Update center if lat/lon props change ─────────────────────
    useEffect(() => {
        if (!mapRef.current || !lat || !lon) return;
        mapRef.current.flyTo({
            center  : [lon, lat],
            zoom,
            duration: 1200,
            essential: true,
        });
    }, [lat, lon]);

    return (
        <div className={className}>
            <div ref={containerRef} className="w-full h-full" />
        </div>
    );
}