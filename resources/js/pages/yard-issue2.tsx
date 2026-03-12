import React, { useState } from 'react';
import { ArrowRight, Check } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, useForm } from '@inertiajs/react';

// Image map — add new entries here as you add services
const SERVICE_IMAGES = {
  lawn:   "https://images.unsplash.com/photo-1558904541-efa843a96f01?q=80&w=1632&auto=format&fit=crop",
  pest:   "https://images.unsplash.com/photo-1488920233920-d0a545a56b1d?q=80&w=1170&auto=format&fit=crop",
  garden: "https://images.unsplash.com/photo-1594498653385-d5172c532c00?q=80&w=1074&auto=format&fit=crop",
};

const MAIN_HERO = "https://images.unsplash.com/photo-1534361960057-19889db9621e?auto=format&fit=crop&q=80&w=1600";

export default function YardIssue({ zip_code, services }) {
  const LOCKED_SLUG = 'lawn';

  const [selectedIds, setSelectedIds] = useState([LOCKED_SLUG]);

  const { data, setData, post, processing } = useForm({
    selected_services: [LOCKED_SLUG],
  });

  const toggleSelection = (slug) => {
    if (slug === LOCKED_SLUG) return;

    const updated = selectedIds.includes(slug)
      ? selectedIds.filter((item) => item !== slug)
      : [...selectedIds, slug];

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
            {/* <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2">
              What can we help with?
            </h1>
            <p className="text-gray-500 italic">Select all that apply</p> */}
          </div>

          <div className="grid grid-cols-3 gap-3 md:gap-4 mb-10">
            {services.map((service) => {
              const isSelected = selectedIds.includes(service.slug);
              const isLocked   = service.slug === LOCKED_SLUG;

              return (
                <div
                  key={service.slug}
                  onClick={() => toggleSelection(service.slug)}
                  className={`
                    group relative flex flex-col rounded-xl overflow-hidden border-2 transition-all duration-200 ease-in-out
                    ${isSelected
                      ? 'border-[#2E7D32] shadow-lg scale-[1.02]'
                      : 'border-gray-200 hover:border-gray-300 hover:shadow-md'
                    }
                    ${isLocked ? 'cursor-default' : 'cursor-pointer'}
                  `}
                >
                  {isLocked && (
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
                      src={SERVICE_IMAGES[service.slug] ?? SERVICE_IMAGES.lawn}
                      alt={service.name}
                      className="w-full h-full object-cover"
                    />
                    {!isSelected && (
                      <div className="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors" />
                    )}
                  </div>

                  <div className="p-3 bg-white flex items-center justify-center gap-1 sm:gap-2">
                    <span className={`text-sm sm:text-base font-bold ${isSelected ? 'text-gray-900' : 'text-gray-600'}`}>
                      {service.name}
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
            <img src={MAIN_HERO} alt="Beautiful green lawn" className="w-full h-full object-cover" />
          </div>
        </div>

      </div>
    </AppHeaderLayout>
  );
}