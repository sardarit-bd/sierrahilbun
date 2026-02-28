import { useState, useEffect } from 'react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import {
  LawnSection,
  WeedsSection,
  GardenSection,
  PlanSidebar,
  GardenQuizModal,
  FALLBACK_IMAGE,
} from '../../components/YardPlan';

// -------------------------------------------------------
// Loading screen
// -------------------------------------------------------

const LoadingScreen = () => (
  <div className="flex-grow flex flex-col items-center justify-center bg-gray-50 min-h-screen">
    <h2 className="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-6">Building your plan</h2>
    <div className="flex gap-2">
      <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-75"  />
      <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-150" />
      <div className="w-3 h-3 bg-green-600 rounded-full animate-bounce delay-300" />
    </div>
  </div>
);

// -------------------------------------------------------
// Main Page
// -------------------------------------------------------

export default function App({ assessment, lawn_products = [], all_plans, tiers }) {
  const [isLoading, setIsLoading]              = useState(true);
  const [weedsPlanEnabled, setWeedsPlanEnabled] = useState(true);
  const [gardenEnabled, setGardenEnabled]       = useState(true);
  const [gardenModalOpen, setGardenModalOpen]   = useState(false);

  // ── Lawn plans ──────────────────────────────────────────
  const lawnPlansMap        = all_plans?.lawn ?? {};
  const lawnPlansList       = Object.values(lawnPlansMap);
  const recommendedLawnTier = tiers?.lawn ?? 'bronze';
  const defaultLawnPlan     = lawnPlansMap[recommendedLawnTier] ?? lawnPlansList[0];
  const [selectedLawnPlanId, setSelectedLawnPlanId] = useState(defaultLawnPlan?.id);
  const selectedLawnPlan = lawnPlansList.find(p => p.id === Number(selectedLawnPlanId)) ?? defaultLawnPlan;

  // ── Weeds plans ─────────────────────────────────────────
  const weedsPlansMap        = all_plans?.weeds ?? {};
  const weedsPlansList       = Object.values(weedsPlansMap);
  const recommendedWeedsTier = tiers?.weeds ?? 'bronze';
  const defaultWeedsPlan     = weedsPlansMap[recommendedWeedsTier] ?? weedsPlansList[0];
  const [selectedWeedsPlanId, setSelectedWeedsPlanId] = useState(defaultWeedsPlan?.id);
  const selectedWeedsPlan = weedsPlansList.find(p => p.id === Number(selectedWeedsPlanId)) ?? defaultWeedsPlan;

  // ── Garden ──────────────────────────────────────────────
  const gardenPlan     = all_plans?.garden?.standard ?? null;
  const gardenFeatures = gardenPlan?.features ?? [];
  const gardenItems    = assessment?.garden_products?.items ?? [];

  // ── Feature flags ────────────────────────────────────────
  const hasWeeds      = (assessment?.selected_services?.includes('weeds')  ?? false) && weedsPlansList.length > 0;
  const hasGarden     = (assessment?.selected_services?.includes('garden') ?? false);
  const hasGardenPlan = hasGarden && gardenItems.length > 0;

  // ── Product split ────────────────────────────────────────
  const includedProducts = lawn_products.filter(p => p.pricing_type === 'included');
  const addonProducts    = lawn_products.filter(p => p.pricing_type === 'addon');

  useEffect(() => { setGardenEnabled(hasGardenPlan); }, [hasGardenPlan]);
  useEffect(() => {
    const timer = setTimeout(() => setIsLoading(false), 1000);
    return () => clearTimeout(timer);
  }, []);

  // ── Pricing ──────────────────────────────────────────────
  const lawnYearly   = parseFloat(selectedLawnPlan?.current_price_yearly  ?? selectedLawnPlan?.base_price_yearly  ?? 0);
  const weedsYearly  = parseFloat(selectedWeedsPlan?.current_price_yearly ?? selectedWeedsPlan?.base_price_yearly ?? 0);
  const gardenToday  = parseFloat(assessment?.garden_products?.total_price ?? 0);
  const gardenYearly = gardenToday * 12;
  const lawnToday    = lawnYearly  / 12;
  const weedsToday   = weedsYearly / 12;

  const totalYearly = lawnYearly
    + (weedsPlanEnabled && hasWeeds      ? weedsYearly  : 0)
    + (gardenEnabled    && hasGardenPlan ? gardenYearly : 0);
  const totalToday = lawnToday
    + (weedsPlanEnabled && hasWeeds      ? weedsToday  : 0)
    + (gardenEnabled    && hasGardenPlan ? gardenToday : 0);

  // ── Section numbering ────────────────────────────────────
  let sectionIndex  = 1;
  const lawnIndex   = sectionIndex++;
  const weedsIndex  = hasWeeds  ? sectionIndex++ : null;
  const gardenIndex = hasGarden ? sectionIndex++ : null;

  if (isLoading) return <LoadingScreen />;

  // ── Cart product ─────────────────────────────────────────
  const cartProduct = {
    id: ['bundle', selectedLawnPlanId,
      weedsPlanEnabled && hasWeeds      ? selectedWeedsPlanId : null,
      gardenEnabled    && hasGardenPlan ? `garden-${assessment?.garden_products?.garden_size}` : null,
    ].filter(Boolean).join('-'),
    name: [
      `Lawn Care (${selectedLawnPlan?.name})`,
      weedsPlanEnabled && hasWeeds      ? `Weeds Control (${selectedWeedsPlan?.name})` : null,
      gardenEnabled    && hasGardenPlan ? 'Garden Care' : null,
    ].filter(Boolean).join(' + '),
    title:           selectedLawnPlan?.name,
    image:           FALLBACK_IMAGE,
    price:           totalToday,
    price_yearly:    totalYearly,
    lawn_plan_id:    selectedLawnPlanId,
    weed_plan_id:    weedsPlanEnabled && hasWeeds      ? selectedWeedsPlanId         : null,
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
              <span className="font-bold text-gray-900">{Number(assessment?.square_feet ?? 0).toLocaleString()} sq feet</span>,{' '}
              your all-in-one plan combines easy to use products with expert guidance. All delivered for free.
            </p>
          </div>

          <div className="flex flex-col lg:flex-row gap-8 relative">

            {/* LEFT COLUMN */}
            <div className="w-full lg:w-2/3 space-y-10">
              <LawnSection
                index={lawnIndex}
                lawnPlansList={lawnPlansList}
                selectedLawnPlanId={selectedLawnPlanId}
                setSelectedLawnPlanId={setSelectedLawnPlanId}
                selectedLawnPlan={selectedLawnPlan}
                recommendedLawnTier={recommendedLawnTier}
                lawnToday={lawnToday}
                includedProducts={includedProducts}
                addonProducts={addonProducts}
                lawn_products={lawn_products}
              />

              {hasWeeds && (
                <WeedsSection
                  index={weedsIndex}
                  weedsPlansList={weedsPlansList}
                  selectedWeedsPlanId={selectedWeedsPlanId}
                  setSelectedWeedsPlanId={setSelectedWeedsPlanId}
                  selectedWeedsPlan={selectedWeedsPlan}
                  recommendedWeedsTier={recommendedWeedsTier}
                  weedsPlanEnabled={weedsPlanEnabled}
                  setWeedsPlanEnabled={setWeedsPlanEnabled}
                  weedsToday={weedsToday}
                />
              )}

              {hasGarden && (
                <GardenSection
                  index={gardenIndex}
                  hasGardenPlan={hasGardenPlan}
                  gardenEnabled={gardenEnabled}
                  setGardenEnabled={setGardenEnabled}
                  gardenToday={gardenToday}
                  gardenFeatures={gardenFeatures}
                  gardenItems={gardenItems}
                  assessment={assessment}
                  onOpenModal={() => setGardenModalOpen(true)}
                />
              )}
            </div>

            {/* RIGHT COLUMN */}
            <PlanSidebar
              lawnToday={lawnToday}
              weedsToday={weedsToday}
              gardenToday={gardenToday}
              totalToday={totalToday}
              weedsPlanEnabled={weedsPlanEnabled}
              hasWeeds={hasWeeds}
              gardenEnabled={gardenEnabled}
              hasGardenPlan={hasGardenPlan}
              cartProduct={cartProduct}
            />
          </div>
        </div>
      </div>

      <GardenQuizModal isOpen={gardenModalOpen} onClose={() => setGardenModalOpen(false)} />
    </AppHeaderLayout>
  );
}