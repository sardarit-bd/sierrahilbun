import React, { useState, useEffect, useRef } from 'react';
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
  HelpCircle,
  Award
} from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import AddToCartButton from '@/components/AddToCartButton';

/**
 * --- Premium Select Component ---
 * A high-fidelity, custom dropdown designed for a premium feel.
 */
const PremiumPlanDropdown = ({ options, value, onChange, recommendedTier, label = "Select Plan" }) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef(null);
  
  const selectedOption = options.find(opt => opt.id === Number(value)) || options[0];

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);
  

  return (
    <div className="relative w-full sm:max-w-xs" ref={dropdownRef}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className={`
          group flex items-center justify-between w-full px-4 py-2.5 bg-white 
          border transition-all duration-300 rounded-lg text-left
          ${isOpen 
            ? 'border-green-600 ring-4 ring-green-50 shadow-sm' 
            : 'border-gray-300 hover:border-gray-400 shadow-sm'
          }
        `}
      >
        <div className="flex flex-col">
          <span className="text-[9px] uppercase tracking-wider font-bold text-gray-400 mb-0.5">
            {label}
          </span>
          <div className="flex items-center gap-2">
            <span className="font-bold text-gray-800 text-sm sm:text-base leading-none">
              {selectedOption?.name}
            </span>
            {selectedOption?.target_audience === recommendedTier && (
              <span className="bg-green-600 text-white text-[8px] px-1.5 py-0.5 rounded-full font-bold uppercase tracking-tighter">
                Best Fit
              </span>
            )}
          </div>
        </div>
        <ChevronDown 
          size={18} 
          className={`text-gray-400 transition-transform duration-300 ${isOpen ? 'rotate-180 text-green-600' : ''}`} 
        />
      </button>

      {/* Dropdown Menu */}
      {isOpen && (
        <div className="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
          <div className="p-1 max-h-72 overflow-y-auto">
            {options.map((plan) => {
              const isSelected = plan.id === Number(value);
              const isRecommended = plan.target_audience === recommendedTier;
              
              return (
                <button
                  key={plan.id}
                  onClick={() => {
                    onChange(plan.id);
                    setIsOpen(false);
                  }}
                  className={`
                    flex flex-col w-full px-4 py-3 text-left transition-colors rounded-lg mb-0.5 last:mb-0
                    ${isSelected ? 'bg-green-50' : 'hover:bg-gray-50'}
                  `}
                >
                  <div className="flex items-center justify-between w-full mb-0.5">
                    <div className="flex items-center gap-2">
                      <span className={`font-bold text-sm sm:text-base ${isSelected ? 'text-green-800' : 'text-gray-900'}`}>
                        {plan.name}
                      </span>
                      {isRecommended && (
                        <span className="bg-green-100 text-green-700 text-[9px] px-2 py-0.5 rounded-full font-bold uppercase flex items-center gap-1">
                          <Award size={10} /> Recommended
                        </span>
                      )}
                    </div>
                    {isSelected && <Check size={14} className="text-green-600" strokeWidth={3} />}
                  </div>
                  {plan.description && (
                    <span className="text-xs text-gray-500 line-clamp-1 italic">
                      {plan.description}
                    </span>
                  )}
                </button>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};

// --- Helpers ---

const resolveUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    if (!path.includes('/')) return `/storage/plan-features/${path}`;
    if (!path.startsWith('/storage') && !path.startsWith('storage')) return `/storage/${path}`;
    return path.startsWith('/') ? path : `/${path}`;
};

const mapFeatureToAsset = (feature, index) => {
    const assets = [
        { color: 'bg-green-100 text-green-800',   img: 'https://images.unsplash.com/photo-1605117882932-f9e32b03fea9?q=80&w=1019&auto=format&fit=crop', defaultTag: 'Essential' },
        { color: 'bg-green-100 text-green-800',   img: 'https://images.unsplash.com/photo-1621778029697-e648b727ddc7?q=80&w=828&auto=format&fit=crop',  defaultTag: 'Control'   },
        { color: 'bg-green-100 text-green-800',   img: 'https://images.unsplash.com/photo-1590682680695-43b964a3ae17?auto=format&fit=crop&q=80&w=200',   defaultTag: 'Growth'    },
        { color: 'bg-[#2A9D8F] text-white',       img: 'https://plus.unsplash.com/premium_photo-1729087867520-6b9a869ed39a?q=80&w=735&auto=format&fit=crop', defaultTag: 'Analysis' },
        { color: 'bg-orange-100 text-orange-800', img: 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&q=80&w=200',     defaultTag: 'Bonus'     },
    ];
    const asset          = assets[index % assets.length];
    const iconUrl        = feature.icon_url ? resolveUrl(feature.icon_url) : asset.img;
    const expandedImageUrl = feature.image_url ? resolveUrl(feature.image_url) : null;
    return { ...feature, tag: feature.tag || asset.defaultTag, tagColor: asset.color, displayIcon: iconUrl, displayImage: expandedImageUrl };
};

// --- Sub-Components ---

const ProductCard = ({ feature, index }) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const visualFeature = mapFeatureToAsset(feature, index);

  return (
    <div className="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">
      <div className="flex gap-4">
        <div className="w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
          <img src={visualFeature.displayIcon} alt={feature.title} className="w-full h-full object-cover" />
        </div>
        <div className="flex-grow">
          <div className="flex flex-wrap items-center gap-2 mb-1">
            <span className={`px-2 py-0.5 rounded text-[10px] sm:text-xs font-bold uppercase tracking-wide ${visualFeature.tagColor}`}>
              {visualFeature.tag}
            </span>
          </div>
          <h4 className="text-sm sm:text-base font-bold text-gray-900 leading-snug mb-1">{feature.title}</h4>
          <div className={`overflow-hidden transition-all duration-500 ease-in-out ${isExpanded ? 'max-h-[600px] opacity-100 mt-2' : 'max-h-0 opacity-0'}`}>
            <p className="text-sm text-gray-600 mb-2">{feature.subtitle || 'No description available.'}</p>
            {visualFeature.displayImage && (
              <img src={visualFeature.displayImage} alt={feature.title} className="w-32 h-32 rounded-lg shadow-sm border border-gray-100 object-cover mt-2" />
            )}
          </div>
          <button onClick={() => setIsExpanded(!isExpanded)} className="mt-2 text-xs font-bold text-gray-400 flex items-center gap-1 hover:text-green-700 transition-colors">
            {isExpanded ? 'See less' : 'See more'} <ChevronDown size={14} className={`transform transition-transform ${isExpanded ? 'rotate-180' : ''}`} />
          </button>
        </div>
      </div>
    </div>
  );
};

const ToggleSwitch = ({ enabled, onToggle }) => (
  <button onClick={onToggle} className={`relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-300 focus:outline-none ${enabled ? 'bg-green-700' : 'bg-gray-300'}`}>
    <span className={`inline-block h-6 w-6 transform rounded-full bg-white transition duration-300 ease-in-out shadow-sm ${enabled ? 'translate-x-7' : 'translate-x-1'}`} />
  </button>
);

// --- Main Page Component ---

export default function App({ assessment, recommended_plans, all_plans, tiers }) {
  const [isLoading, setIsLoading]       = useState(true);
  const [pestPlanEnabled, setPestPlanEnabled] = useState(true);

  // Derive lawn plans list
  const lawnPlansMap    = all_plans?.lawn ?? {};
  const lawnPlansList   = Object.values(lawnPlansMap);
  const recommendedLawnTier = tiers?.lawn ?? 'bronze';
  const defaultLawnPlan = lawnPlansMap[recommendedLawnTier] ?? lawnPlansList[0];
  const [selectedLawnPlanId, setSelectedLawnPlanId] = useState(defaultLawnPlan?.id);
  const selectedLawnPlan = lawnPlansList.find(p => p.id === Number(selectedLawnPlanId)) ?? defaultLawnPlan;

  // Pest plan
  const pestPlansMap        = all_plans?.pest ?? {};
  const pestPlansList       = Object.values(pestPlansMap);
  const recommendedPestTier = tiers?.pest ?? 'bronze';
  const defaultPestPlan     = pestPlansMap[recommendedPestTier] ?? pestPlansList[0];
  const [selectedPestPlanId, setSelectedPestPlanId] = useState(defaultPestPlan?.id);
  const selectedPestPlan = pestPlansList.find(p => p.id === Number(selectedPestPlanId)) ?? defaultPestPlan;

  const hasPest   = assessment?.selected_services?.includes('pest')   ?? false;
  const hasGarden = assessment?.selected_services?.includes('garden') ?? false;

  useEffect(() => {
    const timer = setTimeout(() => setIsLoading(false), 1000);
    return () => clearTimeout(timer);
  }, []);

  const lawnYearly  = parseFloat(selectedLawnPlan?.current_price_yearly ?? selectedLawnPlan?.base_price_yearly ?? 0);
  const pestYearly  = parseFloat(selectedPestPlan?.current_price_yearly ?? selectedPestPlan?.base_price_yearly ?? 0);
  const lawnToday   = lawnYearly / 12;
  const pestToday   = pestYearly / 12;
  const totalYearly = lawnYearly + (pestPlanEnabled && hasPest ? pestYearly : 0);
  const totalToday  = lawnToday  + (pestPlanEnabled && hasPest ? pestToday  : 0);

  if (isLoading) {
    return (
      <div className="flex-grow flex flex-col items-center justify-center bg-gray-50 min-h-screen">
        <h2 className="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-6">Building your plan</h2>
        <div className="flex gap-2">
          <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-75"></div>
          <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-150"></div>
          <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-300"></div>
        </div>
      </div>
    );
  }

  const cartProduct = {
    id: `bundle-${selectedLawnPlanId}${pestPlanEnabled && hasPest ? `-${selectedPestPlanId}` : ''}`,
    name: pestPlanEnabled && hasPest
      ? `${selectedLawnPlan?.name} + ${selectedPestPlan?.name}`
      : selectedLawnPlan?.name,
    title: selectedLawnPlan?.name,
    image: 'https://images.unsplash.com/photo-1605117882932-f9e32b03fea9?q=80&w=300&auto=format&fit=crop',
    price: totalToday,
    price_yearly: totalYearly,
    plans: {
      lawn: selectedLawnPlan,
      ...(pestPlanEnabled && hasPest ? { pest: selectedPestPlan } : {}),
    },
  };
  return (
    <AppHeaderLayout>
      <div className="min-h-screen bg-gray-50 selection:bg-green-100">
        <div className="container mx-auto px-4 py-8 lg:px-8 max-w-7xl">
          
          {/* Page Header */}
          <div className="mb-8">
            <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-2">Custom yard plan ready!</h1>
            <p className="text-gray-600 max-w-3xl leading-relaxed">
              Tailored to your soil, climate, and yard condition at{' '}
              <span className="font-bold text-gray-900">
                {Number(assessment?.square_feet ?? 0).toLocaleString()} sq feet
              </span>,{' '}
              your all-in-one plan combines easy to use products with expert guidance.
              All delivered for free.{' '}
              <a href="#" className="underline text-green-700 hover:text-green-800">See FAQs</a>
            </p>
          </div>

          <div className="flex flex-col lg:flex-row gap-8 relative">
            
            {/* LEFT COLUMN */}
            <div className="w-full lg:w-2/3 space-y-10">
              
              {/* 1. LAWN PLAN */}
              <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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

                <div className="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-visible">
                  {/* Premium Dropdown */}
                  <PremiumPlanDropdown 
                    options={lawnPlansList}
                    value={selectedLawnPlanId}
                    onChange={setSelectedLawnPlanId}
                    recommendedTier={recommendedLawnTier}
                    label="Selected Tier"
                  />

                  <div className="text-right">
                    <div className="text-lg font-bold text-gray-900">
                      ${lawnToday.toFixed(2)} <span className="text-sm font-normal text-gray-500">today</span>{' '}
                      <Info size={12} className="inline text-gray-400" />
                    </div>
                    <div className="text-xs text-gray-500">${lawnYearly.toFixed(2)} total for the year</div>
                  </div>
                </div>

                <div className="px-6 py-3 bg-green-50/50 text-xs sm:text-sm font-medium text-green-800 border-b border-gray-100">
                  {selectedLawnPlan?.description}
                </div>

                <div className="p-6 space-y-4 bg-gray-50/30">
                  {(selectedLawnPlan?.features ?? []).map((feature, index) => (
                    <ProductCard key={index} feature={feature} index={index} />
                  ))}
                </div>
              </div>

              {/* 2. PEST PLAN */}
              {hasPest && (
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

                  <div className={`transition-all duration-300 ${pestPlanEnabled ? 'opacity-100' : 'opacity-50 grayscale'}`}>
                    <div className="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-visible">
                      <PremiumPlanDropdown 
                        options={pestPlansList}
                        value={selectedPestPlanId}
                        onChange={setSelectedPestPlanId}
                        recommendedTier={recommendedPestTier}
                        label="Pest Level"
                      />
                      <div className="text-right">
                        <div className="text-lg font-bold text-gray-900">
                          ${pestToday.toFixed(2)} <span className="text-sm font-normal text-gray-500">today</span>{' '}
                          <Info size={12} className="inline text-gray-400" />
                        </div>
                        <div className="text-xs text-gray-500">${pestYearly.toFixed(2)} total for the year</div>
                      </div>
                    </div>
                    <div className="p-6 space-y-4 bg-gray-50/30">
                      {(selectedPestPlan?.features ?? []).map((feature, index) => (
                        <ProductCard key={index} feature={feature} index={index + 2} />
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* 3. GARDEN UPSELL */}
              {hasGarden && (
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:border-green-200 transition-colors cursor-pointer group">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-700 group-hover:scale-110 transition-transform">
                      <Flower size={24} />
                    </div>
                    <h2 className="text-xl font-bold text-gray-900">3. Add garden care</h2>
                  </div>
                  <button className="bg-green-700 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-800 transition-colors shadow-sm">Start</button>
                </div>
              )}

              {/* FAQ */}
              <div className="bg-[#E0F2F1] rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div className="flex items-center gap-4">
                  <div className="bg-[#26A69A] text-white rounded-full p-2"><HelpCircle size={28} /></div>
                  <span className="text-lg font-bold text-gray-900">Still have questions?</span>
                </div>
                <button className="w-full sm:w-auto bg-white border border-gray-300 text-gray-700 font-bold px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                  See all FAQs
                </button>
              </div>
            </div>

            {/* RIGHT COLUMN: Sidebar */}
            <div className="w-full lg:w-1/3 relative">
              <div className="lg:sticky lg:top-8 space-y-4">
                <div className="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                  <div className="bg-gray-50 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                    <h3 className="font-bold text-gray-800 flex items-center gap-2"><ShoppingCart size={18} /> Review plan</h3>
                    <div className="flex gap-1">
                      <Sprout size={16} className="text-green-600" />
                      {pestPlanEnabled && hasPest && <Bug size={16} className="text-blue-600" />}
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
                    {/* <button className="w-full bg-[#2E7D32] text-gray-50 font-extrabold text-lg py-3 rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98] mb-4 cursor-pointer">
                      Add to cart
                    </button> */}
                    <AddToCartButton
                      product={cartProduct}
                      quantity={1}
                      className="w-full"
                      size="large"
                      showIcon={true}
                    />
                    <p className="text-[10px] text-gray-400 text-center leading-tight mt-2">
                      Your fertilizer selection and timing may be updated based on real-time weather and soil analysis. Yearly plan renews each spring. Cancel anytime.
                    </p>
                  </div>
                </div>
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
      </div>
    </AppHeaderLayout>
  );
}