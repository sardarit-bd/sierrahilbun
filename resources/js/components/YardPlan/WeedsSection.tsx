import { Leaf } from 'lucide-react';
import PremiumPlanDropdown from './PremiumPlanDropdown';
import ToggleSwitch from './ToggleSwitch';
import { ProductCard } from './ProductCard';

const WeedsSection = ({
  index,
  weedsPlansList,
  selectedWeedsPlanId,
  setSelectedWeedsPlanId,
  selectedWeedsPlan,
  recommendedWeedsTier,
  weedsPlanEnabled,
  setWeedsPlanEnabled,
  weedsToday,
}) => (
  <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
      <div className="flex items-center gap-3">
        <div className="w-10 h-10 bg-lime-100 rounded-full flex items-center justify-center text-lime-700">
          <Leaf size={24} />
        </div>
        <h2 className="text-xl font-bold text-gray-900">{index}. Your weed control plan</h2>
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
        {(selectedWeedsPlan?.features ?? []).map((feature, i) => (
          <ProductCard key={i} feature={feature} index={i + 2} />
        ))}
      </div>
    </div>
  </div>
);

export default WeedsSection;