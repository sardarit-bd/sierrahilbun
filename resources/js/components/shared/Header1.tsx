import React, { useState, useEffect } from 'react';
import { User, ShoppingCart, Menu, X, LogOut } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';
import { useCart } from '../../context/CartContext';
import DiscountBanner from './DiscountBanner';

const Header = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const { getCartCount } = useCart();
  const [cartBounce, setCartBounce] = useState(false);
  const { props } = usePage();
  const authUser = props.auth?.user;

  // Lock body scroll when mobile menu is open
  useEffect(() => {
    if (isMobileMenuOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [isMobileMenuOpen]);

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

  return (
    <>
      <header className="w-full bg-white font-sans text-slate-900 border-b border-gray-100 font-poppins relative z-40">

        <DiscountBanner />

        {/* Single-row Header */}
        <div className="max-w-7xl mx-auto px-4 lg:px-8">
          <div className="flex items-center justify-between h-16 lg:h-[68px] relative">

            {/* LEFT — Hamburger (mobile/tablet) / Logo (desktop) */}
            <div className="flex items-center gap-2">
              {/* Hamburger — mobile & tablet only */}
              <button
                className="lg:hidden p-2 -ml-2 text-slate-900 hover:bg-gray-100 rounded-full transition-colors focus:outline-none"
                onClick={() => setIsMobileMenuOpen(true)}
                aria-label="Open menu"
              >
                <Menu size={26} strokeWidth={2} />
              </button>

              {/* Logo — desktop only (left-aligned) */}
              <Link href="/" className="hidden lg:inline-flex cursor-pointer">
                <img
                  src="/images/turftec-logo.png"
                  alt="TurfTec"
                  className="h-11 w-auto object-contain"
                />
              </Link>
            </div>

            {/* CENTER — Logo (mobile/tablet, absolutely centered) */}
            <Link href="/" className="lg:hidden absolute left-1/2 -translate-x-1/2 inline-flex cursor-pointer">
              <img
                src="/images/turftec-logo.png"
                alt="TurfTec"
                className="h-10 w-auto object-contain"
              />
            </Link>

            {/* CENTER — Desktop nav links */}
            <nav className="hidden lg:flex items-center gap-1">
              {navLinks.map((link) => (
                <Link
                  key={link.name}
                  href={link.href}
                  className="px-4 py-2 text-sm font-bold text-slate-700 hover:text-[#2E7D32] transition-colors whitespace-nowrap rounded-full hover:bg-[#2E7D32]/5 border-b-2 border-transparent hover:border-[#2E7D32]"
                >
                  {link.name}
                </Link>
              ))}
            </nav>

            {/* RIGHT — Auth + Cart */}
            <div className="flex items-center gap-1 lg:gap-3">

              {/* Auth — desktop only */}
              {authUser ? (
                <>
                  <Link
                    href="/dashboard"
                    className="hidden lg:flex items-center gap-2 px-3 py-2 text-slate-700 hover:text-[#2E7D32] font-bold text-sm group transition-colors rounded-full hover:bg-[#2E7D32]/5"
                  >
                    <User size={18} className="text-slate-900 group-hover:text-[#2E7D32] transition-colors" />
                    <span className="group-hover:underline decoration-[#2E7D32] decoration-2 underline-offset-4">
                      Dashboard
                    </span>
                  </Link>

                  <Link
                    href="/logout"
                    method="post"
                    as="button"
                    className="hidden lg:flex items-center gap-2 px-3 py-2 text-slate-700 hover:text-red-600 font-bold text-sm group transition-colors rounded-full hover:bg-red-50"
                  >
                    <LogOut size={18} className="text-slate-900 group-hover:text-red-600 transition-colors" />
                    <span className="group-hover:underline decoration-red-600 decoration-2 underline-offset-4">
                      Logout
                    </span>
                  </Link>
                </>
              ) : (
                <Link
                  href="/login"
                  className="hidden lg:flex items-center gap-2 px-3 py-2 text-slate-700 hover:text-[#2E7D32] font-bold text-sm group transition-colors rounded-full hover:bg-[#2E7D32]/5"
                >
                  <User size={18} className="text-slate-900 group-hover:text-[#2E7D32] transition-colors" />
                  <span className="group-hover:underline decoration-[#2E7D32] decoration-2 underline-offset-4">
                    Sign In
                  </span>
                </Link>
              )}

              {/* Cart — all devices */}
              <Link
                href="/cart"
                id="cart-icon"
                className="flex items-center gap-2 p-2 lg:px-3 lg:py-2 text-slate-700 hover:text-[#2E7D32] font-bold text-sm group transition-colors relative rounded-full hover:bg-[#2E7D32]/5"
              >
                <span className="hidden lg:inline group-hover:underline decoration-[#2E7D32] decoration-2 underline-offset-4">
                  Cart
                </span>
                <div className={`relative ${cartBounce ? 'animate-cart-bounce' : ''}`}>
                  <ShoppingCart size={22} strokeWidth={2} className="text-slate-900 group-hover:text-[#2E7D32] transition-colors" />
                  {getCartCount() > 0 && (
                    <span className="absolute -top-1.5 -right-1.5 bg-[#2E7D32] text-[10px] font-black text-white min-w-[16px] h-4 flex items-center justify-center rounded-full ring-2 ring-white px-1">
                      {getCartCount()}
                    </span>
                  )}
                </div>
              </Link>
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

      {/* Backdrop */}
      <div
        className={`fixed inset-0 bg-black/30 backdrop-blur-sm z-50 transition-opacity duration-300 lg:hidden ${isMobileMenuOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
        onClick={() => setIsMobileMenuOpen(false)}
      />

      {/* Mobile / Tablet Drawer */}
      <div className={`fixed inset-y-0 left-0 w-[85%] max-w-[320px] bg-white z-[60] shadow-2xl transform transition-transform duration-300 ease-in-out lg:hidden flex flex-col ${isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'}`}>

        {/* Drawer Header */}
        <div className="flex items-center justify-between p-4 border-b border-gray-100">
          <img
            src="/images/turftec-logo.png"
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

        {/* Drawer Nav */}
        <div className="flex-1 overflow-y-auto py-4">
          <nav className="flex flex-col">
            {navLinks.map((link) => (
              <Link
                key={link.name}
                href={link.href}
                onClick={() => setIsMobileMenuOpen(false)}
                className="block px-6 py-3 text-[15px] font-bold text-slate-800 hover:bg-gray-50 transition-colors border-l-4 border-transparent hover:border-[#2E7D32]"
              >
                {link.name}
              </Link>
            ))}

            <div className="mt-4 mb-1 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider">Account</div>

            {authUser ? (
              <>
                <Link
                  href="/dashboard"
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="flex items-center gap-3 px-6 py-3 text-[15px] font-bold text-slate-800 hover:bg-gray-50 transition-colors"
                >
                  <User size={18} className="text-slate-500" />
                  Dashboard
                </Link>

                <Link
                  href="/logout"
                  method="post"
                  as="button"
                  className="flex items-center gap-3 px-6 py-3 text-[15px] font-bold text-red-600 hover:bg-gray-50 transition-colors"
                >
                  <LogOut size={18} />
                  Logout
                </Link>
              </>
            ) : (
              <Link
                href="/login"
                onClick={() => setIsMobileMenuOpen(false)}
                className="flex items-center gap-3 px-6 py-3 text-[15px] font-bold text-slate-800 hover:bg-gray-50 transition-colors"
              >
                <User size={18} className="text-slate-500" />
                Sign In
              </Link>
            )}
          </nav>
        </div>
      </div>
    </>
  );
};

export default Header;