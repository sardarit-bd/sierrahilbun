import React, { useState } from 'react';
import { ArrowRight, Check } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, useForm } from '@inertiajs/react';

const ASSETS = {
  lawnProduct:   "https://images.unsplash.com/photo-1558904541-efa843a96f01?q=80&w=1632&auto=format&fit=crop",
  pestProduct:   "https://images.unsplash.com/photo-1581578017093-cd30fce4eeb7?q=80&w=1170&auto=format&fit=crop",
  gardenProduct: "https://images.unsplash.com/photo-1594498653385-d5172c532c00?q=80&w=1074&auto=format&fit=crop",
  mainHero:      "https://images.unsplash.com/photo-1534361960057-19889db9621e?auto=format&fit=crop&q=80&w=1600",
};

const OPTIONS = [
  { id: 'lawn',   label: 'Lawn',   image: ASSETS.lawnProduct,   locked: true },
  { id: 'pest',   label: 'Pest',   image: ASSETS.pestProduct,   locked: false },
  { id: 'garden', label: 'Garden', image: ASSETS.gardenProduct, locked: false },
];

export default function YardIssue({ zip_code }) {
  const [selectedIds, setSelectedIds] = useState(['lawn']);

  const { data, setData, post, processing } = useForm({
    selected_services: ['lawn'],
  });

  const toggleSelection = (id) => {
    if (id === 'lawn') return;

    const updated = selectedIds.includes(id)
      ? selectedIds.filter((item) => item !== id)
      : [...selectedIds, id];

    setSelectedIds(updated);
    setData('selected_services', updated);
  };

  const handleSubmit = () => {
    post(route('yard.category.store'));
  };

  return (
    <AppHeaderLayout>
      <Head title="Select Yard" />
      <div className="flex flex-col lg:flex-row font-sans text-gray-900 bg-white">

        {/* Left Panel */}
        <div className="w-full lg:w-1/2 xl:w-5/12 flex flex-col justify-center px-6 py-16 sm:px-12 sm:py-20 lg:px-16 lg:py-24 xl:px-24 xl:py-32 order-2 lg:order-1">

          <div className="mb-8">
            <p className="text-xs font-bold tracking-wider text-gray-500 mb-2 uppercase">
              Get Started: {zip_code}
            </p>
            <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2">
              What can we help with?
            </h1>
            <p className="text-gray-500 italic">Select all that apply</p>
          </div>

          <div className="grid grid-cols-3 gap-3 md:gap-4 mb-10">
            {OPTIONS.map((option) => {
              const isSelected = selectedIds.includes(option.id);

              return (
                <div
                  key={option.id}
                  onClick={() => toggleSelection(option.id)}
                  className={`
                    group relative flex flex-col rounded-xl overflow-hidden border-2 transition-all duration-200 ease-in-out
                    ${isSelected
                      ? 'border-[#2E7D32] shadow-lg scale-[1.02]'
                      : 'border-gray-200 hover:border-gray-300 hover:shadow-md'
                    }
                    ${option.locked ? 'cursor-default' : 'cursor-pointer'}
                  `}
                >
                  {option.locked && (
                    <div className="absolute top-2 right-2 z-10 bg-green-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">
                      Required
                    </div>
                  )}

                  {isSelected && (
                    <div className="absolute top-2 left-2 z-10 bg-white rounded-full p-0.5 shadow-sm">
                      <div className="bg-green-500 rounded-full p-0.5">
                        <Check size={12} className="text-white" strokeWidth={3} />
                      </div>
                    </div>
                  )}

                  <div className="h-24 sm:h-32 w-full bg-gray-100 relative">
                    <img
                      src={option.image}
                      alt={option.label}
                      className="w-full h-full object-cover"
                    />
                    {!isSelected && (
                      <div className="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors" />
                    )}
                  </div>

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

          <button
            onClick={handleSubmit}
            disabled={processing}
            className="w-full bg-[#2E7D32] text-gray-50 font-bold text-md py-3 rounded-lg shadow-sm hover:shadow-md transition-all active:scale-[0.99] cursor-pointer disabled:opacity-50"
          >
            {processing ? 'Loading...' : 'Continue'}
          </button>
        </div>

        {/* Right Panel */}
        <div className="w-full lg:w-1/2 xl:w-7/12 relative min-h-[300px] order-1 lg:order-2">
          <div className="absolute inset-0 bg-gray-200">
            <img
              src={ASSETS.mainHero}
              alt="Beautiful green lawn"
              className="w-full h-full object-cover"
            />
          </div>
        </div>

      </div>
    </AppHeaderLayout>
  );
}