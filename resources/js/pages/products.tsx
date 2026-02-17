import React, { useState, useEffect, useRef } from 'react';
import { Star, Truck, Eye, Search, X, Minus, Plus, ChevronDown, ListFilter } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, router, Link } from '@inertiajs/react';
import AddToCartButton from '../components/AddToCartButton';
import { assert } from 'console';

declare function route(name: string, params?: any): string;

interface ProductImage {
  image_url: string;
}

interface Product {
  id: number;
  name: string;
  slug: string;
  subtitle: string;
  category: string | null;
  image: string;
  price: number;
  min_price: number;
  max_price: number;
  rating: number;
  reviews_count: number;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedProducts {
  data: Product[];
  links: PaginationLink[];
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

interface Category {
  name: string;
  slug: string;
}

interface Filters {
  category?: string | null;
  sort?: string | null;
  search?: string | null;
}

interface Props {
  products: PaginatedProducts;
  categories: Category[];
  filters: Filters;
}

// --- Quick View Modal ---

const QuickViewModal = ({ product, onClose }: { product: Product; onClose: () => void }) => {
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    document.body.style.overflow = 'hidden';
    return () => { document.body.style.overflow = 'unset'; };
  }, []);

  const savingsPercent = product.max_price > product.price
    ? Math.round(((product.max_price - product.price) / product.max_price) * 100)
    : 0;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div className="relative bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col md:flex-row animate-in fade-in zoom-in-95 duration-200">
        <button onClick={onClose} className="absolute top-4 right-4 z-10 p-2 bg-gray-50 text-gray-600 rounded-full hover:bg-gray-100">
          <X size={20} />
        </button>

        <div className="w-full md:w-1/2 bg-gray-50 flex items-center justify-center p-8 relative">
          <img
            src={product.image}
            alt={product.name}
            className="w-full h-full max-h-[400px] object-contain mix-blend-multiply"
          />
          {savingsPercent > 0 && (
            <span className="absolute top-6 left-6 bg-[#2E7D32] text-white text-xs font-bold px-3 py-1.5 rounded-full">
              SAVE {savingsPercent}%
            </span>
          )}
        </div>

        <div className="w-full md:w-1/2 p-8 md:p-10 flex flex-col">
          <h2 className="text-2xl md:text-3xl font-black text-gray-900 font-serif mb-1">{product.name}</h2>
          {product.subtitle && <p className="text-gray-500 font-medium mb-4">{product.subtitle}</p>}

          <div className="flex items-center gap-3 mb-4">
            <div className="flex text-yellow-400">
              {[...Array(5)].map((_, i) => (
                <Star key={i} size={16} fill={i < Math.round(product.rating) ? 'currentColor' : 'none'} className={i >= Math.round(product.rating) ? 'text-gray-200' : ''} />
              ))}
            </div>
            <span className="text-sm text-gray-500">({product.reviews_count})</span>
          </div>

          <div className="flex items-baseline gap-3 mb-6">
            <span className="text-3xl font-black text-[#2E7D32]">${product.price.toFixed(2)}</span>
            {product.max_price > product.price && (
              <span className="text-lg text-gray-400 line-through">${product.max_price.toFixed(2)}</span>
            )}
          </div>

          <div className="mt-auto flex gap-4">
            <div className="flex items-center border border-gray-200 rounded-xl px-2 py-1">
              <button onClick={() => setQuantity(Math.max(1, quantity - 1))} className="p-2 text-gray-400 hover:text-gray-600">
                <Minus size={16} />
              </button>
              <span className="w-8 text-center font-bold text-gray-900">{quantity}</span>
              <button onClick={() => setQuantity(quantity + 1)} className="p-2 text-gray-400 hover:text-gray-600">
                <Plus size={16} />
              </button>
            </div>

            <AddToCartButton 
                product={product} 
                quantity={quantity}
                size="large"
                className="flex-1"
            />
          </div>
        </div>
      </div>
    </div>
  );
};

// --- Product Card ---

const ProductCard = ({ product, onQuickView }: { product: Product; onQuickView: (p: Product) => void }) => {
  const savingsPercent = product.max_price > product.price
    ? Math.round(((product.max_price - product.price) / product.max_price) * 100)
    : 0;

  return (
    <div className="group relative h-full">
      <div className="h-full bg-white rounded-3xl p-5 flex flex-col transition-all duration-500 border border-gray-100 hover:border-transparent hover:shadow-xl relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none" />

        <div className="flex justify-between items-start mb-4 relative z-20 min-h-[24px]">
          {savingsPercent > 0 ? (
            <span className="bg-emerald-50 text-emerald-700 text-[10px] font-black tracking-wider uppercase px-2 py-1 rounded-lg border border-emerald-100">
              -{savingsPercent}%
            </span>
          ) : <div />}
        </div>

        <div className="relative mb-6 group-hover:-translate-y-1 transition-transform duration-500">
          <div className="absolute inset-0 bg-gray-100/50 rounded-2xl transform rotate-3 group-hover:rotate-6 transition-transform duration-500 origin-bottom-right" />
          <div className="relative bg-gray-50 rounded-2xl p-6 overflow-hidden">
            <img
              src={product.image ?? '/images/placeholder.png'}
              alt={product.name}
              className="w-full aspect-[4/5] object-contain mix-blend-multiply"
            />
            <div className="absolute inset-0 bg-black/5 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
              <button
                onClick={() => onQuickView(product)}
                className="bg-white text-gray-900 px-5 py-2.5 rounded-full font-bold text-sm shadow-xl flex items-center gap-2 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 hover:bg-emerald-50 hover:text-[#2E7D32]"
              >
                <Eye size={16} /> Quick View
              </button>
            </div>
          </div>
        </div>

        <div className="flex-1 flex flex-col gap-2 relative z-20">
          <Link href={route('products.show', product.slug)}>
            <h3 className="text-gray-900 font-bold text-lg leading-tight group-hover:text-emerald-700 transition-colors">
              {product.name}
            </h3>
          </Link>
          {product.subtitle && (
            <p className="text-gray-500 text-xs font-medium">{product.subtitle}</p>
          )}

          {product.rating > 0 && (
            <div className="flex items-center gap-2 mt-1">
              <div className="flex gap-0.5">
                {[...Array(5)].map((_, i) => (
                  <Star
                    key={i}
                    size={14}
                    className={i < Math.round(product.rating) ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 text-gray-200'}
                  />
                ))}
              </div>
              <span className="text-gray-400 text-xs font-medium">({product.reviews_count})</span>
            </div>
          )}

          <div className="mt-auto pt-4 flex items-end justify-between border-t border-gray-50">
            <div>
              <div className="flex items-center gap-2">
                <span className="text-gray-900 font-extrabold text-2xl">${product.price.toFixed(2)}</span>
                {product.max_price > product.price && (
                  <span className="text-gray-400 text-sm line-through">${product.max_price.toFixed(2)}</span>
                )}
              </div>
            </div>
            
            <AddToCartButton 
                product={product} 
                size="default"
            />
          </div>
        </div>
      </div>
    </div>
  );
};

// --- Main Page ---

export default function AllProductsPage({ products, categories, filters }: Props) {
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
  const [searchQuery, setSearchQuery] = useState(filters.search ?? '');
  const [isSortOpen, setIsSortOpen] = useState(false);
  const sortRef = useRef<HTMLDivElement>(null);
  
  // Track if it's the first mount to avoid searching on initial load
  const isFirstRender = useRef(true);

  // Debounced search logic
  useEffect(() => {
    if (isFirstRender.current) {
      isFirstRender.current = false;
      return;
    }

    const timer = setTimeout(() => {
        // Only trigger if the search query actually changed from the filter value
        if (searchQuery !== (filters.search ?? '')) {
            router.get(
                route('products.index'), 
                { ...filters, search: searchQuery || null, page: 1 }, 
                { preserveState: true, replace: true, preserveScroll: true }
            );
        }
    }, 500); // 500ms delay

    return () => clearTimeout(timer);
  }, [searchQuery]);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (sortRef.current && !sortRef.current.contains(event.target as Node)) {
        setIsSortOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const sortOptions = [
    { label: 'Newest Arrivals', value: 'newest' },
    { label: 'Price: Low to High', value: 'price_low' },
    { label: 'Price: High to Low', value: 'price_high' },
    { label: 'Top Rated', value: 'top_rated' },
  ];

  const currentSortLabel = sortOptions.find(o => o.value === (filters.sort || 'newest'))?.label;

  const handleCategoryChange = (slug: string | null) => {
    router.get(
      route('products.index'), 
      { ...filters, category: slug, page: 1 }, 
      { preserveState: true, replace: true, preserveScroll: true }
    );
  };

  const handleSortChange = (sort: string) => {
    router.get(
      route('products.index'), 
      { ...filters, sort, page: 1 }, 
      { preserveState: true, replace: true, preserveScroll: true }
    );
    setIsSortOpen(false);
  };

  const handleClearSearch = () => {
    setSearchQuery('');
    router.get(
      route('products.index'), 
      { ...filters, search: null, page: 1 }, 
      { preserveState: true, replace: true, preserveScroll: true }
    );
  };

  return (
    <AppHeaderLayout>
      <Head title="Shop All Products" />

      {/* Hero */}
      <div className="bg-[#4C8C4A] text-white pt-20 pb-16 px-6">
        <div className="max-w-7xl mx-auto text-center">
          <h1 className="text-4xl md:text-6xl font-black mb-4 font-serif">Shop All Products</h1>
          <p className="text-white/90 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
            Professional-grade lawn care solutions delivered straight to your door.
            Backed by science, safe for your family.
          </p>
        </div>
      </div>

      {/* Toolbar */}
      <div className="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-200 px-6 py-4 shadow-sm">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row gap-4 justify-between items-center">

          {/* Category Filters */}
          <div className="flex items-center gap-2 overflow-x-auto no-scrollbar w-full md:w-auto">
            <button
              onClick={() => handleCategoryChange(null)}
              className={`px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all ${
                !filters.category ? 'bg-[#2E7D32] text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              }`}
            >
              All
            </button>
            {categories.map(cat => (
              <button
                key={cat.slug}
                onClick={() => handleCategoryChange(cat.slug)}
                className={`px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all ${
                  filters.category === cat.slug ? 'bg-[#2E7D32] text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                }`}
              >
                {cat.name}
              </button>
            ))}
          </div>

          {/* Premium Search + Sort */}
          <div className="flex gap-3 w-full md:w-auto items-center">
            
            {/* Custom Premium Sort Dropdown */}
            <div className="relative" ref={sortRef}>
              <button
                onClick={() => setIsSortOpen(!isSortOpen)}
                className="flex items-center justify-between gap-3 px-4 py-2 bg-white border border-gray-200 hover:border-emerald-500 rounded-full transition-all duration-200 min-w-[160px]"
              >
                <div className="flex items-center gap-2">
                  <ListFilter size={14} className="text-emerald-600" />
                  <span className="text-sm font-semibold text-gray-700">{currentSortLabel}</span>
                </div>
                <ChevronDown size={14} className={`text-gray-400 transition-transform duration-200 ${isSortOpen ? 'rotate-180' : ''}`} />
              </button>

              {isSortOpen && (
                <div className="absolute right-0 mt-2 w-56 bg-white border border-gray-100 shadow-xl rounded-2xl py-2 z-50 animate-in fade-in zoom-in-95 duration-150">
                  {sortOptions.map((option) => (
                    <button
                      key={option.value}
                      onClick={() => handleSortChange(option.value)}
                      className={`w-full text-left px-4 py-2 text-sm font-semibold transition-colors
                        ${filters.sort === option.value || (!filters.sort && option.value === 'newest')
                          ? 'bg-emerald-50 text-emerald-700' 
                          : 'text-gray-600 hover:bg-gray-50'}
                      `}
                    >
                      {option.label}
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Premium Search (Automatic with Debounce) */}
            <div className="relative w-full md:w-64 group">
              <input
                type="text"
                placeholder="Search products..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-10 pr-10 py-2 text-gray-900 rounded-full border border-gray-200 focus:border-emerald-500 bg-white focus:outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all text-sm font-medium"
              />
              <Search className="absolute left-3.5 top-2.5 text-gray-400 group-focus-within:text-emerald-500 transition-colors" size={16} />
              {searchQuery && (
                <button type="button" onClick={handleClearSearch} className="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                  <X size={16} />
                </button>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Grid */}
      <main className="max-w-7xl mx-auto px-6 py-12">
        {products.data.length > 0 ? (
          <>
            <p className="text-sm text-gray-500 mb-6">
              Showing {products.data.length} of {products.total} products
              {filters.search && <> for <span className="font-semibold text-gray-700">"{filters.search}"</span></>}
            </p>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 xl:gap-8">
              {products.data.map((product) => (
                <ProductCard key={product.id} product={product} onQuickView={setSelectedProduct} />
              ))}
            </div>

            {/* Pagination Links */}
            {products.last_page > 1 && (
              <div className="mt-16 flex justify-center gap-2 flex-wrap">
                {products.links.map((link, i) => (
                  link.url ? (
                    <Link
                      key={i}
                      href={link.url}
                      preserveScroll
                      className={`px-4 py-2 rounded-lg text-sm font-semibold transition-colors ${
                        link.active
                          ? 'bg-emerald-600 text-white'
                          : 'bg-white border border-gray-200 text-gray-600 hover:border-emerald-400 hover:text-emerald-600'
                      }`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ) : (
                    <span
                      key={i}
                      className="px-4 py-2 rounded-lg text-sm font-semibold text-gray-300 bg-white border border-gray-100"
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  )
                ))}
              </div>
            )}
          </>
        ) : (
          <div className="text-center py-24">
            <div className="bg-gray-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
              <Search className="text-gray-400" size={32} />
            </div>
            <h3 className="text-xl font-bold text-gray-900 mb-2">No products found</h3>
            <p className="text-gray-500 mb-6">
              {filters.search
                ? `No products match "${filters.search}"`
                : 'Try adjusting your filters.'}
            </p>
            <div className="flex gap-3 justify-center">
              {filters.search && (
                <button onClick={handleClearSearch} className="text-emerald-600 font-bold hover:underline">
                  Clear search
                </button>
              )}
              {filters.category && (
                <button onClick={() => handleCategoryChange(null)} className="text-emerald-600 font-bold hover:underline">
                  View all categories
                </button>
              )}
            </div>
          </div>
        )}
      </main>

      {selectedProduct && (
        <QuickViewModal product={selectedProduct} onClose={() => setSelectedProduct(null)} />
      )}
    </AppHeaderLayout>
  );
}