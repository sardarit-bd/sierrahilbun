import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { Check, Flower, X } from 'lucide-react';
import { GARDEN_TYPES, GARDEN_SIZES } from './helpers';

const GardenQuizModal = ({ isOpen, onClose }) => {
  const [step, setStep]                 = useState(1);
  const [gardenTypes, setGardenTypes]   = useState([]);
  const [gardenSize, setGardenSize]     = useState(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors]             = useState({});

  useEffect(() => {
    if (isOpen) {
      setStep(1);
      setGardenTypes([]);
      setGardenSize(null);
      setErrors({});
      setIsSubmitting(false);
    }
  }, [isOpen]);

  useEffect(() => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [isOpen]);

  if (!isOpen) return null;

  const toggleType = (value) => {
    setGardenTypes(prev => prev.includes(value) ? prev.filter(t => t !== value) : [...prev, value]);
    setErrors(prev => ({ ...prev, garden_types: null }));
  };

  const handleNext = () => {
    if (gardenTypes.length === 0) {
      setErrors({ garden_types: 'Please select at least one garden type.' });
      return;
    }
    setStep(2);
  };

  const handleSubmit = () => {
    if (!gardenSize) {
      setErrors({ garden_size: 'Please select a garden size.' });
      return;
    }
    setIsSubmitting(true);
    router.post(
      route('yard.garden-quiz'),
      { garden_types: gardenTypes, garden_size: gardenSize },
      {
        preserveScroll: true,
        onSuccess: () => onClose(),
        onError:   (errs) => { setErrors(errs); setIsSubmitting(false); },
        onFinish:  () => setIsSubmitting(false),
      },
    );
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div className="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">

        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 bg-orange-100 rounded-full flex items-center justify-center text-orange-700">
              <Flower size={20} />
            </div>
            <div>
              <h3 className="font-bold text-gray-900 text-base leading-none">Garden Care</h3>
              <p className="text-xs text-gray-400 mt-0.5">Step {step} of 2</p>
            </div>
          </div>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 transition-colors">
            <X size={20} />
          </button>
        </div>

        {/* Progress */}
        <div className="flex gap-1 px-6 pt-4">
          <div className="h-1 flex-1 rounded-full bg-green-600" />
          <div className={`h-1 flex-1 rounded-full transition-colors duration-300 ${step === 2 ? 'bg-green-600' : 'bg-gray-200'}`} />
        </div>

        {/* Body */}
        <div className="px-6 py-5">
          {step === 1 && (
            <div>
              <h4 className="font-bold text-gray-900 text-base mb-1">What do you grow?</h4>
              <p className="text-sm text-gray-500 mb-4">Select all that apply.</p>
              <div className="space-y-2">
                {GARDEN_TYPES.map((type) => {
                  const isSelected = gardenTypes.includes(type.value);
                  return (
                    <button
                      key={type.value}
                      onClick={() => toggleType(type.value)}
                      className={`w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 transition-all duration-150 text-left ${
                        isSelected
                          ? 'border-green-600 bg-green-50 text-green-900'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      }`}
                    >
                      <span className="font-semibold text-sm">{type.label}</span>
                      {isSelected && <Check size={16} className="text-green-600" strokeWidth={3} />}
                    </button>
                  );
                })}
              </div>
              {errors.garden_types && <p className="text-xs text-red-600 mt-2">{errors.garden_types}</p>}
            </div>
          )}

          {step === 2 && (
            <div>
              <h4 className="font-bold text-gray-900 text-base mb-1">How large is your garden?</h4>
              <p className="text-sm text-gray-500 mb-4">We'll use this to calculate your product quantities.</p>
              <div className="space-y-2">
                {GARDEN_SIZES.map((size) => {
                  const isSelected = gardenSize === size.value;
                  return (
                    <button
                      key={size.value}
                      onClick={() => { setGardenSize(size.value); setErrors(prev => ({ ...prev, garden_size: null })); }}
                      className={`w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 transition-all duration-150 text-left ${
                        isSelected
                          ? 'border-green-600 bg-green-50 text-green-900'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                      }`}
                    >
                      <div>
                        <span className="font-bold text-sm">{size.label}</span>
                        <span className="text-xs text-gray-500 ml-2">{size.description}</span>
                      </div>
                      {isSelected && <Check size={16} className="text-green-600" strokeWidth={3} />}
                    </button>
                  );
                })}
              </div>
              {errors.garden_size && <p className="text-xs text-red-600 mt-2">{errors.garden_size}</p>}
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="px-6 pb-6 flex items-center justify-between gap-3">
          {step === 2
            ? <button onClick={() => setStep(1)} className="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">Back</button>
            : <div />
          }
          {step === 1 && (
            <button onClick={handleNext} className="ml-auto bg-green-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-green-800 transition-colors shadow-sm">
              Next
            </button>
          )}
          {step === 2 && (
            <button
              onClick={handleSubmit}
              disabled={isSubmitting}
              className="ml-auto bg-green-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-green-800 transition-colors shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {isSubmitting ? 'Saving…' : 'Add to plan'}
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default GardenQuizModal;