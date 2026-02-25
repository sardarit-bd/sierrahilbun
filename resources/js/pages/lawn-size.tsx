import { useState, useEffect } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Check } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import MapboxLawnMap from '@/components/lawn/mapbox-lawn-map';
import LawnSizePanel from '@/components/lawn/lawn-size-panel';
import AddressForm   from '@/components/lawn/address-form';
import useLawnPolygon from '@/hooks/use-lawn-polygon';

// ─── Loading steps ────────────────────────────────────────────────────────────

const LOADING_STEPS = [
    { id: 1, label: 'Satellite imagery'  },
    { id: 2, label: 'Property data'      },
    { id: 3, label: 'Climate history'    },
    { id: 4, label: 'Soil profile'       },
];

// ─── Loading View ─────────────────────────────────────────────────────────────

const LoadingView = () => {
    const [completedSteps, setCompletedSteps] = useState([]);

    useEffect(() => {
        let index = 0;
        const interval = setInterval(() => {
            if (index < LOADING_STEPS.length) {
                setCompletedSteps((prev) => [...prev, LOADING_STEPS[index].id]);
                index++;
            } else {
                clearInterval(interval);
                router.visit(route('yard.soil'));
            }
        }, 1000);
        return () => clearInterval(interval);
    }, []);

    return (
        <div className="bg-[#EFF5F3] flex flex-col items-center justify-center font-sans px-6 text-gray-900 py-24 min-h-[60vh]">
            <div className="text-center mb-10">
                <h1 className="text-3xl sm:text-4xl font-extrabold mb-2 text-gray-900">We're learning</h1>
                <p className="text-gray-600 text-base sm:text-lg font-medium">to help us create your lawn plan</p>
            </div>

            <div className="mb-12 relative">
                <div className="absolute inset-0 bg-green-400/30 blur-xl rounded-full transform scale-150 animate-pulse" />
                <div className="relative w-16 h-16 bg-green-600 rounded-md shadow-lg flex items-center justify-center z-10" />
            </div>

            <div className="flex flex-col items-start gap-5 w-full max-w-[280px]">
                {LOADING_STEPS.map((step) => {
                    const done = completedSteps.includes(step.id);
                    return (
                        <div key={step.id} className="flex items-center gap-4">
                            <div className={`w-7 h-7 rounded-full flex items-center justify-center transition-all duration-500 border-2
                                ${done ? 'bg-green-500 border-green-500' : 'bg-transparent border-green-500/40'}`}>
                                <Check size={16} strokeWidth={4} className={`text-white transition-all duration-300 ${done ? 'opacity-100' : 'opacity-0'}`} />
                            </div>
                            <span className={`text-lg sm:text-xl font-bold transition-colors duration-500 ${done ? 'text-gray-900' : 'text-gray-400'}`}>
                                {step.label}
                            </span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

// ─── Confirm Area View ────────────────────────────────────────────────────────

const ConfirmAreaView = ({
    squareFeet,
    estimated,
    matchedAddress,
    confidence,
    source,
    lat,
    lon,
    lawnPolygon,
    buildingPolygon,
    onConfirm,
    onBack,
}) => {
    // Shared props from HandleInertiaRequests
    const { mapbox } = usePage().props;

    const {
        activeSqft,
        sourceLabel,
        isDrawn,
        onPolygonDrawn,
        clearDrawn,
        onManualInput,
    } = useLawnPolygon(squareFeet, lawnPolygon);

    const { data, setData, post, processing } = useForm({
        square_feet: squareFeet,
    });

    // Keep form in sync with active sqft
    useEffect(() => {
        setData('square_feet', activeSqft);
    }, [activeSqft]);

    const handleConfirm = () => {
        post(route('yard.size.confirm'), {
            onSuccess: () => onConfirm(),
        });
    };

    return (
        <div className="relative h-[100dvh] w-full flex flex-col md:flex-row overflow-hidden bg-gray-100">

            {/* Left panel — bottom on mobile */}
            <div className="relative z-20 w-full md:w-[440px] lg:w-[480px] flex-shrink-0 bg-white shadow-2xl flex flex-col h-[55dvh] md:h-full order-2 md:order-1">
                <LawnSizePanel
                    activeSqft     ={activeSqft}
                    sourceLabel    ={sourceLabel}
                    estimated      ={estimated}
                    isDrawn        ={isDrawn}
                    matchedAddress ={matchedAddress}
                    confidence     ={confidence}
                    source         ={source}
                    processing     ={processing}
                    onManualInput  ={onManualInput}
                    onConfirm      ={handleConfirm}
                    onBack         ={onBack}
                    onClearDrawn   ={clearDrawn}
                />
            </div>

            {/* Right map — top on mobile */}
            <div className="relative w-full h-[45dvh] md:h-auto md:flex-grow z-0 order-1 md:order-2">
                {mapbox?.token ? (
                    <MapboxLawnMap
                        token          ={mapbox.token}
                        styleId        ={mapbox.styleId}
                        lat            ={lat}
                        lon            ={lon}
                        zoom           ={19}
                        lawnPolygon    ={lawnPolygon    ?? []}
                        buildingPolygon={buildingPolygon ?? []}
                        onPolygonDrawn ={onPolygonDrawn}
                        className      ="w-full h-full"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center bg-gray-200">
                        <p className="text-gray-500 text-sm font-medium">
                            Map unavailable — Mapbox token not configured.
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
};

// ─── Main Page ────────────────────────────────────────────────────────────────

export default function LawnSize({ zip_code }) {
    const [currentView,      setCurrentView]      = useState('form');
    const [calculatedSqft,   setCalculatedSqft]   = useState(null);
    const [estimated,        setEstimated]         = useState(false);
    const [matchedAddress,   setMatchedAddress]    = useState('');
    const [confidence,       setConfidence]        = useState('high');
    const [source,           setSource]            = useState(null);
    const [coords,           setCoords]            = useState({ lat: null, lon: null });
    const [lawnPolygon,      setLawnPolygon]       = useState([]);
    const [buildingPolygon,  setBuildingPolygon]   = useState([]);

    const handleManualContinue = () => setCurrentView('loading');

    const handleAddressCalculated = (props) => {
        setCalculatedSqft(props.square_feet);
        setEstimated(props.estimated   ?? false);
        setMatchedAddress(props.matched_address ?? '');
        setConfidence(props.confidence  ?? 'high');
        setSource(props.source       ?? null);
        setCoords({ lat: props.latitude, lon: props.longitude });
        setLawnPolygon(props.lawn_polygon      ?? []);
        setBuildingPolygon(props.building_polygon ?? []);
        setCurrentView('confirmArea');
    };

    return (
        <AppHeaderLayout>
            <Head title="Lawn Size" />

            {currentView === 'form' && (
                <AddressForm
                    zipCode             ={zip_code}
                    onManualContinue    ={handleManualContinue}
                    onAddressCalculated ={handleAddressCalculated}
                />
            )}

            {currentView === 'confirmArea' && (
                <ConfirmAreaView
                    squareFeet     ={calculatedSqft}
                    estimated      ={estimated}
                    matchedAddress ={matchedAddress}
                    confidence     ={confidence}
                    source         ={source}
                    lat            ={coords.lat}
                    lon            ={coords.lon}
                    lawnPolygon    ={lawnPolygon}
                    buildingPolygon={buildingPolygon}
                    onConfirm      ={() => setCurrentView('loading')}
                    onBack         ={() => setCurrentView('form')}
                />
            )}

            {currentView === 'loading' && <LoadingView />}
        </AppHeaderLayout>
    );
}