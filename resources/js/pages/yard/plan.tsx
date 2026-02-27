import React, { useState, useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';
import {
  Check,
  ChevronDown,
  Sprout,
  Flower,
  ShoppingCart,
  ShieldCheck,
  Award,
  Leaf,
  X,
  Pencil,
} from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import AddToCartButton from '@/components/AddToCartButton';

// -------------------------------------------------------
// Premium Select Component
// -------------------------------------------------------

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

      {isOpen && (
        <div className="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
          <div className="p-1 max-h-72 overflow-y-auto">
            {options.map((plan) => {
              const isSelected    = plan.id === Number(value);
              const isRecommended = plan.target_audience === recommendedTier;

              return (
                <button
                  key={plan.id}
                  onClick={() => { onChange(plan.id); setIsOpen(false); }}
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

// -------------------------------------------------------
// Helpers
// -------------------------------------------------------

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
  const asset            = assets[index % assets.length];
  const iconUrl          = feature.icon_url ? resolveUrl(feature.icon_url) : asset.img;
  const images = Array.isArray(feature.image_url)
    ? feature.image_url
    : typeof feature.image_url === 'string'
      ? JSON.parse(feature.image_url)
      : [];
  const expandedImageUrl = images.length ? resolveUrl(images[0]) : null;
  return { ...feature, tag: feature.tag || asset.defaultTag, tagColor: asset.color, displayIcon: iconUrl, displayImage: expandedImageUrl };
};

// -------------------------------------------------------
// Sub-Components
// -------------------------------------------------------

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
          <button
            onClick={() => setIsExpanded(!isExpanded)}
            className="mt-2 text-xs font-bold text-gray-400 flex items-center gap-1 hover:text-green-700 transition-colors"
          >
            {isExpanded ? 'See less' : 'See more'}{' '}
            <ChevronDown size={14} className={`transform transition-transform ${isExpanded ? 'rotate-180' : ''}`} />
          </button>
        </div>
      </div>
    </div>
  );
};

// -------------------------------------------------------
// Garden Product Card
// Merges DB-backed feature (title, subtitle, icon_url) with
// calculated quantities from garden_products.items.
// Matched by index — order guaranteed by plan_feature.sort_order
// matching the order of GardenQuizCalculatorService::PRODUCTS.
// -------------------------------------------------------

const GardenProductCard = ({ feature, item, index }) => {
  const quartsLabel = item
    ? `${item.quarts} quart${item.quarts !== 1 ? 's' : ''} · $${item.price_per_quart}/quart · $${item.total.toFixed(2)} total`
    : null;

  const mergedFeature = {
    ...feature,
    subtitle: quartsLabel ?? feature.subtitle,
  };

  return <ProductCard feature={mergedFeature} index={index} />;
};

const ToggleSwitch = ({ enabled, onToggle }) => (
  <button
    onClick={onToggle}
    className={`relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-300 focus:outline-none ${enabled ? 'bg-green-700' : 'bg-gray-300'}`}
  >
    <span className={`inline-block h-6 w-6 transform rounded-full bg-white transition duration-300 ease-in-out shadow-sm ${enabled ? 'translate-x-7' : 'translate-x-1'}`} />
  </button>
);

// -------------------------------------------------------
// Garden Quiz Modal
// -------------------------------------------------------

const GARDEN_TYPES = [
  { value: 'flowers',      label: 'Flowers'       },
  { value: 'vegetables',   label: 'Vegetables'     },
  { value: 'trees_shrubs', label: 'Trees & Shrubs' },
];

const GARDEN_SIZES = [
  { value: 'xs', label: 'XS',  description: 'Less than 500 sq ft' },
  { value: 'sm', label: 'S-M', description: '500–1,000 sq ft'     },
  { value: 'l',  label: 'L',   description: '1,000+ sq ft'        },
];

const GardenQuizModal = ({ isOpen, onClose }) => {
  const [step, setStep]                 = useState(1);
  const [gardenTypes, setGardenTypes]   = useState([]);
  const [gardenSize, setGardenSize]     = useState(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors]             = useState({});

  useEffect(() => {
    if (isOpen) {
      setStep(1);
      setGardenTypes([]);
      setGardenSize(null);
      setErrors({});
      setIsSubmitting(false);
    }
  }, [isOpen]);

  useEffect(() => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [isOpen]);

  if (!isOpen) return null;

  const toggleType = (value) => {
    setGardenTypes(prev =>
      prev.includes(value) ? prev.filter(t => t !== value) : [...prev, value]
    );
    setErrors(prev => ({ ...prev, garden_types: null }));
  };

  const handleNext = () => {
    if (gardenTypes.length === 0) {
      setErrors({ garden_types: 'Please select at least one garden type.' });
      return;
    }
    setStep(2);
  };

  const handleSubmit = () => {
    if (!gardenSize) {
      setErrors({ garden_size: 'Please select a garden size.' });
      return;
    }

    setIsSubmitting(true);

    router.post(
      route('yard.garden-quiz'),
      { garden_types: gardenTypes, garden_size: gardenSize },
      {
        preserveScroll: true,
        onSuccess: () => { onClose(); },
        onError:   (errs) => { setErrors(errs); setIsSubmitting(false); },
        onFinish:  () => { setIsSubmitting(false); },
      }
    );
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div
        className="absolute inset-0 bg-black/40 backdrop-blur-sm"
        onClick={onClose}
      />
      <div className="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">

        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center text-orange-700">
              <Flower size={20} />
            </div>
            <div>
              <h3 className="font-bold text-gray-900 text-base leading-none">Garden Care</h3>
              <p className="text-xs text-gray-400 mt-0.5">Step {step} of 2</p>
            </div>
          </div>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 transition-colors">
            <X size={20} />
          </button>
        </div>

        {/* Step indicator */}
        <div className="flex gap-1 px-6 pt-4">
          <div className="h-1 flex-1 rounded-full bg-green-600" />
          <div className={`h-1 flex-1 rounded-full transition-colors duration-300 ${step === 2 ? 'bg-green-600' : 'bg-gray-200'}`} />
        </div>

        {/* Body */}
        <div className="px-6 py-5">
          {step === 1 && (
            <div>
              <h4 className="font-bold text-gray-900 text-base mb-1">What do you grow?</h4>
              <p className="text-sm text-gray-500 mb-4">Select all that apply.</p>
              <div className="space-y-2">
                {GARDEN_TYPES.map((type) => {
                  const isSelected = gardenTypes.includes(type.value);
                  return (
                    <button
                      key={type.value}
                      onClick={() => toggleType(type.value)}
                      className={`
                        w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 transition-all duration-150 text-left
                        ${isSelected
                          ? 'border-green-600 bg-green-50 text-green-900'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                        }
                      `}
                    >
                      <span className="font-semibold text-sm">{type.label}</span>
                      {isSelected && <Check size={16} className="text-green-600" strokeWidth={3} />}
                    </button>
                  );
                })}
              </div>
              {errors.garden_types && (
                <p className="text-xs text-red-600 mt-2">{errors.garden_types}</p>
              )}
            </div>
          )}

          {step === 2 && (
            <div>
              <h4 className="font-bold text-gray-900 text-base mb-1">How large is your garden?</h4>
              <p className="text-sm text-gray-500 mb-4">We'll use this to calculate your product quantities.</p>
              <div className="space-y-2">
                {GARDEN_SIZES.map((size) => {
                  const isSelected = gardenSize === size.value;
                  return (
                    <button
                      key={size.value}
                      onClick={() => {
                        setGardenSize(size.value);
                        setErrors(prev => ({ ...prev, garden_size: null }));
                      }}
                      className={`
                        w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 transition-all duration-150 text-left
                        ${isSelected
                          ? 'border-green-600 bg-green-50 text-green-900'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                        }
                      `}
                    >
                      <div>
                        <span className="font-bold text-sm">{size.label}</span>
                        <span className="text-xs text-gray-500 ml-2">{size.description}</span>
                      </div>
                      {isSelected && <Check size={16} className="text-green-600" strokeWidth={3} />}
                    </button>
                  );
                })}
              </div>
              {errors.garden_size && (
                <p className="text-xs text-red-600 mt-2">{errors.garden_size}</p>
              )}
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="px-6 pb-6 flex items-center justify-between gap-3">
          {step === 2 ? (
            <button
              onClick={() => setStep(1)}
              className="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors"
            >
              Back
            </button>
          ) : (
            <div />
          )}
          {step === 1 && (
            <button
              onClick={handleNext}
              className="ml-auto bg-green-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-green-800 transition-colors shadow-sm"
            >
              Next
            </button>
          )}
          {step === 2 && (
            <button
              onClick={handleSubmit}
              disabled={isSubmitting}
              className="ml-auto bg-green-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-green-800 transition-colors shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {isSubmitting ? 'Saving…' : 'Add to plan'}
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

// -------------------------------------------------------
// Main Page Component
// -------------------------------------------------------

export default function App({ assessment, recommended_plans, all_plans, tiers }) {
  const [isLoading, setIsLoading]               = useState(true);
  const [weedsPlanEnabled, setWeedsPlanEnabled]  = useState(true);
  const [gardenEnabled, setGardenEnabled]        = useState(true);
  const [gardenModalOpen, setGardenModalOpen]    = useState(false);

  // Lawn plans
  const lawnPlansMap        = all_plans?.lawn ?? {};
  const lawnPlansList       = Object.values(lawnPlansMap);
  const recommendedLawnTier = tiers?.lawn ?? 'bronze';
  const defaultLawnPlan     = lawnPlansMap[recommendedLawnTier] ?? lawnPlansList[0];
  const [selectedLawnPlanId, setSelectedLawnPlanId] = useState(defaultLawnPlan?.id);
  const selectedLawnPlan = lawnPlansList.find(p => p.id === Number(selectedLawnPlanId)) ?? defaultLawnPlan;

  // Weeds plans
  const weedsPlansMap        = all_plans?.weeds ?? {};
  const weedsPlansList       = Object.values(weedsPlansMap);
  const recommendedWeedsTier = tiers?.weeds ?? 'bronze';
  const defaultWeedsPlan     = weedsPlansMap[recommendedWeedsTier] ?? weedsPlansList[0];
  const [selectedWeedsPlanId, setSelectedWeedsPlanId] = useState(defaultWeedsPlan?.id);
  const selectedWeedsPlan = weedsPlansList.find(p => p.id === Number(selectedWeedsPlanId)) ?? defaultWeedsPlan;

  // Garden — DB-backed features + calculated items
  const gardenPlan     = all_plans?.garden?.standard ?? null;
  const gardenFeatures = gardenPlan?.features ?? [];
  const gardenItems    = assessment?.garden_products?.items ?? [];

  // Service flags
  const hasWeeds      = (assessment?.selected_services?.includes('weeds')  ?? false) && weedsPlansList.length > 0;
  const hasGarden     = (assessment?.selected_services?.includes('garden') ?? false);
  const hasGardenPlan = hasGarden && gardenItems.length > 0;

  useEffect(() => {
    setGardenEnabled(hasGardenPlan);
  }, [hasGardenPlan]);

  useEffect(() => {
    const timer = setTimeout(() => setIsLoading(false), 1000);
    return () => clearTimeout(timer);
  }, []);

  // Pricing
  const lawnYearly   = parseFloat(selectedLawnPlan?.current_price_yearly  ?? selectedLawnPlan?.base_price_yearly  ?? 0);
  const weedsYearly  = parseFloat(selectedWeedsPlan?.current_price_yearly ?? selectedWeedsPlan?.base_price_yearly ?? 0);
  const gardenToday  = parseFloat(assessment?.garden_products?.total_price ?? 0);
  const gardenYearly = gardenToday * 12;

  const lawnToday  = lawnYearly  / 12;
  const weedsToday = weedsYearly / 12;

  const totalYearly = lawnYearly
    + (weedsPlanEnabled && hasWeeds      ? weedsYearly  : 0)
    + (gardenEnabled    && hasGardenPlan ? gardenYearly : 0);

  const totalToday = lawnToday
    + (weedsPlanEnabled && hasWeeds      ? weedsToday  : 0)
    + (gardenEnabled    && hasGardenPlan ? gardenToday : 0);

  // Section numbering — dynamic
  let sectionIndex  = 1;
  const lawnIndex   = sectionIndex++;
  const weedsIndex  = hasWeeds  ? sectionIndex++ : null;
  const gardenIndex = hasGarden ? sectionIndex++ : null;

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
    id: [
      'bundle',
      selectedLawnPlanId,
      weedsPlanEnabled && hasWeeds      ? selectedWeedsPlanId                                  : null,
      gardenEnabled    && hasGardenPlan ? `garden-${assessment?.garden_products?.garden_size}` : null,
    ].filter(Boolean).join('-'),

    name: [
      `Lawn Care (${selectedLawnPlan?.name})`,
      weedsPlanEnabled && hasWeeds      ? `Weeds Control (${selectedWeedsPlan?.name})` : null,
      gardenEnabled    && hasGardenPlan ? `Garden Care`                                : null,
    ].filter(Boolean).join(' + '),

    title:        selectedLawnPlan?.name,
    image:        'https://images.unsplash.com/photo-1605117882932-f9e32b03fea9?q=80&w=300&auto=format&fit=crop',
    price:        totalToday,
    price_yearly: totalYearly,

    lawn_plan_id:    selectedLawnPlanId,
    weed_plan_id:    weedsPlanEnabled && hasWeeds      ? selectedWeedsPlanId        : null,
    garden_products: gardenEnabled    && hasGardenPlan ? assessment?.garden_products : null,
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
              All delivered for free.
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
                    <h2 className="text-xl font-bold text-gray-900">{lawnIndex}. Your lawn plan</h2>
                  </div>
                  <div className="bg-green-600 text-white rounded-full p-1">
                    <Check size={16} strokeWidth={3} />
                  </div>
                </div>

                <div className="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-visible">
                  <PremiumPlanDropdown
                    options={lawnPlansList}
                    value={selectedLawnPlanId}
                    onChange={setSelectedLawnPlanId}
                    recommendedTier={recommendedLawnTier}
                    label="Lawn Plan"
                  />
                  <div className="text-right">
                    <div className="text-lg font-bold text-gray-900">${lawnToday.toFixed(2)}</div>
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

              {/* 2. WEEDS PLAN */}
              {hasWeeds && (
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                  <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 bg-lime-100 rounded-full flex items-center justify-center text-lime-700">
                        <Leaf size={24} />
                      </div>
                      <h2 className="text-xl font-bold text-gray-900">{weedsIndex}. Your weed control plan</h2>
                    </div>
                    <ToggleSwitch enabled={weedsPlanEnabled} onToggle={() => setWeedsPlanEnabled(!weedsPlanEnabled)} />
                  </div>

                  <div className={`transition-all duration-300 ${weedsPlanEnabled ? 'opacity-100' : 'opacity-50 grayscale'}`}>
                    <div className="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-visible">
                      <PremiumPlanDropdown
                        options={weedsPlansList}
                        value={selectedWeedsPlanId}
                        onChange={setSelectedWeedsPlanId}
                        recommendedTier={recommendedWeedsTier}
                        label="Weed Control Plan"
                      />
                      <div className="text-right">
                        <div className="text-lg font-bold text-gray-900">${weedsToday.toFixed(2)}</div>
                      </div>
                    </div>
                    <div className="p-6 space-y-4 bg-gray-50/30">
                      {(selectedWeedsPlan?.features ?? []).map((feature, index) => (
                        <ProductCard key={index} feature={feature} index={index + 2} />
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* GARDEN SECTION */}
              {hasGarden && (
                <>
                  {/* Not yet configured — Start button */}
                  {!hasGardenPlan && (
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:border-green-200 transition-colors group">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-700 group-hover:scale-110 transition-transform">
                          <Flower size={24} />
                        </div>
                        <h2 className="text-xl font-bold text-gray-900">{gardenIndex}. Add garden care</h2>
                      </div>
                      <button
                        onClick={() => setGardenModalOpen(true)}
                        className="bg-green-700 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-800 transition-colors shadow-sm"
                      >
                        Start
                      </button>
                    </div>
                  )}

                  {/* Configured — DB-backed features merged with calculated quantities */}
                  {hasGardenPlan && (
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                      <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
                        <div className="flex items-center gap-3">
                          <div className="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-700">
                            <Flower size={24} />
                          </div>
                          <h2 className="text-xl font-bold text-gray-900">{gardenIndex}. Your garden care plan</h2>
                        </div>
                        <div className="flex items-center gap-3">
                          <button
                            onClick={() => setGardenModalOpen(true)}
                            className="flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-green-700 transition-colors"
                          >
                            <Pencil size={13} /> Edit
                          </button>
                          <ToggleSwitch enabled={gardenEnabled} onToggle={() => setGardenEnabled(!gardenEnabled)} />
                        </div>
                      </div>

                      <div className={`transition-all duration-300 ${gardenEnabled ? 'opacity-100' : 'opacity-50 grayscale'}`}>
                        <div className="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                          <div className="flex flex-col">
                            <span className="text-[9px] uppercase tracking-wider font-bold text-gray-400 mb-1">Garden Plan</span>
                            <div className="flex flex-wrap gap-1.5">
                              {(assessment?.garden_products?.garden_types ?? []).map((type) => (
                                <span key={type} className="bg-orange-100 text-orange-800 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wide">
                                  {type.replace('_', ' ')}
                                </span>
                              ))}
                              <span className="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wide">
                                {GARDEN_SIZES.find(s => s.value === assessment?.garden_products?.garden_size)?.label ?? ''}{' '}
                                {GARDEN_SIZES.find(s => s.value === assessment?.garden_products?.garden_size)?.description ?? ''}
                              </span>
                            </div>
                          </div>
                          <div className="text-right">
                            <div className="text-lg font-bold text-gray-900">${gardenToday.toFixed(2)}</div>
                          </div>
                        </div>

                        <div className="p-6 space-y-4 bg-gray-50/30">
                          {gardenFeatures.map((feature, index) => (
                            <GardenProductCard
                              key={feature.title}
                              feature={feature}
                              item={gardenItems[index] ?? null}
                              index={index}
                            />
                          ))}
                        </div>
                      </div>
                    </div>
                  )}
                </>
              )}

            </div>

            {/* RIGHT COLUMN: Sidebar */}
            <div className="w-full lg:w-1/3 relative">
              <div className="lg:sticky lg:top-8 space-y-4">
                <div className="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                  <div className="bg-gray-50 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                    <h3 className="font-bold text-gray-800 flex items-center gap-2">
                      <ShoppingCart size={18} /> Review plan
                    </h3>
                    <div className="flex gap-1">
                      <Sprout size={16} className="text-green-600" />
                      {weedsPlanEnabled && hasWeeds      && <Leaf   size={16} className="text-lime-600"   />}
                      {gardenEnabled    && hasGardenPlan && <Flower size={16} className="text-orange-500" />}
                    </div>
                  </div>
                  <div className="p-6">
                    <div className="space-y-2 mb-4">
                      <div className="flex justify-between text-sm">
                        <span className="text-gray-600">Lawn Care</span>
                        <span className="font-semibold text-gray-800">${lawnToday.toFixed(2)}</span>
                      </div>
                      {weedsPlanEnabled && hasWeeds && (
                        <div className="flex justify-between text-sm">
                          <span className="text-gray-600">Weed Control</span>
                          <span className="font-semibold text-gray-800">${weedsToday.toFixed(2)}</span>
                        </div>
                      )}
                      {gardenEnabled && hasGardenPlan && (
                        <div className="flex justify-between text-sm">
                          <span className="text-gray-600">Garden Care</span>
                          <span className="font-semibold text-gray-800">${gardenToday.toFixed(2)}</span>
                        </div>
                      )}
                      <div className="border-t border-gray-100 pt-2 flex justify-between items-baseline">
                        <span className="text-gray-600 font-medium">Total</span>
                        <span className="text-3xl font-extrabold text-gray-900">${totalToday.toFixed(2)}</span>
                      </div>
                    </div>

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

      <GardenQuizModal
        isOpen={gardenModalOpen}
        onClose={() => setGardenModalOpen(false)}
      />
    </AppHeaderLayout>
  );
}