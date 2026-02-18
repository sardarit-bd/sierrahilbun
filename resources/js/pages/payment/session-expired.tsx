import { Link } from "@inertiajs/react";
import AppHeaderLayout from "@/layouts/app/app-header-layout";

export default function SessionExpired() {
    return (
        <AppHeaderLayout>
            <div className="min-h-screen bg-[#F9FAFB] flex items-center justify-center p-4">
                <div className="text-center max-w-md">
                    <div className="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-6">
                        <svg className="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900 mb-2">Session Expired</h1>
                    <p className="text-gray-500 mb-8">
                        Your checkout session has expired after 30 minutes. Please return to your cart and try again.
                    </p>
                    <Link
                        href="/cart"
                        className="inline-flex items-center gap-2 bg-[#2E7D32] text-white font-bold py-3 px-8 rounded-xl hover:bg-[#1B5E20] transition-colors"
                    >
                        Return to Cart
                    </Link>
                </div>
            </div>
        </AppHeaderLayout>
    );
}