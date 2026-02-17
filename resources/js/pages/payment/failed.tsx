import { useEffect, useState } from "react";
import { Link, router } from "@inertiajs/react";

export default function PaymentFailed({ error }) {
    const [visible, setVisible] = useState(false);
    const [crossDrawn, setCrossDrawn] = useState(false);
    const [retrying, setRetrying] = useState(false);

    useEffect(() => {
        setTimeout(() => setVisible(true), 100);
        setTimeout(() => setCrossDrawn(true), 600);
    }, []);

    const errorMessage = error || "Your payment could not be processed. Please check your details and try again.";

    const commonReasons = [
        { icon: "💳", text: "Insufficient funds" },
        { icon: "🔒", text: "Card declined by bank" },
        { icon: "📅", text: "Expired card details" },
        { icon: "❌", text: "Incorrect card number" },
    ];

    return (
        <div className="min-h-screen bg-[#FDFBF7] flex items-center justify-center p-4 overflow-hidden">
            {/* Ambient */}
            <div className="fixed inset-0 pointer-events-none">
                <div
                    className="absolute top-[-10%] left-[20%] w-[500px] h-[500px] rounded-full blur-[120px] transition-all duration-1000"
                    style={{ background: "#ff4444", opacity: visible ? 0.04 : 0 }}
                />
                <div className="absolute bottom-[-10%] right-[20%] w-[400px] h-[400px] rounded-full bg-[#7b3030] opacity-[0.05] blur-[100px]" />
                <div className="absolute inset-0 opacity-[0.02]"
                    style={{ backgroundImage: "linear-gradient(#ff4444 1px, transparent 1px), linear-gradient(90deg, #ff4444 1px, transparent 1px)", backgroundSize: "60px 60px" }} />
            </div>

            <div
                className="relative w-full max-w-[440px] z-10 transition-all duration-700"
                style={{ opacity: visible ? 1 : 0, transform: visible ? "translateY(0) scale(1)" : "translateY(24px) scale(0.97)" }}
            >
                {/* Card */}
                <div className="rounded-2xl overflow-hidden shadow-[0_40px_80px_rgba(0,0,0,0.6)] border border-white/[0.06]"
                    style={{ background: "linear-gradient(145deg, #13131f 0%, #0e0e1a 100%)" }}>

                    {/* Top error band */}
                    <div className="relative h-1 w-full overflow-hidden">
                        <div
                            className="absolute inset-0 transition-all duration-1000"
                            style={{
                                background: "linear-gradient(90deg, #ff4444, #ff8c42, #ff4444)",
                                width: visible ? "100%" : "0%",
                            }}
                        />
                    </div>

                    <div className="px-8 pt-10 pb-8 text-center">
                        {/* Animated X mark */}
                        <div className="relative mx-auto w-24 h-24 mb-8">
                            <div className="absolute inset-0 rounded-full bg-[#ff4444]/10 animate-ping" style={{ animationDuration: "2.5s" }} />
                            <div className="absolute inset-2 rounded-full bg-[#ff4444]/5" />
                            <svg className="w-24 h-24 absolute inset-0" viewBox="0 0 96 96">
                                <circle
                                    cx="48" cy="48" r="40"
                                    fill="none"
                                    stroke="#ff4444"
                                    strokeWidth="2"
                                    strokeDasharray="251"
                                    strokeDashoffset={crossDrawn ? "0" : "251"}
                                    style={{ transition: "stroke-dashoffset 0.8s cubic-bezier(0.4, 0, 0.2, 1)", transformOrigin: "center", transform: "rotate(-90deg)" }}
                                />
                                {/* X mark - line 1 */}
                                <path
                                    d="M34 34 L62 62"
                                    fill="none"
                                    stroke="#ff4444"
                                    strokeWidth="3"
                                    strokeLinecap="round"
                                    strokeDasharray="40"
                                    strokeDashoffset={crossDrawn ? "0" : "40"}
                                    style={{ transition: "stroke-dashoffset 0.4s ease 0.8s" }}
                                />
                                {/* X mark - line 2 */}
                                <path
                                    d="M62 34 L34 62"
                                    fill="none"
                                    stroke="#ff4444"
                                    strokeWidth="3"
                                    strokeLinecap="round"
                                    strokeDasharray="40"
                                    strokeDashoffset={crossDrawn ? "0" : "40"}
                                    style={{ transition: "stroke-dashoffset 0.4s ease 1.1s" }}
                                />
                            </svg>
                        </div>

                        <h1 className="text-white text-3xl font-light mb-2"
                            style={{ fontFamily: "'Cormorant Garamond', Georgia, serif" }}>
                            Payment Failed
                        </h1>
                        <p className="text-[#ffffff]/50] text-sm tracking-wide">We were unable to process your payment</p>

                        {/* Error message */}
                        <div className="mt-6 py-4 px-5 rounded-xl border border-[#ff4444]/20 bg-[#ff4444]/5 text-left">
                            <div className="flex items-start gap-3">
                                <svg className="w-4 h-4 text-[#ff4444] mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p className="text-[#ff6b6b] text-sm leading-relaxed">{errorMessage}</p>
                            </div>
                        </div>

                        {/* Common reasons */}
                        <div className="mt-6">
                            <p className="text-[#ffffff]/50 text-xs tracking-widest uppercase mb-3">Common Reasons</p>
                            <div className="grid grid-cols-2 gap-2">
                                {commonReasons.map((reason, i) => (
                                    <div key={i}
                                        className="flex items-center gap-2 py-2.5 px-3 rounded-xl bg-white/[0.02] border border-white/[0.04] text-left"
                                        style={{ transition: `all 0.3s ease ${i * 0.1 + 0.3}s`, opacity: visible ? 1 : 0, transform: visible ? "translateY(0)" : "translateY(8px)" }}
                                    >
                                        <span className="text-base">{reason.icon}</span>
                                        <span className="text-[#ffffff]/50] text-xs">{reason.text}</span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Divider */}
                        <div className="my-6 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent" />

                        {/* Actions */}
                        <div className="space-y-3">
                            <Link
                                href={route("payment.index")}
                                className="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl text-[#0a0a14] text-sm font-semibold tracking-widest uppercase transition-all duration-300 hover:shadow-[0_8px_32px_rgba(201,168,76,0.4)]"
                                style={{ background: "linear-gradient(135deg, #c9a84c 0%, #d4b86a 50%, #c9a84c 100%)" }}
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Try Again
                            </Link>

                            <button
                                className="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl text-white/50 text-sm tracking-widest uppercase border border-white/[0.07] hover:border-white/20 hover:text-white/70 transition-all duration-300"
                                onClick={() => window.history.back()}
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Go Back
                            </button>
                        </div>

                        {/* Support */}
                        <p className="mt-6 text-[#ffffff]/50 text-xs tracking-wide">
                            Need help?{" "}
                            <a href="mailto:support@example.com" className="text-[#c9a84c]/60 hover:text-[#c9a84c] transition-colors underline underline-offset-2">
                                Contact support
                            </a>
                        </p>
                    </div>
                </div>

                <p className="text-center text-[#2a2a3e] text-xs mt-6 tracking-wider">
                    No charges were made to your account
                </p>
            </div>

            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap');
            `}</style>
        </div>
    );
}