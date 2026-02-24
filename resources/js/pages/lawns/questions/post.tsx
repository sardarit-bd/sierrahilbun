import React, { useState, useEffect } from 'react';
import { ArrowLeft, Check, ChevronDown, ChevronUp } from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head, router } from '@inertiajs/react';

// -------------------------------------------------------
// Progress Bar
// -------------------------------------------------------

const ProgressBar = ({ current, total }) => {
  const progress = ((current + 1) / total) * 100;
  return (
    <div className="w-full h-1.5 bg-gray-200 top-28 lg:top-44 left-0 z-40">
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
      <div
        className={`absolute z-20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
          ${option.recommended
            ? 'top-4 left-4 bg-yellow-400 text-yellow-900 rounded-sm shadow-sm'
            : 'top-0 left-0 bg-green-600 text-white rounded-br-lg'
          }`}
      >
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
      <img
        src={option.img}
        alt={option.label}
        className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
      />
      {!selected && (
        <div className="absolute inset-0 bg-black/5 group-hover:bg-transparent transition-colors" />
      )}
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

export default function Questionnaire({ zip_code, square_feet, questions }) {
  const [currentStep, setCurrentStep]       = useState(0);
  const [answers, setAnswers]               = useState({});
  const [showMoreOptions, setShowMoreOptions] = useState(false);
  const [isAnimating, setIsAnimating]       = useState(false);
  const [submitting, setSubmitting]         = useState(false);

  // Guard: nothing to render if questions didn't arrive
  if (!questions || questions.length === 0) {
    return null;
  }

  const currentQuestion = questions[currentStep];
  const currentAnswer   = answers[currentQuestion.id];
  const isLastStep      = currentStep === questions.length - 1;
  const canSubmit       = isLastStep && !!currentAnswer;

  // Primary options are those without isMore flag
  const primaryOptions = currentQuestion.options ?? [];
  // More options come from moreOptions key (set by QuestionResource)
  const extraOptions   = currentQuestion.moreOptions ?? [];
  const hasMore        = currentQuestion.hasMore ?? false;

  const visibleOptions = showMoreOptions
    ? [...primaryOptions, ...extraOptions]
    : primaryOptions;

  // Expanded card layout for any question that has a recommended option or is 'preference'
  const isExpandedLayout = currentQuestion.id === 'preference';

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, [currentStep]);

  const handleSelect = (optionId) => {
    const newAnswers = { ...answers, [currentQuestion.id]: optionId };
    setAnswers(newAnswers);

    // Auto-advance unless we're on the last step
    if (currentStep < questions.length - 1) {
      setTimeout(() => {
        setIsAnimating(true);
        setTimeout(() => {
          setCurrentStep((prev) => prev + 1);
          setIsAnimating(false);
          setShowMoreOptions(false);
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
        setShowMoreOptions(false);
      }, 300);
    }
  };

  const handleSubmit = () => {
    router.post(route('yard.quiz.store'), answers, {
      onStart:  () => setSubmitting(true),
      onFinish: () => setSubmitting(false),
    });
  };

  return (
    <AppHeaderLayout>
      <Head title="Lawn Questionnaire" />

      <ProgressBar current={currentStep} total={questions.length} />

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
          <h1
            className={`text-3xl sm:text-4xl font-extrabold text-gray-900 text-center leading-tight transition-all duration-300 transform
              ${isAnimating ? 'opacity-0 -translate-x-4' : 'opacity-100 translate-x-0'}`}
          >
            {currentQuestion.title}
          </h1>
        </div>

        {/* Options */}
        <div
          className={`w-full max-w-2xl grid gap-4 transition-all duration-300 transform
            ${isAnimating ? 'opacity-0 translate-y-4' : 'opacity-100 translate-y-0'}
            grid-cols-1`}
        >
          {visibleOptions.map((option) => (
            <OptionCard
              key={option.id}
              option={option}
              selected={currentAnswer === option.id}
              onSelect={handleSelect}
              expandedView={isExpandedLayout || !!option.recommended}
            />
          ))}
        </div>

        {/* See More / See Fewer toggle */}
        {hasMore && !showMoreOptions && (
          <button
            onClick={() => setShowMoreOptions(true)}
            className="mt-6 flex items-center gap-2 px-6 py-3 bg-white border border-green-600 text-green-700 font-bold rounded-full hover:bg-green-50 transition-all shadow-sm"
          >
            See more grass types <ChevronDown size={20} />
          </button>
        )}

        {hasMore && showMoreOptions && (
          <button
            onClick={() => setShowMoreOptions(false)}
            className="mt-8 text-green-700 font-bold flex items-center gap-1 hover:underline text-sm"
          >
            See fewer grass types <ChevronUp size={16} />
          </button>
        )}

        {/* Submit — only visible on last step once an answer is selected */}
        {canSubmit && (
          <div className="mt-8 w-full max-w-2xl">
            <button
              onClick={handleSubmit}
              disabled={submitting}
              className="w-full bg-green-700 hover:bg-green-800 disabled:opacity-50 text-white font-bold py-4 rounded-xl shadow-lg transition-all text-lg flex items-center justify-center gap-2"
            >
              {submitting ? 'Building your plan...' : 'Get my lawn plan'}
            </button>
          </div>
        )}

      </div>
    </AppHeaderLayout>
  );
}