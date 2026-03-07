import { useState } from 'react';
import { ChevronDown, Check, Droplets, Package, FlaskConical, MinusCircle } from 'lucide-react';
import { FALLBACK_IMAGE, PRICING_TYPE_STYLES } from '@/components/YardPlan/helpers';

// ─────────────────────────────────────────────
// Inline Product Card — shown in expanded row
// ─────────────────────────────────────────────
const InlineProductCard = ({ product }) => {
    const [imgError, setImgError] = useState(false);

    const pricingStyle  = PRICING_TYPE_STYLES[product.pricing_type] ?? PRICING_TYPE_STYLES.addon;
    const displayImage  = (!imgError && product.primary_image) ? product.primary_image : FALLBACK_IMAGE;
    const benefits      = Array.isArray(product.benefits) ? product.benefits : [];
    const shippingUnits = product.units?.map(u => `${u.qty}× ${u.size_label}`).join(' + ') ?? '';
    const ozNeeded      = product.oz_needed  ? Number(product.oz_needed).toFixed(1)  : null;
    const ozShipped     = product.oz_shipped ? Number(product.oz_shipped).toFixed(1) : null;

    return (
        <div className="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-green-100 transition-all duration-300 overflow-hidden self-start">

            {/* Top accent line */}
            <div className="h-0.5 w-full" style={{ backgroundColor: '#007A55' }} />

            <div className="p-4 flex gap-4">

                {/* ── LEFT: image + metrics ── */}
                <div className="flex flex-col gap-2 flex-shrink-0 w-24">

                    {/* Image with badge */}
                    <div className="relative">
                        <div className="w-24 h-24 rounded-xl overflow-hidden border border-gray-100 bg-gray-50">
                            <img
                                src={displayImage}
                                alt={product.name}
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                onError={() => setImgError(true)}
                            />
                        </div>
                        {product.pricing_type === 'addon' && (
                            <span className={`absolute -top-1.5 -right-1.5 px-1.5 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wide shadow-sm ${pricingStyle.bg} ${pricingStyle.text}`}>
                                {pricingStyle.label}
                            </span>
                        )}
                    </div>

                    {/* Metrics */}
                    {ozNeeded && (
                        <div className="flex items-center gap-1.5 bg-gray-50 rounded-lg px-2 py-1.5 border border-gray-100">
                            <FlaskConical size={11} className="text-blue-400 flex-shrink-0" />
                            <div className="min-w-0">
                                <p className="text-[10px] font-extrabold text-gray-800 leading-none">{ozNeeded} oz</p>
                                <p className="text-[9px] text-gray-400 leading-none mt-0.5">needed</p>
                            </div>
                        </div>
                    )}
                    {ozShipped && (
                        <div className="flex items-center gap-1.5 bg-gray-50 rounded-lg px-2 py-1.5 border border-gray-100">
                            <Droplets size={11} className="text-green-500 flex-shrink-0" />
                            <div className="min-w-0">
                                <p className="text-[10px] font-extrabold text-gray-800 leading-none">{ozShipped} oz</p>
                                <p className="text-[9px] text-gray-400 leading-none mt-0.5">shipped</p>
                            </div>
                        </div>
                    )}
                    {shippingUnits && (
                        <div className="flex items-center gap-1.5 bg-gray-50 rounded-lg px-2 py-1.5 border border-gray-100">
                            <Package size={11} className="text-purple-400 flex-shrink-0" />
                            <div className="min-w-0">
                                <p className="text-[10px] font-extrabold text-gray-800 leading-none truncate">{shippingUnits}</p>
                                <p className="text-[9px] text-gray-400 leading-none mt-0.5">shipped</p>
                            </div>
                        </div>
                    )}
                </div>

                {/* ── RIGHT: title + subtitle + details ── */}
                <div className="flex-grow min-w-0 flex flex-col gap-1.5">

                    {/* Name + price */}
                    <div>
                        <h4 className="text-sm font-extrabold text-gray-900 leading-snug">
                            {product.name}
                        </h4>
                        {product.subtitle && (
                            <p className="text-[11px] text-gray-400 leading-snug mt-0.5">
                                {product.subtitle}
                            </p>
                        )}
                        {product.pricing_type === 'addon' && product.total_price > 0 && (
                            <span className="inline-block mt-1 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-md px-2 py-0.5">
                                +${Number(product.total_price).toFixed(2)}
                            </span>
                        )}
                    </div>

                    {/* Divider */}
                    {(product.description || benefits.length > 0) && (
                        <div className="border-t border-gray-100 pt-1.5 flex flex-col gap-1.5">

                            {/* Description */}
                            {product.description && (
                                <p className="text-[11px] text-gray-500 leading-relaxed">
                                    {product.description}
                                </p>
                            )}

                            {/* Benefits */}
                            {benefits.length > 0 && (
                                <ul className="space-y-1">
                                    {benefits.map((b, i) => (
                                        <li key={i} className="flex items-start gap-1.5 text-[11px] text-gray-600">
                                            <Check size={10} className="text-green-500 flex-shrink-0 mt-0.5" strokeWidth={3} />
                                            <span>{b}</span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    )}
                </div>

            </div>
        </div>
    );
};

const LawnFeatureRow = ({ feature }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const [iconError, setIconError]   = useState(false);

    const isNeeded     = feature.needed ?? true;
    const productCount = feature.products?.length ?? 0;
    const canExpand    = isNeeded && productCount > 0;

    return (
        <div className={`border rounded-xl overflow-hidden shadow-sm transition-all duration-200 ${
            isNeeded ? 'border-gray-100 bg-white hover:border-gray-200' : 'border-gray-100 bg-gray-50/60'
        }`}>

            {/* Feature header */}
            <div
                className={`w-full flex items-center gap-4 p-4 text-left ${
                    canExpand ? 'cursor-pointer hover:bg-gray-50 transition-colors' : 'cursor-default'
                }`}
                onClick={() => canExpand && setIsExpanded(!isExpanded)}
            >
                {/* Icon */}
                {feature.image_url && !iconError ? (
                    <img
                        src={feature.icon_url}
                        alt={feature.title}
                        className={`w-10 h-10 rounded-full object-cover flex-shrink-0 ${!isNeeded ? 'opacity-40' : ''}`}
                        onError={() => setIconError(true)}
                    />
                ) : (
                    <div className={`w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ${
                        isNeeded ? 'bg-green-100' : 'bg-gray-100'
                    }`}>
                        <span className={`text-xs font-bold ${isNeeded ? 'text-green-700' : 'text-gray-400'}`}>
                            {feature.title?.charAt(0) ?? '?'}
                        </span>
                    </div>
                )}

                {/* Title + subtitle */}
                <div className="flex-grow min-w-0">
                    <div className="flex items-center gap-2 flex-wrap">
                        <p className={`text-sm font-bold leading-snug ${
                            isNeeded ? 'text-gray-900' : 'text-gray-400'
                        }`}>
                            {feature.title}
                        </p>
                        {!isNeeded && (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-400">
                                <MinusCircle size={10} />
                                not needed for your lawn
                            </span>
                        )}
                    </div>
                    {feature.subtitle && (
                        <p className={`text-xs mt-0.5 truncate ${
                            isNeeded ? 'text-gray-500' : 'text-gray-400'
                        }`}>
                            {feature.subtitle}
                        </p>
                    )}
                </div>

                {/* Count + chevron */}
                {canExpand && (
                    <div className="flex items-center gap-2 flex-shrink-0">
                        <span className="text-xs text-gray-400 font-medium">
                            {productCount} {productCount === 1 ? 'product' : 'products'}
                        </span>
                        <div className={`w-6 h-6 rounded-full flex items-center justify-center transition-colors duration-200 ${
                            isExpanded ? 'bg-green-100' : 'bg-gray-100'
                        }`}>
                            <ChevronDown
                                size={14}
                                className={`transition-all duration-300 ${
                                    isExpanded ? 'rotate-180 text-green-600' : 'text-gray-400'
                                }`}
                            />
                        </div>
                    </div>
                )}
            </div>

            {/* Expanded: inline product cards */}
            {canExpand && (
                <div className={`overflow-hidden transition-all duration-500 ease-in-out ${
                    isExpanded ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0'
                }`}>
                    <div className="px-4 pb-4 pt-2 border-t border-gray-100 bg-gray-50/40">
                        <div className="flex flex-wrap gap-3 mt-2">
                            {feature.products.map((product) => (
                                <div key={product.slug} className="w-full sm:w-[calc(50%-6px)]">
                                    <InlineProductCard product={product} />
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default LawnFeatureRow;