import { Check, Sprout, Tag } from 'lucide-react';
import PremiumPlanDropdown from './PremiumPlanDropdown';
import LawnProductCard from './LawnProductCard';

const LawnSection = ({
  index,
  lawnPlansList,
  selectedLawnPlanId,
  setSelectedLawnPlanId,
  selectedLawnPlan,
  recommendedLawnTier,
  lawnToday,
  includedProducts,
  addonProducts,
  lawn_products,
}) => (
  <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
      <div className="flex items-center gap-3">
        <div className="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700">
          <Sprout size={24} />
        </div>
        <h2 className="text-xl font-bold text-gray-900">{index}. Your lawn plan</h2>
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

    {selectedLawnPlan?.description && (
      <div className="px-6 py-3 bg-green-50/50 text-xs sm:text-sm font-medium text-green-800 border-b border-gray-100">
        {selectedLawnPlan.description}
      </div>
    )}

    <div className="p-6 space-y-4 bg-gray-50/30">
      {includedProducts.length > 0 && (
        <div className="space-y-3">
          {includedProducts.map((product) => (
            <LawnProductCard key={product.slug} product={product} />
          ))}
        </div>
      )}

      {/* {addonProducts.length > 0 && (
        <div className="mt-4">
          <div className="flex items-center gap-2 mb-3">
            <Tag size={14} className="text-amber-600" />
            <span className="text-xs font-bold uppercase tracking-widest text-amber-600">
              Recommended Add-ons
            </span>
          </div>
          <div className="space-y-3">
            {addonProducts.map((product) => (
              <LawnProductCard key={product.slug} product={product} />
            ))}
          </div>
        </div>
      )} */}

      {lawn_products.length === 0 && (
        <p className="text-sm text-gray-400 text-center py-4">No products calculated yet.</p>
      )}
    </div>
  </div>
);

export default LawnSection;