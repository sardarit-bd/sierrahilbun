import { useEffect, useRef, useState } from "react";
import { router } from "@inertiajs/react";

const MAX_POLLS = 10; 
const POLL_MS   = 2000;

export default function PaymentPending({ transaction_id }) {
    const [attempt, setAttempt]     = useState(0);
    const [statusMsg, setStatusMsg] = useState("Contacting payment provider...");
    const [dots, setDots]           = useState("");
    const pollRef                   = useRef(null);
    const dotRef                    = useRef(null);

    // Animated dots
    useEffect(() => {
        dotRef.current = setInterval(() => {
            setDots(d => d.length >= 3 ? "" : d + ".");
        }, 400);
        return () => clearInterval(dotRef.current);
    }, []);

    // Status messages cycling while pending
    const messages = [
        "Contacting payment provider...",
        "Verifying transaction...",
        "Waiting for bank confirmation...",
        "Almost there...",
        "Finalising your payment...",
    ];

    useEffect(() => {
        if (!transaction_id) return;

        let count = 0;

        const poll = async () => {
            try {
                setStatusMsg(messages[count % messages.length]);
                setAttempt(count + 1);

                const res  = await fetch(route("payment.status", transaction_id));
                const data = await res.json();

                if (data.status === "succeeded") {
                    clearInterval(pollRef.current);
                    clearInterval(dotRef.current);
                    router.visit(route("payment.success"), {
                        method: "get",
                        data: {
                            transaction_id: data.transaction_id,
                            amount:         data.amount,
                            currency:       data.currency,
                        },
                    });
                    return;
                }

                if (data.status === "failed") {
                    clearInterval(pollRef.current);
                    clearInterval(dotRef.current);
                    router.visit(route("payment.failed"), {
                        method: "get",
                        data: { error: "Your payment was declined by the bank." },
                    });
                    return;
                }

                count++;

                // Timeout after MAX_POLLS
                if (count >= MAX_POLLS) {
                    clearInterval(pollRef.current);
                    clearInterval(dotRef.current);
                    router.visit(route("payment.failed"), {
                        method: "get",
                        data: { error: "Payment confirmation timed out. Please contact support." },
                    });
                }

            } catch (err) {
                count++;
                console.error("Polling error:", err);
            }
        };

        // Initial poll immediately then every POLL_MS
        poll();
        pollRef.current = setInterval(poll, POLL_MS);

        return () => {
            clearInterval(pollRef.current);
            clearInterval(dotRef.current);
        };
    }, [transaction_id]);

    const progress = Math.min((attempt / MAX_POLLS) * 100, 95);

    return (
        <div className="min-h-screen bg-[#FDFBF7] flex items-center justify-center p-4 overflow-hidden">
            {/* Ambient */}
            <div className="fixed inset-0 pointer-events-none">
                <div className="absolute top-[-10%] left-[20%] w-[500px] h-[500px] rounded-full bg-[#c9a84c] opacity-[0.04] blur-[120px]" />
                <div className="absolute bottom-[-10%] right-[20%] w-[400px] h-[400px] rounded-full bg-[#7b5ea7] opacity-[0.05] blur-[100px]" />
                <div className="absolute inset-0 opacity-[0.02]"
                    style={{ backgroundImage: "linear-gradient(#c9a84c 1px, transparent 1px), linear-gradient(90deg, #c9a84c 1px, transparent 1px)", backgroundSize: "60px 60px" }} />
            </div>

            <div className="relative w-full max-w-[420px] z-10">
                {/* Card */}
                <div className="rounded-2xl overflow-hidden shadow-[0_40px_80px_rgba(0,0,0,0.6)] border border-white/[0.06]"
                    style={{ background: "linear-gradient(145deg, #13131f 0%, #0e0e1a 100%)" }}>

                    {/* Animated top progress bar */}
                    <div className="h-[2px] w-full bg-white/5 relative overflow-hidden">
                        <div
                            className="absolute inset-y-0 left-0 transition-all duration-500"
                            style={{
                                width: `${progress}%`,
                                background: "linear-gradient(90deg, #c9a84c, #d4b86a)",
                                boxShadow: "0 0 12px rgba(201,168,76,0.6)",
                            }}
                        />
                        {/* Shimmer */}
                        <div className="absolute inset-0 animate-pulse"
                            style={{ background: "linear-gradient(90deg, transparent, rgba(201,168,76,0.1), transparent)" }} />
                    </div>

                    <div className="px-8 py-12 text-center">
                        {/* Spinner */}
                        <div className="relative mx-auto w-24 h-24 mb-8">
                            {/* Outer slow ring */}
                            <svg className="absolute inset-0 w-24 h-24 animate-spin" style={{ animationDuration: "3s" }} viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="42" fill="none" stroke="#c9a84c" strokeWidth="1"
                                    strokeDasharray="60 200" strokeLinecap="round"
                                    style={{ opacity: 0.3 }} />
                            </svg>
                            {/* Middle ring */}
                            <svg className="absolute inset-0 w-24 h-24 animate-spin" style={{ animationDuration: "1.5s", animationDirection: "reverse" }} viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="34" fill="none" stroke="#7b5ea7" strokeWidth="1"
                                    strokeDasharray="40 180" strokeLinecap="round"
                                    style={{ opacity: 0.4 }} />
                            </svg>
                            {/* Inner fast ring */}
                            <svg className="absolute inset-0 w-24 h-24 animate-spin" style={{ animationDuration: "0.8s" }} viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="24" fill="none" stroke="#c9a84c" strokeWidth="2"
                                    strokeDasharray="30 120" strokeLinecap="round" />
                            </svg>
                            {/* Center icon */}
                            <div className="absolute inset-0 flex items-center justify-center">
                                <div className="w-10 h-10 rounded-full bg-[#c9a84c]/10 flex items-center justify-center">
                                    <svg className="w-5 h-5 text-[#c9a84c]" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <h1 className="text-white text-3xl font-light mb-2"
                            style={{ fontFamily: "'Cormorant Garamond', Georgia, serif" }}>
                            Processing Payment
                        </h1>

                        <p className="text-[#c9a84c]/70 text-sm tracking-wide min-h-[20px]">
                            {statusMsg}{dots}
                        </p>

                        {/* Transaction ID */}
                        {transaction_id && (
                            <div className="mt-8 py-3 px-4 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                                <p className="text-[#3a3a5a] text-[10px] tracking-widest uppercase mb-1">Transaction Reference</p>
                                <p className="text-white/40 text-xs font-mono truncate">{transaction_id}</p>
                            </div>
                        )}

                        {/* Steps */}
                        <div className="mt-6 space-y-2">
                            {[
                                { label: "Payment submitted",     done: attempt >= 1 },
                                { label: "Bank verification",      done: attempt >= 3 },
                                { label: "Confirming transaction", done: attempt >= 7 },
                            ].map((step, i) => (
                                <div key={i} className="flex items-center gap-3 py-2.5 px-4 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                                    <div className={`w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-500 ${step.done ? "bg-[#c9a84c]" : "border border-white/10"}`}>
                                        {step.done && (
                                            <svg className="w-2.5 h-2.5 text-[#0a0a14]" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        )}
                                    </div>
                                    <span className={`text-xs tracking-wide transition-colors duration-500 ${step.done ? "text-white/60" : "text-[#3a3a5a]"}`}>
                                        {step.label}
                                    </span>
                                    {!step.done && (
                                        <div className="ml-auto flex gap-0.5">
                                            {[0, 1, 2].map(d => (
                                                <div key={d} className="w-1 h-1 rounded-full bg-[#3a3a5a] animate-bounce"
                                                    style={{ animationDelay: `${d * 0.15}s` }} />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>

                        <p className="mt-8 text-[#2a2a3e] text-xs tracking-wider">
                            Please do not close or refresh this page
                        </p>
                    </div>
                </div>

                <p className="text-center text-[#2E7D32] text-xs mt-6 tracking-wider">
                    Secured by <span className="text-gray-900">Stripe</span> · 256-bit encryption
                </p>
            </div>

            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap');
            `}</style>
        </div>
    );
}