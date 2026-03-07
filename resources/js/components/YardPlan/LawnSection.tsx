import { Check, Sprout } from 'lucide-react';
import PremiumPlanDropdown from './PremiumPlanDropdown';
import LawnFeatureRow from './LawnFeatureRow';

const LawnSection = ({
    sectionIndex,
    plans,
    selectedLawnPlanId,
    setSelectedLawnPlanId,
    recommendedLawnTier,
    lawnPrice,
}) => {
    const plansList = Object.values(plans).filter(p => !p.is_redundant);

    const selectedEntry = plansList.find(p => p.plan.id === Number(selectedLawnPlanId))
                       ?? plansList.find(p => p.is_recommended)
                       ?? plansList[0];

    const selectedPlan = selectedEntry?.plan;
    const features     = selectedEntry?.features ?? [];

    return (
        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-100">

            {/* Header */}
            <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700">
                        <Sprout size={24} />
                    </div>
                    <h2 className="text-xl font-bold text-gray-900">{sectionIndex}. Your lawn plan</h2>
                </div>
                <div className="bg-green-600 text-white rounded-full p-1">
                    <Check size={16} strokeWidth={3} />
                </div>
            </div>

            {/* Plan selector + price */}
            <div className="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-visible">
                <PremiumPlanDropdown
                    options={plansList.map(p => ({
                        ...p.plan,
                        is_recommended: p.is_recommended,
                    }))}
                    value={selectedLawnPlanId}
                    onChange={setSelectedLawnPlanId}
                    recommendedTier={recommendedLawnTier}
                    label="Lawn Plan"
                />
                <div className="text-right shrink-0">
                    <div className="text-lg font-bold text-gray-900">
                        ${lawnPrice.toFixed(2)}
                    </div>
                </div>
            </div>

            {/* Plan description */}
            {selectedPlan?.description && (
                <div className="px-6 py-3 bg-green-50/50 text-xs sm:text-sm font-medium text-green-800 border-b border-gray-100">
                    {selectedPlan.description}
                </div>
            )}

            {/* Features list */}
            <div className="p-6 space-y-3 bg-gray-50/30">
                {features.length > 0 ? (
                    features.map((feature) => (
                        <LawnFeatureRow key={feature.id} feature={feature} />
                    ))
                ) : (
                    <p className="text-sm text-gray-400 text-center py-4">
                        No features available.
                    </p>
                )}
            </div>
        </div>
    );
};

export default LawnSection;