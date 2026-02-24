import { useEffect, useRef } from 'react';
import { MapContainer, TileLayer, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-draw/dist/leaflet.draw.css';
import L from 'leaflet';
import 'leaflet-draw';

// Fix Leaflet default marker icon (known bundler issue)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl      : 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl    : 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

// ─── Marker for the geocoded address ─────────────────────────────────────────

const AddressMarker = ({ lat, lon, label }) => {
    const map = useMap();

    useEffect(() => {
        if (!lat || !lon) return;

        const marker = L.marker([lat, lon]).addTo(map);

        if (label) {
            marker.bindPopup(label).openPopup();
        }

        return () => {
            map.removeLayer(marker);
        };
    }, [map, lat, lon, label]);

    return null;
};

// ─── Drawing Controller ───────────────────────────────────────────────────────

const DrawingController = ({ onAreaCalculated }) => {
    const map        = useMap();
    const drawnItems = useRef(new L.FeatureGroup());

    useEffect(() => {
        map.addLayer(drawnItems.current);

        // Override tooltips so users know how to close the polygon
        L.drawLocal.draw.handlers.polygon.tooltip = {
            start : 'Click to start drawing your lawn area.',
            cont  : 'Click to continue drawing. Click the first point to close the shape.',
            end   : 'Click the first point to close this shape.',
        };

        const drawControl = new L.Control.Draw({
            position: 'topright',
            draw: {
                polygon: {
                    allowIntersection: false,
                    showArea         : true,
                    // finishOn: null means the polygon only closes when the user
                    // clicks back on the first point — NOT on double-click.
                    // This is what was causing the "only 3 points" problem:
                    // double-click was registering as a point + finish simultaneously.
                    finishOn         : null,
                    shapeOptions: {
                        color      : '#2E7D32',
                        fillColor  : '#4CAF50',
                        fillOpacity: 0.25,
                        weight     : 2,
                    },
                },
                rectangle   : false,
                circle      : false,
                circlemarker: false,
                marker      : false,
                polyline    : false,
            },
            edit: {
                featureGroup: drawnItems.current,
                remove      : true,
            },
        });

        map.addControl(drawControl);

        // ── Events ───────────────────────────────────────────────────────────

        const onCreated = (e) => {
            // Replace previous shape — one polygon at a time
            drawnItems.current.clearLayers();
            drawnItems.current.addLayer(e.layer);
            recalculate();
        };

        const onEdited  = () => recalculate();
        const onDeleted = () => onAreaCalculated(0);

        map.on(L.Draw.Event.CREATED, onCreated);
        map.on(L.Draw.Event.EDITED,  onEdited);
        map.on(L.Draw.Event.DELETED, onDeleted);

        function recalculate() {
            let totalSqft = 0;

            drawnItems.current.eachLayer((layer) => {
                if (layer instanceof L.Polygon) {
                    const latlngs = layer.getLatLngs()[0];
                    const areaSqm = calculatePolygonAreaSqm(latlngs);
                    totalSqft    += sqmToSqft(areaSqm);
                }
            });

            onAreaCalculated(Math.round(totalSqft));
        }

        return () => {
            map.removeControl(drawControl);
            map.removeLayer(drawnItems.current);
            map.off(L.Draw.Event.CREATED, onCreated);
            map.off(L.Draw.Event.EDITED,  onEdited);
            map.off(L.Draw.Event.DELETED, onDeleted);
        };
    }, [map, onAreaCalculated]);

    return null;
};

// ─── Area Math ────────────────────────────────────────────────────────────────

function calculatePolygonAreaSqm(latlngs) {
    const n = latlngs.length;
    if (n < 3) return 0;

    const avgLat             = latlngs.reduce((sum, p) => sum + p.lat, 0) / n;
    const metersPerDegreeLat = 111320;
    const metersPerDegreeLon = 111320 * Math.cos((avgLat * Math.PI) / 180);

    let area = 0;
    for (let i = 0; i < n; i++) {
        const j  = (i + 1) % n;
        const xi = latlngs[i].lng * metersPerDegreeLon;
        const yi = latlngs[i].lat * metersPerDegreeLat;
        const xj = latlngs[j].lng * metersPerDegreeLon;
        const yj = latlngs[j].lat * metersPerDegreeLat;
        area += xi * yj;
        area -= xj * yi;
    }

    return Math.abs(area) / 2;
}

function sqmToSqft(sqm) {
    return sqm * 10.7639;
}

// ─── Public Component ─────────────────────────────────────────────────────────

/**
 * DrawableMap
 *
 * Props:
 *   lat              {number}    Centre latitude (from geocoded address)
 *   lon              {number}    Centre longitude
 *   zoom             {number}    Initial zoom level (default 19)
 *   markerLabel      {string}    Popup text on the address pin
 *   onAreaCalculated {function}  Called with (sqft: number) on draw / edit / delete
 */
const DrawableMap = ({ lat, lon, zoom = 19, markerLabel = null, onAreaCalculated }) => {
    const center     = lat && lon ? [lat, lon] : [39.8283, -98.5795];
    const actualZoom = lat && lon ? zoom : 4;

    return (
        <MapContainer
            center={center}
            zoom={actualZoom}
            className="w-full h-full"
            zoomControl={true}
            scrollWheelZoom={true}
        >
            {/* Satellite base layer */}
            <TileLayer
                url="https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"
                attribution="Tiles &copy; Esri"
                maxZoom={20}
            />
            {/* Street name / label overlay */}
            <TileLayer
                url="https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}"
                maxZoom={20}
            />

            {/* Address pin — shows where we geocoded to */}
            {lat && lon && (
                <AddressMarker lat={lat} lon={lon} label={markerLabel} />
            )}

            {/* Drawing toolbar + polygon event handling */}
            <DrawingController onAreaCalculated={onAreaCalculated} />
        </MapContainer>
    );
};

export default DrawableMap;