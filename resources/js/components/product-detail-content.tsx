import React, { useState } from 'react';
import {
    Star, Minus, Plus, Truck, ShieldCheck,
    ChevronDown, ChevronUp, Check, Droplets, AlertCircle,
    Share2, ThumbsUp, ArrowLeft
} from 'lucide-react';
import { Head, Link, router } from '@inertiajs/react';
import AddToCartButton from '@/components/AddToCartButton';

declare function route(name: string, params?: any): string;

// ─── Types ────────────────────────────────────────────────────────────────────

interface Variant {
    id: number;
    sku: string;
    size: string;
    price: number;
    originalPrice: number;
    inStock: boolean;
    isDefault: boolean;
}

interface Review {
    id: number;
    author: string;
    verified: boolean;
    rating: number;
    date: string;
    title: string;
    content: string;
    helpful: number;
    images: string[];
    hasLiked: boolean;
}

interface Product {
    id: number;
    title: string;
    slug: string;
    subtitle: string;
    description: string;
    benefits: string[];
    ingredients: string;
    howToUse: string;
    rating: number;
    reviewCount: number;
    category: string | null;
    images: string[];
    variants: Variant[];
}

interface Props {
    product: Product;
    reviews?: Review[];
}

// ─── Sub-Components ───────────────────────────────────────────────────────────

const Accordion = ({
    title,
    children,
    defaultOpen = false,
    icon: Icon,
}: {
    title: string;
    children: React.ReactNode;
    defaultOpen?: boolean;
    icon?: React.ComponentType<{ size?: number; className?: string }>;
}) => {
    const [isOpen, setIsOpen] = useState(defaultOpen);

    return (
        <div className="border-b border-gray-100 last:border-0">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="w-full py-5 flex items-center justify-between text-left group hover:bg-gray-50/50 transition-colors px-2 -mx-2 rounded-lg"
            >
                <div className="flex items-center gap-3">
                    {Icon && <Icon size={20} className="text-[#2E7D32]" />}
                    <span className="font-bold text-gray-900 text-lg group-hover:text-[#2E7D32] transition-colors font-serif">
                        {title}
                    </span>
                </div>
                {isOpen ? (
                    <ChevronUp className="text-gray-400" />
                ) : (
                    <ChevronDown className="text-gray-400" />
                )}
            </button>
            <div
                className={`overflow-hidden transition-all duration-300 ease-in-out ${
                    isOpen ? 'max-h-[500px] opacity-100 pb-6' : 'max-h-0 opacity-0'
                }`}
            >
                <div className="text-gray-600 leading-relaxed text-base pl-2">{children}</div>
            </div>
        </div>
    );
};

const ReviewCard = ({ review }: { review: Review }) => {
    const [helpful, setHelpful]   = useState(review.helpful);
    const [hasLiked, setHasLiked] = useState(review.hasLiked);
    const [loading, setLoading]   = useState(false);

    const toggleHelpful = () => {
        if (loading) return;
        setLoading(true);

        // Optimistic UI update
        const wasLiked = hasLiked;
        setHasLiked(!wasLiked);
        setHelpful((prev) => wasLiked ? prev - 1 : prev + 1);

        router.post(
            route('reviews.helpful', review.id),
            {},
            {
                preserveScroll: true,
                onError: () => {
                    // Revert on error
                    setHasLiked(wasLiked);
                    setHelpful((prev) => wasLiked ? prev + 1 : prev - 1);
                },
                onFinish: () => setLoading(false),
            }
        );
    };

    return (
        <div className="border-b border-gray-200 py-8 last:border-0">
            <div className="flex justify-between items-start mb-2">
                <div className="flex items-center gap-2">
                    <div className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-500 text-sm">
                        {review.author.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h4 className="font-bold text-gray-900 text-sm">{review.author}</h4>
                        {review.verified && (
                            <span className="text-xs text-[#2E7D32] flex items-center gap-0.5">
                                <ShieldCheck size={12} /> Verified Buyer
                            </span>
                        )}
                    </div>
                </div>
                <span className="text-xs text-gray-400">{review.date}</span>
            </div>

            <div className="flex items-center gap-2 my-3">
                <div className="flex">
                    {[...Array(5)].map((_, i) => (
                        <Star
                            key={i}
                            size={16}
                            className={i < review.rating ? 'fill-yellow-400 text-yellow-400' : 'fill-gray-100 text-gray-200'}
                        />
                    ))}
                </div>
                {review.title && (
                    <span className="font-bold text-gray-900 text-sm">{review.title}</span>
                )}
            </div>

            {review.content && (
                <p className="text-gray-600 text-sm leading-relaxed mb-4">{review.content}</p>
            )}

            {/* Review images */}
            {review.images?.length > 0 && (
                <div className="flex gap-2 mb-4 flex-wrap">
                    {review.images.map((img, i) => (
                        <img
                            key={i}
                            src={img}
                            alt=""
                            className="w-16 h-16 rounded-lg object-cover border border-gray-100"
                        />
                    ))}
                </div>
            )}

            {/* Helpful toggle */}
            <button
                onClick={toggleHelpful}
                disabled={loading}
                className={`flex items-center gap-1.5 text-xs font-bold transition-all px-3 py-1.5 rounded-full border ${
                    hasLiked
                        ? 'border-[#2E7D32] text-[#2E7D32] bg-green-50'
                        : 'border-gray-200 text-gray-400 hover:border-gray-400 hover:text-gray-600'
                } ${loading ? 'opacity-50 cursor-not-allowed' : ''}`}
            >
                <ThumbsUp
                    size={13}
                    className={hasLiked ? 'fill-[#2E7D32]' : ''}
                />
                {hasLiked ? 'Helpful' : 'Helpful'} ({helpful})
            </button>
        </div>
    );
};

// ─── Loading Skeleton ─────────────────────────────────────────────────────────

const LoadingSkeleton = () => (
    <div className="bg-[#FDFBF7] min-h-screen animate-pulse">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-12">
            <div className="h-4 w-48 bg-gray-200 rounded mb-8" />
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <div className="lg:col-span-7">
                    <div className="bg-gray-200 rounded-[2rem] aspect-square w-full" />
                </div>
                <div className="lg:col-span-5 space-y-4">
                    <div className="h-6 w-24 bg-gray-200 rounded-full" />
                    <div className="h-12 w-3/4 bg-gray-200 rounded" />
                    <div className="h-6 w-1/2 bg-gray-200 rounded" />
                    <div className="h-16 w-full bg-gray-200 rounded-xl" />
                    <div className="h-40 w-full bg-gray-200 rounded-3xl" />
                </div>
            </div>
        </div>
    </div>
);

// ─── Main Export ─────────────────────────────────────────────────────────────
// Guards against undefined product (Inertia race condition on first render)

export default function ProductShow({ product, reviews = [] }: Props) {
    if (!product) return <LoadingSkeleton />;

    // Safely normalise arrays that might be missing in some DB rows
    const safeProduct = {
        ...product,
        variants: product.variants ?? [],
        images:   product.images   ?? [],
        benefits: (() => {
            if (Array.isArray(product.benefits)) return product.benefits;
            if (typeof product.benefits === 'string') {
                try { return JSON.parse(product.benefits); } catch { return []; }
            }
            return [];
        })(),
    };

    return <ProductShowInner product={safeProduct} reviews={reviews} />;
}

// ─── Inner Component (product is guaranteed defined here) ─────────────────────

function ProductShowInner({ product, reviews }: { product: Product; reviews: Review[] }) {
    const [activeImage, setActiveImage]       = useState(0);
    const [quantity, setQuantity]             = useState(1);
    const [selectedVariant, setSelectedVariant] = useState<Variant | null>(
        product.variants.find((v) => v.isDefault) ?? product.variants[0] ?? null
    );

    const currentPrice  = selectedVariant?.price         ?? 0;
    const originalPrice = selectedVariant?.originalPrice ?? currentPrice;
    const inStock       = selectedVariant?.inStock        ?? false;
    const savingsPercent =
        originalPrice > currentPrice
            ? Math.round(((originalPrice - currentPrice) / originalPrice) * 100)
            : 0;

    const cartProduct = {
        id:            selectedVariant?.id ?? product.id,
        name:          product.title,
        slug:          '',
        subtitle:      product.subtitle      ?? '',
        category:      product.category      ?? null,
        image:         product.images[0]     ?? '/images/placeholder.png',
        price:         currentPrice,
        min_price:     currentPrice,
        max_price:     originalPrice,
        rating:        product.rating        ?? 0,
        reviews_count: product.reviewCount   ?? 0,
    };

    const approvedRating = reviews.length > 0
        ? reviews.reduce((sum, r) => sum + r.rating, 0) / reviews.length
        : 0;

    const approvedCount = reviews.length;
    

    return (
        <>
            <Head title={product.title} />

            <div className="bg-[#FDFBF7] min-h-screen font-sans text-gray-900 pb-32 lg:pb-20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-12">

                    {/* Breadcrumb */}
                    <nav className="flex items-center gap-2 text-xs font-bold text-gray-400 mb-8 uppercase tracking-wider">
                        <Link
                            href={route('products.index')}
                            className="hover:text-[#2E7D32] transition-colors flex items-center gap-1"
                        >
                            <ArrowLeft size={12} /> Shop
                        </Link>
                        {product.category && (
                            <>
                                <ChevronDown className="-rotate-90" size={12} />
                                <span>{product.category}</span>
                            </>
                        )}
                        <ChevronDown className="-rotate-90" size={12} />
                        <span className="text-gray-900 truncate max-w-[180px]">{product.title}</span>
                    </nav>

                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">

                        {/* ── Left: Image Gallery ── */}
                        <div className="lg:col-span-7">
                            <div className="sticky top-24">
                                <div className="flex flex-col-reverse lg:flex-row gap-4">

                                    {/* Thumbnails — only render when there's more than one image */}
                                    {product.images.length > 1 && (
                                        <div className="flex lg:flex-col gap-3 overflow-x-auto lg:overflow-visible no-scrollbar p-2 lg:pb-0">
                                            {product.images.map((img, idx) => (
                                                <button
                                                    key={idx}
                                                    onClick={() => setActiveImage(idx)}
                                                    className={`relative w-20 h-20 lg:w-24 lg:h-24 flex-shrink-0 rounded-2xl overflow-hidden border-2 transition-all duration-200 ${
                                                        activeImage === idx
                                                            ? 'border-[#2E7D32] shadow-md scale-105'
                                                            : 'border-transparent bg-white opacity-70 hover:opacity-100'
                                                    }`}
                                                >
                                                    <img src={img} alt="" className="w-full h-full object-cover mix-blend-multiply p-2" />
                                                </button>
                                            ))}
                                        </div>
                                    )}

                                    {/* Main Image */}
                                    <div className="flex-1 bg-white rounded-[2rem] p-8 md:p-12 shadow-sm border border-gray-100 flex items-center justify-center relative group overflow-hidden">
                                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-green-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700" />

                                        {savingsPercent > 0 && (
                                            <span className="absolute top-5 left-5 z-20 bg-[#2E7D32] text-white text-xs font-black px-3 py-1.5 rounded-full">
                                                SAVE {savingsPercent}%
                                            </span>
                                        )}

                                        <img
                                            src={product.images[activeImage] ?? '/images/placeholder.png'}
                                            alt={product.title}
                                            className="w-full h-auto max-h-[500px] object-contain mix-blend-multiply transition-transform duration-500 group-hover:scale-110 relative z-10"
                                        />

                                        <button className="absolute top-4 right-4 z-20 p-3 bg-white/90 backdrop-blur rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-[#2E7D32] shadow-sm">
                                            <Share2 size={20} />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* ── Right: Product Info ── */}
                        <div className="lg:col-span-5">
                            <div className="lg:sticky lg:top-24">

                                <div className="mb-6">
                                    <span className="text-[#2E7D32] font-bold text-xs uppercase tracking-widest bg-green-50 px-3 py-1 rounded-full">
                                        {product.category ?? 'Product'}
                                    </span>
                                </div>

                                <h1 className="text-3xl md:text-5xl font-black font-serif text-gray-900 mb-2 leading-[1.1]">
                                    {product.title}
                                </h1>

                                {product.subtitle && (
                                    <p className="text-xl text-gray-500 font-medium mb-6">{product.subtitle}</p>
                                )}

                                {/* Ratings */}
                                <div className="flex items-center gap-4 mb-8">
                                    <div className="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100">
                                        <Star size={16} className="fill-yellow-400 text-yellow-400" />
                                        <span className="font-bold text-yellow-700">
                                            {approvedRating ?? 0}
                                        </span>
                                    </div>
                                    <a
                                        href="#reviews"
                                        className="text-sm font-bold text-gray-500 hover:text-[#2E7D32] transition-colors border-b border-gray-300 hover:border-[#2E7D32] pb-0.5"
                                    >
                                        Read {approvedCount ?? 0} Reviews
                                    </a>
                                </div>

                                {/* Variant Selector */}
                                {product.variants.length > 1 && (
                                    <div className="mb-6">
                                        <span className="font-bold text-gray-900 text-sm block mb-3">Size / Option</span>
                                        <div className="flex flex-wrap gap-2">
                                            {product.variants.map((v) => (
                                                <button
                                                    key={v.id}
                                                    onClick={() => setSelectedVariant(v)}
                                                    disabled={!v.inStock}
                                                    className={`px-4 py-2 rounded-xl border-2 text-sm font-bold transition-all ${
                                                        selectedVariant?.id === v.id
                                                            ? 'border-[#2E7D32] bg-green-50 text-[#2E7D32]'
                                                            : v.inStock
                                                            ? 'border-gray-200 text-gray-700 hover:border-gray-400'
                                                            : 'border-gray-100 text-gray-300 cursor-not-allowed line-through'
                                                    }`}
                                                >
                                                    {v.size}{!v.inStock && ' (Out)'}
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Price */}
                                <div className="flex items-end gap-3 mb-8 pb-8 border-b border-gray-200">
                                    <span className="text-5xl font-black text-[#1A1A1A]">
                                        ${currentPrice.toFixed(2)}
                                    </span>
                                    {originalPrice > currentPrice && (
                                        <span className="text-xl text-gray-400 line-through mb-2 font-medium">
                                            ${originalPrice.toFixed(2)}
                                        </span>
                                    )}
                                </div>

                                {/* Description */}
                                {product.description && (
                                    <div dangerouslySetInnerHTML={{ __html: product.description ?? '' }} />
                                )}

                                {/* Benefits */}
                                {product.benefits.length > 0 && (
                                    <div className="grid grid-cols-1 gap-3 mb-8 mt-3">
                                        {product.benefits.map((benefit, i) => (
                                            <div
                                                key={i}
                                                className="flex items-center gap-3 text-sm text-gray-700 font-bold bg-white p-3 rounded-xl border border-gray-100 shadow-sm"
                                            >
                                                <div className="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 text-[#2E7D32]">
                                                    <Check size={14} strokeWidth={3} />
                                                </div>
                                                {benefit}
                                            </div>
                                        ))}
                                    </div>
                                )}

                                {/* Desktop Add to Cart */}
                                <div className="hidden lg:block bg-white p-6 rounded-3xl border border-gray-100 shadow-lg shadow-gray-100/50 mb-8">
                                    <div className="flex flex-col gap-4">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="font-bold text-gray-900">Quantity</span>
                                            <div className="flex items-center bg-gray-50 rounded-xl px-2 py-1 border border-gray-200">
                                                <button
                                                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                                                    className="p-2 text-gray-400 hover:text-gray-700 transition-colors"
                                                >
                                                    <Minus size={18} />
                                                </button>
                                                <span className="w-10 text-center font-black text-lg text-gray-900">
                                                    {quantity}
                                                </span>
                                                <button
                                                    onClick={() => setQuantity(quantity + 1)}
                                                    className="p-2 text-gray-400 hover:text-gray-700 transition-colors"
                                                >
                                                    <Plus size={18} />
                                                </button>
                                            </div>
                                        </div>

                                        {inStock ? (
                                            <AddToCartButton
                                                product={cartProduct}
                                                quantity={quantity}
                                                size="large"
                                                className="w-full"
                                            />
                                        ) : (
                                            <button
                                                disabled
                                                className="w-full bg-gray-200 text-gray-400 font-extrabold text-lg py-4 px-8 rounded-xl cursor-not-allowed"
                                            >
                                                Out of Stock
                                            </button>
                                        )}

                                        <div className="grid grid-cols-2 gap-4 mt-2">
                                            <div className="flex items-center justify-center gap-2 text-xs font-bold text-gray-500 bg-gray-50 py-2 rounded-lg">
                                                <Truck size={14} className="text-[#2E7D32]" /> Free Shipping
                                            </div>
                                            <div className="flex items-center justify-center gap-2 text-xs font-bold text-gray-500 bg-gray-50 py-2 rounded-lg">
                                                <ShieldCheck size={14} className="text-[#2E7D32]" /> Secure Checkout
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Accordions */}
                                <div className="border-t border-gray-200">
                                    {product.ingredients && (
                                        <Accordion title="Ingredients" icon={AlertCircle}>
                                            <p>{product.ingredients}</p>
                                        </Accordion>
                                    )}
                                    {product.howToUse && (
                                        <Accordion title="How to use" defaultOpen icon={Droplets}>
                                            <div className="flex flex-col gap-4">
                                                <div dangerouslySetInnerHTML={{ __html: product.howToUse ?? '' }} />
                                                <div className="grid grid-cols-3 gap-3">
                                                    {['Mix', 'Apply', 'Dry'].map((step, i) => (
                                                        <div key={i} className="bg-gray-50 p-3 rounded-lg text-center border border-gray-100">
                                                            <div className="w-8 h-8 mx-auto bg-white rounded-full flex items-center justify-center text-[#2E7D32] font-black text-sm mb-1 shadow-sm">
                                                                {i + 1}
                                                            </div>
                                                            <span className="text-[10px] font-bold uppercase tracking-wide text-gray-500">{step}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </Accordion>
                                    )}
                                    <Accordion title="Shipping & Returns" icon={Truck}>
                                        <p>Ships within 1–2 business days. 30-day money-back guarantee if you are not satisfied with the results.</p>
                                    </Accordion>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* ── Reviews ── */}
                    <div id="reviews" className="mt-24 border-t border-gray-200 pt-16">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">

                            {/* Summary sidebar */}
                            <div className="lg:col-span-4">
                                <h2 className="text-3xl font-black font-serif text-gray-900 mb-6">Customer Reviews</h2>

                                <div className="flex items-end gap-4 mb-6">
                                    <span className="text-6xl font-black text-gray-900">
                                        {approvedRating.toFixed(1)}
                                    </span>
                                    <div className="mb-2">
                                        <div className="flex text-yellow-400 mb-1">
                                            {[...Array(5)].map((_, i) => (
                                                <Star
                                                    key={i}
                                                    size={20}
                                                    fill={i < Math.round(approvedRating) ? 'currentColor' : 'none'}
                                                    className={i >= Math.round(approvedRating) ? 'text-gray-200' : ''}
                                                />
                                            ))}
                                        </div>
                                        <p className="text-sm font-bold text-gray-500">
                                            Based on {approvedCount} reviews
                                        </p>
                                    </div>
                                </div>

                                {/* Rating breakdown — computed from actual reviews */}
                                {reviews.length > 0 && (
                                    <div className="space-y-2 mb-8">
                                        {[5, 4, 3, 2, 1].map((star) => {
                                            const count = reviews.filter((r) => r.rating === star).length;
                                            const pct   = reviews.length > 0 ? Math.round((count / reviews.length) * 100) : 0;
                                            return (
                                                <div key={star} className="flex items-center gap-3">
                                                    <span className="text-sm font-bold text-gray-500 w-3">{star}</span>
                                                    <Star size={12} className="text-gray-400 flex-shrink-0" />
                                                    <div className="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                        <div
                                                            className="h-full bg-yellow-400 rounded-full transition-all duration-500"
                                                            style={{ width: `${pct}%` }}
                                                        />
                                                    </div>
                                                    <span className="text-xs font-bold text-gray-400 w-8 text-right">{pct}%</span>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}

                                <button className="w-full bg-white border-2 border-[#007A55] text-gray-900 hover:bg-[#007A55] hover:text-white font-bold py-3 rounded-xl transition-colors">
                                    <Link href={route('product.review', product.slug)}>Write a Review</Link>
                                </button>
                            </div>

                            {/* Reviews list */}
                            <div className="lg:col-span-8">
                                {reviews.length > 0 ? (
                                    <>
                                        <div className="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                                            <span className="font-bold text-gray-900">{reviews.length} Reviews</span>
                                            <div className="flex items-center gap-2">
                                                <span className="text-sm text-gray-500">Sort by:</span>
                                                <select className="bg-transparent font-bold text-gray-900 text-sm outline-none cursor-pointer">
                                                    <option>Most Recent</option>
                                                    <option>Highest Rated</option>
                                                    <option>Lowest Rated</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            {reviews.map((review) => (
                                                <ReviewCard key={review.id} review={review} />
                                            ))}
                                        </div>
                                    </>
                                ) : (
                                    <div className="text-center py-16 text-gray-400">
                                        <Star size={40} className="mx-auto mb-4 opacity-30" />
                                        <p className="font-bold text-lg">No reviews yet</p>
                                        <p className="text-sm mt-1">Be the first to share your experience.</p>
                                    </div>
                                )}
                            </div>

                        </div>
                    </div>
                </div>

                {/* ── Sticky Mobile Bar ── */}
                <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 p-4 lg:hidden z-40 shadow-[0_-5px_20px_-5px_rgba(0,0,0,0.1)]">
                    <div className="flex gap-4 items-center max-w-7xl mx-auto">
                        <div className="flex-1">
                            <div className="text-xs text-gray-500 font-bold">Total Price</div>
                            <div className="text-xl font-black text-[#1A1A1A]">
                                ${(currentPrice * quantity).toFixed(2)}
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <div className="flex items-center border border-gray-200 rounded-lg h-12 px-2 bg-gray-50">
                                <button onClick={() => setQuantity(Math.max(1, quantity - 1))} className="px-2 text-gray-400">
                                    <Minus size={16} />
                                </button>
                                <span className="w-6 text-center font-bold text-sm">{quantity}</span>
                                <button onClick={() => setQuantity(quantity + 1)} className="px-2 text-gray-400">
                                    <Plus size={16} />
                                </button>
                            </div>

                            {inStock ? (
                                <AddToCartButton product={cartProduct} quantity={quantity} size="default" className="h-12 px-6" />
                            ) : (
                                <button disabled className="bg-gray-200 text-gray-400 h-12 px-6 rounded-xl font-bold">
                                    Out of Stock
                                </button>
                            )}
                        </div>
                    </div>
                </div>

            </div>
        </>
    );
}