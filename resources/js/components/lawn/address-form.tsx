import { useState, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { MapPin, ArrowRight, Mountain, Sprout, Thermometer, Droplets } from 'lucide-react';

// ─── Constants ────────────────────────────────────────────────────────────────

const FEATURES = [
    {
        id         : 'soil',
        title      : 'Regional soil profile',
        description: 'The average ratios of sand, silt, and clay in your area tell us what will grow best in your yard.',
        icon       : Mountain,
        color      : 'text-amber-700',
        bg         : 'bg-amber-50',
    },
    {
        id         : 'growth',
        title      : 'Growth potential',
        description: 'Based on grass type, temperature, and rainfall — helps us nail your fertilizer schedule.',
        icon       : Sprout,
        color      : 'text-green-700',
        bg         : 'bg-green-50',
    },
    {
        id         : 'temps',
        title      : 'Historical temps',
        description: 'Predicts when spring greenup starts and when to prep for heat stress.',
        icon       : Thermometer,
        color      : 'text-red-600',
        bg         : 'bg-red-50',
    },
    {
        id         : 'rainfall',
        title      : 'Historical rainfall',
        description: 'Tells us whether your lawn needs nutrients for dry or arid climates.',
        icon       : Droplets,
        color      : 'text-blue-600',
        bg         : 'bg-blue-50',
    },
];

// ─── Radio Option ─────────────────────────────────────────────────────────────

const RadioOption = ({ label, value, selectedValue, onChange }) => {
    const isSelected = selectedValue === value;
    return (
        <div
            onClick={() => onChange(value)}
            className={`
                relative flex items-center p-4 cursor-pointer rounded-xl border-2 transition-all duration-200 group bg-white
                ${isSelected
                    ? 'border-green-600 bg-green-50/40 shadow-sm'
                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'}
            `}
        >
            <div className={`
                w-5 h-5 rounded-full border-2 flex items-center justify-center mr-4 flex-shrink-0 transition-colors
                ${isSelected ? 'border-green-600' : 'border-gray-300 group-hover:border-gray-400'}
            `}>
                {isSelected && <div className="w-2.5 h-2.5 bg-green-600 rounded-full" />}
            </div>
            <span className={`font-semibold text-base ${isSelected ? 'text-green-900' : 'text-gray-700'}`}>
                {label}
            </span>
        </div>
    );
};

// ─── Main Component ───────────────────────────────────────────────────────────

/**
 * AddressForm
 *
 * Step 1: User either enters manual sq footage or an address for auto-detection.
 *
 * @param {string}   zipCode
 * @param {Function} onManualContinue      () => void  — after manual sq ft saved
 * @param {Function} onAddressCalculated   (props) => void  — after address resolved
 */
export default function AddressForm({ zipCode, onManualContinue, onAddressCalculated }) {
    const [hasLawnSize, setHasLawnSize] = useState(null);
    const { mapbox } = usePage().props;

    const satellitePreviewUrl = mapbox?.token
        ? `https://api.mapbox.com/styles/v1/mapbox/satellite-streets-v12/static/-98.5795,39.8283,3.5,0/800x600?access_token=${mapbox.token}`
        : null;

    const manualForm  = useForm({ source: 'manual',  square_feet: '' });
    const addressForm = useForm({ source: 'address', address: ''     });

    useEffect(() => {
        if (hasLawnSize === 'yes') document.getElementById('sqft-input')?.focus();
        if (hasLawnSize === 'no')  document.getElementById('address-input')?.focus();
    }, [hasLawnSize]);

    const handleManualSubmit = (e) => {
        e.preventDefault();
        manualForm.post(route('yard.size.store'), {
            onSuccess: () => onManualContinue(),
        });
    };

    const handleAddressSubmit = (e) => {
        e.preventDefault();
        addressForm.post(route('yard.size.store'), {
            onSuccess: (page) => onAddressCalculated(page.props),
        });
    };

    return (
        <div className="min-h-screen bg-white font-sans text-gray-900 pb-24">

            {/* Hero split */}
            <div className="flex flex-col lg:flex-row min-h-[60vh]">

                {/* Left: Form */}
                <div className="w-full lg:w-1/2 flex flex-col justify-center px-6 py-14 sm:px-14 lg:px-20 bg-white order-2 lg:order-1 relative z-10">
                    <div className="max-w-md mx-auto lg:mx-0 w-full">

                        {/* Zip badge */}
                        <div className="flex items-center gap-2 mb-7">
                            <MapPin size={14} className="text-green-600" />
                            <p className="text-xs font-bold tracking-widest text-gray-400 uppercase">
                                Zip: {zipCode}
                            </p>
                        </div>

                        <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-8 leading-tight">
                            Do you know your<br />lawn size?
                        </h1>

                        <div className="space-y-4">

                            {/* ── Yes path ─────────────────────────────────── */}
                            <RadioOption
                                label="Yes, I know it"
                                value="yes"
                                selectedValue={hasLawnSize}
                                onChange={setHasLawnSize}
                            />

                            {hasLawnSize === 'yes' && (
                                <form onSubmit={handleManualSubmit} className="pl-1 pt-1 pb-2 space-y-3">
                                    <p className="text-gray-700">
                                        <span className="font-bold text-gray-900">Awesome!</span> Enter your square footage below.
                                    </p>
                                    <div className="relative">
                                        <input
                                            id="sqft-input"
                                            type="number"
                                            value={manualForm.data.square_feet}
                                            onChange={(e) => manualForm.setData('square_feet', e.target.value)}
                                            placeholder="Area to treat"
                                            className="w-full p-4 border-2 border-gray-200 rounded-xl text-lg focus:border-green-600 focus:ring-4 focus:ring-green-600/10 outline-none transition-all bg-white text-gray-900 placeholder-gray-400 shadow-sm"
                                        />
                                        <span className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 text-sm font-medium">
                                            SQ. FT
                                        </span>
                                    </div>
                                    {manualForm.errors.square_feet && (
                                        <p className="text-red-500 text-sm">{manualForm.errors.square_feet}</p>
                                    )}
                                    {manualForm.data.square_feet && (
                                        <button
                                            type="submit"
                                            disabled={manualForm.processing}
                                            className="w-full bg-[#2E7D32] text-white font-bold text-base py-3.5 rounded-xl shadow flex items-center justify-center gap-2 disabled:opacity-50 transition-all hover:bg-[#256628]"
                                        >
                                            {manualForm.processing ? 'Saving...' : 'Continue'}
                                            <ArrowRight size={18} />
                                        </button>
                                    )}
                                </form>
                            )}

                            {/* ── No path ──────────────────────────────────── */}
                            <RadioOption
                                label="No, I don't know"
                                value="no"
                                selectedValue={hasLawnSize}
                                onChange={setHasLawnSize}
                            />

                            {hasLawnSize === 'no' && (
                                <form onSubmit={handleAddressSubmit} className="pl-1 pt-1 pb-2 space-y-3">
                                    <p className="text-gray-700">
                                        <span className="font-bold text-gray-900">No problem!</span> Enter your address and we'll detect it automatically.
                                    </p>
                                    <input
                                        id="address-input"
                                        type="text"
                                        value={addressForm.data.address}
                                        onChange={(e) => addressForm.setData('address', e.target.value)}
                                        placeholder="123 Main St, City, State"
                                        className="w-full p-4 border-2 border-gray-200 rounded-xl text-lg focus:border-green-600 focus:ring-4 focus:ring-green-600/10 outline-none transition-all bg-white text-gray-900 placeholder-gray-400 shadow-sm"
                                    />
                                    {addressForm.errors.address && (
                                        <p className="text-red-500 text-sm">{addressForm.errors.address}</p>
                                    )}
                                    {addressForm.data.address && (
                                        <button
                                            type="submit"
                                            disabled={addressForm.processing}
                                            className="w-full bg-[#2E7D32] text-white font-bold text-base py-3.5 rounded-xl shadow flex items-center justify-center gap-2 disabled:opacity-50 transition-all hover:bg-[#256628]"
                                        >
                                            {addressForm.processing ? (
                                                <>
                                                    <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                                                    Detecting your lawn...
                                                </>
                                            ) : (
                                                <>Find My Lawn Size <ArrowRight size={18} /></>
                                            )}
                                        </button>
                                    )}
                                </form>
                            )}
                        </div>
                    </div>
                </div>

                {/* Right: Static satellite map preview */}
                <div className="hidden lg:flex lg:w-1/2 order-1 lg:order-2 bg-gray-900 items-center justify-center relative overflow-hidden">
                    {satellitePreviewUrl ? (
                        <img
                            src={satellitePreviewUrl}
                            alt="Satellite map preview"
                            className="w-full h-full object-cover opacity-70"
                        />
                    ) : (
                        <div className="w-full h-full bg-gray-800" />
                    )}
                    <div className="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent pointer-events-none" />
                    <div className="absolute bottom-8 left-8 text-white">
                        <p className="text-sm font-bold tracking-widest uppercase opacity-70">Powered by</p>
                        <p className="text-2xl font-black tracking-tight">Satellite Imagery</p>
                    </div>
                </div>
            </div>

            {/* Features section */}
            <div className="max-w-7xl mx-auto px-6 py-16 sm:px-12 lg:py-24">
                <div className="text-center max-w-2xl mx-auto mb-14">
                    <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                        What your location tells us
                    </h2>
                    <p className="text-gray-500 text-base">
                        We analyse millions of data points specific to your neighbourhood to personalise your plan.
                    </p>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    {FEATURES.map((f) => (
                        <div key={f.id} className="flex flex-col items-start group">
                            <div className={`mb-5 p-4 rounded-2xl ${f.bg} ${f.color} transition-transform duration-300 group-hover:scale-110 shadow-sm`}>
                                <f.icon size={28} strokeWidth={1.5} />
                            </div>
                            <h3 className="text-base font-bold text-gray-900 mb-2">{f.title}</h3>
                            <p className="text-gray-500 leading-relaxed text-sm">{f.description}</p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}