import React, { useState } from 'react';
import { Star, Truck, ShoppingCart, Sparkles, TrendingUp, Eye, Filter, ChevronDown, Search } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';

// --- Mock Data ---
const allProducts = [
  {
    id: 1,
    badge: { text: 'SAVE $6', type: 'save' },
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Lawn+Strong',
    title: 'Lawn Strong Liquid Fertilizer',
    subtitle: 'Premium Root Support',
    price: 54.00,
    originalPrice: 60.00,
    rating: 5,
    reviewCount: 42,
    isFreeShipping: true,
    category: 'Fertilizer'
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
    category: 'Pest Control'
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
    category: 'Bundles'
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
    category: 'Weed Control'
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
    category: 'Weed Control'
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
    category: 'Pest Control'
  },
  {
    id: 7,
    badge: null,
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Pet+Patch',
    title: 'Pet Patch Lawn Repair',
    subtitle: 'Fix Urine Spots Fast',
    price: 29.99,
    originalPrice: null,
    rating: 4.2,
    reviewCount: 18,
    isFreeShipping: false,
    category: 'Repair'
  },
  {
    id: 8,
    badge: { text: 'NEW ARRIVAL', type: 'new' },
    image: 'https://placehold.co/400x500/f0fdf4/166534?text=Soil+Test',
    title: 'Pro-Grade Soil Test Kit',
    subtitle: 'Lab Analysis Included',
    price: 24.00,
    originalPrice: null,
    rating: 5,
    reviewCount: 6,
    isFreeShipping: true,
    category: 'Tools'
  },
];

// --- Product Card Component ---
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
    new: {
      bg: 'bg-blue-500',
      text: 'text-white',
      shadow: 'shadow-blue-500/30',
      icon: <Star className="w-3 h-3 fill-current" />,
    }
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
    <div className="group relative h-full">
      <div className="h-full bg-white rounded-3xl p-5 flex flex-col transition-all duration-500 border border-gray-100 hover:border-transparent hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] relative z-10 overflow-hidden">
        
        {/* Hover Gradient Border Effect */}
        <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none" />

        {/* Top Badges */}
        <div className="flex justify-between items-start mb-4 relative z-20 min-h-[24px]">
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
              className="w-full aspect-[4/5] object-contain mix-blend-multiply filter contrast-105"
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

            <button className="bg-[#1A1A1A] hover:bg-emerald-600 text-white p-3 rounded-xl shadow-lg shadow-gray-200 hover:shadow-emerald-500/30 transition-all duration-300 active:scale-95 group/btn">
              <ShoppingCart size={20} className="stroke-2 group-hover/btn:hidden" />
              <span className="hidden group-hover/btn:inline font-bold text-sm px-1">Add</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default function AllProductsPage() {
  const [activeCategory, setActiveCategory] = useState('All');
  const categories = ['All', 'Fertilizer', 'Pest Control', 'Weed Control', 'Bundles', 'Tools'];

  const filteredProducts = activeCategory === 'All' 
    ? allProducts 
    : allProducts.filter(p => p.category === activeCategory);

  return (
    <AppHeaderLayout>
      
      {/* Page Hero/Header */}
      <div className="bg-[#4C8C4A] text-white pt-20 pb-16 px-6">
        <div className="max-w-7xl mx-auto text-center">
          <h1 className="text-4xl md:text-6xl font-black mb-4 font-serif">
            Shop All Products
          </h1>
          <p className="text-white font-poppins text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
            Professional-grade lawn care solutions delivered straight to your door. 
            Backed by science, safe for your family.
          </p>
        </div>
      </div>

      {/* Toolbar (Search & Filter) */}
      <div className="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-200 px-6 py-4 shadow-sm">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row gap-4 justify-between items-center">
          
          {/* Categories (Desktop) */}
          <div className="hidden md:flex items-center gap-2 overflow-x-auto no-scrollbar">
            {categories.map(cat => (
              <button
                key={cat}
                onClick={() => setActiveCategory(cat)}
                className={`px-4 py-2 rounded-full text-sm font-bold transition-all whitespace-nowrap ${
                  activeCategory === cat 
                    ? 'bg-[#2E7D32] text-white shadow-md' 
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                }`}
              >
                {cat}
              </button>
            ))}
          </div>

          {/* Mobile Category Dropdown */}
          <div className="md:hidden w-full">
            <div className="relative">
              <select 
                value={activeCategory}
                onChange={(e) => setActiveCategory(e.target.value)}
                className="w-full appearance-none bg-gray-100 border border-transparent text-gray-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-3 font-bold"
              >
                {categories.map(cat => <option key={cat} value={cat}>{cat}</option>)}
              </select>
              <ChevronDown className="absolute right-3 top-3.5 text-gray-500 pointer-events-none" size={16} />
            </div>
          </div>

          {/* Search & Sort Actions */}
          <div className="flex gap-3 w-full md:w-auto">
            <div className="relative flex-1 md:w-64">
              <input 
                type="text" 
                placeholder="Search products..." 
                className="w-full pl-10 pr-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm text-gray-900"
              />
              <Search className="absolute left-3.5 top-2.5 text-gray-400" size={16} />
            </div>
          </div>
        </div>
      </div>

      {/* Product Grid */}
      <main className="max-w-7xl mx-auto px-6 py-12">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 xl:gap-8">
          {filteredProducts.map((product) => (
            <div key={product.id} className="w-full">
              <ProductCard product={product} />
            </div>
          ))}
        </div>

        {/* Empty State */}
        {filteredProducts.length === 0 && (
          <div className="text-center py-20">
            <div className="bg-gray-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
              <Search className="text-gray-400" size={32} />
            </div>
            <h3 className="text-xl font-bold text-gray-900 mb-2">No products found</h3>
            <p className="text-gray-500">Try adjusting your filters or search terms.</p>
            <button 
              onClick={() => setActiveCategory('All')}
              className="mt-6 text-emerald-600 font-bold hover:underline"
            >
              Clear all filters
            </button>
          </div>
        )}

        {/* Load More / Pagination */}
        {filteredProducts.length > 0 && (
          <div className="mt-16 text-center">
            <p className="text-gray-500 text-sm mb-4">
              Showing {filteredProducts.length} of {allProducts.length} products
            </p>
            <div className="w-48 h-1 bg-gray-200 rounded-full mx-auto mb-6 overflow-hidden">
              <div 
                className="h-full bg-emerald-500 rounded-full" 
                style={{ width: `${(filteredProducts.length / allProducts.length) * 100}%` }}
              ></div>
            </div>
            <button className="px-8 py-3 bg-white border-2 border-gray-200 text-gray-900 font-bold rounded-full hover:border-emerald-500 hover:text-emerald-600 transition-colors">
              Load More Products
            </button>
          </div>
        )}
      </main>

    </AppHeaderLayout>
  );
}