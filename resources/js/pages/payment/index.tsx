import { useState, useEffect } from "react";
import { router, usePage } from "@inertiajs/react";

const CARD_BRANDS = {
    visa: (
        <svg viewBox="0 0 48 48" className="w-10 h-7">
            <rect width="48" height="48" rx="6" fill="#1A1F71" />
            <text x="50%" y="58%" dominantBaseline="middle" textAnchor="middle" fill="#FFFFFF" fontSize="14" fontWeight="bold" fontFamily="Arial">VISA</text>
        </svg>
    ),
    mastercard: (
        <svg viewBox="0 0 48 48" className="w-10 h-7">
            <rect width="48" height="48" rx="6" fill="#252525" />
            <circle cx="18" cy="24" r="10" fill="#EB001B" />
            <circle cx="30" cy="24" r="10" fill="#F79E1B" />
            <path d="M24 16.5a10 10 0 0 1 0 15 10 10 0 0 1 0-15z" fill="#FF5F00" />
        </svg>
    ),
};

function CardIcon({ number }) {
    if (number.startsWith("4")) return CARD_BRANDS.visa;
    if (number.startsWith("5") || number.startsWith("2")) return CARD_BRANDS.mastercard;
    return (
        <svg viewBox="0 0 48 48" className="w-10 h-7">
            <rect width="48" height="48" rx="6" fill="#2a2a3e" stroke="#3d3d5c" strokeWidth="1" />
            <rect x="6" y="16" width="36" height="6" rx="1" fill="#3d3d5c" />
            <rect x="6" y="28" width="12" height="4" rx="1" fill="#3d3d5c" />
            <rect x="22" y="28" width="8" height="4" rx="1" fill="#3d3d5c" />
        </svg>
    );
}

function formatCardNumber(val) {
    return val.replace(/\D/g, "").slice(0, 16).replace(/(.{4})/g, "$1 ").trim();
}
function formatExpiry(val) {
    const digits = val.replace(/\D/g, "").slice(0, 4);
    if (digits.length >= 3) return digits.slice(0, 2) + "/" + digits.slice(2);
    return digits;
}

export default function PaymentIndex() {
    const { errors, flash } = usePage().props;

    const [form, setForm] = useState({
        gateway: "stripe",
        amount: "",
        currency: "USD",
        payment_method_id: "pm_card_visa",
        description: "",
        cardNumber: "",
        expiry: "",
        cvv: "",
        cardHolder: "",
    });

    const [focused, setFocused] = useState(null);
    const [isFlipped, setIsFlipped] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [step, setStep] = useState(1);
    const [ripple, setRipple] = useState(null);

    useEffect(() => {
        setIsFlipped(focused === "cvv");
    }, [focused]);

    const handleChange = (field) => (e) => {
        let val = e.target.value;
        if (field === "cardNumber") val = formatCardNumber(val);
        if (field === "expiry") val = formatExpiry(val);
        if (field === "cvv") val = val.replace(/\D/g, "").slice(0, 4);
        if (field === "amount") val = val.replace(/[^\d.]/g, "");
        setForm((p) => ({ ...p, [field]: val }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const btn = e.nativeEvent.submitter;
        if (btn) {
            const rect = btn.getBoundingClientRect();
            setRipple({ x: rect.width / 2, y: rect.height / 2 });
            setTimeout(() => setRipple(null), 600);
        }
        setSubmitting(true);
        router.post(route("payment.charge"), {
            gateway: form.gateway,
            amount: form.amount,
            currency: form.currency,
            payment_method_id: form.payment_method_id,
            description: form.description,
        }, {
            onFinish: () => setSubmitting(false),
        });
    };

    const maskedNumber = form.cardNumber
        ? form.cardNumber.padEnd(19, "·").split("").map((c, i) => {
            if ([4, 9, 14].includes(i)) return " ";
            if (i < 14 && c !== " " && c !== "·") return "•";
            return c;
        }).join("")
        : "•••• •••• •••• ••••";

    const displayExpiry = form.expiry || "MM/YY";
    const displayCVV = form.cvv ? "•".repeat(form.cvv.length) : "•••";
    const displayHolder = form.cardHolder || "FULL NAME";

    return (
        <div className="min-h-screen bg-[#F9FAFB] flex items-center justify-center p-4 overflow-hidden">
            {/* Ambient background effects */}
            <div className="fixed inset-0 pointer-events-none">
                <div className="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-[#c9a84c] opacity-[0.04] blur-[120px]" />
                <div className="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] rounded-full bg-[#7b5ea7] opacity-[0.06] blur-[100px]" />
                <div className="absolute top-[40%] left-[40%] w-[300px] h-[300px] rounded-full bg-[#1a3a6e] opacity-[0.08] blur-[80px]" />
                {/* Grid lines */}
                <div className="absolute inset-0 opacity-[0.025]"
                    style={{ backgroundImage: "linear-gradient(#c9a84c 1px, transparent 1px), linear-gradient(90deg, #c9a84c 1px, transparent 1px)", backgroundSize: "60px 60px" }} />
            </div>

            <div className="relative w-full max-w-[460px] z-10">
                {/* Logo / Header */}
                <div className="text-center mb-8 animate-[fadeDown_0.6s_ease_forwards]">
                    <div className="inline-flex items-center gap-2 mb-3">
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-[#c9a84c] to-[#a07830] flex items-center justify-center shadow-[0_0_20px_rgba(201,168,76,0.3)]">
                            <svg className="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <span className="text-[#c9a84c] font-semibold tracking-[0.2em] text-md uppercase"
                            style={{ fontFamily: "'Cormorant Garamond', Georgia, serif" }}>
                            Secure Checkout
                        </span>
                    </div>
                    <h1 className="text-gray-900 text-3xl font-bold tracking-tight"
                        style={{ fontFamily: "'Cormorant Garamond', Georgia, serif" }}>
                        Complete Your Payment
                    </h1>
                    {/* <p className="text-gray-900 text-sm mt-1 tracking-wide">256-bit encrypted · PCI compliant</p> */}
                </div>

                {/* Error Flash */}
                {flash?.error && (
                    <div className="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3">
                        <svg className="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {flash.error}
                    </div>
                )}

                {/* Main Card */}
                <div className="rounded-2xl overflow-hidden shadow-[0_40px_80px_rgba(0,0,0,0.6)] border border-white/[0.06]"
                    style={{ background: "linear-gradient(145deg, #2842c6 0%, #8a8a92 100%)" }}>

                    {/* 3D Credit Card Preview */}
                    <div className="px-8 pt-8 pb-6 text-gray-50">
                        <div className="relative h-[200px] perspective-[1200px]" style={{ perspective: "1200px" }}>
                            <div
                                className="w-full h-full relative transition-all duration-700"
                                style={{
                                    transformStyle: "preserve-3d",
                                    transform: isFlipped ? "rotateY(180deg)" : "rotateY(0deg)",
                                }}
                            >
                                {/* Card Front */}
                                <div className="absolute inset-0 rounded-2xl overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.5)]"
                                    style={{ backfaceVisibility: "hidden", background: "linear-gradient(135deg, #1a1a35 0%, #0d0d22 40%, #1a2540 100%)" }}>
                                    {/* Card shimmer */}
                                    <div className="absolute inset-0 opacity-30"
                                        style={{ backgroundImage: "radial-gradient(ellipse at 30% 30%, rgba(201,168,76,0.2) 0%, transparent 60%)" }} />
                                    {/* Chip */}
                                    <div className="absolute top-6 left-6">
                                        <div className="w-10 h-8 rounded bg-gradient-to-br from-[#d4a843] to-[#a07830] relative overflow-hidden shadow-sm">
                                            <div className="absolute inset-0 grid grid-cols-3 grid-rows-3 gap-px p-1">
                                                {[...Array(9)].map((_, i) => (
                                                    <div key={i} className="rounded-sm bg-[#c9a84c]/40" />
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                    {/* Card brand */}
                                    <div className="absolute top-4 right-4">
                                        <CardIcon number={form.cardNumber.replace(/\s/g, "")} />
                                    </div>
                                    {/* Card number */}
                                    <div className="absolute bottom-16 left-6 right-6">
                                        <p className="text-white/90 text-xl tracking-[0.25em] font-mono font-light">
                                            {maskedNumber}
                                        </p>
                                    </div>
                                    {/* Card bottom */}
                                    <div className="absolute bottom-5 left-6 right-6 flex justify-between items-end">
                                        <div>
                                            <p className="text-white/30 text-[9px] uppercase tracking-widest mb-0.5">Card Holder</p>
                                            <p className="text-white/80 text-sm tracking-widest uppercase font-light truncate max-w-[160px]">
                                                {displayHolder}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-white/30 text-[9px] uppercase tracking-widest mb-0.5">Expires</p>
                                            <p className="text-white/80 text-sm tracking-widest font-mono">
                                                {displayExpiry}
                                            </p>
                                        </div>
                                    </div>
                                    {/* Decorative circles */}
                                    <div className="absolute -bottom-8 -right-8 w-32 h-32 rounded-full border border-white/5" />
                                    <div className="absolute -bottom-4 -right-4 w-20 h-20 rounded-full border border-white/5" />
                                </div>

                                {/* Card Back */}
                                <div className="absolute inset-0 rounded-2xl overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.5)]"
                                    style={{ backfaceVisibility: "hidden", transform: "rotateY(180deg)", background: "linear-gradient(135deg, #1a1a35 0%, #0d0d22 100%)" }}>
                                    <div className="w-full h-8 mt-8 bg-[#0a0a14]" />
                                    <div className="mx-6 mt-4">
                                        <div className="h-10 rounded bg-white/5 flex items-center justify-end px-4">
                                            <p className="text-white/70 font-mono text-sm tracking-widest">{displayCVV}</p>
                                        </div>
                                        <p className="text-white/20 text-[10px] mt-2 text-right">CVV / CVC</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Divider */}
                    <div className="mx-8 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent" />

                    {/* Form */}
                    <form onSubmit={handleSubmit} className="px-8 py-6 space-y-4">
                        {/* Amount + Currency */}
                        <div className="flex gap-3">
                            <div className="flex-1">
                                <label className="block text-gray-50 text-xs tracking-widest uppercase mb-1.5">Amount</label>
                                <div className={`relative flex items-center rounded-xl border transition-all duration-300 ${focused === "amount" ? "border-[#c9a84c]/50 bg-[#c9a84c]/5 shadow-[0_0_20px_rgba(201,168,76,0.08)]" : "border-white/[0.07] bg-white/[0.03]"}`}>
                                    <span className="pl-4 text-gray-50 text-lg font-light">$</span>
                                    <input
                                        type="text"
                                        value={form.amount}
                                        onChange={handleChange("amount")}
                                        onFocus={() => setFocused("amount")}
                                        onBlur={() => setFocused(null)}
                                        placeholder="0.00"
                                        className="flex-1 bg-transparent text-white pl-2 pr-4 py-3.5 text-lg font-light outline-none placeholder-[#ffffff]/50"
                                        style={{ fontFamily: "'DM Mono', monospace, serif" }}
                                        required
                                    />
                                </div>
                                {errors?.amount && <p className="text-red-400 text-xs mt-1">{errors.amount}</p>}
                            </div>
                            <div className="w-[90px]">
                                <label className="block text-gray-50 text-xs tracking-widest uppercase mb-1.5">Currency</label>
                                <select
                                    value={form.currency}
                                    onChange={handleChange("currency")}
                                    className="w-full bg-white/[0.03] border border-white/[0.07] text-white rounded-xl px-3 py-3.5 outline-none text-sm appearance-none text-center cursor-pointer hover:border-white/20 transition-colors"
                                >
                                    {["USD", "EUR", "GBP", "CAD", "AUD"].map(c => (
                                        <option key={c} value={c} className="bg-[#13131f]">{c}</option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {/* Card Holder */}
                        <div>
                            <label className="block text-gray-50 text-xs tracking-widest uppercase mb-1.5">Card Holder</label>
                            <input
                                type="text"
                                value={form.cardHolder}
                                onChange={handleChange("cardHolder")}
                                onFocus={() => setFocused("cardHolder")}
                                onBlur={() => setFocused(null)}
                                placeholder="Full name on card"
                                className={`w-full bg-white/[0.03] border rounded-xl px-4 py-3.5 text-white outline-none placeholder-[#ffffff]/50 transition-all duration-300 uppercase tracking-wider text-sm ${focused === "cardHolder" ? "border-[#c9a84c]/50 bg-[#c9a84c]/5 shadow-[0_0_20px_rgba(201,168,76,0.08)]" : "border-white/[0.07]"}`}
                            />
                        </div>

                        {/* Card Number */}
                        <div>
                            <label className="block text-gray-50 text-xs tracking-widest uppercase mb-1.5">Card Number</label>
                            <div className={`relative flex items-center rounded-xl border transition-all duration-300 ${focused === "cardNumber" ? "border-[#c9a84c]/50 bg-[#c9a84c]/5 shadow-[0_0_20px_rgba(201,168,76,0.08)]" : "border-white/[0.07] bg-white/[0.03]"}`}>
                                <input
                                    type="text"
                                    value={form.cardNumber}
                                    onChange={handleChange("cardNumber")}
                                    onFocus={() => setFocused("cardNumber")}
                                    onBlur={() => setFocused(null)}
                                    placeholder="•••• •••• •••• ••••"
                                    className="flex-1 bg-transparent text-white px-4 py-3.5 outline-none placeholder-[#ffffff]/50 tracking-widest font-mono text-sm"
                                />
                                <div className="pr-4">
                                    <CardIcon number={form.cardNumber.replace(/\s/g, "")} />
                                </div>
                            </div>
                        </div>

                        {/* Expiry + CVV */}
                        <div className="flex gap-3">
                            <div className="flex-1">
                                <label className="block text-gray-50 text-xs tracking-widest uppercase mb-1.5">Expiry</label>
                                <input
                                    type="text"
                                    value={form.expiry}
                                    onChange={handleChange("expiry")}
                                    onFocus={() => setFocused("expiry")}
                                    onBlur={() => setFocused(null)}
                                    placeholder="MM / YY"
                                    className={`w-full bg-white/[0.03] border rounded-xl px-4 py-3.5 text-white outline-none placeholder-[#ffffff]/50 transition-all duration-300 font-mono text-sm tracking-widest ${focused === "expiry" ? "border-[#c9a84c]/50 bg-[#c9a84c]/5 shadow-[0_0_20px_rgba(201,168,76,0.08)]" : "border-white/[0.07]"}`}
                                />
                            </div>
                            <div className="flex-1">
                                <label className="block text-gray-50 text-xs tracking-widest uppercase mb-1.5">CVV</label>
                                <input
                                    type="text"
                                    value={form.cvv}
                                    onChange={handleChange("cvv")}
                                    onFocus={() => setFocused("cvv")}
                                    onBlur={() => setFocused(null)}
                                    placeholder="•••"
                                    className={`w-full bg-white/[0.03] border rounded-xl px-4 py-3.5 text-white outline-none placeholder-[#ffffff]/50 transition-all duration-300 font-mono text-sm tracking-widest ${focused === "cvv" ? "border-[#c9a84c]/50 bg-[#c9a84c]/5 shadow-[0_0_20px_rgba(201,168,76,0.08)]" : "border-white/[0.07]"}`}
                                />
                            </div>
                        </div>

                        {/* Description */}
                        <div>
                            <label className="block text-gray-50 text-xs tracking-widest uppercase mb-1.5">Description <span className="normal-case">(optional)</span></label>
                            <input
                                type="text"
                                value={form.description}
                                onChange={handleChange("description")}
                                onFocus={() => setFocused("description")}
                                onBlur={() => setFocused(null)}
                                placeholder="Order #1234, Subscription renewal..."
                                className={`w-full bg-white/[0.03] border rounded-xl px-4 py-3.5 text-white outline-none placeholder-[#ffffff]/50 transition-all duration-300 text-sm ${focused === "description" ? "border-[#c9a84c]/50 bg-[#c9a84c]/5 shadow-[0_0_20px_rgba(201,168,76,0.08)]" : "border-white/[0.07]"}`}
                            />
                        </div>

                        {/* Submit */}
                        <div className="pt-2">
                            <button
                                type="submit"
                                disabled={submitting}
                                className="relative w-full py-4 rounded-xl font-semibold text-[#0a0a14] tracking-widest text-sm uppercase overflow-hidden transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed"
                                style={{
                                    background: submitting
                                        ? "linear-gradient(135deg, #a07830, #7a5a20)"
                                        : "linear-gradient(135deg, #c9a84c 0%, #d4b86a 50%, #c9a84c 100%)",
                                    boxShadow: submitting ? "none" : "0 8px 32px rgba(201,168,76,0.35), 0 0 0 1px rgba(201,168,76,0.1)",
                                }}
                            >
                                {ripple && (
                                    <span
                                        className="absolute rounded-full bg-white/20 animate-ping"
                                        style={{ width: 200, height: 200, left: ripple.x - 100, top: ripple.y - 100 }}
                                    />
                                )}
                                <span className="relative flex items-center justify-center gap-2">
                                    {submitting ? (
                                        <>
                                            <svg className="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" strokeDasharray="32" strokeDashoffset="12" />
                                            </svg>
                                            Processing...
                                        </>
                                    ) : (
                                        <>
                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            {form.amount ? `Pay $${parseFloat(form.amount || 0).toFixed(2)} ${form.currency}` : "Pay Securely"}
                                        </>
                                    )}
                                </span>
                            </button>
                        </div>

                        {/* Trust badges */}
                        <div className="flex items-center justify-center gap-4 pt-1">
                            {["SSL Secured", "PCI DSS", "256-bit"].map((badge) => (
                                <div key={badge} className="flex items-center gap-1 text-[#3a3a5a]">
                                    <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd" />
                                    </svg>
                                    <span className="text-[10px] tracking-wider uppercase">{badge}</span>
                                </div>
                            ))}
                        </div>
                    </form>
                </div>

                {/* Footer */}
                <p className="text-center text-[#2E7D32] text-xs mt-6 tracking-wider">
                    Powered by <span className="text-gray-900">Stripe</span> · Your payment is fully encrypted
                </p>
            </div>

            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap');
                @keyframes fadeDown {
                    from { opacity: 0; transform: translateY(-16px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            `}</style>
        </div>
    );
}