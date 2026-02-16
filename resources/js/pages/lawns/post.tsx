import React from 'react';
import { 
  Check, 
  Mountain, 
  Sprout, 
  Thermometer, 
  Droplets, 
  MapPin, 
  Info,
  Wind,
  ThumbsUp,
  AlertTriangle
} from 'lucide-react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Head } from '@inertiajs/react';

// --- Helpers for Dynamic SVG ---

/**
 * Generates an SVG path string from an array of data points.
 * Maps values to a 100x50 coordinate system.
 */
const generateLinePath = (data) => {
  if (!data || data.length === 0) return "";
  
  const width = 100;
  const height = 50;
  const maxVal = Math.max(...data) || 100;
  const minVal = 0; // Assuming 0 baseline for charts like rain/growth
  const range = maxVal - minVal;

  const points = data.map((val, index) => {
    const x = (index / (data.length - 1)) * width;
    const y = height - ((val - minVal) / range) * height;
    return `${x},${y}`;
  });

  // Construct a path connecting all points
  // Using simple line segments (L) for robustness, but you could use curves (C/Q)
  return `M ${points.join(" L ")}`;
};

const MiniChart = ({ type, data }) => {
  // --- SOIL CHART (Dynamic Pie) ---
  if (type === 'soil') {
    const { silt, sand, clay } = data || { silt: 33, sand: 33, clay: 33 };
    const radius = 40;
    const circumference = 2 * Math.PI * radius; // ~251.3
    
    // Calculate stroke dash arrays based on percentages
    const siltStroke = (silt / 100) * circumference;
    const sandStroke = (sand / 100) * circumference;
    const clayStroke = (clay / 100) * circumference;

    // Calculate rotation offsets to stack segments
    // Silt starts at 0. Sand starts after Silt. Clay starts after Sand.
    const sandOffset = -siltStroke;
    const clayOffset = -(siltStroke + sandStroke);

    return (
      <div className="relative w-28 h-28 mx-auto mt-2">
         {/* Pie Chart SVG */}
         <svg viewBox="0 0 100 100" className="w-full h-full transform -rotate-90">
            {/* Silt (Red) */}
            <circle cx="50" cy="50" r={radius} fill="transparent" stroke="#E07A5F" strokeWidth="20" 
              strokeDasharray={`${siltStroke} ${circumference}`} /> 
            
            {/* Sand (Blue) */}
            <circle cx="50" cy="50" r={radius} fill="transparent" stroke="#264653" strokeWidth="20" 
              strokeDasharray={`${sandStroke} ${circumference}`} strokeDashoffset={sandOffset} />
            
            {/* Clay (Green) */}
            <circle cx="50" cy="50" r={radius} fill="transparent" stroke="#2A9D8F" strokeWidth="20" 
              strokeDasharray={`${clayStroke} ${circumference}`} strokeDashoffset={clayOffset} />
         </svg>
        
      </div>
    );
  }

  // --- LINE CHARTS (Dynamic Path) ---
  if (['growth', 'temp', 'rain'].includes(type)) {
    // Colors based on type
    const colors = {
      growth: '#4ADE80',
      temp: '#F4A261',
      rain: '#3DA9FC'
    };
    const color = colors[type];
    const pathData = generateLinePath(data);

    // Get circle position for the 'current' or specific month (e.g., month 5 / June)
    // For demo, we highlight the peak or middle. Let's pick index 5 (June).
    const highlightIndex = 5;
    const highlightVal = data[highlightIndex];
    const maxVal = Math.max(...data) || 100;
    const cx = (highlightIndex / (data.length - 1)) * 100;
    const cy = 50 - ((highlightVal / maxVal) * 50);

    return (
       <div className="w-full h-24 mt-4 relative">
          <svg viewBox="0 0 100 50" preserveAspectRatio="none" className="w-full h-full overflow-visible">
            <path d={pathData} fill="none" stroke={color} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            <circle cx={cx} cy={cy} r="3" fill={color} />
            {type === 'growth' && (
              <line x1="0" y1="50" x2="100" y2="50" stroke="#ccc" strokeWidth="1" strokeDasharray="4 2" />
            )}
          </svg>
          <div className="flex justify-between text-[8px] text-gray-400 mt-1">
             <span>Jan</span><span>Jun</span><span>Dec</span>
          </div>
       </div>
    );
  }
  return null;
};

const Slider = ({ label, value, labelLeft="low", labelRight="high" }) => (
  <div className="mb-6">
    <div className="flex items-center gap-2 mb-2">
      <span className="font-bold text-gray-800 text-sm">{label}</span>
      <Info size={12} className="text-gray-400" />
    </div>
    <div className="relative h-2 bg-gray-200 rounded-full w-full">
      <div className="absolute top-0 left-0 bottom-0 border-l border-gray-400 h-3 -mt-0.5"></div>
      <div className="absolute top-0 right-0 bottom-0 border-r border-gray-400 h-3 -mt-0.5"></div>
      <div className="absolute top-0 left-1/3 bottom-0 border-l border-dashed border-gray-400 h-full"></div>
      <div className="absolute top-0 right-1/3 bottom-0 border-r border-dashed border-gray-400 h-full"></div>
      
      {/* Marker */}
      <div 
        className="absolute top-1/2 -mt-3 w-0.5 h-6 bg-gray-600 z-10 transition-all duration-1000 ease-out"
        style={{ left: `${value}%` }}
      >
        <div className={`absolute -top-6 left-1/2 -translate-x-1/2 px-1.5 py-0.5 rounded text-[10px] font-bold text-gray-700 border border-gray-300 bg-[#fffde7] whitespace-nowrap`}>
           {value > 66 ? 'high' : value > 33 ? 'med' : 'low'}
        </div>
      </div>
    </div>
    <div className="flex justify-between text-xs text-gray-500 mt-2">
      <span>{labelLeft}</span>
      <span>{labelRight}</span>
    </div>
  </div>
);

const NutrientItem = ({ symbol, name, desc, status, isGood }) => (
  <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-gray-100 py-4 gap-4">
    <div className="flex items-start gap-4">
      <div className="w-10 h-10 bg-gray-100 rounded flex items-center justify-center font-bold text-gray-700 text-lg">
        {symbol}
      </div>
      <div>
        <h4 className="font-bold text-gray-900">{name}</h4>
        <p className="text-xs text-gray-500">{desc}</p>
      </div>
    </div>
    <div className={`px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 w-full sm:w-auto justify-center ${isGood ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}`}>
      {isGood ? <ThumbsUp size={14} /> : <AlertTriangle size={14} />}
      {status}
    </div>
  </div>
);

// --- Main Page Component ---

export default function ResultsPage() {
  
  // Example data that would come from your backend
  const mockBackendData = {
    summary: {
      grass: "cool-season",
      soilType: "silty, acidic",
      size: 3500
    },
    soilComposition: {
      silt: 69,
      sand: 17,
      clay: 14
    },
    // Arrays of 12 numbers (Jan-Dec)
    growthHistory: [0, 0, 10, 30, 80, 100, 80, 90, 100, 40, 5, 0],
    tempHistory: [30, 35, 45, 55, 65, 75, 80, 78, 70, 55, 45, 35],
    rainHistory: [3.5, 3.2, 4.0, 4.2, 4.8, 4.5, 5.0, 4.2, 3.8, 3.5, 3.2, 3.8],
    predictions: {
      organicMatter: 75, // percentage for slider (0-100 scale of range)
      phLevel: 25,       // percentage for slider
      nutrients: [
        { symbol: 'K', name: 'Potassium', desc: "Vital to grass' ability to endure stress", status: "Soil is likely sufficient", isGood: true },
        { symbol: 'P', name: 'Phosphorus', desc: "An energy source in plant metabolism", status: "Likely needs more", isGood: false },
      ]
    }
  };

  const data = mockBackendData;

  return (
    <AppHeaderLayout>
      <div className="container mx-auto px-4 lg:px-8 py-8 lg:py-12 max-w-7xl">
        <h1 className="text-4xl font-extrabold text-gray-900 mb-6 font-sans">
                What your zip code can tell us
              </h1>
        <div className="flex flex-col lg:flex-row gap-8 lg:gap-12 relative">
          
          {/* --- LEFT PANEL: Sticky Summary & CTA --- */}
          <div className="w-full lg:w-1/3 lg:flex-shrink-0">
            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 lg:sticky lg:top-24">
              
              
              <div className="space-y-6 mb-8">
                {/* Dynamic Summary Item 1 */}
                <div className="flex items-center gap-4 group cursor-help">
                   <div className="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 group-hover:bg-green-100 transition-colors">
                     <Sprout size={20} />
                   </div>
                   <span className="font-semibold text-gray-700">
                     {data.summary.grass} <span className="font-normal text-gray-500 block text-xs sm:text-sm">grass</span>
                   </span>
                </div>

                {/* Dynamic Summary Item 2 */}
                <div className="flex items-center gap-4 group cursor-help">
                   <div className="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 group-hover:bg-green-100 transition-colors">
                     <Wind size={20} />
                   </div>
                   <span className="font-semibold text-gray-700">
                     {data.summary.soilType} <span className="font-normal text-gray-500 block text-xs sm:text-sm">soil</span>
                   </span>
                </div>

                {/* Dynamic Summary Item 3 */}
                <div className="flex items-center gap-4 group cursor-help">
                   <div className="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 group-hover:bg-green-100 transition-colors">
                      <div className="w-4 h-4 border-2 border-green-600 rounded flex items-center justify-center">
                        <div className="w-2 h-2 bg-green-600 rounded-sm"></div>
                      </div>
                   </div>
                   <span className="font-semibold text-gray-700">
                     {data.summary.size.toLocaleString()} <span className="font-normal text-gray-500 block text-xs sm:text-sm">sq. ft</span>
                   </span>
                </div>
              </div>

              <div className="flex items-center gap-2 text-green-700 font-bold mb-6 animate-fade-in bg-green-50 p-3 rounded-lg justify-center">
                 <Check className="bg-green-200 text-green-800 rounded-full p-0.5" size={20} />
                 Plan ready!
              </div>

              <button className="w-full bg-[#2E7D32] text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                See custom lawn plan
              </button>
            </div>
          </div>

          {/* --- RIGHT PANEL: Details & Analysis --- */}
          <div className="w-full lg:w-2/3 space-y-12">
             
             {/* Section 1: Detailed Charts Grid */}
             <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {/* Card 1: Soil */}
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                   <div className="flex justify-between items-start mb-2">
                      <h3 className="font-bold text-sm text-gray-900 leading-tight">Regional soil profile</h3>
                      <div className="bg-orange-50 text-orange-700 p-1.5 rounded-md"><Mountain size={14}/></div>
                   </div>
                   <div className="text-[10px] space-y-1 text-gray-500 mb-2">
                      <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-[#E07A5F]"></span> Silt ({data.soilComposition.silt}%)</div>
                      <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-[#264653]"></span> Sand ({data.soilComposition.sand}%)</div>
                      <div className="flex items-center gap-1"><span className="w-2 h-2 rounded-full bg-[#2A9D8F]"></span> Clay ({data.soilComposition.clay}%)</div>
                   </div>
                   <MiniChart type="soil" data={data.soilComposition} />
                </div>

                {/* Card 2: Growth */}
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                   <div className="flex justify-between items-start mb-2">
                      <h3 className="font-bold text-sm text-gray-900">Growth potential</h3>
                      <div className="bg-green-50 text-green-700 p-1.5 rounded-md"><Sprout size={14}/></div>
                   </div>
                   <div className="flex items-center gap-1 text-[10px] font-bold text-gray-600 mb-2">
                     <div className="w-2 h-2 rounded-full bg-green-500"></div> historical
                   </div>
                   <MiniChart type="growth" data={data.growthHistory} />
                </div>

                {/* Card 3: Temps */}
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                   <div className="flex justify-between items-start mb-2">
                      <h3 className="font-bold text-sm text-gray-900">Temperature</h3>
                      <div className="bg-red-50 text-red-700 p-1.5 rounded-md"><Thermometer size={14}/></div>
                   </div>
                   <p className="text-[10px] text-gray-400 mb-2">Average of highs and lows</p>
                   <MiniChart type="temp" data={data.tempHistory} />
                </div>

                {/* Card 4: Rain */}
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                   <div className="flex justify-between items-start mb-2">
                      <h3 className="font-bold text-sm text-gray-900">Rainfall</h3>
                      <div className="bg-blue-50 text-blue-700 p-1.5 rounded-md"><Droplets size={14}/></div>
                   </div>
                   <div className="flex items-center gap-1 text-[10px] font-bold text-gray-600 mb-2">
                     <div className="w-2 h-2 rounded-full bg-[#3DA9FC]"></div> historical
                   </div>
                   <MiniChart type="rain" data={data.rainHistory} />
                </div>

             </div>

             {/* Section 2: Predicted Soil Analysis */}
             <div className="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100">
                <h2 className="text-2xl font-extrabold text-gray-900 mb-2">Predicted soil analysis</h2>
                <p className="text-gray-600 mb-8 max-w-2xl text-sm md:text-base">
                   Every new plan comes with a free lab soil analysis. While you're waiting for your results, we can predict your soil health.
                </p>

                <div className="bg-green-50 rounded-xl p-5 flex flex-col md:flex-row items-start gap-4 mb-10 border border-green-100">
                   <div className="bg-white p-3 rounded-full shadow-sm text-green-700 flex-shrink-0">
                      <MapPin size={24} />
                   </div>
                   <div>
                      <h4 className="font-bold text-gray-900 text-sm mb-1">What is a predicted soil analysis?</h4>
                      <p className="text-xs text-gray-600 leading-relaxed">
                         We provide a prediction of your soil profile based on results we have from your 15 closest neighbors. We have data from over 100,000 lawns across the country. 
                         <span className="font-bold block mt-1 text-green-800"> The results below are estimates, your actual soil analysis may vary.</span>
                      </p>
                   </div>
                </div>

                {/* Subsection: Properties */}
                <div className="mb-12">
                   <h3 className="text-lg font-bold text-gray-900 border-b-2 border-gray-200 inline-block pb-1 mb-6">
                      Projected soil properties
                   </h3>
                   <div className="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-16">
                      <Slider label="Organic matter" value={data.predictions.organicMatter} />
                      <Slider label="Soil pH" value={data.predictions.phLevel} />
                   </div>
                   <div className="flex items-start gap-2 mt-[-1rem]">
                      <Info size={14} className="text-gray-400 mt-0.5 flex-shrink-0"/>
                      <p className="text-xs text-gray-500">Low pH can impact nutrient availability; might need to add lime to help raise it.</p>
                   </div>
                </div>

                {/* Subsection: Nutrients */}
                <div>
                   <h3 className="text-lg font-bold text-gray-900 border-b-2 border-gray-200 inline-block pb-1 mb-6">
                      Projected nutrient levels
                   </h3>
                   <div className="space-y-2">
                      {data.predictions.nutrients.map((item, idx) => (
                        <NutrientItem 
                           key={idx}
                           symbol={item.symbol} 
                           name={item.name} 
                           desc={item.desc} 
                           status={item.status} 
                           isGood={item.isGood} 
                        />
                      ))}
                   </div>
                </div>
             </div>
          </div>
        </div>
      </div>
    </AppHeaderLayout>
  );
}