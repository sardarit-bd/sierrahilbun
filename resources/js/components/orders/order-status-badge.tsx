import { getStatusConfig } from '@/lib/order-status';

export function OrderStatusBadge({ status }: { status: string }) {
    const { label, className } = getStatusConfig(status);
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${className}`}>
            {label}
        </span>
    );
}