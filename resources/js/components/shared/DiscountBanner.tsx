import { usePage } from '@inertiajs/react';

interface Banner {
    text: string;
    promo_code: string | null;
}

export default function DiscountBanner() {
    const { discountBanner } = usePage<{ discountBanner: Banner | null }>().props;

    if (!discountBanner) return null;

    return (
        <div className="bg-[#2E7D32] text-white text-center py-2.5 px-4 text-xs md:text-sm font-bold tracking-wide">
            <p>
                {discountBanner.text}
                {discountBanner.promo_code && (
                    <>
                        <span className="mx-1 opacity-60">|</span>
                        Code:{' '}
                        <span className="underline decoration-white/40 underline-offset-2">
                            {discountBanner.promo_code}
                        </span>
                    </>
                )}
            </p>
        </div>
    );
}