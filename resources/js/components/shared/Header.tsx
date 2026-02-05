import React, { useState, useEffect } from 'react';
import { Search, User, ShoppingCart, Menu, X, ChevronRight, Sprout, Star, ArrowRight } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { useCart } from '../../context/CartContext';

const Header = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const { getCartCount } = useCart();
  const [cartBounce, setCartBounce] = useState(false);

  // Lock body scroll when mobile menu or search is open
  useEffect(() => {
    if (isMobileMenuOpen || isSearchOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [isMobileMenuOpen, isSearchOpen]);

  // Bounce animation when cart count changes
  useEffect(() => {
    if (getCartCount() > 0) {
      setCartBounce(true);
      const timer = setTimeout(() => setCartBounce(false), 600);
      return () => clearTimeout(timer);
    }
  }, [getCartCount()]);

  const navLinks = [
    { name: 'Home', href: '/' },
    { name: 'Products', href: '/products' },
    { name: 'Custom Lawn Plan', href: '/custom-lawn' },
    { name: 'TurfTalk Blog', href: '/blogs' },
  ];

  // Mock Search Results for the overlay
  const searchResults = [
    {
      id: 1,
      name: "Sunday Ultimate Weeding Kit",
      description: "Sunday's weed control system for your entire yard",
      price: 69,
      originalPrice: 86,
      rating: 4.8,
      reviews: 49,
      image: "https://placehold.co/100x100/e0f2f1/00796b?text=Weed+Kit"
    },
    {
      id: 2,
      name: "Texas Green Lawn Kit",
      description: "Heat-resistant nutrient blend",
      price: 45,
      originalPrice: 55,
      rating: 4.5,
      reviews: 120,
      image: "https://placehold.co/100x100/e0f2f1/00796b?text=Green+Lawn"
    }
  ];

  return (
    <>
      <header className="w-full bg-white font-sans text-slate-900 border-b border-gray-100 font-poppins relative z-40">
        
        {/* Top Banner - Visible on ALL devices now */}
        <div className="bg-[#2E7D32] text-white text-center py-2.5 px-4 text-xs md:text-sm font-bold tracking-wide">
          <p>
            Pre-order 2026 plans and save 20%! <span className="mx-1 opacity-60">|</span> Code: <span className="underline decoration-white/40 underline-offset-2">SPRINGREADY20</span>
          </p>
        </div>

        {/* Main Header Container */}
        <div className="max-w-7xl mx-auto px-4 lg:px-8 py-3 lg:py-4">
          <div className="flex items-center justify-between">
            

            <div className="lg:hidden flex items-center">
              <button 
                className="p-2 -ml-2 text-slate-900 hover:bg-gray-100 rounded-full transition-colors focus:outline-none"
                onClick={() => setIsMobileMenuOpen(true)}
                aria-label="Open menu"
              >
                <Menu size={28} strokeWidth={2} />
              </button>
            </div>

            <div className="flex-1 lg:flex-none flex justify-center lg:justify-start">
              <div className="relative group cursor-pointer">
                <img 
                  src="/images/turftec-logo.png"
                  alt="TurfTec" 
                  className="h-14 w-auto object-contain"
                />
              </div>
            </div>

            {/* Desktop Search Bar */}
            <div className="hidden lg:flex flex-1 max-w-xl mx-8">
              <div className="relative w-full group cursor-text" onClick={() => setIsSearchOpen(true)}>
                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <Search size={18} className="text-slate-400 group-hover:text-[#2E7D32] transition-colors" />
                </div>
                <input
                  type="text"
                  readOnly
                  className="w-full bg-gray-100 text-slate-800 rounded-full py-3 pl-11 pr-4 text-sm focus:outline-none cursor-text transition-all placeholder-slate-500 font-medium hover:bg-gray-50"
                  placeholder="What can we help you find today?"
                />
              </div>
            </div>

            {/* 3. Right Actions: Search (Mobile), Sign In & Cart */}
            <div className="flex items-center gap-2 lg:gap-6 flex-shrink-0">
              {/* Mobile Search Icon */}
              <button 
                className="lg:hidden p-2 text-slate-900 hover:bg-gray-100 rounded-full transition-colors"
                onClick={() => setIsSearchOpen(true)}
              >
                <Search size={24} strokeWidth={2} />
              </button>

              <Link href="/login" className="hidden lg:flex items-center gap-2 text-slate-700 hover:text-[#2E7D32] font-bold text-sm group transition-colors">
                <User size={20} className="text-slate-900 group-hover:text-[#2E7D32] transition-colors" />
                <span className="group-hover:underline decoration-[#2E7D32] decoration-2 underline-offset-4">Sign in</span>
              </Link>
              
              <Link 
                href="/cart" 
                id="cart-icon"
                className="flex items-center gap-2 text-slate-700 hover:text-[#2E7D32] font-bold text-sm group transition-colors relative p-2 md:p-0"
              >
                <span className="hidden lg:inline group-hover:underline decoration-[#2E7D32] decoration-2 underline-offset-4">Cart</span>
                <div className={`relative ${cartBounce ? 'animate-cart-bounce' : ''}`}>
                  <ShoppingCart size={24} strokeWidth={2} className="text-slate-900 group-hover:text-[#2E7D32] transition-colors" />
                  {getCartCount() > 0 && (
                    <span className="absolute -top-1 -right-1 bg-[#2E7D32] text-[10px] font-black text-white min-w-[16px] h-4 flex items-center justify-center rounded-full ring-2 ring-white px-1">
                      {getCartCount()}
                    </span>
                  )}
                </div>
              </Link>
            </div>
          </div>
        </div>

        {/* Desktop Bottom Navigation */}
        <div className="hidden lg:block border-t border-gray-100">
          <div className="max-w-7xl mx-auto px-4 lg:px-8">
            <div className="flex items-center justify-between py-3">
              <nav className="flex gap-8">
                {navLinks.map((link) => (
                  <Link
                    key={link.name}
                    href={link.href}
                    className="text-sm font-bold text-slate-700 hover:text-[#2E7D32] transition-colors whitespace-nowrap py-2 border-b-2 border-transparent hover:border-[#2E7D32]"
                  >
                    {link.name}
                  </Link>
                ))}
              </nav>
              <div className="flex-shrink-0 pl-4">
                <button className="bg-[#2E7D32] hover:scale-105 text-white font-extrabold py-2 px-6 rounded-full transition-colors text-sm shadow-sm hover:shadow-md cursor-pointer">
                  Get your plan
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <style>{`
        @keyframes cart-bounce {
          0%, 100% { transform: scale(1); }
          25% { transform: scale(1.2) rotate(-5deg); }
          50% { transform: scale(1.1) rotate(5deg); }
          75% { transform: scale(1.15) rotate(-3deg); }
        }
        
        .animate-cart-bounce {
          animation: cart-bounce 0.6s ease-in-out;
        }
      `}</style>

      {/* --- Mobile Menu (Drawer) --- */}
      {/* Backdrop */}
      <div 
        className={`fixed inset-0 bg-black/30 backdrop-blur-sm z-50 transition-opacity duration-300 lg:hidden ${isMobileMenuOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
        onClick={() => setIsMobileMenuOpen(false)}
      />

      {/* Drawer */}
      <div className={`fixed inset-y-0 left-0 w-[85%] max-w-[320px] bg-white z-[60] shadow-2xl transform transition-transform duration-300 ease-in-out lg:hidden flex flex-col ${isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        
        {/* Drawer Header */}
        <div className="flex items-center justify-between p-4 border-b border-gray-100">
           <img 
              src="images/turftec-logo.png"
              alt="TurfTec" 
              className="h-8 w-auto object-contain"
            />
          <button 
            onClick={() => setIsMobileMenuOpen(false)}
            className="p-2 bg-gray-100 rounded-full hover:bg-gray-200 transition-colors"
          >
            <X size={20} className="text-slate-600" />
          </button>
        </div>

        {/* Drawer Content */}
        <div className="flex-1 overflow-y-auto py-4">
          
          {/* Navigation Links (No "Shop" text, No Chevrons) */}
          <nav className="flex flex-col">
            {navLinks.map((link) => (
              <Link
                key={link.name}
                href={link.href}
                className="block px-6 py-2 text-[16px] font-bold text-slate-800 hover:bg-gray-50 transition-colors border-l-4 border-transparent hover:border-[#2E7D32]"
              >
                {link.name}
              </Link>
            ))}
            
            <div className="mt-4 px-7 text-xs font-bold text-gray-400 uppercase tracking-wider">Account</div>
            <Link href="/login" className="flex items-center gap-3 px-6 py-2 text-[15px] font-bold text-slate-800 hover:bg-gray-50 transition-colors">
              <User size={20} className="text-slate-400" />
              Sign In
            </Link>
          </nav>
        </div>

        {/* Drawer Footer (CTA) */}
        <div className="p-4 border-t border-gray-100 bg-gray-50">
          <button className="w-full bg-[#4C8C4A] text-gray-50 font-extrabold text-lg py-4 rounded-xl shadow-sm active:scale-[0.98] transition-all flex items-center justify-center gap-2">
            Get your plan
            <ArrowRight size={20} className="text-gray-50" />
          </button>
        </div>
      </div>

      {/* --- Search Overlay Modal (Pop up result box) --- */}
      {isSearchOpen && (
        <div className="fixed inset-0 z-[70] bg-black/60 backdrop-blur-sm flex justify-center items-start pt-0 sm:pt-20 animate-in fade-in duration-200">
          <div className="bg-white w-full max-w-2xl sm:rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            {/* Search Header */}
            <div className="p-4 sm:p-6 border-b border-gray-100 flex items-center gap-4">
               {/* Mobile Back Button (only visual consistency, acts as close) */}
               <button onClick={() => setIsSearchOpen(false)} className="sm:hidden p-2 -ml-2 text-slate-500">
                 <ChevronRight className="rotate-180" size={24} />
               </button>

               <div className="relative flex-1">
                 <input 
                   type="text" 
                   autoFocus
                   placeholder="It takes one to grow one..." 
                   className="w-full text-lg sm:text-xl font-bold text-slate-900 placeholder-slate-400 focus:outline-none"
                 />
               </div>
               
               <button 
                 onClick={() => setIsSearchOpen(false)} 
                 className="hidden sm:block p-2 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors"
               >
                 <X size={20} className="text-slate-600" />
               </button>
            </div>

            {/* Results Content */}
            <div className="flex-1 overflow-y-auto p-4 sm:p-6 bg-gray-50/50">
              <h3 className="text-lg font-black text-slate-900 mb-4">Products</h3>
              
              <div className="space-y-4">
                {searchResults.map((product) => (
                  <div key={product.id} className="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex gap-4 hover:shadow-md transition-shadow cursor-pointer">
                    <div className="w-20 h-20 bg-gray-50 rounded-lg flex-shrink-0 flex items-center justify-center p-2">
                      <img src={product.image} alt={product.name} className="w-full h-full object-contain mix-blend-multiply" />
                    </div>
                    <div className="flex-1">
                      <div className="flex justify-between items-start">
                        <h4 className="font-bold text-slate-900 text-sm sm:text-base leading-tight">{product.name}</h4>
                        <div className="text-right">
                          <span className="block font-black text-[#2E7D32]">${product.price}</span>
                          {product.originalPrice && (
                            <span className="text-xs text-gray-400 line-through">${product.originalPrice}</span>
                          )}
                        </div>
                      </div>
                      <p className="text-xs text-gray-500 mt-1 line-clamp-1">{product.description}</p>
                      <div className="flex items-center gap-1 mt-2">
                        <div className="flex text-yellow-400">
                          {[...Array(5)].map((_, i) => (
                            <Star key={i} size={12} fill={i < Math.floor(product.rating) ? "currentColor" : "none"} className={i >= Math.floor(product.rating) ? "text-gray-300" : ""} />
                          ))}
                        </div>
                        <span className="text-xs text-gray-400">({product.reviews})</span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              <div className="mt-6 flex justify-between items-center text-sm font-bold">
                <a href="#" className="text-slate-600 hover:text-[#2E7D32] underline underline-offset-4 decoration-2">View more results</a>
                <a href="#" className="text-[#2E7D32] hover:underline underline-offset-4">Need help?</a>
              </div>
            </div>

            {/* Search Footer Button */}
            <div className="p-4 border-t border-gray-100 bg-white">
               <button className="w-full bg-[#4C8C4A] text-white font-extrabold py-4 rounded-xl transition-colors shadow-lg">
                 Get your plan
               </button>
            </div>

          </div>
        </div>
      )}

    </>
  );
};

export default Header;