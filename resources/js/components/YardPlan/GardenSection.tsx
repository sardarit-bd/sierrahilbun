import { Flower, Pencil } from 'lucide-react';
import ToggleSwitch from './ToggleSwitch';
import GardenProductCard from './GardenProductCard';
import { GARDEN_SIZES } from './helpers';

const GardenSection = ({
  sectionIndex,
  hasGardenPlan,
  gardenEnabled,
  setGardenEnabled,
  gardenFeatures,
  gardenItems,
  gardenToday,
  assessment,
  setGardenModalOpen,
}) => (
  <>
    {!hasGardenPlan && (
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:border-green-200 transition-colors group">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-700 group-hover:scale-110 transition-transform"><Flower size={24} /></div>
          <h2 className="text-xl font-bold text-gray-900">{sectionIndex}. Add garden care</h2>
        </div>
        <button onClick={() => setGardenModalOpen(true)} className="bg-green-700 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-800 transition-colors shadow-sm">Start</button>
      </div>
    )}
    {hasGardenPlan && (
      <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-white">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-700"><Flower size={24} /></div>
            <h2 className="text-xl font-bold text-gray-900">{sectionIndex}. Your garden care plan</h2>
          </div>
          <div className="flex items-center gap-3">
            <button onClick={() => setGardenModalOpen(true)} className="flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-green-700 transition-colors">
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
                  <span key={type} className="bg-orange-100 text-orange-800 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wide">{type.replace('_', ' ')}</span>
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
              <GardenProductCard key={feature.title} feature={feature} item={gardenItems[index] ?? null} index={index} />
            ))}
          </div>
        </div>
      </div>
    )}
  </>
);

export default GardenSection;