import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { CheckCircle, XCircle, X } from 'lucide-react';

interface Flash {
    success?: string;
    error?: string;
}

export default function FlashMessage() {
    const { flash } = usePage<{ flash: Flash }>().props;
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (flash?.success || flash?.error) {
            setVisible(true);

            // Auto-dismiss after 5 seconds
            const timer = setTimeout(() => setVisible(false), 5000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    if (!visible || (!flash?.success && !flash?.error)) return null;

    const isSuccess = !!flash.success;

    return (
        <div
            className={`fixed top-6 right-6 z-[100] flex items-start gap-3 px-5 py-4 rounded-2xl shadow-xl max-w-sm w-full transition-all duration-300 ${
                isSuccess
                    ? 'bg-[#2E7D32] text-white'
                    : 'bg-red-600 text-white'
            }`}
        >
            {isSuccess
                ? <CheckCircle size={20} className="flex-shrink-0 mt-0.5" />
                : <XCircle size={20} className="flex-shrink-0 mt-0.5" />
            }

            <p className="text-sm font-semibold flex-1">
                {flash.success ?? flash.error}
            </p>

            <button
                onClick={() => setVisible(false)}
                className="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity"
            >
                <X size={16} />
            </button>
        </div>
    );
}