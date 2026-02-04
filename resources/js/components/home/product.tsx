import React, { useRef, useState, useEffect } from 'react';
import { ChevronLeft, ChevronRight, Star, Truck, ShoppingCart, Sparkles, TrendingUp, Eye } from 'lucide-react';

const mockProducts = [
  {
    id: 1,
    badge: { text: 'SAVE $6', type: 'save' },
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Lawn+Strong',
    title: 'Lawn Strong Liquid Fertilizer',
    subtitle: 'Premium Root Support',
    price: 54.00,
    originalPrice: 60.00,
    rating: 5,
    reviewCount: 1,
    isFreeShipping: true,
  },
  {
    id: 2,
    badge: { text: 'SAVE $8', type: 'save' },
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Bug+Doom',
    title: 'Bug Doom Home Insect Control',
    subtitle: 'Gallon with Wand (2-pack)',
    price: 72.00,
    originalPrice: 80.00,
    rating: 4,
    reviewCount: 53,
    isFreeShipping: true,
  },
  {
    id: 3,
    badge: { text: 'BUNDLE & SAVE', type: 'bundle' },
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Pest+Kit',
    title: 'Indoor & Outdoor Pest Kit',
    subtitle: 'Complete Home Protection',
    price: 60.00,
    originalPrice: 75.00,
    rating: 4.8,
    reviewCount: 12,
    isFreeShipping: true,
  },
  {
    id: 4,
    badge: { text: 'SAVE $8.80', type: 'save' },
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Dandelion',
    title: 'Dandelion Doom Herbicide',
    subtitle: 'Concentrate Refill (2-pack)',
    price: 35.20,
    originalPrice: 44.00,
    rating: 4.5,
    reviewCount: 119,
    isFreeShipping: true,
  },
  {
    id: 5,
    badge: { text: 'BEST SELLER', type: 'bestseller' },
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Weed+Warrior',
    title: 'Weed Warrior Herbicide',
    subtitle: 'Concentrate Starter (2-pack)',
    price: 43.20,
    originalPrice: 54.00,
    rating: 5,
    reviewCount: 72,
    isFreeShipping: true,
  },
  {
    id: 6,
    badge: { text: 'SAVE $5', type: 'save' },
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Mosquito',
    title: 'Mosquito Deleter Concentrate',
    subtitle: 'Refill (2-pack)',
    price: 45.00,
    originalPrice: 50.00,
    rating: 4,
    reviewCount: 25,
    isFreeShipping: true,
  },
];

const ProductCard = ({ product }) => {
  const badgeConfig = {
    save: {
      bg: 'bg-rose-500',
      text: 'text-white',
      shadow: 'shadow-rose-500/30',
      icon: null,
    },
    bundle: {
      bg: 'bg-violet-600',
      text: 'text-white',
      shadow: 'shadow-violet-600/30',
      icon: <Sparkles className="w-3 h-3" />,
    },
    bestseller: {
      bg: 'bg-amber-400',
      text: 'text-amber-950',
      shadow: 'shadow-amber-400/30',
      icon: <TrendingUp className="w-3 h-3" />,
    },
  };

  const badge = product.badge ? badgeConfig[product.badge.type] : null;

  const renderStars = (rating) => {
    return (
      <div className="flex gap-0.5">
        {[...Array(5)].map((_, i) => (
          <Star
            key={i}
            size={14}
            className={`${
              i < Math.floor(rating)
                ? 'fill-amber-400 text-amber-400'
                : i < rating
                ? 'fill-amber-400 text-amber-400 opacity-50'
                : 'fill-gray-200 text-gray-200'
            }`}
          />
        ))}
      </div>
    );
  };

  const savingsPercent = product.originalPrice 
    ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
    : 0;

  return (
    <div className="w-80 flex-shrink-0 group relative">
      <div className="h-full bg-white rounded-3xl p-5 flex flex-col transition-all duration-500 border border-gray-100 hover:border-transparent hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] relative z-10 overflow-hidden">
        
        {/* Hover Gradient Border Effect */}
        <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none" />

        {/* Top Badges */}
        <div className="flex justify-between items-start mb-4 relative z-20">
          {savingsPercent > 0 ? (
            <span className="bg-emerald-50 text-emerald-700 text-[10px] font-black tracking-wider uppercase px-2 py-1 rounded-lg border border-emerald-100">
              -{savingsPercent}%
            </span>
          ) : <div></div>}

          {product.badge && badge && (
            <span className={`${badge.bg} ${badge.text} ${badge.shadow} shadow-lg px-3 py-1 text-[10px] font-bold tracking-wide uppercase rounded-full flex items-center gap-1.5`}>
              {badge.icon}
              {product.badge.text}
            </span>
          )}
        </div>

        {/* Image Area */}
        <div className="relative mb-6 group-hover:-translate-y-1 transition-transform duration-500">
          <div className="absolute inset-0 bg-gray-100/50 rounded-2xl transform rotate-3 group-hover:rotate-6 transition-transform duration-500 origin-bottom-right" />
          <div className="relative bg-gray-50 rounded-2xl p-6 overflow-hidden">
             <img 
              src={product.image} 
              alt={product.title} 
              className="w-full h-48 object-contain mix-blend-multiply filter contrast-105"
            />
            
            {/* Quick View Button on Hover */}
            <div className="absolute inset-0 bg-black/5 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
               <button className="bg-white text-gray-900 px-5 py-2.5 rounded-full font-bold text-sm shadow-xl flex items-center gap-2 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 hover:bg-emerald-50">
                 <Eye size={16} /> Quick View
               </button>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="flex-1 flex flex-col gap-2 relative z-20">
          <div>
            <h3 className="text-gray-900 font-bold text-lg leading-tight group-hover:text-emerald-700 transition-colors">
              {product.title}
            </h3>
            {product.subtitle && (
              <p className="text-gray-500 text-xs font-medium mt-1">{product.subtitle}</p>
            )}
          </div>

          {/* Rating */}
          <div className="flex items-center gap-2 mt-1">
            {renderStars(product.rating)}
            <span className="text-gray-400 text-xs font-medium pt-0.5">
              ({product.reviewCount})
            </span>
          </div>

          {/* Price & Shipping */}
          <div className="mt-auto pt-4 flex items-end justify-between border-t border-gray-50">
            <div>
              <div className="flex items-center gap-2">
                <span className="text-gray-900 font-extrabold text-2xl">
                  ${product.price.toFixed(2)}
                </span>
                {product.originalPrice && (
                  <span className="text-gray-400 text-sm line-through decoration-red-400/50">
                    ${product.originalPrice.toFixed(2)}
                  </span>
                )}
              </div>
              {product.isFreeShipping && (
                <div className="flex items-center gap-1 text-emerald-600 text-[10px] font-bold uppercase tracking-wide mt-1">
                  <Truck size={12} />
                  <span>Free Shipping</span>
                </div>
              )}
            </div>

            <button className="bg-[#1A1A1A] hover:bg-emerald-600 text-white p-3 rounded-xl shadow-lg shadow-gray-200 hover:shadow-emerald-500/30 transition-all duration-300 active:scale-95 group/btn cursor-pointer">
              <ShoppingCart size={20} className="stroke-2 group-hover/btn:hidden" />
              <span className="hidden group-hover/btn:inline font-bold text-sm px-1">Add</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default function ProductSection() {
  const scrollContainerRef = useRef(null);
  const [canScrollLeft, setCanScrollLeft] = useState(false);
  const [canScrollRight, setCanScrollRight] = useState(true);

  const checkScrollButtons = () => {
    if (scrollContainerRef.current) {
      const { scrollLeft, scrollWidth, clientWidth } = scrollContainerRef.current;
      setCanScrollLeft(scrollLeft > 0);
      setCanScrollRight(scrollLeft < scrollWidth - clientWidth - 5);
    }
  };

  useEffect(() => {
    checkScrollButtons();
    const handleResize = () => checkScrollButtons();
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const scroll = (direction) => {
    if (scrollContainerRef.current) {
      const scrollAmount = 350;
      scrollContainerRef.current.scrollBy({
        left: direction === 'left' ? -scrollAmount : scrollAmount,
        behavior: 'smooth',
      });
      setTimeout(checkScrollButtons, 300);
    }
  };

  return (
    <section className="w-full py-24 bg-[#FAFAF9] relative overflow-hidden font-sans">
      
      {/* Background Mesh Gradient (Subtle) */}
      <div className="absolute top-0 left-0 w-[1000px] h-[1000px] bg-emerald-100/20 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 mix-blend-multiply pointer-events-none" />
      <div className="absolute bottom-0 right-0 w-[800px] h-[800px] bg-blue-100/20 rounded-full blur-3xl translate-x-1/3 translate-y-1/3 mix-blend-multiply pointer-events-none" />

      {/* Styles for hiding scrollbar */}
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
          
          {/* 
             FIX: Removed 'hidden md:flex'. 
             Changed to 'flex' so buttons show on mobile.
             Adjusted 'left-0' to 'left-4' on mobile for padding against phone bezel.
             Added 'bg-white/90' backdrop blur to ensure visibility over images.
          */}
          
          {/* Left Navigation Button */}
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
            {mockProducts.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>

          {/* Right Navigation Button */}
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
          
          {/* Mobile Overlay Gradients for scroll hints */}
          <div className="absolute left-0 top-0 bottom-0 w-12 bg-gradient-to-r from-[#FAFAF9] to-transparent md:hidden pointer-events-none z-20" />
          <div className="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-[#FAFAF9] to-transparent md:hidden pointer-events-none z-20" />
        </div>

        <div className="mt-4 text-center">
            <a href="/products" className="inline-flex items-center text-sm font-bold text-gray-900 border-b-2 border-transparent hover:border-emerald-500 transition-colors pb-1">
               View All Products <ChevronRight size={16} className="ml-1" />
            </a>
        </div>

      </div>
    </section>
  );
}