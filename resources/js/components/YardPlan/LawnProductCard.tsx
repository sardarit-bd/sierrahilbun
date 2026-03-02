import { useState } from 'react';
import { ChevronDown, Check } from 'lucide-react';
import { FALLBACK_IMAGE, PRICING_TYPE_STYLES } from './helpers';

const LawnProductCard = ({ product }) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const [imgError, setImgError]     = useState(false);

  const pricingStyle  = PRICING_TYPE_STYLES[product.pricing_type] ?? PRICING_TYPE_STYLES.addon;
  const displayImage  = (!imgError && product.primary_image) ? product.primary_image : FALLBACK_IMAGE;
  const expandImages  = product.images?.length ? product.images : [];
  const benefits      = Array.isArray(product.benefits) ? product.benefits : [];
  const shippingLabel = product.units?.map(u => `${u.qty}× ${u.size_label}`).join(' + ') ?? '';

  const cardTitle    = product.feature_title    ?? product.name;
  const cardSubtitle = product.feature_subtitle ?? product.subtitle ?? '';

  return (
    <div className="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200">

      {/* ── Collapsed view (always visible) ── */}
      <div className="flex gap-4">
        <div className="w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
          <img
            src={displayImage}
            alt={cardTitle}
            className="w-full h-full object-cover"
            onError={() => setImgError(true)}
          />
        </div>

        <div className="flex-grow">
          {/* Badge row */}
          <div className="flex flex-wrap items-center gap-1.5 mb-1">
            <span className={`px-2 py-0.5 rounded text-[10px] sm:text-xs font-bold uppercase tracking-wide ${pricingStyle.bg} ${pricingStyle.text}`}>
              {pricingStyle.label}
            </span>
            {product.pricing_type === 'addon' && product.total_price > 0 && (
              <span className="px-2 py-0.5 rounded text-[10px] font-bold text-gray-600 bg-gray-100">
                +${product.total_price.toFixed(2)}
              </span>
            )}
          </div>

          {/* Feature title */}
          <h4 className="text-sm sm:text-base font-bold text-gray-900 leading-snug mb-1">
            {cardTitle}
          </h4>

          {/* Feature subtitle */}
          {cardSubtitle && (
            <p className="text-xs text-gray-500">{cardSubtitle}</p>
          )}

          {/* See more toggle */}
          <button
            onClick={() => setIsExpanded(!isExpanded)}
            className="mt-2 text-xs font-bold text-gray-400 flex items-center gap-1 hover:text-green-700 transition-colors"
          >
            {isExpanded ? 'See less' : 'See more'}
            <ChevronDown size={14} className={`transform transition-transform ${isExpanded ? 'rotate-180' : ''}`} />
          </button>
        </div>
      </div>

      {/* ── Expanded details ── */}
      <div className={`overflow-hidden transition-all duration-500 ease-in-out ${isExpanded ? 'max-h-[700px] opacity-100 mt-3' : 'max-h-0 opacity-0'}`}>

        {/* Product name header */}
        <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-1">Product</p>
        <p className="text-sm font-bold text-gray-800 mb-3">{product.name}</p>

        {/* Product images */}
        {expandImages.length > 0 && (
          <div className="flex gap-2 flex-wrap mb-3">
            {expandImages.map((img, i) => (
              <img
                key={i}
                src={img}
                alt={product.name}
                className="w-32 h-32 rounded-lg shadow-sm border border-gray-100 object-cover"
                onError={(e) => { e.target.src = FALLBACK_IMAGE; }}
              />
            ))}
          </div>
        )}

        {/* Description */}
        {product.description && (
          <p className="text-sm text-gray-600 mb-3">{product.description}</p>
        )}

        {/* Benefits */}
        {benefits.length > 0 && (
          <ul className="space-y-1 mb-3">
            {benefits.map((benefit, i) => (
              <li key={i} className="flex items-start gap-2 text-sm text-gray-600">
                <Check size={13} className="text-green-600 flex-shrink-0 mt-0.5" strokeWidth={3} />
                <span>{benefit}</span>
              </li>
            ))}
          </ul>
        )}

        {/* Usage instructions */}
        {product.usage_instructions && (
          <p className="text-sm text-gray-500 italic mb-3">{product.usage_instructions}</p>
        )}

        {/* Oz + shipping */}
        <div className="flex flex-wrap gap-2 text-xs text-gray-500">
          <span className="bg-gray-50 rounded px-2 py-1">
            <span className="font-semibold text-gray-700">{product.oz_needed} oz</span> needed
          </span>
          <span className="bg-gray-50 rounded px-2 py-1">
            <span className="font-semibold text-gray-700">{product.oz_shipped} oz</span> shipped
          </span>
          {shippingLabel && (
            <span className="bg-gray-50 rounded px-2 py-1 font-medium">{shippingLabel}</span>
          )}
        </div>
      </div>
    </div>
  );
};

export default LawnProductCard;