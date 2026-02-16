import React, { useState, useEffect } from 'react';
import { 
  Check, 
  ChevronDown, 
  Info, 
  Star, 
  Bug, 
  Sprout, 
  Flower, 
  ShoppingCart,
  ShieldCheck,
  HelpCircle
} from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, usePage } from '@inertiajs/react';

// --- Helpers ---

// Helper to resolve URL from DB path
const resolveUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    
    // Check if it's just a filename (no slashes) - assume plan-features directory
    if (!path.includes('/')) {
        return `/storage/plan-features/${path}`;
    } 
    
    // If it's a relative path like 'plan-features/img.png', prepend /storage/
    if (!path.startsWith('/storage') && !path.startsWith('storage')) {
        return `/storage/${path}`;
    }
    
    // Ensure leading slash if it starts with storage
    return path.startsWith('/') ? path : `/${path}`;
};

// Helper to map DB features to UI assets
const mapFeatureToAsset = (feature, index) => {
    const assets = [
        { 
            color: 'bg-green-100 text-green-800', 
            img: 'https://images.unsplash.com/photo-1605117882932-f9e32b03fea9?q=80&w=1019&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            defaultTag: 'Essential'
        },
        { 
            color: 'bg-green-100 text-green-800', 
            img: 'https://images.unsplash.com/photo-1621778029697-e648b727ddc7?q=80&w=828&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            defaultTag: 'Control'
        },
        { 
            color: 'bg-green-100 text-green-800', 
            img: 'https://images.unsplash.com/photo-1590682680695-43b964a3ae17?auto=format&fit=crop&q=80&w=200',
            defaultTag: 'Growth'
        },
        { 
            color: 'bg-[#2A9D8F] text-white', 
            img: 'https://plus.unsplash.com/premium_photo-1729087867520-6b9a869ed39a?q=80&w=735&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            defaultTag: 'Analysis'
        },
        { 
            color: 'bg-orange-100 text-orange-800', 
            img: 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&q=80&w=200',
            defaultTag: 'Bonus'
        }
    ];

    // Cycle through assets based on index to ensure every feature has a fallback style
    const asset = assets[index % assets.length];

    // 1. Resolve Icon (displayed on the card left)
    // Use DB icon_url if present, otherwise use the fallback asset image (placeholder)
    const iconUrl = feature.icon_url ? resolveUrl(feature.icon_url) : asset.img;

    // 2. Resolve Expanded Image (displayed in description)
    // Only use if DB image_url is present. No placeholder used here.
    const expandedImageUrl = feature.image_url ? resolveUrl(feature.image_url) : null;

    return {
        ...feature,
        tag: feature.tag || asset.defaultTag, 
        tagColor: asset.color,
        displayIcon: iconUrl,
        displayImage: expandedImageUrl
    };
};

// --- Sub-Components ---

const ProductCard = ({ feature, index }) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const visualFeature = mapFeatureToAsset(feature, index);

  return (
    <div className="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
      <div className="flex gap-4">
        {/* Image / Icon (Uses placeholder fallback) */}
        <div className="w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
          <img src={visualFeature.displayIcon} alt={feature.title} className="w-full h-full object-cover" />
        </div>

        {/* Content */}
        <div className="flex-grow">
          <div className="flex flex-wrap items-center gap-2 mb-1">
            <span className={`px-2 py-0.5 rounded text-[10px] sm:text-xs font-bold uppercase tracking-wide ${visualFeature.tagColor}`}>
              {visualFeature.tag}
            </span>
          </div>
          
          <h4 className="text-sm sm:text-base font-bold text-gray-900 leading-snug mb-1">
            {feature.title}
          </h4>

          {/* Expandable Description */}
          <div className={`overflow-hidden transition-all duration-500 ease-in-out ${isExpanded ? 'max-h-[600px] opacity-100 mt-2' : 'max-h-0 opacity-0'}`}>
            <p className="text-sm text-gray-600 mb-2">
              {feature.subtitle || 'No description available.'}
            </p>
            {/* Display small image only if it exists in DB (No placeholder) */}
            {visualFeature.displayImage && (
                <img 
                  src={visualFeature.displayImage} 
                  alt={feature.title} 
                  className="w-32 h-32 rounded-lg shadow-sm border border-gray-100 object-cover mt-2" 
                />
            )}
          </div>
          
          <button 
            onClick={() => setIsExpanded(!isExpanded)}
            className="mt-2 text-xs font-bold text-gray-400 flex items-center gap-1 hover:text-green-700 transition-colors"
          >
            {isExpanded ? 'See less' : 'See more'} <ChevronDown size={14} className={`transform transition-transform ${isExpanded ? 'rotate-180' : ''}`} />
          </button>
        </div>
      </div>
    </div>
  );
};

const ToggleSwitch = ({ enabled, onToggle }) => (
  <button 
    onClick={onToggle}
    className={`relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-300 focus:outline-none ${enabled ? 'bg-green-700' : 'bg-gray-300'}`}
  >
    <span className={`inline-block h-6 w-6 transform rounded-full bg-white transition duration-300 ease-in-out shadow-sm ${enabled ? 'translate-x-7' : 'translate-x-1'}`} />
  </button>
);

// --- Main Page Component ---

export default function LawnPlanPage({ lawnService, pestService, gardenService }) {
  const [isLoading, setIsLoading] = useState(true);
  
  // State for selections
  // Default to the 'Recommended' plan (is_recommended=1) or the first one
  const defaultLawnPlan = lawnService?.plans?.find(p => p.is_recommended) || lawnService?.plans?.[0];
  const [selectedLawnPlanId, setSelectedLawnPlanId] = useState(defaultLawnPlan?.id);
  
  const [pestPlanEnabled, setPestPlanEnabled] = useState(true);

  // Derived state
  const selectedLawnPlan = lawnService?.plans?.find(p => p.id === Number(selectedLawnPlanId));
  const selectedPestPlan = pestService?.plans?.[0]; // Assuming first pest plan is the main one for the toggle

  // Simulate loading
  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 1500);
    return () => clearTimeout(timer);
  }, []);

  // Pricing Calculation
  
  const lawnYearly = parseFloat(selectedLawnPlan?.current_price_yearly || 0);
  const pestYearly = parseFloat(selectedPestPlan?.current_price_yearly || 0);
  
  const lawnToday = lawnYearly / 12; // Example installment logic
  const pestToday = pestYearly / 12;

  const totalYearly = lawnYearly + (pestPlanEnabled ? pestYearly : 0);
  const totalToday = lawnToday + (pestPlanEnabled ? pestToday : 0);

  if (isLoading) {
    return (
      <AppHeaderLayout>
        <Head title="Building your plan" />
        <div className="flex-grow flex flex-col items-center justify-center bg-gray-50 animate-fade-in min-h-[60vh]">
          <h2 className="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-6">
            Building your plan
          </h2>
          <div className="flex gap-2">
            <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-75"></div>
            <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-150"></div>
            <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-300"></div>
          </div>
          <p className="mt-8 text-gray-500 font-medium animate-pulse">
            Analyzing soil data...
          </p>
        </div>
      </AppHeaderLayout>
    );
  }

  return (
    <AppHeaderLayout>
      <Head title="Your Custom Plan" />
      <div className="flex-grow container mx-auto px-4 py-8 lg:px-8 max-w-7xl animate-fade-in-up">
        
        {/* Page Header */}
        <div className="mb-8">
          <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-2">
            Custom yard plan ready!
          </h1>
          <p className="text-gray-600 max-w-3xl leading-relaxed">
            Tailored to your soil, climate, and yard condition at <span className="font-bold text-gray-900">3,500 sq feet</span>, 
            your all-in-one plan combines easy to use products with expert guidance.
            All delivered for free. <a href="#" className="underline text-green-700 hover:text-green-800">See FAQs</a>
          </p>
        </div>

        <div className="flex flex-col lg:flex-row gap-8 relative">
          
          {/* --- LEFT COLUMN: Plans --- */}
          <div className="w-full lg:w-2/3 space-y-10">
            
            {/* 1. LAWN PLAN SECTION */}
            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
              {/* Header */}
              <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700">
                    <Sprout size={24} />
                  </div>
                  <h2 className="text-xl font-bold text-gray-900">1. Your lawn plan</h2>
                </div>
                <div className="bg-green-600 text-white rounded-full p-1">
                  <Check size={16} strokeWidth={3} />
                </div>
              </div>

              {/* Sub-Header / Dropdown Selector */}
              <div className="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div className="relative w-full sm:max-w-xs">
                  <select 
                    value={selectedLawnPlanId}
                    onChange={(e) => setSelectedLawnPlanId(Number(e.target.value))}
                    className="appearance-none w-full bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 rounded-lg text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-green-500 cursor-pointer"
                  >
                    {lawnService?.plans?.map(plan => (
                        <option key={plan.id} value={plan.id}>
                            {plan.name} {plan.is_recommended ? '— Recommended' : ''}
                        </option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none" size={16} />
                </div>
                <div className="text-right">
                  <div className="text-lg font-bold text-gray-900">${lawnToday.toFixed(2)} <span className="text-sm font-normal text-gray-500">today</span> <Info size={12} className="inline text-gray-400" /></div>
                  <div className="text-xs text-gray-500">${lawnYearly.toFixed(2)} total for the year</div>
                </div>
              </div>

              <div className="px-6 py-3 bg-green-50/50 text-xs sm:text-sm font-medium text-green-800 border-b border-gray-100">
                {selectedLawnPlan?.description}
              </div>

              {/* Products/Features List */}
              <div className="p-6 space-y-4 bg-gray-50/30">
                {selectedLawnPlan?.features?.map((feature, index) => (
                  <ProductCard key={feature.id} feature={feature} index={index} />
                ))}
              </div>
            </div>

            {/* 2. PEST PLAN SECTION */}
            {selectedPestPlan && (
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
                    <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-700">
                        <Bug size={24} />
                    </div>
                    <h2 className="text-xl font-bold text-gray-900">2. Your pest plan</h2>
                    </div>
                    <ToggleSwitch enabled={pestPlanEnabled} onToggle={() => setPestPlanEnabled(!pestPlanEnabled)} />
                </div>

                {/* Pricing & Content */}
                <div className={`transition-all duration-300 ${pestPlanEnabled ? 'opacity-100' : 'opacity-50 grayscale'}`}>
                    <div className="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="relative w-full sm:max-w-xs">
                        <div className="w-full bg-white border border-gray-200 px-4 py-2 rounded-lg text-gray-700 font-medium flex justify-between items-center">
                            {selectedPestPlan.name}
                            <ChevronDown size={16} className="text-gray-400" />
                        </div>
                        </div>
                        <div className="text-right">
                        <div className="text-lg font-bold text-gray-900">${pestToday.toFixed(2)} <span className="text-sm font-normal text-gray-500">today</span> <Info size={12} className="inline text-gray-400" /></div>
                        <div className="text-xs text-gray-500">${pestYearly.toFixed(2)} total for the year</div>
                        </div>
                    </div>

                    <div className="px-6 py-4 text-sm text-gray-600">
                        {selectedPestPlan.description}
                    </div>

                    <div className="p-6 space-y-4 bg-gray-50/30">
                    {selectedPestPlan.features.map((feature, index) => (
                        <ProductCard key={feature.id} feature={feature} index={index + 2} /> // Offset index for diverse colors
                    ))}
                    
                    <div className="text-center mt-4">
                        <span className="text-xs font-bold text-green-700 tracking-wide uppercase flex items-center justify-center gap-1 cursor-pointer hover:underline">
                            ••• See pest shipping dates in your cart •••
                        </span>
                    </div>
                    </div>
                </div>
                </div>
            )}

             {/* 3. GARDEN SECTION (Upsell) */}
             {gardenService && (
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:border-green-200 transition-colors cursor-pointer group">
                    <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-700 group-hover:scale-110 transition-transform">
                        <Flower size={24} />
                    </div>
                    <h2 className="text-xl font-bold text-gray-900">3. Add garden care</h2>
                    </div>
                    <button className="bg-green-700 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-800 transition-colors shadow-sm">
                        Start
                    </button>
                </div>
             )}

             {/* FAQ Section */}
             <div className="bg-[#E0F2F1] rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div className="flex items-center gap-4">
                   <div className="bg-[#26A69A] text-white rounded-full p-2">
                      <HelpCircle size={28} />
                   </div>
                   <span className="text-lg font-bold text-gray-900">Still have questions?</span>
                </div>
                <button className="w-full sm:w-auto bg-white border border-gray-300 text-gray-700 font-bold px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                   See all FAQs
                </button>
             </div>

          </div>

          {/* --- RIGHT COLUMN: Sticky Sidebar --- */}
          <div className="w-full lg:w-1/3 relative">
            <div className="lg:sticky lg:top-24 space-y-4">
              
              {/* Order Summary Card */}
              <div className="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div className="bg-gray-50 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                   <h3 className="font-bold text-gray-800 flex items-center gap-2">
                     <ShoppingCart size={18} /> Review plan
                   </h3>
                   <div className="flex gap-1">
                      <Sprout size={16} className="text-green-600" />
                      {pestPlanEnabled && <Bug size={16} className="text-blue-600" />}
                   </div>
                </div>

                <div className="p-6">
                  <div className="flex justify-between items-baseline mb-1">
                    <span className="text-gray-600 font-medium">Total due today</span>
                    <span className="text-3xl font-extrabold text-gray-900">${totalToday.toFixed(2)}</span>
                  </div>
                  <div className="text-right text-xs text-gray-500 mb-6">
                    ${totalYearly.toFixed(2)} total for the year <Info size={10} className="inline" />
                  </div>

                  <button className="w-full bg-[#FFB84D] hover:bg-[#ffad33] text-gray-900 font-extrabold text-lg py-4 rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98] mb-4">
                    Add to cart
                  </button>
                  
                  <div className="flex items-center justify-center gap-2 text-sm text-gray-600 mb-4">
                    <span className="font-bold flex items-center gap-1">4.3 <div className="flex text-[#FFB84D]"><Star size={12} fill="currentColor" /><Star size={12} fill="currentColor" /><Star size={12} fill="currentColor" /><Star size={12} fill="currentColor" /><Star size={12} fill="currentColor" className="opacity-50" /></div></span>
                    <span className="underline cursor-pointer hover:text-green-700 text-green-600 font-medium">Trusted by 10,000+</span>
                  </div>

                  <p className="text-[10px] text-gray-400 text-center leading-tight">
                    Your fertilizer selection and timing may be updated based on real-time weather and soil analysis to ensure your lawn gets exactly what it needs. Yearly plan renews each spring. Cancel anytime.
                  </p>
                </div>
              </div>

              {/* Trust/Guarantee Mini Card */}
              <div className="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex items-center gap-3">
                 <ShieldCheck className="text-green-600 flex-shrink-0" size={24} />
                 <p className="text-xs text-gray-600 font-medium">
                   <span className="font-bold text-gray-900 block mb-0.5">Grass Growth Guarantee</span>
                   If you're not happy, we'll make it right.
                 </p>
              </div>

            </div>
          </div>

        </div>
      </div>
    </AppHeaderLayout>
  );
}