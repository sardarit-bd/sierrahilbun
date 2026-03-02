import { useState } from 'react';
import { ChevronDown, X } from 'lucide-react';
import { FALLBACK_IMAGE } from '@/components/YardPlan/helpers';
import LawnProductCard from '@/components/YardPlan/LawnProductCard';

// -------------------------------------------------------
// Product Thumbnail
// -------------------------------------------------------
const ProductThumbnail = ({ product, onClick }) => {
    const [imgError, setImgError] = useState(false);
    const image        = (!imgError && product.primary_image) ? product.primary_image : FALLBACK_IMAGE;
    const shippingLabel = product.units?.map(u => `${u.qty}× ${u.size_label}`).join(' + ') ?? '';
    const ozShipped     = product.ozShipped ? `${product.ozShipped} oz` : null;

    return (
        <button
            onClick={onClick}
            className="flex flex-col items-center gap-1.5 group flex-shrink-0"
        >
            {/* Image */}
            <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border border-gray-100 shadow-sm group-hover:shadow-md group-hover:border-green-200 transition-all duration-200">
                <img
                    src={image}
                    alt={product.name}
                    className="w-full h-full object-cover"
                    onError={() => setImgError(true)}
                />
            </div>

            {/* Shipping info */}
            <div className="text-center">
                {shippingLabel && (
                    <p className="text-[10px] font-bold text-gray-700 leading-tight">
                        {shippingLabel}
                    </p>
                )}
                {ozShipped && (
                    <p className="text-[10px] text-gray-400 leading-tight">
                        {ozShipped}
                    </p>
                )}
            </div>
        </button>
    );
};

// -------------------------------------------------------
// Product Detail Modal
// -------------------------------------------------------
const ProductModal = ({ product, onClose }) => {
    if (!product) return null;

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            onClick={onClose}
        >
            <div
                className="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto"
                onClick={e => e.stopPropagation()}
            >
                {/* Modal header */}
                <div className="flex items-center justify-between p-4 border-b border-gray-100">
                    <p className="text-sm font-bold text-gray-900">{product.name}</p>
                    <button
                        onClick={onClose}
                        className="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors"
                    >
                        <X size={14} className="text-gray-600" />
                    </button>
                </div>

                {/* Full product card inside modal */}
                <div className="p-4">
                    <LawnProductCard product={product} defaultExpanded />
                </div>
            </div>
        </div>
    );
};

// -------------------------------------------------------
// LawnFeatureRow
// -------------------------------------------------------
const LawnFeatureRow = ({ feature }) => {
    const [isExpanded, setIsExpanded]       = useState(false);
    const [selectedProduct, setSelectedProduct] = useState(null);
    const [iconError, setIconError]         = useState(false);

    const productCount = feature.products?.length ?? 0;

    return (
        <>
            <div className="border border-gray-100 rounded-xl overflow-hidden bg-white shadow-sm">

                {/* Feature header */}
                <button
                    onClick={() => setIsExpanded(!isExpanded)}
                    className="w-full flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors text-left"
                >
                    {/* Icon */}
                    {feature.icon_url && !iconError ? (
                        <img
                            src={feature.icon_url}
                            alt={feature.title}
                            className="w-10 h-10 rounded-full object-cover flex-shrink-0"
                            onError={() => setIconError(true)}
                        />
                    ) : (
                        <div className="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <span className="text-green-700 text-xs font-bold">
                                {feature.title?.charAt(0) ?? '?'}
                            </span>
                        </div>
                    )}

                    {/* Title + subtitle */}
                    <div className="flex-grow min-w-0">
                        <p className="text-sm font-bold text-gray-900 leading-snug">
                            {feature.title}
                        </p>
                        {feature.subtitle && (
                            <p className="text-xs text-gray-500 mt-0.5 truncate">
                                {feature.subtitle}
                            </p>
                        )}
                    </div>

                    {/* Count + chevron */}
                    <div className="flex items-center gap-2 flex-shrink-0">
                        <span className="text-xs text-gray-400 font-medium">
                            {productCount} {productCount === 1 ? 'product' : 'products'}
                        </span>
                        <ChevronDown
                            size={16}
                            className={`text-gray-400 transform transition-transform duration-300 ${isExpanded ? 'rotate-180' : ''}`}
                        />
                    </div>
                </button>

                {/* Expanded — thumbnails row */}
                <div className={`overflow-hidden transition-all duration-500 ease-in-out ${isExpanded ? 'max-h-[400px] opacity-100' : 'max-h-0 opacity-0'}`}>
                    <div className="px-4 pb-4 pt-3 border-t border-gray-100">
                        <div className="flex flex-wrap gap-4">
                            {feature.products.map((product) => (
                                <ProductThumbnail
                                    key={product.slug}
                                    product={product}
                                    onClick={() => setSelectedProduct(product)}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Product detail modal */}
            <ProductModal
                product={selectedProduct}
                onClose={() => setSelectedProduct(null)}
            />
        </>
    );
};

export default LawnFeatureRow;