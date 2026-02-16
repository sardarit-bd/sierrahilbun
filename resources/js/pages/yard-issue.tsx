import React, { useState } from 'react';
import { ArrowRight, Check, Star } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, Link } from '@inertiajs/react';

// --- Data & Assets ---

// Using Unsplash images to match the vibe of the screenshot
const ASSETS = {
  lawnProduct: "https://images.unsplash.com/photo-1558904541-efa843a96f01?q=80&w=1632&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
  pestProduct: "https://images.unsplash.com/photo-1581578017093-cd30fce4eeb7?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D", 
  gardenProduct: "https://images.unsplash.com/photo-1594498653385-d5172c532c00?q=80&w=1074&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D", 
  mainHero: "https://images.unsplash.com/photo-1534361960057-19889db9621e?auto=format&fit=crop&q=80&w=1600",
};

const OPTIONS = [
  { id: 'lawn', label: 'Lawn', image: ASSETS.lawnProduct },
  { id: 'pest', label: 'Pest', image: ASSETS.pestProduct },
  { id: 'garden', label: 'Garden', image: ASSETS.gardenProduct },
];

export default function App() {
  // State for multi-selection
  const [selectedIds, setSelectedIds] = useState(['lawn']);

  const toggleSelection = (id) => {
    setSelectedIds((prev) => {
      if (prev.includes(id)) {
        // Prevent deselecting the last remaining option if desired, 
        // or just allow deselecting all. Let's allow deselecting all.
        return prev.filter((item) => item !== id);
      } else {
        return [...prev, id];
      }
    });
  };

  return (
    <AppHeaderLayout>
      <Head title="Select Yard" />
      <div className="flex flex-col lg:flex-row font-sans text-gray-900 bg-white">
        
        {/* Left Panel: Form Content */}
        <div className="w-full lg:w-1/2 xl:w-5/12 flex flex-col justify-center px-6 py-16 sm:px-12 sm:py-20 lg:px-16 lg:py-24 xl:px-24 xl:py-32 order-2 lg:order-1">
          
          {/* Header Section */}
          <div className="mb-8">
            <p className="text-xs font-bold tracking-wider text-gray-500 mb-2 uppercase">
              Get Started: 26546
            </p>
            <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2 font-sans">
              What can we help with?
            </h1>
            <p className="text-gray-500 italic">
              Select all that apply
            </p>
          </div>

          {/* Cards Grid */}
          <div className="grid grid-cols-3 gap-3 md:gap-4 mb-10">
            {OPTIONS.map((option) => {
              const isSelected = selectedIds.includes(option.id);
              return (
                <div
                  key={option.id}
                  onClick={() => toggleSelection(option.id)}
                  className={`
                    group cursor-pointer relative flex flex-col rounded-xl overflow-hidden border-2 transition-all duration-200 ease-in-out
                    ${isSelected 
                      ? 'border-[#2E7D32] shadow-lg scale-[1.02]' 
                      : 'border-gray-200 hover:border-gray-300 hover:shadow-md'
                    }
                  `}
                >
                  {/* Selection Checkmark Indicator */}
                  {isSelected && (
                    <div className="absolute top-2 left-2 z-10 bg-white rounded-full p-0.5 shadow-sm">
                      <div className="bg-green-500 rounded-full p-0.5">
                        <Check size={12} className="text-green" strokeWidth={3} />
                      </div>
                    </div>
                  )}

                  {/* Card Image */}
                  <div className="h-24 sm:h-32 w-full bg-gray-100 relative">
                    <img 
                      src={option.image} 
                      alt={option.label}
                      className="w-full h-full object-cover"
                    />
                    {/* Overlay for unselected state to dim slightly */}
                    {!isSelected && <div className="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors" />}
                  </div>

                  {/* Card Footer/Label */}
                  <div className="p-3 bg-white flex items-center justify-center gap-1 sm:gap-2">
                    <span className={`text-sm sm:text-base font-bold ${isSelected ? 'text-gray-900' : 'text-gray-600'}`}>
                      {option.label}
                    </span>
                    <ArrowRight 
                      size={16} 
                      className={`transition-transform duration-300 ${isSelected ? 'translate-x-1 text-gray-900' : 'text-gray-400'}`} 
                    />
                  </div>
                </div>
              );
            })}
          </div>

          {/* Action Button */}
          <button 
            className="w-full bg-[#2E7D32] text-gray-50 font-bold text-md py-3 rounded-lg shadow-sm hover:shadow-md transition-all active:scale-[0.99] cursor-pointer"
          >
            <Link href="/lawn-size">Continue</Link>
          </button>
        </div>

        {/* Right Panel: Hero Image */}
        <div className="w-full lg:w-1/2 xl:w-7/12 relative min-h-[300px] order-1 lg:order-2">
          <div className="absolute inset-0 bg-gray-200">
            <img 
              src={ASSETS.mainHero} 
              alt="Beautiful green lawn with a dog" 
              className="w-full h-full object-cover"
            />
          </div>
        </div>

      </div>
    </AppHeaderLayout>
  );
}