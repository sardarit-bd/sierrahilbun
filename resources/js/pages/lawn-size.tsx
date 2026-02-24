import React, { useState, useEffect } from 'react';
import {
  Check,
  Mountain,
  Sprout,
  Thermometer,
  Droplets,
  MapPin,
  ArrowRight,
} from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import DrawableMap from '@/components/lawn/drawable-map';

// Fix Leaflet default marker icon (known issue with bundlers)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

// ─── Constants ────────────────────────────────────────────────────────────────

const FEATURES = [
  {
    id: 'soil',
    title: 'Regional soil profile',
    description:
      'The average ratios of sand, silt, and clay in your area tell us what will grow best in your yard—and what nutrients you need to succeed.',
    icon: Mountain,
    color: 'text-amber-700',
    bg: 'bg-amber-50',
  },
  {
    id: 'growth',
    title: 'Growth potential',
    description:
      'Growth potential is based on grass type, temperature, and rainfall. This timeline helps us determine the best application dates for your fertilizers.',
    icon: Sprout,
    color: 'text-green-700',
    bg: 'bg-green-50',
  },
  {
    id: 'temps',
    title: 'Historical temps',
    description:
      'This lets us predict when spring greenup will start, and when to prep for heat—so we can recommend the best time to apply nutrients.',
    icon: Thermometer,
    color: 'text-red-600',
    bg: 'bg-red-50',
  },
  {
    id: 'rainfall',
    title: 'Historical rainfall',
    description:
      "This helps us understand whether your lawn requires nutrients designed for dry or arid climates—and how much supplemental water you'll need.",
    icon: Droplets,
    color: 'text-blue-600',
    bg: 'bg-blue-50',
  },
];

const LOADING_STEPS = [
  { id: 1, label: 'Satellite imagery' },
  { id: 2, label: 'Property data' },
  { id: 3, label: 'Climate history' },
  { id: 4, label: 'Soil profile' },
];

// ─── Shared Components ────────────────────────────────────────────────────────

const RadioOption = ({ label, value, selectedValue, onChange }) => {
  const isSelected = selectedValue === value;
  return (
    <div
      onClick={() => onChange(value)}
      className={`
        relative flex items-center p-4 cursor-pointer rounded-xl border-2 transition-all duration-200 group bg-white
        ${isSelected
          ? 'border-green-600 bg-green-50/30 shadow-sm'
          : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'}
      `}
    >
      <div
        className={`w-6 h-6 rounded-full border-2 flex items-center justify-center mr-4 transition-colors flex-shrink-0
          ${isSelected ? 'border-green-600' : 'border-gray-300 group-hover:border-gray-400'}`}
      >
        {isSelected && <div className="w-3 h-3 bg-green-600 rounded-full" />}
      </div>
      <span className={`font-semibold text-lg ${isSelected ? 'text-green-900' : 'text-gray-700'}`}>
        {label}
      </span>
    </div>
  );
};

const SatelliteMap = ({ lat, lon, zoom = 18, markerLabel = null }) => {
  const center = lat && lon ? [lat, lon] : [39.8283, -98.5795];
  const actualZoom = lat && lon ? zoom : 4;

  return (
    <MapContainer
      center={center}
      zoom={actualZoom}
      className="w-full h-full"
      zoomControl={false}
      scrollWheelZoom={false}
    >
      {/* Satellite tiles — free, no API key */}
      <TileLayer
        url="https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}"
        attribution="Tiles &copy; Esri"
        maxZoom={20}
      />
      {/* Street/place name overlay */}
      <TileLayer
        url="https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}"
        maxZoom={20}
      />
      {lat && lon && markerLabel && (
        <Marker position={[lat, lon]}>
          <Popup>{markerLabel}</Popup>
        </Marker>
      )}
    </MapContainer>
  );
};

// ─── View 1: Form ─────────────────────────────────────────────────────────────

const LawnSizeView = ({ onManualContinue, onAddressCalculated, zipCode }) => {
  const [hasLawnSize, setHasLawnSize] = useState(null);

  const manualForm  = useForm({ source: 'manual', square_feet: '' });
  const addressForm = useForm({ source: 'address', address: '' });

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
    <div className="min-h-screen bg-white font-sans text-gray-900 pb-20">
      <div className="flex flex-col lg:flex-row min-h-[600px] lg:h-[65vh]">

        {/* Left: Form */}
        <div className="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 sm:px-16 lg:px-24 bg-white order-2 lg:order-1 relative z-10">
          <div className="max-w-md mx-auto lg:mx-0 w-full">
            <div className="flex items-center gap-2 mb-6">
              <MapPin size={16} className="text-green-600" />
              <p className="text-xs font-bold tracking-widest text-gray-500 uppercase">
                Zip Code: {zipCode}
              </p>
            </div>

            <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-8 leading-tight">
              Do you know your lawn size?
            </h1>

            <div className="space-y-4 mb-10">
              {/* Yes option */}
              <RadioOption
                label="Yes, I do!"
                value="yes"
                selectedValue={hasLawnSize}
                onChange={setHasLawnSize}
              />

              {hasLawnSize === 'yes' && (
                <form onSubmit={handleManualSubmit} className="pl-1 pt-2 pb-2">
                  <label htmlFor="sqft-input" className="block text-gray-700 text-lg mb-3">
                    <span className="font-bold text-gray-900">Awesome!</span> Enter your square footage
                  </label>
                  <div className="relative">
                    <input
                      id="sqft-input"
                      type="number"
                      value={manualForm.data.square_feet}
                      onChange={(e) => manualForm.setData('square_feet', e.target.value)}
                      placeholder="Area to treat (sq. ft)"
                      className="w-full p-4 border-2 border-gray-200 rounded-xl text-lg focus:border-green-600 focus:ring-4 focus:ring-green-600/10 outline-none transition-all duration-200 bg-white text-gray-900 placeholder-gray-400 shadow-sm"
                    />
                    <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400 text-sm font-medium">
                      SQ. FT
                    </div>
                  </div>
                  {manualForm.errors.square_feet && (
                    <p className="text-red-500 text-sm mt-2">{manualForm.errors.square_feet}</p>
                  )}
                  {manualForm.data.square_feet && (
                    <button
                      type="submit"
                      disabled={manualForm.processing}
                      className="mt-4 w-full bg-[#2E7D32] text-white font-bold text-lg py-3 rounded-lg shadow-md flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                      {manualForm.processing ? 'Saving...' : 'Continue'} <ArrowRight size={20} />
                    </button>
                  )}
                </form>
              )}

              {/* No option */}
              <RadioOption
                label="No, I don't"
                value="no"
                selectedValue={hasLawnSize}
                onChange={setHasLawnSize}
              />

              {hasLawnSize === 'no' && (
                <form onSubmit={handleAddressSubmit} className="pl-1 pt-2 pb-2">
                  <label htmlFor="address-input" className="block text-gray-700 text-lg mb-3">
                    <span className="font-bold text-gray-900">No problem!</span> Enter your address and we'll find it
                  </label>
                  <input
                    id="address-input"
                    type="text"
                    value={addressForm.data.address}
                    onChange={(e) => addressForm.setData('address', e.target.value)}
                    placeholder="123 Main St, City, State"
                    className="w-full p-4 border-2 border-gray-200 rounded-xl text-lg focus:border-green-600 focus:ring-4 focus:ring-green-600/10 outline-none transition-all duration-200 bg-white text-gray-900 placeholder-gray-400 shadow-sm"
                  />
                  {addressForm.errors.address && (
                    <p className="text-red-500 text-sm mt-2">{addressForm.errors.address}</p>
                  )}
                  {addressForm.data.address && (
                    <button
                      type="submit"
                      disabled={addressForm.processing}
                      className="mt-4 w-full bg-[#2E7D32] text-white font-bold text-lg py-3 rounded-lg shadow-md flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                      {addressForm.processing ? 'Calculating...' : 'Find My Lawn Size'} <ArrowRight size={20} />
                    </button>
                  )}
                </form>
              )}
            </div>
          </div>
        </div>

        {/* Right: Live satellite map (zoomed out, no marker) */}
        <div className="hidden lg:block lg:w-1/2 relative order-1 lg:order-2 overflow-hidden bg-gray-900">
          <SatelliteMap lat={null} lon={null} />
        </div>
      </div>

      {/* Features section */}
      <div className="max-w-7xl mx-auto px-6 py-16 sm:px-12 lg:py-24">
        <div className="text-center max-w-3xl mx-auto mb-16">
          <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Look at what your location can tell us
          </h2>
          <p className="text-gray-500 text-lg">
            We analyze millions of data points specific to your neighborhood to customize your plan.
          </p>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-12">
          {FEATURES.map((feature) => (
            <div key={feature.id} className="flex flex-col items-start group">
              <div
                className={`mb-6 p-4 rounded-2xl ${feature.bg} ${feature.color} transition-transform duration-300 group-hover:scale-110 shadow-sm`}
              >
                <feature.icon size={32} strokeWidth={1.5} />
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-3">{feature.title}</h3>
              <p className="text-gray-500 leading-relaxed text-sm lg:text-base">{feature.description}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

// ─── View 2: Confirm Area ─────────────────────────────────────────────────────

// const ConfirmAreaView = ({ squareFeet, estimated, matchedAddress, lat, lon, onConfirm, onBack }) => {
//   const [editedSqft, setEditedSqft] = useState(squareFeet);

//   const { data, setData, post, processing } = useForm({
//         square_feet: squareFeet, 
//     });

//   const handleConfirm = () => {
//         post(route('yard.size.confirm'), {
//             onSuccess: () => onConfirm(),
//         });
//     };

//   return (
//     <div className="relative h-[100dvh] w-full flex flex-col md:flex-row overflow-hidden bg-gray-100 font-sans">

//       {/* Left: Panel (Bottom on mobile) */}
//       <div className="relative z-20 w-full md:w-[480px] lg:w-[500px] flex-shrink-0 bg-white h-[60dvh] md:h-full shadow-2xl flex flex-col overflow-y-auto order-2 md:order-1">
//         <div className="p-8 md:p-12 flex flex-col md:justify-center md:h-full">
//           <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 leading-tight">
//             Confirm the area you want treated
//           </h1>

//           {/* Amber banner when estimate is used */}
//           {estimated && (
//             <div className="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
//               <span className="text-amber-500 mt-0.5 flex-shrink-0">
//                 <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
//                   <path
//                     fillRule="evenodd"
//                     d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
//                     clipRule="evenodd"
//                   />
//                 </svg>
//               </span>
//               <div>
//                 <p className="text-sm font-semibold text-amber-800">
//                   We couldn't find exact boundary data for your address
//                 </p>
//                 {matchedAddress && (
//                   <p className="text-sm text-amber-700 mt-1">
//                     Nearest match: <span className="font-semibold">{matchedAddress}</span>
//                   </p>
//                 )}
//                 <p className="text-sm text-amber-700 mt-1">
//                   No worries — we've used a typical estimate for your area. Adjust the square footage below if you know a better number, then continue.
//                 </p>
//               </div>
//             </div>
//           )}

//           <div className="mb-6">
//             <p className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">
//               Estimated yard size
//             </p>
//             <div className="text-5xl font-extrabold text-gray-900 tracking-tight">
//               {Number(squareFeet).toLocaleString()}
//               <span className="text-2xl text-gray-500 font-bold ml-1">sq. ft</span>
//             </div>
//           </div>

//           <div className="mb-8">
//             <label className="block text-xs font-semibold text-gray-500 mb-2">
//               Edit the area of lawn you want treated (in sq. ft)
//             </label>
//             <input
//               type="number"
//               value={data.square_feet}                          
//               onChange={(e) => setData('square_feet', e.target.value)}
//               className="w-full border border-gray-300 rounded-md px-4 py-3 text-lg font-semibold text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all"
//           />
//           </div>

//           <div className="space-y-3">
//             <button
//               onClick={handleConfirm}
//               disabled={processing}
//               className="w-full bg-[#2E7D32] hover:bg-[#256628] text-white font-bold py-4 rounded-lg shadow-lg transition-all text-lg disabled:opacity-50"
//             >
//               {processing ? 'Saving...' : 'Continue'}
//             </button>
//             <button
//               onClick={onBack}
//               className="w-full bg-white border-2 border-gray-200 text-gray-700 font-bold py-4 rounded-lg hover:bg-gray-50 transition-all text-lg"
//             >
//               Something's not right
//             </button>
//           </div>
//         </div>
//       </div>

//       {/* Right: Live satellite map (Top on mobile) */}
//       <div className="relative w-full h-[40dvh] md:h-auto md:flex-grow z-0 order-1 md:order-2">
//         <SatelliteMap
//           lat={lat}
//           lon={lon}
//           zoom={19}
//           markerLabel={matchedAddress || `Est. ${Number(squareFeet).toLocaleString()} sq ft`}
//         />
//       </div>
//     </div>
//   );
// };

// ─── Add this import at the top of your lawn-size.jsx ───────────────────────
// import DrawableMap from '@/components/lawn/drawable-map';
// (add alongside your other imports)

// ─── View 2: Confirm Area ─────────────────────────────────────────────────────

const ConfirmAreaView = ({ squareFeet, estimated, matchedAddress, lat, lon, onConfirm, onBack }) => {
    const [drawnSqft, setDrawnSqft] = useState(null);

    // What we show and submit:
    // - If user has drawn a polygon → use that
    // - Otherwise fall back to the auto-calculated value from the server
    const activeSqft = drawnSqft ?? squareFeet;
    const isDrawn    = drawnSqft !== null;

    const { data, setData, post, processing } = useForm({
        square_feet: squareFeet,
    });

    // Keep the form in sync whenever the active value changes
    useEffect(() => {
        setData('square_feet', activeSqft);
    }, [activeSqft]);

    const handleAreaCalculated = (sqft) => {
        setDrawnSqft(sqft > 0 ? sqft : null);
    };

    const handleConfirm = () => {
        post(route('yard.size.confirm'), {
            onSuccess: () => onConfirm(),
        });
    };

    return (
        <div className="relative h-[100dvh] w-full flex flex-col md:flex-row overflow-hidden bg-gray-100 font-sans">

            {/* Left: Panel (Bottom on mobile) */}
            <div className="relative z-20 w-full md:w-[480px] lg:w-[500px] flex-shrink-0 bg-white h-[60dvh] md:h-full shadow-2xl flex flex-col overflow-y-auto order-2 md:order-1">
                <div className="p-8 md:p-12 flex flex-col md:justify-center md:h-full">
                    <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3 leading-tight">
                        Confirm the area you want treated
                    </h1>

                    {/* Drawing instruction banner */}
                    <div className="mb-5 flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <span className="text-blue-500 mt-0.5 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                            </svg>
                        </span>
                        <p className="text-sm text-blue-800">
                            <span className="font-semibold">Draw your lawn on the map.</span> Use the polygon tool (top-right of map) to trace the area you want treated. Your drawing will update the square footage automatically.
                        </p>
                    </div>

                    {/* Amber banner when estimate is used */}
                    {estimated && !isDrawn && (
                        <div className="mb-5 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <span className="text-amber-500 mt-0.5 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                </svg>
                            </span>
                            <div>
                                <p className="text-sm font-semibold text-amber-800">
                                    We couldn't find exact boundary data for your address
                                </p>
                                {matchedAddress && (
                                    <p className="text-sm text-amber-700 mt-1">
                                        Nearest match: <span className="font-semibold">{matchedAddress}</span>
                                    </p>
                                )}
                                <p className="text-sm text-amber-700 mt-1">
                                    Please draw your lawn on the map for an accurate measurement.
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Square footage display */}
                    <div className="mb-2">
                        <p className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">
                            {isDrawn ? 'Drawn lawn size' : 'Estimated yard size'}
                        </p>
                        <div className="flex items-end gap-3">
                            <div className="text-5xl font-extrabold text-gray-900 tracking-tight">
                                {Number(activeSqft).toLocaleString()}
                                <span className="text-2xl text-gray-500 font-bold ml-1">sq. ft</span>
                            </div>
                            {/* Badge showing source */}
                            {isDrawn ? (
                                <span className="mb-2 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                    ✓ Drawn
                                </span>
                            ) : (
                                <span className="mb-2 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                    ~ Estimated
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Manual override input */}
                    <div className="mb-8">
                        <label className="block text-xs font-semibold text-gray-500 mb-2">
                            Or type a number manually
                        </label>
                        <input
                            type="number"
                            value={data.square_feet}
                            onChange={(e) => {
                                setDrawnSqft(null); // manual override clears drawn state
                                setData('square_feet', e.target.value);
                            }}
                            className="w-full border border-gray-300 rounded-md px-4 py-3 text-lg font-semibold text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all"
                        />
                    </div>

                    <div className="space-y-3">
                        <button
                            onClick={handleConfirm}
                            disabled={processing || !data.square_feet}
                            className="w-full bg-[#2E7D32] hover:bg-[#256628] text-white font-bold py-4 rounded-lg shadow-lg transition-all text-lg disabled:opacity-50"
                        >
                            {processing ? 'Saving...' : 'Continue'}
                        </button>
                        <button
                            onClick={onBack}
                            className="w-full bg-white border-2 border-gray-200 text-gray-700 font-bold py-4 rounded-lg hover:bg-gray-50 transition-all text-lg"
                        >
                            Something's not right
                        </button>
                    </div>
                </div>
            </div>

            {/* Right: Drawable satellite map (Top on mobile) */}
            <div className="relative w-full h-[40dvh] md:h-auto md:flex-grow z-0 order-1 md:order-2">
                <DrawableMap
                    lat={lat}
                    lon={lon}
                    zoom={19}
                    markerLabel={matchedAddress || `Est. ${Number(squareFeet).toLocaleString()} sq ft`}
                    onAreaCalculated={handleAreaCalculated}
                />
            </div>
        </div>
    );
};
// ─── View 3: Loading ──────────────────────────────────────────────────────────

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
          const isCompleted = completedSteps.includes(step.id);
          return (
            <div key={step.id} className="flex items-center gap-4">
              <div
                className={`w-7 h-7 rounded-full flex items-center justify-center transition-all duration-500 border-2
                  ${isCompleted ? 'bg-green-500 border-green-500' : 'bg-transparent border-green-500/40'}`}
              >
                <Check
                  size={16}
                  strokeWidth={4}
                  className={`text-white transition-all duration-300 ${isCompleted ? 'opacity-100' : 'opacity-0'}`}
                />
              </div>
              <span
                className={`text-lg sm:text-xl font-bold transition-colors duration-500
                  ${isCompleted ? 'text-gray-900' : 'text-gray-400'}`}
              >
                {step.label}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
};

// ─── Main Page ────────────────────────────────────────────────────────────────

export default function LawnSize({ zip_code }) {
  const [currentView, setCurrentView]       = useState('form');
  const [calculatedSqft, setCalculatedSqft] = useState(null);
  const [estimated, setEstimated]           = useState(false);
  const [matchedAddress, setMatchedAddress] = useState('');
  const [coords, setCoords]                 = useState({ lat: null, lon: null });

  const handleManualContinue = () => setCurrentView('loading');

  const handleAddressCalculated = (data) => {
    setCalculatedSqft(data.square_feet);
    setEstimated(data.estimated ?? false);
    setMatchedAddress(data.matched_address ?? '');
    setCoords({ lat: data.latitude, lon: data.longitude });
    setCurrentView('confirmArea');
  };

  const handleConfirmContinue = () => setCurrentView('loading');

  return (
    <AppHeaderLayout>
      <Head title="Lawn Size" />

      {currentView === 'form' && (
        <LawnSizeView
          zipCode={zip_code}
          onManualContinue={handleManualContinue}
          onAddressCalculated={handleAddressCalculated}
        />
      )}

      {currentView === 'confirmArea' && (
        <ConfirmAreaView
          squareFeet={calculatedSqft}
          estimated={estimated}
          matchedAddress={matchedAddress}
          lat={coords.lat}
          lon={coords.lon}
          onConfirm={handleConfirmContinue}
          onBack={() => setCurrentView('form')}
        />
      )}

      {currentView === 'loading' && <LoadingView />}
    </AppHeaderLayout>
  );
}