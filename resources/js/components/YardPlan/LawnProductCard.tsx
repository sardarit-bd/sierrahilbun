import { useState } from 'react';
import { Check, ChevronDown, Package, Droplets, FlaskConical } from 'lucide-react';
import { FALLBACK_IMAGE, PRICING_TYPE_STYLES } from './helpers';

const LawnProductCard = ({ product }) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const [imgError, setImgError]     = useState(false);

  const pricingStyle  = PRICING_TYPE_STYLES[product.pricing_type] ?? PRICING_TYPE_STYLES.addon;
  const displayImage  = (!imgError && product.primary_image) ? product.primary_image : FALLBACK_IMAGE;
  const expandImages  = product.images?.length ? product.images : [FALLBACK_IMAGE];
  const benefits      = Array.isArray(product.benefits) ? product.benefits : [];
  const shippingLabel = product.units?.map(u => `${u.qty}× ${u.size_label}`).join(' + ') ?? '';

  return (
    <div className="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
      <div className="flex gap-4 p-4">
        <div className="w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
          <img
            src={displayImage}
            alt={product.name}
            className="w-full h-full object-cover"
            onError={() => setImgError(true)}
          />
        </div>

        <div className="flex-grow min-w-0">
          <div className="flex flex-wrap items-center gap-1.5 mb-1.5">
            <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide ${pricingStyle.bg} ${pricingStyle.text}`}>
              {pricingStyle.label}
            </span>
            {product.pricing_type === 'addon' && product.total_price > 0 && (
              <span className="px-2 py-0.5 rounded text-[10px] font-bold text-gray-600 bg-gray-100">
                ${product.total_price.toFixed(2)}
              </span>
            )}
          </div>

          <h4 className="text-sm sm:text-base font-bold text-gray-900 leading-snug">{product.name}</h4>

          {product.subtitle && (
            <p className="text-xs text-gray-500 mt-0.5 line-clamp-1">{product.subtitle}</p>
          )}

          {shippingLabel && (
            <div className="flex items-center gap-1 mt-1.5">
              <Package size={11} className="text-gray-400 flex-shrink-0" />
              <span className="text-[11px] text-gray-400 font-medium">{shippingLabel}</span>
            </div>
          )}

          <button
            onClick={() => setIsExpanded(!isExpanded)}
            className="mt-2 text-xs font-bold text-gray-400 flex items-center gap-1 hover:text-green-700 transition-colors"
          >
            {isExpanded ? 'See less' : 'See more'}
            <ChevronDown size={14} className={`transform transition-transform duration-200 ${isExpanded ? 'rotate-180' : ''}`} />
          </button>
        </div>
      </div>

      <div className={`overflow-hidden transition-all duration-400 ease-in-out ${isExpanded ? 'max-h-[600px] opacity-100' : 'max-h-0 opacity-0'}`}>
        <div className="px-4 pb-4 space-y-4 border-t border-gray-50 pt-3">
          {product.description && (
            <p className="text-sm text-gray-600 leading-relaxed">{product.description}</p>
          )}

          {benefits.length > 0 && (
            <div>
              <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Key Benefits</p>
              <ul className="space-y-1.5">
                {benefits.map((benefit, i) => (
                  <li key={i} className="flex items-start gap-2 text-sm text-gray-700">
                    <Check size={13} className="text-green-600 flex-shrink-0 mt-0.5" strokeWidth={3} />
                    <span>{benefit}</span>
                  </li>
                ))}
              </ul>
            </div>
          )}

          {product.usage_instructions && (
            <div>
              <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-1">How to use</p>
              <p className="text-sm text-gray-600 leading-relaxed">{product.usage_instructions}</p>
            </div>
          )}

          <div className="flex flex-wrap gap-3">
            <div className="flex items-center gap-1.5 bg-gray-50 rounded-lg px-3 py-2">
              <Droplets size={13} className="text-blue-500" />
              <span className="text-xs text-gray-600">
                <span className="font-semibold">{product.oz_needed} oz</span> needed
              </span>
            </div>
            <div className="flex items-center gap-1.5 bg-gray-50 rounded-lg px-3 py-2">
              <Package size={13} className="text-green-500" />
              <span className="text-xs text-gray-600">
                <span className="font-semibold">{product.oz_shipped} oz</span> shipped
              </span>
            </div>
          </div>

          {expandImages.length > 0 && (
            <div className="flex gap-2 flex-wrap">
              {expandImages.map((img, i) => (
                <img
                  key={i}
                  src={img}
                  alt={`${product.name} ${i + 1}`}
                  className="w-24 h-24 rounded-lg object-cover border border-gray-100 shadow-sm"
                  onError={(e) => { e.target.src = FALLBACK_IMAGE; }}
                />
              ))}
            </div>
          )}

          {product.units?.length > 0 && (
            <div>
              <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">What you'll receive</p>
              <div className="flex flex-wrap gap-2">
                {product.units.map((unit, i) => (
                  <div key={i} className="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    <FlaskConical size={13} className="text-gray-400" />
                    <span className="text-xs font-semibold text-gray-700">{unit.qty}× {unit.size_label}</span>
                    <span className="text-[10px] text-gray-400">{unit.sku}</span>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default LawnProductCard;