import React, { useState, useEffect } from 'react';
import { 
  Check, 
  Mountain, 
  Sprout, 
  Thermometer, 
  Droplets, 
  MapPin, 
  ArrowRight,
  Plus,
  Minus,
  Maximize,
  Layers,
  ChevronDown,
  AlertCircle
} from 'lucide-react';

import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, Link } from '@inertiajs/react';

// --- Constants & Data ---

const ASSETS = {
  satelliteMap: "https://images.unsplash.com/photo-1524813686514-a57563d77965?auto=format&fit=crop&q=80&w=1600",
};

const FEATURES = [
  {
    id: 'soil',
    title: 'Regional soil profile',
    description: 'The average ratios of sand, silt, and clay in your area tell us what will grow best in your yard—and what nutrients you need to succeed.',
    icon: Mountain,
    color: 'text-amber-700',
    bg: 'bg-amber-50',
  },
  {
    id: 'growth',
    title: 'Growth potential',
    description: 'Growth potential is based on grass type, temperature, and rainfall. This timeline helps us determine the best application dates for your fertilizers.',
    icon: Sprout,
    color: 'text-green-700',
    bg: 'bg-green-50',
  },
  {
    id: 'temps',
    title: 'Historical temps',
    description: 'This lets us predict when spring greenup will start, and when to prep for heat—so we can recommend the best time to apply nutrients.',
    icon: Thermometer,
    color: 'text-red-600',
    bg: 'bg-red-50',
  },
  {
    id: 'rainfall',
    title: 'Historical rainfall',
    description: "This helps us understand whether your lawn requires nutrients designed for dry or arid climates—and how much supplemental water you'll need.",
    icon: Droplets,
    color: 'text-blue-600',
    bg: 'bg-blue-50',
  }
];

const LOADING_STEPS = [
  { id: 1, label: 'Satellite imagery' },
  { id: 2, label: 'Property data' },
  { id: 3, label: 'Climate history' },
  { id: 4, label: 'Soil profile' },
];

// --- Sub-Components ---

const RadioOption = ({ label, value, selectedValue, onChange }) => {
  const isSelected = selectedValue === value;
  
  return (
    <div 
      onClick={() => onChange(value)}
      className={`
        relative flex items-center p-4 cursor-pointer rounded-xl border-2 transition-all duration-200 group bg-white
        ${isSelected 
          ? 'border-green-600 bg-green-50/30 shadow-sm' 
          : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
        }
      `}
    >
      <div className={`
        w-6 h-6 rounded-full border-2 flex items-center justify-center mr-4 transition-colors flex-shrink-0
        ${isSelected ? 'border-green-600' : 'border-gray-300 group-hover:border-gray-400'}
      `}>
        {isSelected && <div className="w-3 h-3 bg-green-600 rounded-full animate-pop-in" />}
      </div>
      <span className={`font-semibold text-lg ${isSelected ? 'text-green-900' : 'text-gray-700'}`}>
        {label}
      </span>
    </div>
  );
};

// --- View 1: Lawn Size Form ---
const LawnSizeView = ({ onContinue }) => {
  const [hasLawnSize, setHasLawnSize] = useState(null);
  const [sqFt, setSqFt] = useState('');

  // Auto-focus input when it appears
  useEffect(() => {
    if (hasLawnSize === 'yes') {
        const input = document.getElementById('sqft-input');
        if (input) input.focus();
    }
  }, [hasLawnSize]);

  return (
    <div className="min-h-screen bg-white font-sans text-gray-900 pb-20">
      <style>{`
        @keyframes fade-in-down {
          0% { opacity: 0; transform: translateY(-10px); }
          100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down {
          animation: fade-in-down 0.3s ease-out forwards;
        }
        @keyframes pop-in {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }
        .animate-pop-in {
            animation: pop-in 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
      `}</style>

      {/* Split Hero (Question + Map) */}
      <div className="flex flex-col lg:flex-row min-h-[600px] lg:h-[65vh]">
        
        {/* Left: Form Content */}
        <div className="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 sm:px-16 lg:px-24 bg-gray-50/50 lg:bg-white order-2 lg:order-1 relative z-10">
          <div className="max-w-md mx-auto lg:mx-0 w-full">
            <div className="flex items-center gap-2 mb-6">
              <MapPin size={16} className="text-green-600" />
              <p className="text-xs font-bold tracking-widest text-gray-500 uppercase">
                Zip Code: 26546
              </p>
            </div>
            
            <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-8 leading-tight font-sans">
              Do you know your lawn size?
            </h1>

            <div className="space-y-4 mb-10">
              <RadioOption 
                label="Yes, I do!" 
                value="yes" 
                selectedValue={hasLawnSize} 
                onChange={setHasLawnSize} 
              />
              
              {/* Conditional Input Field */}
              {hasLawnSize === 'yes' && (
                <div className="animate-fade-in-down pl-1 pt-2 pb-2">
                   <label htmlFor="sqft-input" className="block text-gray-700 text-lg mb-3">
                     <span className="font-bold text-gray-900">Awesome!</span> Enter your square footage
                   </label>
                   <div className="relative">
                     <input
                       id="sqft-input"
                       type="number"
                       value={sqFt}
                       onChange={(e) => setSqFt(e.target.value)}
                       placeholder="Area to treat (sq. ft)"
                       className="w-full p-4 pl-4 border-2 border-gray-200 rounded-xl text-lg focus:border-green-600 focus:ring-4 focus:ring-green-600/10 outline-none transition-all duration-200 bg-white text-gray-900 placeholder-gray-400 shadow-sm"
                     />
                     <div className="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-400 text-sm font-medium">
                        SQ. FT
                     </div>
                   </div>
                </div>
              )}

              <RadioOption 
                label="No, I don't" 
                value="no" 
                selectedValue={hasLawnSize} 
                onChange={setHasLawnSize} 
              />
            </div>

            {hasLawnSize && (
              <button 
                onClick={() => onContinue(hasLawnSize)}
                className="w-full bg-[#2E7D32] text-gray-50 font-bold text-lg py-3 rounded-lg shadow-md hover:shadow-lg transition-all transform active:scale-[0.99] flex items-center justify-center gap-2 animate-fade-in-up cursor-pointer hover:bg-[#256628]"
              >
                Continue <ArrowRight size={20} />
              </button>
            )}
          </div>
        </div>

        {/* Right: Map Visual */}
        <div className="w-full lg:w-1/2 relative min-h-[300px] lg:h-auto order-1 lg:order-2 overflow-hidden bg-gray-900">
          <img 
            src={ASSETS.satelliteMap} 
            alt="Satellite view of neighborhood" 
            className="w-full h-full object-cover opacity-90 hover:scale-105 transition-transform duration-[20s] ease-linear"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-black/10 pointer-events-none"></div>

          {/* Map Overlay Simulation */}
          <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
             <svg className="w-3/4 h-3/4 opacity-90 drop-shadow-2xl animate-pulse-slow" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20,80 L30,30 L70,25 L80,75 Z" stroke="#FFB84D" strokeWidth="1" strokeDasharray="4 2" fill="rgba(34, 197, 94, 0.2)" />
                <circle cx="20" cy="80" r="1.5" fill="white" stroke="#FFB84D" strokeWidth="1" />
                <circle cx="30" cy="30" r="1.5" fill="white" stroke="#FFB84D" strokeWidth="1" />
                <circle cx="70" cy="25" r="1.5" fill="white" stroke="#FFB84D" strokeWidth="1" />
                <circle cx="80" cy="75" r="1.5" fill="white" stroke="#FFB84D" strokeWidth="1" />
                
                <foreignObject x="40" y="40" width="40" height="20">
                  <div xmlns="http://www.w3.org/1999/xhtml" className="flex items-center justify-center">
                    <span className="bg-white/90 backdrop-blur-sm text-[6px] font-bold px-2 py-0.5 rounded shadow-sm border border-green-100 text-green-800">
                      High Confidence
                    </span>
                  </div>
                </foreignObject>
             </svg>
          </div>
        </div>
      </div>

      {/* Features Grid */}
      <div className="max-w-7xl mx-auto px-6 py-16 sm:px-12 lg:py-24">
        <div className="text-center max-w-3xl mx-auto mb-16">
          <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-4 font-sans">
            Look at what{' '}
            <span className="relative inline-block">
              your location
              <span className="absolute bottom-1 left-0 w-full h-3 bg-[#FFB84D]/40 -z-10 rounded-sm transform -rotate-1 skew-x-6"></span>
            </span>
            {' '}can tell us
          </h2>
          <p className="text-gray-500 text-lg">
            We analyze millions of data points specific to your neighborhood to customize your plan.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-12">
          {FEATURES.map((feature) => (
            <div key={feature.id} className="flex flex-col items-start group">
              <div className={`mb-6 p-4 rounded-2xl ${feature.bg} ${feature.color} transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-sm`}>
                <feature.icon size={32} strokeWidth={1.5} />
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-3 group-hover:text-green-700 transition-colors">
                {feature.title}
              </h3>
              <p className="text-gray-500 leading-relaxed text-sm lg:text-base">
                {feature.description}
              </p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

// --- View 2: Confirm Area Map View ---
const ConfirmAreaView = ({ onContinue, onBack }) => {
  const [sizeInput, setSizeInput] = useState('7216');

  return (
    <div className="relative h-screen w-full flex flex-col md:flex-row overflow-hidden bg-gray-100 font-sans">
      
      {/* Sidebar Panel */}
      <div className="relative z-20 w-full md:w-[480px] lg:w-[500px] flex-shrink-0 bg-white h-auto md:h-full shadow-2xl flex flex-col overflow-y-auto">
        <div className="p-8 md:p-12 flex flex-col justify-center h-full">
          
          <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 leading-tight">
            Confirm the area you want treated
          </h1>

          <div className="mb-6">
            <p className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">
              Estimated yard size
            </p>
            <div className="text-5xl font-extrabold text-gray-900 tracking-tight">
              7,216 <span className="text-2xl text-gray-500 font-bold ml-1">sq. ft</span>
            </div>
          </div>

          <div className="mb-8">
            <p className="text-gray-500 text-sm font-medium">
              12125 Day Street<br/>
              Moreno Valley, CA 92557
            </p>
          </div>

          <div className="mb-8">
            <label className="block text-xs font-semibold text-gray-500 mb-2">
              Edit the area of lawn you want treated (in sq. ft)
            </label>
            <input 
              type="number" 
              value={sizeInput}
              onChange={(e) => setSizeInput(e.target.value)}
              className="w-full border border-gray-300 rounded-md px-4 py-3 text-lg font-semibold text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all"
            />
            
            <button className="flex items-center text-sm font-medium text-gray-500 mt-4 hover:text-green-600 transition-colors">
              Lawn size guide <ChevronDown size={14} className="ml-1" />
            </button>
          </div>

          <div className="space-y-3">
            <button 
              onClick={onContinue}
              className="w-full bg-[#2E7D32] hover:bg-[#256628] text-white font-bold py-4 rounded-lg shadow-lg hover:shadow-xl transition-all transform active:scale-[0.99] text-lg"
            >
              Continue
            </button>
            <button 
              className="w-full bg-white border-2 border-gray-200 text-gray-700 font-bold py-4 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all text-lg"
            >
              Something's not right
            </button>
          </div>

        </div>
      </div>

      {/* Map Area */}
      <div className="absolute inset-0 md:relative flex-grow bg-gray-200 z-0">
        <img 
          src={ASSETS.satelliteMap} 
          alt="Satellite Map" 
          className="w-full h-full object-cover opacity-90"
        />
        
        {/* Map Overlay: Circle */}
        <div className="absolute inset-0 flex items-center justify-center pl-0 md:pl-[500px] pointer-events-none">
          <div className="w-64 h-64 md:w-96 md:h-96 rounded-full border-2 border-white/80 bg-green-500/10 backdrop-blur-[1px] shadow-[0_0_0_9999px_rgba(0,0,0,0.3)] animate-pulse-slow">
            {/* Center Dot */}
            <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full shadow-lg border-2 border-green-600"></div>
            
            {/* Label on map */}
            <div className="absolute top-[40%] left-1/2 transform -translate-x-1/2 -translate-y-full bg-white/95 backdrop-blur shadow-lg rounded px-3 py-1 text-xs font-bold text-gray-900 whitespace-nowrap border border-gray-200">
               Est. 7,216 sq ft
               <div className="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1/2 w-2 h-2 bg-white rotate-45 border-r border-b border-gray-200"></div>
            </div>
          </div>
        </div>

        {/* Map Controls */}
        <div className="absolute bottom-8 right-6 flex flex-col gap-2">
            <button className="bg-white p-2 rounded shadow-md hover:bg-gray-50 text-gray-700">
               <Maximize size={20} />
            </button>
            <div className="flex flex-col bg-white rounded shadow-md overflow-hidden">
              <button className="p-2 hover:bg-gray-50 text-gray-700 border-b border-gray-100">
                 <Plus size={20} />
              </button>
              <button className="p-2 hover:bg-gray-50 text-gray-700">
                 <Minus size={20} />
              </button>
            </div>
             <button className="bg-white p-2 rounded shadow-md hover:bg-gray-50 text-gray-700">
               <Layers size={20} />
            </button>
        </div>
        
        {/* Google Maps Attribution Mock */}
        <div className="absolute bottom-1 right-1 bg-white/70 px-1 text-[10px] text-gray-600">
           Map data ©2024 Google
        </div>
      </div>

    </div>
  );
};

// --- View 3: Loading / We're Learning ---
const LoadingView = () => {
  const [completedSteps, setCompletedSteps] = useState([]);
  const ANIMATION_SPEED_MS = 1000; 

  useEffect(() => {
    let currentStepIndex = 0;
    
    const interval = setInterval(() => {
      if (currentStepIndex < LOADING_STEPS.length) {
        const stepId = LOADING_STEPS[currentStepIndex].id;
        
        setCompletedSteps((prev) => {
          if (prev.includes(stepId)) return prev;
          return [...prev, stepId];
        });
        
        currentStepIndex++;
      } else {
        clearInterval(interval);
      }
    }, ANIMATION_SPEED_MS);

    return () => clearInterval(interval);
  }, []);

  return (
    <div className="bg-[#EFF5F3] flex flex-col items-center justify-center font-sans px-6 text-gray-900 animate-fade-in py-24 min-h-[60vh]">
      
      {/* Header Text */}
      <div className="text-center mb-10 animate-fade-in-down">
        <h1 className="text-3xl sm:text-4xl font-extrabold mb-2 text-gray-900 font-sans">
          We're learning
        </h1>
        <p className="text-gray-600 text-base sm:text-lg font-medium">
          to help us create your lawn plan
        </p>
      </div>

      {/* Central Lawn Icon */}
      <div className="mb-12 relative">
        <div className="absolute inset-0 bg-green-400/30 blur-xl rounded-full transform scale-150 animate-pulse"></div>
        <div className="relative w-16 h-16 bg-green-600 rounded-md shadow-lg overflow-hidden flex items-center justify-center z-10">
          <div className="absolute inset-0 bg-green-500"></div>
          <div className="absolute top-0 right-0 w-1/2 h-1/2 bg-green-700/20"></div>
          <div className="absolute top-2 left-2 bottom-2 right-2 border-l-4 border-b-4 border-green-200/40 rounded-bl-sm"></div>
          <div className="absolute top-2 right-2 w-1 h-1 bg-green-200/40 rounded-full"></div>
        </div>
      </div>

      {/* Steps List */}
      <div className="flex flex-col items-start gap-5 w-full max-w-[280px]">
        {LOADING_STEPS.map((step) => {
          const isCompleted = completedSteps.includes(step.id);
          return (
            <div key={step.id} className="flex items-center gap-4 group">
              <div className="relative flex-shrink-0">
                <div className={`
                    w-7 h-7 rounded-full flex items-center justify-center transition-all duration-500 ease-out border-2
                    ${isCompleted 
                      ? 'bg-green-500 border-green-500 scale-100' 
                      : 'bg-transparent border-green-500/40 scale-95'
                    }
                  `}>
                  <Check size={16} strokeWidth={4} className={`
                      text-white transition-all duration-300 transform
                      ${isCompleted ? 'opacity-100 scale-100' : 'opacity-0 scale-50'}
                    `} />
                </div>
              </div>
              <span className={`text-lg sm:text-xl font-bold transition-colors duration-500 ${isCompleted ? 'text-gray-900' : 'text-gray-400'}`}>
                {step.label}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
};

// --- Main App Controller ---

export default function App() {
  const [currentView, setCurrentView] = useState('form'); 

  const handleFormContinue = (choice) => {
    if (choice === 'yes') {
        setCurrentView('loading');
    } else {
        setCurrentView('confirmArea');
    }
  };

  const handleMapContinue = () => {
      setCurrentView('loading');
  }

  return (
    <AppHeaderLayout>
      {currentView === 'form' && (
        <LawnSizeView onContinue={handleFormContinue} />
      )}
      {currentView === 'confirmArea' && (
        <ConfirmAreaView onContinue={handleMapContinue} />
      )}
      {currentView === 'loading' && (
        <LoadingView />
      )}
    </AppHeaderLayout>
  );
}