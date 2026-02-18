import { useEffect, useRef, useState } from "react";
import { Link } from "@inertiajs/react";
import { useCart } from "../../context/CartContext";

function ConfettiPiece({ style }) {
    return <div className="absolute rounded-sm animate-bounce" style={style} />;
}

export default function PaymentSuccess({ transaction_id, amount, currency }) {
    const { clearCart } = useCart(); 
    const [visible, setVisible] = useState(false);
    const [checkDrawn, setCheckDrawn] = useState(false);
    const confettiRef = useRef([]);

    useEffect(() => {
        setTimeout(() => setVisible(true), 100);
        setTimeout(() => setCheckDrawn(true), 600);

        clearCart();
    }, [clearCart]);

    const confettiPieces = Array.from({ length: 24 }, (_, i) => ({
        key: i,
        style: {
            width: Math.random() * 8 + 4,
            height: Math.random() * 8 + 4,
            left: `${Math.random() * 100}%`,
            top: `${Math.random() * 60}%`,
            background: ["#c9a84c", "#7b5ea7", "#4a9eff", "#ff6b6b", "#51cf66"][i % 5],
            opacity: Math.random() * 0.6 + 0.4,
            animationDelay: `${Math.random() * 2}s`,
            animationDuration: `${Math.random() * 2 + 1}s`,
            transform: `rotate(${Math.random() * 360}deg)`,
        },
    }));

    const formattedAmount = amount
        ? new Intl.NumberFormat("en-US", { style: "currency", currency: currency || "USD" }).format(amount)
        : null;

    const now = new Date();
    const dateStr = now.toLocaleDateString("en-US", { weekday: "long", year: "numeric", month: "long", day: "numeric" });
    const timeStr = now.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });

    return (
        <div className="min-h-screen bg-[#F9FAFB] flex items-center justify-center p-4 overflow-hidden">
            {/* Ambient */}
            <div className="fixed inset-0 pointer-events-none">
                <div className="absolute top-[-10%] left-[20%] w-[500px] h-[500px] rounded-full bg-[#51cf66] opacity-[0.04] blur-[120px] transition-all duration-1000"
                    style={{ opacity: visible ? 0.05 : 0 }} />
                <div className="absolute bottom-[-10%] right-[20%] w-[400px] h-[400px] rounded-full bg-[#c9a84c] opacity-[0.04] blur-[100px]" />
                <div className="absolute inset-0 opacity-[0.02]"
                    style={{ backgroundImage: "linear-gradient(#c9a84c 1px, transparent 1px), linear-gradient(90deg, #c9a84c 1px, transparent 1px)", backgroundSize: "60px 60px" }} />
            </div>

            {/* Confetti */}
            {visible && confettiPieces.map(p => <ConfettiPiece key={p.key} style={p.style} />)}

            <div
                className="relative w-full max-w-[440px] z-10 transition-all duration-700"
                style={{ opacity: visible ? 1 : 0, transform: visible ? "translateY(0) scale(1)" : "translateY(24px) scale(0.97)" }}
            >
                {/* Card */}
                <div className="rounded-2xl overflow-hidden shadow-[0_40px_80px_rgba(0,0,0,0.6)] border border-white/[0.06]"
                    style={{ background: "linear-gradient(145deg, #2842c6 0%, #8a8a92 100%)" }}>

                    {/* Top success band */}
                    <div className="relative h-1 w-full overflow-hidden">
                        <div
                            className="absolute inset-0 transition-all duration-1000"
                            style={{
                                background: "linear-gradient(90deg, #51cf66, #c9a84c, #51cf66)",
                                width: visible ? "100%" : "0%",
                            }}
                        />
                    </div>

                    <div className="px-8 pt-10 pb-8 text-center">
                        {/* Animated checkmark */}
                        <div className="relative mx-auto w-24 h-24 mb-8">
                            {/* Outer ring pulse */}
                            <div className="absolute inset-0 rounded-full bg-[#51cf66]/10 animate-ping" style={{ animationDuration: "2s" }} />
                            <div className="absolute inset-2 rounded-full bg-[#51cf66]/10" />
                            {/* Circle */}
                            <svg className="w-24 h-24 absolute inset-0" viewBox="0 0 96 96">
                                <circle
                                    cx="48" cy="48" r="40"
                                    fill="none"
                                    stroke="#51cf66"
                                    strokeWidth="2"
                                    strokeDasharray="251"
                                    strokeDashoffset={checkDrawn ? "0" : "251"}
                                    style={{ transition: "stroke-dashoffset 0.8s cubic-bezier(0.4, 0, 0.2, 1)", transformOrigin: "center", transform: "rotate(-90deg)" }}
                                />
                                {/* Checkmark */}
                                <path
                                    d="M30 48 L42 60 L66 36"
                                    fill="none"
                                    stroke="#51cf66"
                                    strokeWidth="3"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeDasharray="50"
                                    strokeDashoffset={checkDrawn ? "0" : "50"}
                                    style={{ transition: "stroke-dashoffset 0.5s ease 0.8s" }}
                                />
                            </svg>
                        </div>

                        <h1 className="text-white text-3xl font-light mb-2"
                            style={{ fontFamily: "'Cormorant Garamond', Georgia, serif" }}>
                            Payment Successful
                        </h1>
                        <p className="text-gray-50 text-sm tracking-wide">Your transaction has been completed</p>

                        {/* Amount */}
                        {formattedAmount && (
                            <div className="mt-8 py-6 rounded-xl border border-white/[0.06] bg-white/[0.02]"
                                style={{ transition: "all 0.5s ease 0.3s", opacity: visible ? 1 : 0 }}>
                                <p className="text-gray-50 text-xs tracking-widest uppercase mb-2">Amount Paid</p>
                                <p className="text-[#c9a84c] font-light"
                                    style={{ fontFamily: "'Cormorant Garamond', Georgia, serif", fontSize: "2.5rem" }}>
                                    {formattedAmount}
                                </p>
                            </div>
                        )}

                        {/* Transaction details */}
                        <div className="mt-4 space-y-3 text-left">
                            {transaction_id && (
                                <div className="flex items-center justify-between py-3 px-4 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                                    <span className="text-gray-50 text-xs tracking-widest uppercase">Transaction ID</span>
                                    <span className="text-white/60 text-xs font-mono truncate max-w-[160px]">
                                        {transaction_id}
                                    </span>
                                </div>
                            )}
                            <div className="flex items-center justify-between py-3 px-4 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                                <span className="text-gray-50 text-xs tracking-widest uppercase">Date</span>
                                <span className="text-white/60 text-xs">{dateStr}</span>
                            </div>
                            <div className="flex items-center justify-between py-3 px-4 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                                <span className="text-gray-50 text-xs tracking-widest uppercase">Time</span>
                                <span className="text-white/60 text-xs font-mono">{timeStr}</span>
                            </div>
                            <div className="flex items-center justify-between py-3 px-4 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                                <span className="text-gray-50 text-xs tracking-widest uppercase">Status</span>
                                <span className="flex items-center gap-1.5 text-[#51cf66] text-xs">
                                    <span className="w-1.5 h-1.5 rounded-full bg-[#51cf66] animate-pulse font-bold" />
                                    Confirmed
                                </span>
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
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                New Payment
                            </Link>
                            {/* <button
                                onClick={() => window.print()}
                                className="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl text-white/50 text-sm tracking-widest uppercase border border-white/[0.07] hover:border-white/20 hover:text-white/70 transition-all duration-300"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Receipt
                            </button> */}
                        </div>
                    </div>
                </div>

                {/* <p className="text-center text-[#2a2a3e] text-xs mt-6 tracking-wider">
                    A confirmation has been sent to your email
                </p> */}
            </div>

            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap');
            `}</style>
        </div>
    );
}