import { useState, useEffect, useRef } from 'react';
import { Check, ChevronDown, Award } from 'lucide-react';

const PremiumPlanDropdown = ({ options, value, onChange, recommendedTier, label = 'Select Plan' }) => {
  const [isOpen, setIsOpen]    = useState(false);
  const dropdownRef            = useRef(null);
  const selectedOption         = options.find(opt => opt.id === Number(value)) || options[0];

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target)) setIsOpen(false);
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div className="relative w-full sm:max-w-xs" ref={dropdownRef}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className={`group flex items-center justify-between w-full px-4 py-2.5 bg-white border transition-all duration-300 rounded-lg text-left ${
          isOpen ? 'border-green-600 ring-4 ring-green-50 shadow-sm' : 'border-gray-300 hover:border-gray-400 shadow-sm'
        }`}
      >
        <div className="flex flex-col">
          <span className="text-[9px] uppercase tracking-wider font-bold text-gray-400 mb-0.5">{label}</span>
          <div className="flex items-center gap-2">
            <span className="font-bold text-gray-800 text-sm sm:text-base leading-none">{selectedOption?.name}</span>
            {selectedOption?.target_audience === recommendedTier && (
              <span className="bg-green-600 text-white text-[8px] px-1.5 py-0.5 rounded-full font-bold uppercase tracking-tighter">
                Best Fit
              </span>
            )}
          </div>
        </div>
        <ChevronDown size={18} className={`text-gray-400 transition-transform duration-300 ${isOpen ? 'rotate-180 text-green-600' : ''}`} />
      </button>

      {isOpen && (
        <div className="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-2xl overflow-hidden">
          <div className="p-1 max-h-72 overflow-y-auto">
            {options.map((plan) => {
              const isSelected    = plan.id === Number(value);
              const isRecommended = plan.target_audience === recommendedTier;
              return (
                <button
                  key={plan.id}
                  onClick={() => { onChange(plan.id); setIsOpen(false); }}
                  className={`flex flex-col w-full px-4 py-3 text-left transition-colors rounded-lg mb-0.5 last:mb-0 ${isSelected ? 'bg-green-50' : 'hover:bg-gray-50'}`}
                >
                  <div className="flex items-center justify-between w-full mb-0.5">
                    <div className="flex items-center gap-2">
                      <span className={`font-bold text-sm sm:text-base ${isSelected ? 'text-green-800' : 'text-gray-900'}`}>{plan.name}</span>
                      {isRecommended && (
                        <span className="bg-green-100 text-green-700 text-[9px] px-2 py-0.5 rounded-full font-bold uppercase flex items-center gap-1">
                          <Award size={10} /> Recommended
                        </span>
                      )}
                    </div>
                    {isSelected && <Check size={14} className="text-green-600" strokeWidth={3} />}
                  </div>
                  {plan.description && (
                    <span className="text-xs text-gray-500 line-clamp-1 italic">{plan.description}</span>
                  )}
                </button>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};

export default PremiumPlanDropdown;