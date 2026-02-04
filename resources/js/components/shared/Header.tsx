import React, { useState } from 'react';
import { Search, User, ShoppingCart, Menu, X } from 'lucide-react';
import { Link } from '@inertiajs/react';

const Header = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const navLinks = [
    { name: 'Home', href: '/' },
    { name: 'Products', href: '/products' },
    { name: 'Custom Lawn Plan', href: '/custom-lawn' },
    { name: 'TurfTalk Blog', href: '/blogs' },
  ];

  return (
    <header className="w-full bg-white font-sans text-slate-900 border-b border-gray-100 font-poppins">
      {/* Top Row: Logo, Search, User Actions */}
      <div className="max-w-7xl mx-auto px-4 lg:px-8 py-4">
        <div className="flex items-center justify-between gap-4 lg:gap-8">
          
          {/* Mobile Menu Button */}
          <button 
            className="lg:hidden p-2 -ml-2 text-slate-900"
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          >
            {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
          </button>

          {/* Logo */}
          <div className="flex-shrink-0 relative group cursor-pointer">
            <h1 className="text-3xl font-extrabold tracking-tight text-slate-900 relative z-10">
              TurfTec
            </h1>
          </div>

          {/* Search Bar - Hidden on small mobile, visible on desktop */}
          <div className="hidden md:flex flex-1 max-w-xl mx-4">
            <div className="relative w-full">
              <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Search size={20} className="text-slate-900" />
              </div>
              <input
                type="text"
                className="w-full bg-gray-100 text-slate-800 rounded-lg py-3 pl-11 pr-4 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:bg-white transition-colors placeholder-slate-500 font-medium"
                placeholder="What can we help you find today?"
              />
            </div>
          </div>

          {/* Right Actions: Sign In & Cart */}
          <div className="flex items-center gap-4 sm:gap-6 flex-shrink-0">
            {/* Search Icon for Mobile (replaces bar) */}
            <button className="md:hidden text-slate-900">
              <Search size={24} />
            </button>

            <a href="/login" className="hidden sm:flex items-center gap-2 text-slate-700 hover:text-slate-900 font-medium group">
              <User size={24} className="text-slate-900" />
              <span className="group-hover:underline decoration-yellow-400 decoration-2 underline-offset-4">Sign in</span>
            </a>
            
            <a href="#" className="flex items-center gap-2 text-slate-700 hover:text-slate-900 font-medium group">
              <span className="hidden sm:inline group-hover:underline decoration-yellow-400 decoration-2 underline-offset-4">Cart</span>
              <ShoppingCart size={24} className="text-slate-900" />
            </a>
          </div>
        </div>
      </div>

      {/* Bottom Row: Navigation & CTA - Desktop */}
      <div className="hidden lg:block border-t border-gray-100">
        <div className="max-w-7xl mx-auto px-4 lg:px-8">
          <div className="flex items-center justify-between py-3">
            <nav className="flex gap-8">
              {navLinks.map((link) => (
                <Link
                  key={link.name}
                  href={link.href}
                  className="text-sm font-bold text-slate-800 hover:text-slate-600 transition-colors whitespace-nowrap"
                >
                  {link.name}
                </Link>
              ))}
            </nav>
            <div className="flex-shrink-0 pl-4">
              <button className="bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-bold py-2 px-6 rounded-full transition-colors">
                Get your plan
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Mobile Menu Dropdown */}
      {isMobileMenuOpen && (
        <div className="lg:hidden absolute top-[72px] left-0 w-full bg-white z-50 border-t border-gray-100 shadow-xl">
          <div className="p-4 flex flex-col gap-4">
            {/* Mobile Search */}
            <div className="relative w-full md:hidden">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search size={18} className="text-slate-500" />
              </div>
              <input
                type="text"
                className="w-full bg-gray-100 rounded-md py-2 pl-10 pr-4 focus:outline-none"
                placeholder="Search..."
              />
            </div>

            <nav className="flex flex-col gap-4">
              {navLinks.map((link) => (
                <a
                  key={link.name}
                  href={link.href}
                  className="text-base font-bold text-slate-800 py-1 border-b border-gray-50"
                >
                  {link.name}
                </a>
              ))}
              <a href="#" className="text-base font-bold text-slate-800 py-1 flex items-center gap-2 sm:hidden">
                <User size={18} /> Sign in
              </a>
            </nav>

            <button className="w-full bg-yellow-400 text-slate-900 font-bold py-3 rounded-full mt-2">
              Get your plan
            </button>
          </div>
        </div>
      )}
    </header>
  );
};

export default Header;