import React, { useRef, useState, useEffect } from 'react';
import { ChevronLeft, ChevronRight, Star, Truck, ShoppingCart, Sparkles, TrendingUp, Eye, X, Minus, Plus } from 'lucide-react';
import AddToCartButton from '../AddToCartButton';
import { Link, router } from '@inertiajs/react';

declare function route(name: string, params?: any): string;

interface Product {
    id: number;
    name: string;
    slug: string;
    subtitle: string | null;
    category: string | null;
    image: string;
    price: number;
    min_price: number;
    max_price: number;
    rating: number;
    reviews_count: number;
}

interface Props {
    products: Product[];
}

const QuickViewModal = ({ product, onClose }: { product: Product; onClose: () => void }) => {
    const [quantity, setQuantity] = useState(1);

    useEffect(() => {
        document.body.style.overflow = 'hidden';
        return () => { document.body.style.overflow = 'unset'; };
    }, []);

    const savingsPercent = product.max_price > product.price
        ? Math.round(((product.max_price - product.price) / product.max_price) * 100)
        : 0;

    const cartProduct = {
        id:            product.id,
        name:          product.name,
        slug:          product.slug,
        subtitle:      product.subtitle ?? '',
        category:      product.category ?? null,
        image:         product.image,
        price:         product.price,
        min_price:     product.min_price,
        max_price:     product.max_price,
        rating:        product.rating,
        reviews_count: product.reviews_count,
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
            <div
                className="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity animate-in fade-in duration-200"
                onClick={onClose}
            />

            <div className="relative bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col md:flex-row max-h-[90vh] md:h-auto animate-in fade-in zoom-in-95 duration-200">

                <button
                    onClick={onClose}
                    className="absolute top-4 right-4 z-10 p-2 bg-white/80 backdrop-blur rounded-full hover:bg-gray-100 transition-colors"
                >
                    <X size={20} className="text-gray-500" />
                </button>

                {/* Image */}
                <div className="w-full md:w-1/2 bg-gray-50 flex items-center justify-center p-8 md:p-12 relative">
                    <img
                        src={product.image}
                        alt={product.name}
                        className="w-full h-full max-h-[300px] md:max-h-[400px] object-contain mix-blend-multiply"
                    />
                    {savingsPercent > 0 && (
                        <div className="absolute top-6 left-6 bg-[#2E7D32] text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                            SAVE {savingsPercent}%
                        </div>
                    )}
                </div>

                {/* Details */}
                <div className="w-full md:w-1/2 p-6 md:p-10 flex flex-col overflow-y-auto">
                    <div className="mb-auto">
                        <h2 className="text-2xl md:text-3xl font-black text-gray-900 font-serif leading-tight mb-2">
                            {product.name}
                        </h2>
                        {product.subtitle && (
                            <p className="text-gray-500 font-medium text-lg mb-4">{product.subtitle}</p>
                        )}

                        <div className="flex items-center gap-4 mb-6">
                            <div className="flex text-yellow-400">
                                {[...Array(5)].map((_, i) => (
                                    <Star
                                        key={i}
                                        size={18}
                                        fill={i < Math.floor(product.rating) ? 'currentColor' : 'none'}
                                        className={i >= Math.floor(product.rating) ? 'text-gray-200' : ''}
                                    />
                                ))}
                            </div>
                            <span className="text-sm text-gray-500 font-bold underline decoration-gray-300 underline-offset-4">
                                {product.reviews_count} Reviews
                            </span>
                        </div>

                        <div className="flex items-baseline gap-3 mb-6">
                            <span className="text-3xl font-black text-[#2E7D32]">${product.price.toFixed(2)}</span>
                            {product.max_price > product.price && (
                                <span className="text-lg text-gray-400 line-through font-medium">
                                    ${product.max_price.toFixed(2)}
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="border-t border-gray-100 pt-6 mt-4">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <div className="flex items-center border border-gray-200 rounded-xl px-2 py-1 w-fit">
                                <button
                                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                                    className="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <Minus size={16} />
                                </button>
                                <span className="w-8 text-center font-bold text-gray-900">{quantity}</span>
                                <button
                                    onClick={() => setQuantity(quantity + 1)}
                                    className="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <Plus size={16} />
                                </button>
                            </div>

                            <AddToCartButton
                                product={cartProduct}
                                quantity={quantity}
                                size="large"
                                className="flex-1"
                            />
                        </div>

                        <div className="mt-4 flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-wide">
                            <Truck size={14} className="text-[#2E7D32]" />
                            Free Shipping on this item
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};


const ProductCard = ({ product, onQuickView }: { product: Product; onQuickView: (p: Product) => void }) => {
    const savingsPercent = product.max_price > product.price
        ? Math.round(((product.max_price - product.price) / product.max_price) * 100)
        : 0;

    const cartProduct = {
        id:            product.id,
        name:          product.name,
        slug:          product.slug,
        subtitle:      product.subtitle ?? '',
        category:      product.category ?? null,
        image:         product.image,
        price:         product.price,
        min_price:     product.min_price,
        max_price:     product.max_price,
        rating:        product.rating,
        reviews_count: product.reviews_count,
    };

    return (
        <div className="w-80 flex-shrink-0 group relative">
            <div className="h-full bg-white rounded-3xl p-5 flex flex-col transition-all duration-500 border border-gray-100 hover:border-transparent hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] relative z-10 overflow-hidden">

                <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none" />

                {/* Badge */}
                <div className="flex justify-between items-start mb-4 relative z-20 min-h-[24px]">
                    {savingsPercent > 0 ? (
                        <span className="bg-emerald-50 text-emerald-700 text-[10px] font-black tracking-wider uppercase px-2 py-1 rounded-lg border border-emerald-100">
                            -{savingsPercent}%
                        </span>
                    ) : <div />}

                    {product.category && (
                        <span className="bg-violet-50 text-violet-700 text-[10px] font-bold tracking-wide uppercase px-2 py-1 rounded-lg border border-violet-100">
                            {product.category}
                        </span>
                    )}
                </div>

                {/* Image */}
                <div className="relative mb-6 group-hover:-translate-y-1 transition-transform duration-500">
                    <div className="absolute inset-0 bg-gray-100/50 rounded-2xl transform rotate-3 group-hover:rotate-6 transition-transform duration-500 origin-bottom-right" />
                    <div className="relative bg-gray-50 rounded-2xl p-6 overflow-hidden">
                        <img
                            src={product.image}
                            alt={product.name}
                            className="w-full h-48 object-contain mix-blend-multiply filter contrast-105"
                        />
                        <div className="absolute inset-0 bg-black/5 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <button
                                onClick={() => onQuickView(product)}
                                className="bg-white text-gray-900 px-5 py-2.5 rounded-full font-bold text-sm shadow-xl flex items-center gap-2 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 hover:bg-emerald-50 hover:text-[#2E7D32] cursor-pointer"
                            >
                                <Eye size={16} /> Quick View
                            </button>
                        </div>
                    </div>
                </div>

                {/* Content */}
                <div className="flex-1 flex flex-col gap-2 relative z-20">
                    <div>
                        <Link href={route('products.show', product.slug)}>
                            <h3 className="text-gray-900 font-bold text-lg leading-tight group-hover:text-emerald-700 transition-colors cursor-pointer">
                                {product.name}
                            </h3>
                        </Link>
                        {product.subtitle && (
                            <p className="text-gray-500 text-xs font-medium mt-1">{product.subtitle}</p>
                        )}
                    </div>

                    {/* Rating */}
                    {product.rating > 0 && (
                        <div className="flex items-center gap-2 mt-1">
                            <div className="flex gap-0.5">
                                {[...Array(5)].map((_, i) => (
                                    <Star
                                        key={i}
                                        size={14}
                                        className={`${
                                            i < Math.floor(product.rating)
                                                ? 'fill-amber-400 text-amber-400'
                                                : i < product.rating
                                                ? 'fill-amber-400 text-amber-400 opacity-50'
                                                : 'fill-gray-200 text-gray-200'
                                        }`}
                                    />
                                ))}
                            </div>
                            <span className="text-gray-400 text-xs font-medium pt-0.5">
                                ({product.reviews_count})
                            </span>
                        </div>
                    )}

                    {/* Price */}
                    <div className="mt-auto pt-4 flex items-end justify-between border-t border-gray-50">
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="text-gray-900 font-extrabold text-2xl">
                                    ${product.price.toFixed(2)}
                                </span>
                                {product.max_price > product.price && (
                                    <span className="text-gray-400 text-sm line-through decoration-red-400/50">
                                        ${product.max_price.toFixed(2)}
                                    </span>
                                )}
                            </div>
                            <div className="flex items-center gap-1 text-emerald-600 text-[10px] font-bold uppercase tracking-wide mt-1">
                                <Truck size={12} />
                                <span>Free Shipping</span>
                            </div>
                        </div>

                        <AddToCartButton
                            product={cartProduct}
                            size="default"
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

//Main Section

export default function ProductSection({ products = [] }: Props) {
    const scrollContainerRef = useRef<HTMLDivElement>(null);
    const [canScrollLeft, setCanScrollLeft]   = useState(false);
    const [canScrollRight, setCanScrollRight] = useState(true);
    const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);

    const checkScrollButtons = () => {
        if (scrollContainerRef.current) {
            const { scrollLeft, scrollWidth, clientWidth } = scrollContainerRef.current;
            setCanScrollLeft(scrollLeft > 0);
            setCanScrollRight(scrollLeft < scrollWidth - clientWidth - 5);
        }
    };

    useEffect(() => {
        checkScrollButtons();
        window.addEventListener('resize', checkScrollButtons);
        return () => window.removeEventListener('resize', checkScrollButtons);
    }, [products]);

    const scroll = (direction: 'left' | 'right') => {
        if (scrollContainerRef.current) {
            scrollContainerRef.current.scrollBy({
                left: direction === 'left' ? -350 : 350,
                behavior: 'smooth',
            });
            setTimeout(checkScrollButtons, 300);
        }
    };

    if (products.length === 0) return null;

    return (
        <section className="w-full py-24 bg-[#FAFAF9] relative overflow-hidden font-sans">

            <div className="absolute top-0 left-0 w-[1000px] h-[1000px] bg-emerald-100/20 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 mix-blend-multiply pointer-events-none" />
            <div className="absolute bottom-0 right-0 w-[800px] h-[800px] bg-blue-100/20 rounded-full blur-3xl translate-x-1/3 translate-y-1/3 mix-blend-multiply pointer-events-none" />

            <style>{`
                .no-scrollbar::-webkit-scrollbar { display: none; }
                .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            `}</style>

            <div className="max-w-[1400px] mx-auto px-6 relative z-10">

                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div className="max-w-xl">
                        <h2 className="text-3xl md:text-5xl font-extrabold text-[#1A1A1A] leading-tight mb-4 font-poppins">
                            What Your Lawn Needs
                        </h2>
                        <p className="text-gray-500 text-lg leading-relaxed">
                            Popular products loved by lawn care enthusiasts. Limited time savings on premium solutions.
                        </p>
                    </div>
                </div>

                {/* Carousel */}
                <div className="relative -mx-6 px-6 md:mx-0 md:px-0">

                    <button
                        onClick={() => scroll('left')}
                        className={`absolute left-4 md:-left-4 top-1/2 -translate-y-1/2 z-30 p-3 md:p-4 rounded-full border shadow-xl transition-all duration-300 flex items-center justify-center bg-white/90 backdrop-blur-md ${
                            !canScrollLeft
                                ? 'opacity-0 pointer-events-none'
                                : 'border-gray-100 text-gray-900 hover:border-emerald-500 hover:text-emerald-600 hover:scale-110 active:scale-95'
                        }`}
                        disabled={!canScrollLeft}
                        aria-label="Scroll left"
                    >
                        <ChevronLeft size={24} />
                    </button>

                    <div
                        ref={scrollContainerRef}
                        className="flex gap-6 overflow-x-auto scroll-smooth no-scrollbar pb-12 pt-4 pl-4"
                        onScroll={checkScrollButtons}
                    >
                        {products.map((product) => (
                            <ProductCard
                                key={product.id}
                                product={product}
                                onQuickView={setSelectedProduct}
                            />
                        ))}
                    </div>

                    <button
                        onClick={() => scroll('right')}
                        className={`absolute right-4 md:-right-4 top-1/2 -translate-y-1/2 z-30 p-3 md:p-4 rounded-full border shadow-xl transition-all duration-300 flex items-center justify-center bg-white/90 backdrop-blur-md ${
                            !canScrollRight
                                ? 'opacity-0 pointer-events-none'
                                : 'border-gray-100 text-gray-900 hover:border-emerald-500 hover:text-emerald-600 hover:scale-110 active:scale-95'
                        }`}
                        disabled={!canScrollRight}
                        aria-label="Scroll right"
                    >
                        <ChevronRight size={24} />
                    </button>

                    <div className="absolute left-0 top-0 bottom-0 w-12 bg-gradient-to-r from-[#FAFAF9] to-transparent md:hidden pointer-events-none z-20" />
                    <div className="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-[#FAFAF9] to-transparent md:hidden pointer-events-none z-20" />
                </div>

                <div className="mt-4 text-center">
                    <Link
                        href={route('products.index')}
                        className="inline-flex items-center text-sm font-bold text-gray-900 border-b-2 border-transparent hover:border-emerald-500 transition-colors pb-1"
                    >
                        View All Products <ChevronRight size={16} className="ml-1" />
                    </Link>
                </div>
            </div>

            {selectedProduct && (
                <QuickViewModal
                    product={selectedProduct}
                    onClose={() => setSelectedProduct(null)}
                />
            )}
        </section>
    );
}