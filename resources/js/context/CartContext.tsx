import React, { createContext, useContext, useState, useEffect } from 'react';

const CartContext = createContext();

export const useCart = () => {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};

export const CartProvider = ({ children }) => {
  const [cart, setCart]                 = useState([]);
  const [isLoaded, setIsLoaded]         = useState(false);
  const [appliedPromo, setAppliedPromo] = useState(null);

  useEffect(() => {
    const savedCart  = localStorage.getItem('turftec_cart');
    const savedPromo = localStorage.getItem('turftec_promo');

    if (savedCart) {
      try { setCart(JSON.parse(savedCart)); }
      catch (e) { console.error('Error loading cart:', e); }
    }

    if (savedPromo) {
      try { setAppliedPromo(JSON.parse(savedPromo)); }
      catch (e) { console.error('Error loading promo:', e); }
    }

    setIsLoaded(true);
  }, []);

  useEffect(() => {
    if (isLoaded) localStorage.setItem('turftec_cart', JSON.stringify(cart));
  }, [cart, isLoaded]);

  useEffect(() => {
    if (!isLoaded) return;
    if (appliedPromo) {
      localStorage.setItem('turftec_promo', JSON.stringify(appliedPromo));
    } else {
      localStorage.removeItem('turftec_promo');
    }
  }, [appliedPromo, isLoaded]);

  const addToCart = (product, quantity = 1) => {
    setCart((prevCart) => {
      const existingItem = prevCart.find(item => item.id === product.id);
      if (existingItem) {
        return prevCart.map(item =>
          item.id === product.id
            ? { ...item, quantity: item.quantity + quantity }
            : item
        );
      }
      return [
        ...prevCart,
        {
          id:            product.id,
          name:          product.name || product.title,
          price:         product.base_price || product.price,
          originalPrice: product.original_price ?? null,

          // Plan products use primary_image; direct products use image
          image:         product.image || product.primary_image || null,

          variant:       product.variant ?? '',
          inStock:       product.inStock ?? true,
          quantity,
          lawn_plan_id:    product.lawn_plan_id    ?? null,
          weed_plan_id:    product.weed_plan_id    ?? null,
          garden_products: product.garden_products ?? null,

          // ✅ Persist assessment_id so checkout can resolve sqft-scaled price
          assessment_id:   product.assessment_id   ?? null,
        },
      ];
    });
  };

  const removeFromCart = (productId) =>
    setCart((prev) => prev.filter((item) => item.id !== productId));

  const updateQuantity = (productId, quantity) => {
    if (quantity <= 0) { removeFromCart(productId); return; }
    setCart((prev) =>
      prev.map((item) => item.id === productId ? { ...item, quantity } : item)
    );
  };

  const clearCart = () => {
    setCart([]);
    setAppliedPromo(null);
  };

  const getCartTotal = () =>
    cart.reduce((total, item) => total + item.price * item.quantity, 0);

  const getCartCount = () =>
    cart.reduce((count, item) => count + item.quantity, 0);

  // ── Promo Code ────────────────────────────────────────────────────

  const applyPromoCode = async (code) => {
    const subtotal = getCartTotal();

    try {
      const response = await fetch('/promo/validate', {
        method:  'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept':       'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify({ code: code.trim(), subtotal }),
      });

      const data = await response.json();

      if (!response.ok) {
        return { success: false, message: data.message || 'Invalid promo code.' };
      }

      setAppliedPromo({
        code:     data.promo.code,
        type:     data.promo.type,
        value:    data.promo.value,
        discount: data.promo.discount,
      });

      return { success: true, message: data.message };

    } catch {
      return { success: false, message: 'Could not validate promo code. Please try again.' };
    }
  };

  const removePromoCode = () => setAppliedPromo(null);

  const getDiscountAmount = () => appliedPromo?.discount ?? 0;

  const value = {
    cart,
    isLoaded,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart,
    getCartTotal,
    getCartCount,
    appliedPromo,
    applyPromoCode,
    removePromoCode,
    getDiscountAmount,
  };

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
};