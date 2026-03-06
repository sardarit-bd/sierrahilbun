import { useState, useEffect } from 'react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { FALLBACK_IMAGE } from '@/components/YardPlan/helpers';
import LawnSection from '@/components/YardPlan/LawnSection';
import WeedsSection from '@/components/YardPlan/WeedsSection';
import GardenSection from '@/components/YardPlan/GardenSection';
import PlanSidebar from '@/components/YardPlan/PlanSidebar';
import GardenQuizModal from '@/components/YardPlan/GardenQuizModal';

export default function App({ assessment, plans = {}, recommended_tier, all_plans, tiers }) {
  const [isLoading, setIsLoading]              = useState(true);
  const [weedsPlanEnabled, setWeedsPlanEnabled] = useState(true);
  const [gardenEnabled, setGardenEnabled]       = useState(true);
  const [gardenModalOpen, setGardenModalOpen]   = useState(false);

  // -------------------------------------------------------
  // Lawn plans
  // plans shape per tier:
  //   { plan, is_recommended, is_redundant, features, pricing }
  //   pricing: { base_price, addons_total, total_price }
  //   total_price = base × sqft_multiplier + addon retail — use this for display
  // -------------------------------------------------------
  const lawnPlansList    = Object.values(plans).filter(p => !p.is_redundant);
  const recommendedEntry = lawnPlansList.find(p => p.is_recommended) ?? lawnPlansList[0];
  const defaultLawnPlanId = recommendedEntry?.plan?.id;

  const [selectedLawnPlanId, setSelectedLawnPlanId] = useState(defaultLawnPlanId);

  const selectedLawnEntry = lawnPlansList.find(p => p.plan.id === Number(selectedLawnPlanId))
                          ?? recommendedEntry;

  // Correct price: from packaging engine (base × multiplier + addons)
  // Do NOT use plan.base_price_yearly or plan.current_price_yearly —
  // those are flat base prices that ignore sqft scaling and add-on costs.
  const lawnPrice = parseFloat(selectedLawnEntry?.pricing?.total_price ?? 0);

  // -------------------------------------------------------
  // Weeds plans — unchanged
  // -------------------------------------------------------
  const weedsPlansMap        = all_plans?.weeds ?? {};
  const weedsPlansList       = Object.values(weedsPlansMap);
  const recommendedWeedsTier = tiers?.weeds ?? 'bronze';
  const defaultWeedsPlan     = weedsPlansMap[recommendedWeedsTier] ?? weedsPlansList[0];

  const [selectedWeedsPlanId, setSelectedWeedsPlanId] = useState(defaultWeedsPlan?.id);
  const selectedWeedsPlan = weedsPlansList.find(p => p.id === Number(selectedWeedsPlanId))
                          ?? defaultWeedsPlan;
  const weedsPrice = parseFloat(
    selectedWeedsPlan?.current_price_yearly ?? selectedWeedsPlan?.base_price_yearly ?? 0
  );

  // -------------------------------------------------------
  // Garden — unchanged
  // -------------------------------------------------------
  const gardenPlan     = all_plans?.garden?.standard ?? null;
  const gardenFeatures = gardenPlan?.features ?? [];
  const gardenItems    = assessment?.garden_products?.items ?? [];
  const gardenPrice    = parseFloat(assessment?.garden_products?.total_price ?? 0);

  const hasWeeds      = (assessment?.selected_services?.includes('weeds')  ?? false) && weedsPlansList.length > 0;
  const hasGarden     = (assessment?.selected_services?.includes('garden') ?? false);
  const hasGardenPlan = hasGarden && gardenItems.length > 0;

  useEffect(() => { setGardenEnabled(hasGardenPlan); }, [hasGardenPlan]);
  useEffect(() => {
    const timer = setTimeout(() => setIsLoading(false), 1000);
    return () => clearTimeout(timer);
  }, []);

  // -------------------------------------------------------
  // Total — lawn uses engine price, weeds/garden unchanged
  // -------------------------------------------------------
  const totalPrice = lawnPrice
    + (weedsPlanEnabled && hasWeeds      ? weedsPrice  : 0)
    + (gardenEnabled    && hasGardenPlan ? gardenPrice : 0);

  // -------------------------------------------------------
  // Section numbering
  // -------------------------------------------------------
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

  // const cartProduct = {
  //   id: ['bundle', selectedLawnPlanId, weedsPlanEnabled && hasWeeds ? selectedWeedsPlanId : null, gardenEnabled && hasGardenPlan ? `garden-${assessment?.garden_products?.garden_size}` : null].filter(Boolean).join('-'),
  //   name: [
  //     `Lawn Care (${selectedLawnEntry?.plan?.name})`,
  //     weedsPlanEnabled && hasWeeds      ? `Weeds Control (${selectedWeedsPlan?.name})` : null,
  //     gardenEnabled    && hasGardenPlan ? 'Garden Care'                                : null,
  //   ].filter(Boolean).join(' + '),
  //   title:           selectedLawnEntry?.plan?.name,
  //   image:           FALLBACK_IMAGE,
  //   price:           totalPrice,
  //   lawn_plan_id:    selectedLawnPlanId,
  //   weed_plan_id:    weedsPlanEnabled && hasWeeds      ? selectedWeedsPlanId         : null,
  //   garden_products: gardenEnabled    && hasGardenPlan ? assessment?.garden_products : null,
  // };
  // In plan.jsx — update the cartProduct object to include assessment_id.
// The assessment.id is already available as a prop passed from PlanController.
//
// Replace your existing cartProduct definition with this:

const cartProduct = {
  id: ['bundle', selectedLawnPlanId, weedsPlanEnabled && hasWeeds ? selectedWeedsPlanId : null, gardenEnabled && hasGardenPlan ? `garden-${assessment?.garden_products?.garden_size}` : null].filter(Boolean).join('-'),
  name: [
    `Lawn Care (${selectedLawnEntry?.plan?.name})`,
    weedsPlanEnabled && hasWeeds      ? `Weeds Control (${selectedWeedsPlan?.name})` : null,
    gardenEnabled    && hasGardenPlan ? 'Garden Care'                                : null,
  ].filter(Boolean).join(' + '),
  title:           selectedLawnEntry?.plan?.name,

  // Real plan image from first product in first feature
  image: selectedLawnEntry?.features?.[0]?.products?.[0]?.primary_image
      ?? selectedLawnEntry?.plan?.image_url
      ?? FALLBACK_IMAGE,

  price:           totalPrice,
  lawn_plan_id:    selectedLawnPlanId,
  weed_plan_id:    weedsPlanEnabled && hasWeeds      ? selectedWeedsPlanId         : null,
  garden_products: gardenEnabled    && hasGardenPlan ? assessment?.garden_products : null,

  // ✅ Pass assessment_id so CheckoutService can look up square_feet
  // and apply the correct LawnPricingService sqft multiplier server-side.
  assessment_id: assessment?.id ?? null,
  tier: selectedLawnEntry?.plan?.slug?.split('-').pop() ?? null,
};

  return (
    <AppHeaderLayout>
      <div className="min-h-screen bg-gray-50 selection:bg-green-100">
        <div className="container mx-auto px-4 py-8 lg:px-8 max-w-7xl">

          <div className="mb-8">
            <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-2">Custom yard plan ready!</h1>
            <p className="text-gray-600 max-w-3xl leading-relaxed">
              Tailored to your soil, climate, and yard condition at{' '}
              <span className="font-bold text-gray-900">{Number(assessment?.square_feet ?? 0).toLocaleString()} sq feet</span>,{' '}
              your all-in-one plan combines easy to use products with expert guidance. All delivered for free.
            </p>
          </div>

          <div className="flex flex-col lg:flex-row gap-8 relative">

            <div className="w-full lg:w-2/3 space-y-10">

              <LawnSection
                sectionIndex={lawnIndex}
                plans={plans}
                selectedLawnPlanId={selectedLawnPlanId}
                setSelectedLawnPlanId={setSelectedLawnPlanId}
                recommendedLawnTier={recommended_tier}
                lawnPrice={lawnPrice}
              />

              {hasWeeds && (
                <WeedsSection
                  sectionIndex={weedsIndex}
                  weedsPlansList={weedsPlansList}
                  selectedWeedsPlanId={selectedWeedsPlanId}
                  setSelectedWeedsPlanId={setSelectedWeedsPlanId}
                  recommendedWeedsTier={recommendedWeedsTier}
                  selectedWeedsPlan={selectedWeedsPlan}
                  weedsPrice={weedsPrice}
                  weedsPlanEnabled={weedsPlanEnabled}
                  setWeedsPlanEnabled={setWeedsPlanEnabled}
                />
              )}

              {hasGarden && (
                <GardenSection
                  sectionIndex={gardenIndex}
                  hasGardenPlan={hasGardenPlan}
                  gardenEnabled={gardenEnabled}
                  setGardenEnabled={setGardenEnabled}
                  gardenFeatures={gardenFeatures}
                  gardenItems={gardenItems}
                  gardenPrice={gardenPrice}
                  assessment={assessment}
                  setGardenModalOpen={setGardenModalOpen}
                />
              )}
            </div>

            <div className="w-full lg:w-1/3 relative">
              <PlanSidebar
                lawnPrice={lawnPrice}
                weedsPrice={weedsPrice}
                gardenPrice={gardenPrice}
                totalPrice={totalPrice}
                weedsPlanEnabled={weedsPlanEnabled}
                hasWeeds={hasWeeds}
                gardenEnabled={gardenEnabled}
                hasGardenPlan={hasGardenPlan}
                cartProduct={cartProduct}
              />
            </div>

          </div>
        </div>
      </div>

      <GardenQuizModal isOpen={gardenModalOpen} onClose={() => setGardenModalOpen(false)} />
    </AppHeaderLayout>
  );
}