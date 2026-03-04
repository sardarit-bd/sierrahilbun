import { Leaf } from 'lucide-react';

const GardenProductCard = ({ item, index }) => {
  if (!item) return null;

  const quartsLabel = `${item.quarts} quart${item.quarts !== 1 ? 's' : ''} · $${item.price_per_quart}/quart`;

  return (
    <div className="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-orange-100 transition-all duration-300 overflow-hidden self-start">

      {/* Top accent */}
      <div className="h-0.5 w-full" style={{ backgroundColor: '#007A55' }} />

      <div className="p-4 flex gap-4">

        {/* Left: icon + price */}
        <div className="flex flex-col gap-2 flex-shrink-0 w-24">
          <div className="w-24 h-24 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center">
            <Leaf size={36} className="text-orange-400" />
          </div>
          <div className="flex items-center justify-center bg-gray-50 rounded-lg px-2 py-1.5 border border-gray-100">
            <div className="text-center">
              <p className="text-[11px] font-extrabold text-gray-800 leading-none">${item.total.toFixed(2)}</p>
              <p className="text-[9px] text-gray-400 leading-none mt-0.5">total</p>
            </div>
          </div>
        </div>

        {/* Right: name + details */}
        <div className="flex-grow min-w-0 flex flex-col gap-1.5">
          <div>
            <h4 className="text-sm font-extrabold text-gray-900 leading-snug">{item.name}</h4>
            <p className="text-[11px] text-gray-400 leading-snug mt-0.5">{quartsLabel}</p>
          </div>
        </div>

      </div>
    </div>
  );
};

export default GardenProductCard;