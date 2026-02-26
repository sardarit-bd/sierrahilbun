export const STATUS_CONFIG: Record<string, { label: string; className: string }> = {
    paid:       { label: 'Paid',       className: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' },
    processing: { label: 'Processing', className: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' },
    shipped:    { label: 'Shipped',    className: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' },
};

export function getStatusConfig(status: string) {
    return STATUS_CONFIG[status] ?? { label: status, className: 'bg-gray-100 text-gray-800' };
}