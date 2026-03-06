import { ShoppingCart, Sprout, Leaf, Flower, ShieldCheck } from 'lucide-react';
import AddToCartButton from '@/components/AddToCartButton';

const PlanSidebar = ({
  lawnPrice,
  weedsPrice,
  gardenPrice,
  totalPrice,
  weedsPlanEnabled,
  hasWeeds,
  gardenEnabled,
  hasGardenPlan,
  cartProduct,
}) => (
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
            <span className="font-semibold text-gray-800">${lawnPrice.toFixed(2)}</span>
          </div>
          {weedsPlanEnabled && hasWeeds && (
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Weed Control</span>
              <span className="font-semibold text-gray-800">${weedsPrice.toFixed(2)}</span>
            </div>
          )}
          {gardenEnabled && hasGardenPlan && (
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Garden Care</span>
              <span className="font-semibold text-gray-800">${gardenPrice.toFixed(2)}</span>
            </div>
          )}
          <div className="border-t border-gray-100 pt-2 flex justify-between items-baseline">
            <span className="text-gray-600 font-medium">Total</span>
            <span className="text-3xl font-extrabold text-gray-900">${totalPrice.toFixed(2)}</span>
          </div>
        </div>
        <AddToCartButton product={cartProduct} quantity={1} className="w-full" size="large" showIcon={true} />
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
);

export default PlanSidebar;