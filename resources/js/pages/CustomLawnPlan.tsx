import React, { useState } from 'react';
import { MapPin, ArrowRight, Leaf, Sparkles } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, useForm } from '@inertiajs/react';

export default function LocationQueryPage() {
  const [isFocused, setIsFocused] = useState(false);

  const { data, setData, post, processing, errors } = useForm({
    zip_code: '',
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    post(route('yard.start.store'));
  };

  return (
    <AppHeaderLayout>
      <Head title="Custom Lawn Plan" />
      <div className="max-w-7xl mx-auto px-6 py-12 w-full">

        <div className="relative bg-[#2E7D32] rounded-[2.5rem] overflow-hidden shadow-2xl shadow-green-900/20 isolate">

          <div className="absolute inset-0 bg-gradient-to-br from-[#2E7D32] to-[#1B5E20] z-0"></div>
          <div className="absolute top-0 right-0 w-[600px] h-[600px] bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 z-0 pointer-events-none"></div>
          <div className="absolute bottom-0 left-0 w-[400px] h-[400px] bg-black/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3 z-0 pointer-events-none"></div>

          <div className="relative z-10 grid grid-cols-1 lg:grid-cols-2 min-h-[500px]">

            {/* Left Column */}
            <div className="p-10 md:p-16 flex flex-col justify-center">

              {/* <div className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-4 py-1.5 w-fit mb-8 shadow-sm">
                <Sparkles className="w-4 h-4 text-[#81C784]" />
                <span className="text-white text-xs font-bold tracking-wider uppercase">
                  Limited Offer
                </span>
              </div> */}

              <h1 className="text-3xl md:text-5xl font-black text-white leading-[1.1] mb-4 font-serif tracking-tight mb-10">
                What's the <span className="text-[#A5D6A7] italic">real</span> current location of your home?
              </h1>

              {/* <div className="flex items-center gap-3 mb-10">
                <div className="h-px bg-white/30 w-12"></div>
                <p className="text-white/90 font-medium text-lg">
                  Save <span className="font-bold text-[#FFD54F]">30%</span> on custom yard plans
                </p>
              </div> */}

              <form onSubmit={handleSubmit} className="max-w-md">
                <label
                  htmlFor="zip-input"
                  className="block text-white text-sm font-bold uppercase tracking-wider mb-3 ml-1"
                >
                  Where do you live?
                </label>

                <div
                  className={`
                    relative bg-white rounded-2xl p-2 shadow-xl transition-all duration-300 ease-out flex flex-col sm:flex-row items-center gap-2
                    ${isFocused ? 'ring-4 ring-[#81C784]/30 transform -translate-y-1' : ''}
                  `}
                >
                  <div className="relative w-full">
                    <div className="absolute left-4 top-1/2 -translate-y-1/2 flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-400">
                      <MapPin className="w-4 h-4" />
                    </div>
                    <input
                      id="zip-input"
                      type="text"
                      inputMode="numeric"
                      maxLength={5}
                      value={data.zip_code}
                      onChange={(e) => setData('zip_code', e.target.value.replace(/\D/g, ''))}
                      onFocus={() => setIsFocused(true)}
                      onBlur={() => setIsFocused(false)}
                      placeholder="Enter Zip Code"
                      className="w-full pl-14 pr-4 py-4 rounded-xl outline-none text-gray-900 font-bold placeholder-gray-400 bg-transparent text-lg"
                    />
                  </div>

                  <button
                    type="submit"
                    disabled={processing || data.zip_code.length !== 5}
                    className="w-full sm:w-auto bg-[#1B5E20] hover:bg-[#2E7D32] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-4 px-8 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 group whitespace-nowrap shadow-lg"
                  >
                    {processing ? 'Checking...' : 'Next'}
                    <ArrowRight className="w-5 h-5 transition-transform group-hover:translate-x-1" />
                  </button>
                </div>

                {errors.zip_code && (
                  <p className="text-red-300 text-sm mt-3 ml-2 font-medium">
                    {errors.zip_code}
                  </p>
                )}

                <p className="text-white/60 text-xs mt-3 ml-2 font-medium">
                  Get started: enter your 5-digit zip code
                </p>
              </form>
            </div>

            {/* Right Column */}
            <div className="relative h-64 lg:h-auto overflow-hidden">
              <div className="absolute top-0 bottom-0 left-0 w-16 bg-gradient-to-r from-[#2E7D32] to-transparent z-10 hidden lg:block"></div>
              <div className="absolute top-0 left-0 right-0 h-16 bg-gradient-to-b from-[#2E7D32] to-transparent z-10 lg:hidden"></div>

              <img
                src="https://images.unsplash.com/photo-1575881875475-31023242e3f9?q=80&w=2070&auto=format&fit=crop"
                alt="Lush Green Lawn"
                className="w-full h-full object-cover transition-transform duration-700 hover:scale-105"
              />

              <div className="absolute bottom-8 right-8 bg-white/90 backdrop-blur-sm p-4 rounded-2xl shadow-xl max-w-[200px] hidden sm:block">
                <div className="flex items-center gap-3 mb-2">
                  <div className="w-10 h-10 bg-[#E8F5E9] rounded-full flex items-center justify-center text-[#2E7D32]">
                    <Leaf size={20} />
                  </div>
                  <div>
                    <div className="text-xs text-gray-500 font-bold uppercase">Result</div>
                    <div className="text-gray-900 font-bold text-sm">Healthy Turf</div>
                  </div>
                </div>
                <div className="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                  <div className="h-full bg-[#4CAF50] w-[90%] rounded-full"></div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </AppHeaderLayout>
  );
}