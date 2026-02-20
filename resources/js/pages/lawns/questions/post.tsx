import React, { useState, useEffect } from 'react';
import { ArrowLeft, Check, ChevronDown, ChevronUp } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, useForm } from '@inertiajs/react';

// -------------------------------------------------------
// Questions Definition
// -------------------------------------------------------

const QUESTIONS = [
  {
    id: 'goals',
    title: "What's important to you?",
    options: [
      { id: 'looks',  label: "A great-looking lawn",           img: "https://images.unsplash.com/photo-1558904541-efa843a96f01?auto=format&fit=crop&q=80&w=400" },
      { id: 'health', label: "Healthy soil and healthy grass",  img: "https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&q=80&w=400" },
      { id: 'safety', label: "Safe for people, pets, and nature", img: "https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&q=80&w=400" },
      { id: 'all',    label: "All of the above!",               img: "https://images.unsplash.com/photo-1622383563227-044011358d16?auto=format&fit=crop&q=80&w=400" },
    ],
  },
  {
    id: 'pets',
    title: "How often are pets on your lawn?",
    options: [
      { id: 'lot',      label: "A lot",     img: "https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&q=80&w=400" },
      { id: 'not_much', label: "Not much",  img: "https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&q=80&w=400" },
    ],
  },
  {
    id: 'knowledge',
    title: "Rate your lawn care knowledge.",
    options: [
      { id: 'expert',   label: "Expert",   img: "https://images.unsplash.com/photo-1615423612887-b089c894235e?auto=format&fit=crop&q=80&w=400" },
      { id: 'hobbyist', label: "Hobbyist", img: "https://images.unsplash.com/photo-1592424037340-076a081a2dcc?auto=format&fit=crop&q=80&w=400" },
      { id: 'amateur',  label: "Amateur",  img: "https://images.unsplash.com/photo-1560518883-ce09059ee971?auto=format&fit=crop&q=80&w=400" },
      { id: 'rookie',   label: "Rookie",   img: "https://images.unsplash.com/photo-1595116489814-c8c368689b0d?auto=format&fit=crop&q=80&w=400" },
    ],
  },
  {
    id: 'grass',
    title: "Confirm your grass type.",
    hasMore: true,
    options: [
      { id: 'bermuda',   label: "Bermudagrass",   img: "https://images.unsplash.com/photo-1584444453896-1d374421b88e?auto=format&fit=crop&q=80&w=400" },
      { id: 'centipede', label: "Centipedegrass", img: "https://images.unsplash.com/photo-1576041695662-38d7d63ce3c7?auto=format&fit=crop&q=80&w=400" },
      { id: 'augustine', label: "St. Augustine",  img: "https://images.unsplash.com/photo-1589803158022-83505c21f95d?auto=format&fit=crop&q=80&w=400" },
      { id: 'other',     label: "Other/Not sure", img: "https://images.unsplash.com/photo-1516981879613-9f5da904015f?auto=format&fit=crop&q=80&w=400" },
    ],
    moreOptions: [
      { id: 'zoysia',    label: "Zoysiagrass",        img: "https://images.unsplash.com/photo-1590579491624-f98f36d4c763?auto=format&fit=crop&q=80&w=400" },
      { id: 'fescue',    label: "Fescue",             img: "https://images.unsplash.com/photo-1533460004989-acf6be3fab53?auto=format&fit=crop&q=80&w=400" },
      { id: 'bluegrass', label: "Kentucky Bluegrass", img: "https://images.unsplash.com/photo-1605112525264-165f12e84c98?auto=format&fit=crop&q=80&w=400" },
    ],
  },
  {
    id: 'patches',
    title: "Does your lawn have any bare patches?",
    options: [
      { id: 'none',     label: "Nope, none",      img: "https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&q=80&w=400" },
      { id: 'few',      label: "A few",           img: "https://images.unsplash.com/photo-1600355171739-16e6d1c9533a?auto=format&fit=crop&q=80&w=400" },
      { id: 'moderate', label: "Moderate",        img: "https://images.unsplash.com/photo-1589400266396-e8d19798488e?auto=format&fit=crop&q=80&w=400" },
      { id: 'lots',     label: "Lots of patches", img: "https://images.unsplash.com/photo-1599940778173-e276d4acb2be?auto=format&fit=crop&q=80&w=400" },
    ],
  },
  {
    id: 'weeds',
    title: "What's your weed situation?",
    options: [
      { id: 'none',       label: "Weeds aren't an issue",              img: "https://images.unsplash.com/photo-1598902598372-9694e803a55e?auto=format&fit=crop&q=80&w=400" },
      { id: 'leafy',      label: "Leafy lawn weeds",                   img: "https://images.unsplash.com/photo-1530968033775-2c92733b0c41?auto=format&fit=crop&q=80&w=400" },
      { id: 'stubborn',   label: "Patio, garden, and stubborn weeds",  img: "https://images.unsplash.com/photo-1597843786271-105124152c92?auto=format&fit=crop&q=80&w=400" },
      { id: 'everywhere', label: "Weeds are everywhere",               img: "https://images.unsplash.com/photo-1557053503-0c252e5c8093?auto=format&fit=crop&q=80&w=400" },
      { id: 'pre',        label: "I want a pre-emergent",              img: "https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&q=80&w=400" },
    ],
  },
  {
    id: 'care',
    title: "How do you care for your lawn?",
    options: [
      { id: 'service',   label: "A lawn service does it all",          img: "https://images.unsplash.com/photo-1621981298583-1eb954002c91?auto=format&fit=crop&q=80&w=400" },
      { id: 'fert_high', label: "I fertilize three to five times a year", tag: "3-5x", img: "https://images.unsplash.com/photo-1615423612887-b089c894235e?auto=format&fit=crop&q=80&w=400" },
      { id: 'fert_low',  label: "I fertilize once or twice a year",    tag: "1-2x", img: "https://images.unsplash.com/photo-1530836369250-ef7208b5c300?auto=format&fit=crop&q=80&w=400" },
      { id: 'mow',       label: "I just mow it",                       img: "https://images.unsplash.com/photo-1590682680695-43b964a3ae17?auto=format&fit=crop&q=80&w=400" },
    ],
  },
  {
    id: 'preference',
    title: "What application is easier for you?",
    options: [
      {
        id: 'liquid',
        label: "Liquid hose-on fertilizer",
        recommended: true,
        desc: "You'll love our innovative liquid fertilizers for fastest results with easiest precision. All you need is a hose and about 10 minutes per application.",
        img: "https://images.unsplash.com/photo-1622383563227-044011358d16?auto=format&fit=crop&q=80&w=400",
      },
      {
        id: 'granular',
        label: "Dry granular fertilizer",
        img: "https://images.unsplash.com/photo-1611735341450-74d61e66ee69?auto=format&fit=crop&q=80&w=400",
      },
    ],
  },
];

// -------------------------------------------------------
// Progress Bar
// -------------------------------------------------------

const ProgressBar = ({ current, total }) => {
  const progress = ((current + 1) / total) * 100;
  return (
    <div className="w-full h-1.5 bg-gray-200 fixed top-28 lg:top-44 left-0 z-40">
      <div
        className="h-full bg-green-600 transition-all duration-500 ease-out"
        style={{ width: `${progress}%` }}
      />
    </div>
  );
};

// -------------------------------------------------------
// Option Card
// -------------------------------------------------------

const OptionCard = ({ option, selected, onSelect, expandedView = false }) => (
  <div
    onClick={() => onSelect(option.id)}
    className={`
      group relative cursor-pointer bg-white rounded-xl overflow-hidden border-2 transition-all duration-200 ease-in-out
      ${selected ? 'border-green-600 shadow-lg ring-1 ring-green-600 transform scale-[1.01]' : 'border-gray-200 hover:border-green-300 hover:shadow-md'}
      ${expandedView ? 'flex-col' : 'flex items-center p-2 sm:p-3'}
      w-full
    `}
  >
    {(option.tag || option.recommended) && (
      <div className={`absolute top-0 left-0 z-20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ${option.recommended ? 'bg-yellow-400 text-yellow-900 top-4 left-4 rounded-sm shadow-sm' : 'bg-green-600 text-white rounded-br-lg'}`}>
        {option.recommended ? 'Recommended for your lawn' : option.tag}
      </div>
    )}

    {selected && (
      <div className={`absolute z-20 ${expandedView ? 'bottom-4 left-4' : 'left-4 top-1/2 -translate-y-1/2'}`}>
        <div className="rounded-full flex items-center justify-center shadow-sm bg-green-600 w-8 h-8">
          <Check size={18} className="text-white" strokeWidth={3} />
        </div>
      </div>
    )}

    <div className={`relative overflow-hidden bg-gray-100 flex-shrink-0 ${expandedView ? 'w-full h-48 sm:h-56' : 'w-24 h-24 rounded-lg'}`}>
      <img src={option.img} alt={option.label} className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
      {!selected && <div className="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors" />}
    </div>

    <div className={`flex-grow flex flex-col justify-center ${expandedView ? 'p-6 pt-4' : 'px-4 py-2 pl-12'}`}>
      <h3 className={`font-bold text-gray-900 ${expandedView ? 'text-xl mb-2' : 'text-base sm:text-lg'}`}>
        {option.label}
      </h3>
      {option.desc && (
        <p className="text-sm text-gray-600 leading-relaxed mt-1">{option.desc}</p>
      )}
    </div>
  </div>
);

// -------------------------------------------------------
// Main Page
// -------------------------------------------------------

export default function Questionnaire({ zip_code, square_feet }) {
  const [currentStep, setCurrentStep]   = useState(0);
  const [answers, setAnswers]           = useState({});
  const [showMoreGrass, setShowMoreGrass] = useState(false);
  const [isAnimating, setIsAnimating]   = useState(false);

  const { post, processing } = useForm({});

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, [currentStep]);

  const currentQuestion = QUESTIONS[currentStep];
  const currentAnswer   = answers[currentQuestion.id];
  const isLastStep      = currentStep === QUESTIONS.length - 1;
  const canSubmit       = isLastStep && currentAnswer;

  const visibleOptions = showMoreGrass
    ? [...currentQuestion.options, ...(currentQuestion.moreOptions || [])]
    : currentQuestion.options;

  const isExpandedLayout = currentQuestion.id === 'preference';

  const handleSelect = (optionId) => {
    const newAnswers = { ...answers, [currentQuestion.id]: optionId };
    setAnswers(newAnswers);

    if (currentStep < QUESTIONS.length - 1) {
      setTimeout(() => {
        setIsAnimating(true);
        setTimeout(() => {
          setCurrentStep((prev) => prev + 1);
          setIsAnimating(false);
          setShowMoreGrass(false);
        }, 300);
      }, 400);
    }
  };

  const handleBack = () => {
    if (currentStep > 0) {
      setIsAnimating(true);
      setTimeout(() => {
        setCurrentStep((prev) => prev - 1);
        setIsAnimating(false);
      }, 300);
    }
  };

  const handleSubmit = () => {
    post(route('yard.quiz.store'), {
      data: answers,
    });
  };

  return (
    <AppHeaderLayout>
      <Head title="Lawn Questionnaire" />

      <ProgressBar current={currentStep} total={QUESTIONS.length} />

      <div className="flex-grow flex flex-col items-center justify-start pt-12 pb-20 px-4 sm:px-6">

        {/* Header */}
        <div className="w-full max-w-2xl relative mb-8">
          {currentStep > 0 && (
            <button
              onClick={handleBack}
              className="absolute -top-10 left-0 text-green-700 font-bold flex items-center gap-1 text-sm hover:underline"
            >
              <ArrowLeft size={16} /> Back
            </button>
          )}
          <h1 className={`text-3xl sm:text-4xl font-extrabold text-gray-900 text-center leading-tight transition-all duration-300 transform ${isAnimating ? 'opacity-0 -translate-x-4' : 'opacity-100 translate-x-0'}`}>
            {currentQuestion.title}
          </h1>
        </div>

        {/* Options */}
        <div className={`w-full max-w-2xl grid gap-4 transition-all duration-300 transform ${isAnimating ? 'opacity-0 translate-y-4' : 'opacity-100 translate-y-0'} grid-cols-1`}>
          {visibleOptions.map((option) => (
            <OptionCard
              key={option.id}
              option={option}
              selected={currentAnswer === option.id}
              onSelect={handleSelect}
              expandedView={isExpandedLayout || option.recommended}
            />
          ))}
        </div>

        {/* More Grass Types Toggle */}
        {currentQuestion.hasMore && !showMoreGrass && (
          <button
            onClick={() => setShowMoreGrass(true)}
            className="mt-6 flex items-center gap-2 px-6 py-3 bg-white border border-green-600 text-green-700 font-bold rounded-full hover:bg-green-50 transition-all shadow-sm"
          >
            See more grass types <ChevronDown size={20} />
          </button>
        )}

        {currentQuestion.hasMore && showMoreGrass && (
          <button
            onClick={() => setShowMoreGrass(false)}
            className="mt-8 text-green-700 font-bold flex items-center gap-1 hover:underline text-sm"
          >
            See fewer grass types <ChevronUp size={16} />
          </button>
        )}

        {/* Submit */}
        {canSubmit && (
          <div className="mt-8 w-full max-w-2xl">
            <button
              onClick={handleSubmit}
              disabled={processing}
              className="w-full bg-green-700 hover:bg-green-800 disabled:opacity-50 text-white font-bold py-4 rounded-xl shadow-lg transition-all text-lg flex items-center justify-center gap-2"
            >
              {processing ? 'Building your plan...' : 'Get my lawn plan'}
            </button>
          </div>
        )}

      </div>
    </AppHeaderLayout>
  );
}