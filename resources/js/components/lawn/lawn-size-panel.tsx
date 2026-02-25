import { ArrowRight, Pencil, RotateCcw, CheckCircle2, AlertTriangle, Info } from 'lucide-react';

// ─── Source badge ─────────────────────────────────────────────────────────────

const SourceBadge = ({ sourceLabel }) => {
    const config = {
        drawn     : { label: '✓ Drawn by you',   cls: 'bg-green-100 text-green-700 border-green-200' },
        calculated: { label: '⚡ Auto-calculated', cls: 'bg-blue-100 text-blue-700 border-blue-200'  },
        manual    : { label: '✎ Manual entry',    cls: 'bg-gray-100 text-gray-600 border-gray-200'   },
        unknown   : { label: '~ Estimated',        cls: 'bg-amber-100 text-amber-700 border-amber-200'},
    };

    const { label, cls } = config[sourceLabel] ?? config.unknown;

    return (
        <span className={`inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-bold tracking-wide ${cls}`}>
            {label}
        </span>
    );
};

// ─── Banner ───────────────────────────────────────────────────────────────────

const Banner = ({ type, children }) => {
    const styles = {
        info : { bg: 'bg-blue-50  border-blue-200',  icon: Info,          iconCls: 'text-blue-500'  },
        warn : { bg: 'bg-amber-50 border-amber-200', icon: AlertTriangle, iconCls: 'text-amber-500' },
        success:{ bg: 'bg-green-50 border-green-200', icon: CheckCircle2, iconCls: 'text-green-600' },
    };

    const { bg, icon: Icon, iconCls } = styles[type] ?? styles.info;

    return (
        <div className={`flex items-start gap-3 border rounded-xl p-4 ${bg}`}>
            <Icon size={18} className={`flex-shrink-0 mt-0.5 ${iconCls}`} strokeWidth={2} />
            <div className="text-sm leading-relaxed">{children}</div>
        </div>
    );
};

// ─── Main Component ───────────────────────────────────────────────────────────

/**
 * LawnSizePanel
 *
 * Left-hand panel on the ConfirmArea view.
 * Shows auto-calculated sq ft, source badge, banners and confirm/back actions.
 *
 * @param {number}   activeSqft       Current active square footage
 * @param {string}   sourceLabel      'drawn' | 'calculated' | 'manual' | 'unknown'
 * @param {boolean}  estimated        Whether the value is an estimate
 * @param {boolean}  isDrawn          Whether user has drawn a polygon
 * @param {string}   matchedAddress   Normalized address from geocoder
 * @param {string}   confidence       Geocode confidence: 'high' | 'medium' | 'low'
 * @param {string}   source           Backend source: 'calculated' | 'lot_only' | 'default_estimate'
 * @param {boolean}  processing       Form submitting
 * @param {Function} onManualInput    (value: string) => void
 * @param {Function} onConfirm        () => void
 * @param {Function} onBack           () => void
 * @param {Function} onClearDrawn     () => void
 */
export default function LawnSizePanel({
    activeSqft,
    sourceLabel,
    estimated,
    isDrawn,
    matchedAddress,
    confidence,
    source,
    processing,
    onManualInput,
    onConfirm,
    onBack,
    onClearDrawn,
}) {
    const showEstimateWarning = estimated && !isDrawn;
    const showCalculatedSuccess = !estimated && source === 'calculated' && !isDrawn;
    const showMediumConfidenceWarning = confidence === 'medium' && !isDrawn;

    return (
        <div className="flex flex-col h-full overflow-y-auto">
            <div className="p-8 md:p-10 flex flex-col gap-6 md:justify-center md:min-h-full">

                {/* Header */}
                <div>
                    <p className="text-xs font-bold tracking-widest text-gray-400 uppercase mb-2">
                        Step 2 of 3
                    </p>
                    <h1 className="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight">
                        Confirm your lawn area
                    </h1>
                    {matchedAddress && (
                        <p className="text-sm text-gray-500 mt-1 truncate" title={matchedAddress}>
                            📍 {matchedAddress}
                        </p>
                    )}
                </div>

                {/* Banners */}
                <div className="flex flex-col gap-3">
                    {/* Always show draw instruction */}
                    <Banner type="info">
                        <span className="font-semibold text-blue-900">Use the polygon tool</span>
                        <span className="text-blue-800"> (top-right of map) to trace only the grass area — exclude your house, driveway, and paths.</span>
                    </Banner>

                    {showCalculatedSuccess && (
                        <Banner type="success">
                            <span className="font-semibold text-green-900">We auto-detected your lawn boundary.</span>
                            <span className="text-green-800"> Review the green outline on the map and redraw if needed.</span>
                        </Banner>
                    )}

                    {showMediumConfidenceWarning && (
                        <Banner type="warn">
                            <span className="font-semibold text-amber-900">Address matched with medium confidence.</span>
                            <span className="text-amber-800"> Please verify the map is centered on the right property before confirming.</span>
                        </Banner>
                    )}

                    {showEstimateWarning && (
                        <Banner type="warn">
                            <div>
                                <p className="font-semibold text-amber-900">
                                    We couldn't auto-detect your exact boundary.
                                </p>
                                {matchedAddress && (
                                    <p className="text-amber-800 mt-0.5 text-xs">
                                        Nearest match: <span className="font-semibold">{matchedAddress}</span>
                                    </p>
                                )}
                                <p className="text-amber-800 mt-1">
                                    Please draw your lawn on the map for accurate results.
                                </p>
                            </div>
                        </Banner>
                    )}
                </div>

                {/* Square footage display */}
                <div className="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                    <div className="flex items-center justify-between mb-1">
                        <p className="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            Lawn size
                        </p>
                        <SourceBadge sourceLabel={sourceLabel} />
                    </div>

                    <div className="flex items-end gap-2 mt-2">
                        <span className="text-5xl font-black text-gray-900 tracking-tight leading-none">
                            {Number(activeSqft).toLocaleString()}
                        </span>
                        <span className="text-xl text-gray-400 font-semibold mb-1">sq. ft</span>
                    </div>

                    {isDrawn && (
                        <button
                            onClick={onClearDrawn}
                            className="mt-3 flex items-center gap-1.5 text-xs text-gray-500 hover:text-red-500 transition-colors"
                        >
                            <RotateCcw size={12} />
                            Reset to auto-detected
                        </button>
                    )}
                </div>

                {/* Manual override */}
                <div>
                    <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        <Pencil size={11} className="inline mr-1" />
                        Or enter manually
                    </label>
                    <div className="relative">
                        <input
                            type="number"
                            placeholder="e.g. 4500"
                            onChange={(e) => onManualInput(e.target.value)}
                            className="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-lg font-semibold text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all bg-white placeholder-gray-300"
                        />
                        <span className="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">
                            SQ FT
                        </span>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex flex-col gap-3 pt-1">
                    <button
                        onClick={onConfirm}
                        disabled={processing || !activeSqft}
                        className="w-full bg-[#2E7D32] hover:bg-[#256628] active:bg-[#1b4d20] text-white font-bold py-4 rounded-xl shadow-lg transition-all text-base flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        {processing ? (
                            <>
                                <span className="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                                Saving...
                            </>
                        ) : (
                            <>
                                Confirm & Continue
                                <ArrowRight size={18} />
                            </>
                        )}
                    </button>

                    <button
                        onClick={onBack}
                        className="w-full bg-white border-2 border-gray-200 text-gray-600 font-semibold py-3.5 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all text-base"
                    >
                        ← Something's not right
                    </button>
                </div>

            </div>
        </div>
    );
}