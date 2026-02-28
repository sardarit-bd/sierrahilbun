import { useState } from 'react';
import { ChevronDown } from 'lucide-react';
import { mapFeatureToAsset } from './helpers';

export const ProductCard = ({ feature, index }) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const visualFeature               = mapFeatureToAsset(feature, index);

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
              <img
                src={visualFeature.displayImage}
                alt={feature.title}
                className="w-32 h-32 rounded-lg shadow-sm border border-gray-100 object-cover mt-2"
              />
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

export const GardenProductCard = ({ feature, item, index }) => {
  const quartsLabel = item
    ? `${item.quarts} quart${item.quarts !== 1 ? 's' : ''} · $${item.price_per_quart}/quart · $${item.total.toFixed(2)} total`
    : null;
  const mergedFeature = { ...feature, subtitle: quartsLabel ?? feature.subtitle };
  return <ProductCard feature={mergedFeature} index={index} />;
};