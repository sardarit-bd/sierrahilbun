import React, { useState } from 'react';
import { Trash2, Plus, Minus, ArrowRight, Truck, ShieldCheck, CreditCard, ChevronRight, Star, Tag } from 'lucide-react';
import { Link } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';

// --- Mock Data ---
const initialCartItems = [
  {
    id: 1,
    name: "Lawn Strong Liquid Fertilizer",
    variant: "2-pack (covers 5,000 sq ft)",
    price: 54.00,
    originalPrice: 60.00,
    image: "https://placehold.co/400x500/f0fdf4/166534?text=Lawn+Strong",
    quantity: 1,
    inStock: true,
  },
  {
    id: 2,
    name: "Dandelion Doom Herbicide",
    variant: "Concentrate Refill",
    price: 35.20,
    originalPrice: 44.00,
    image: "https://placehold.co/400x500/f0fdf4/166534?text=Dandelion",
    quantity: 1,
    inStock: true,
  }
];

export default function CartPage() {
  const [cartItems, setCartItems] = useState(initialCartItems);
  const [promoCode, setPromoCode] = useState("");

  // Calculations
  const subtotal = cartItems.reduce((acc, item) => acc + (item.price * item.quantity), 0);
  const shippingThreshold = 75;
  const isFreeShipping = subtotal >= shippingThreshold;
  const shippingCost = isFreeShipping ? 0 : 9.99;
  const taxEstimate = subtotal * 0.08; // 8% tax example
  const total = subtotal + shippingCost + taxEstimate;
  
  const updateQuantity = (id, change) => {
    setCartItems(prev => prev.map(item => {
      if (item.id === id) {
        const newQuantity = Math.max(1, item.quantity + change);
        return { ...item, quantity: newQuantity };
      }
      return item;
    }));
  };

  

  const removeItem = (id) => {
    setCartItems(prev => prev.filter(item => item.id !== id));
  };

  return (
    <AppHeaderLayout>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <h1 className="text-3xl md:text-4xl font-black font-serif text-gray-900 mb-8">Your Cart</h1>

        {cartItems.length > 0 ? (
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            {/* Left Column: Cart Items */}
            <div className="lg:col-span-8">
              
              {/* Free Shipping Progress Bar */}
              {/* <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6">
                <div className="flex items-center gap-3 mb-3">
                  <div className={`p-2 rounded-full ${isFreeShipping ? 'bg-green-100 text-[#2E7D32]' : 'bg-gray-100 text-gray-500'}`}>
                    <Truck size={20} />
                  </div>
                  <div>
                    {isFreeShipping ? (
                      <p className="font-bold text-[#2E7D32]">You've unlocked FREE shipping!</p>
                    ) : (
                      <p className="font-medium text-gray-600">
                        Add <span className="font-bold text-gray-900">${(shippingThreshold - subtotal).toFixed(2)}</span> more to unlock FREE shipping
                      </p>
                    )}
                  </div>
                </div>
                <div className="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                  <div 
                    className="h-full bg-[#2E7D32] transition-all duration-500 ease-out" 
                    style={{ width: `${Math.min((subtotal / shippingThreshold) * 100, 100)}%` }}
                  ></div>
                </div>
              </div> */}

              {/* Cart List */}
              <div className="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div className="divide-y divide-gray-100">
                  {cartItems.map((item) => (
                    <div key={item.id} className="p-6 flex flex-col sm:flex-row gap-6 group">
                      {/* Product Image */}
                      <div className="w-24 h-24 sm:w-32 sm:h-32 bg-gray-50 rounded-xl flex-shrink-0 flex items-center justify-center overflow-hidden">
                        <img 
                          src={item.image} 
                          alt={item.name} 
                          className="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-300" 
                        />
                      </div>

                      {/* Details */}
                      <div className="flex-1 flex flex-col justify-between">
                        <div>
                          <div className="flex justify-between items-start">
                            <div>
                              <h3 className="font-bold text-lg text-gray-900">{item.name}</h3>
                              <p className="text-gray-500 text-sm mt-1">{item.variant}</p>
                              {item.inStock ? (
                                <span className="text-xs font-bold text-[#2E7D32] mt-2 inline-block">In Stock</span>
                              ) : (
                                <span className="text-xs font-bold text-red-500 mt-2 inline-block">Out of Stock</span>
                              )}
                            </div>
                            <div className="text-right">
                              <p className="font-black text-lg text-gray-900">${(item.price * item.quantity).toFixed(2)}</p>
                              {item.originalPrice && (
                                <p className="text-sm text-gray-400 line-through">${(item.originalPrice * item.quantity).toFixed(2)}</p>
                              )}
                            </div>
                          </div>
                        </div>

                        {/* Controls */}
                        <div className="flex justify-between items-end mt-4 sm:mt-0">
                          <div className="flex items-center gap-4">
                            <div className="flex items-center border border-gray-200 rounded-lg p-1">
                              <button 
                                onClick={() => updateQuantity(item.id, -1)}
                                className="p-1 text-black hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors"
                                disabled={item.quantity <= 1}
                              >
                                <Minus size={16} />
                              </button>
                              <span className="w-8 text-center text-black font-bold text-sm">{item.quantity}</span>
                              <button 
                                onClick={() => updateQuantity(item.id, 1)}
                                className="p-1 text-black hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors"
                              >
                                <Plus size={16} />
                              </button>
                            </div>
                            <button 
                              onClick={() => removeItem(item.id)}
                              className="text-gray-400 hover:text-red-500 transition-colors underline text-sm decoration-gray-300 hover:decoration-red-300 underline-offset-2 cursor-pointer"
                            >
                              Remove
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

            </div>

            {/* Right Column: Summary */}
            <div className="lg:col-span-4">
              <div className="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50 p-6 lg:sticky lg:top-24">
                <h2 className="text-xl font-bold font-serif mb-6 text-gray-900">Order Summary</h2>
                
                <div className="space-y-4 text-sm">
                  <div className="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span className="font-bold text-gray-900">${subtotal.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-gray-600">
                    <span>Shipping estimate</span>
                    {isFreeShipping ? (
                      <span className="font-bold text-[#2E7D32]">FREE</span>
                    ) : (
                      <span className="font-bold text-gray-900">${shippingCost.toFixed(2)}</span>
                    )}
                  </div>
                  <div className="flex justify-between text-gray-600">
                    <span>Tax estimate</span>
                    <span className="font-bold text-gray-900">${taxEstimate.toFixed(2)}</span>
                  </div>
                  
                  {/* Promo Code */}
                  <div className="pt-4 pb-2">
                    <label htmlFor="promo" className="flex items-center gap-2 text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                      <Tag size={14} className="text-[#2E7D32]" />
                      Promo Code
                    </label>
                    <div className="relative flex items-center">
                      <input 
                        id="promo"
                        type="text" 
                        placeholder="Enter code"
                        value={promoCode}
                        onChange={(e) => setPromoCode(e.target.value)}
                        className="w-full bg-gray-50 border border-gray-200 rounded-lg py-3 pl-4 pr-24 text-sm focus:outline-none focus:border-[#2E7D32] focus:ring-1 focus:ring-[#2E7D32] transition-all"
                      />
                      <button className="absolute right-1 top-1 bottom-1 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold px-4 rounded-md transition-colors shadow-sm">
                        Apply
                      </button>
                    </div>
                  </div>

                  <div className="border-t border-gray-100 pt-4 flex justify-between items-center">
                    <span className="text-base font-black text-gray-900">Total</span>
                    <span className="text-2xl font-black text-gray-900">${total.toFixed(2)}</span>
                  </div>
                </div>

                <button className="w-full bg-[#2E7D32] hover:bg-[#1B5E20] text-white font-extrabold py-4 rounded-xl shadow-lg shadow-green-900/20 mt-8 flex items-center justify-center gap-2 group transition-all active:scale-[0.98]">
                  Checkout
                  <ArrowRight className="group-hover:translate-x-1 transition-transform" size={20} />
                </button>

                <div className="mt-6 flex flex-col gap-3 text-xs text-gray-500 text-center">
                  <div className="flex items-center justify-center gap-2">
                    <ShieldCheck size={14} className="text-[#2E7D32]" />
                    <span>Secure checkout guaranteed</span>
                  </div>
                  <div className="flex items-center justify-center gap-2">
                    <CreditCard size={14} />
                    <span>Ships within 1-2 business days</span>
                  </div>
                </div>

              </div>
            </div>

          </div>
        ) : (
          // Empty State
          <div className="text-center py-20 bg-white rounded-[3rem] border border-dashed border-gray-200">
            <div className="w-24 h-24 bg-[#E8F5E9] rounded-full flex items-center justify-center mx-auto mb-6 text-[#2E7D32]">
              <Truck size={48} />
            </div>
            <h2 className="text-3xl font-black font-serif text-gray-900 mb-4">Your cart is empty</h2>
            <p className="text-gray-500 max-w-md mx-auto mb-8">
              Looks like you haven't started your lawn care journey yet. Let's find the perfect plan for your grass type.
            </p>
            <Link href="/products" className="inline-flex items-center gap-2 bg-[#2E7D32] text-gray-50 hover:scale-105 font-extrabold py-4 px-8 rounded-full transition-colors shadow-sm">
              Start Shopping
              <ChevronRight size={20} />
            </Link>
          </div>
        )}

      </div>
    </AppHeaderLayout>
  );
}