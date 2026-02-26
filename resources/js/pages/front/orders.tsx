// resources/js/pages/front/orders.tsx
import { usePage, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { OrderStatusBadge } from '@/components/orders/order-status-badge';
import { OrderItemsDrawer } from '@/components/orders/order-items-drawer';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedOrders } from '@/types/order';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Orders', href: '/orders' },
];

export default function Orders() {
    const { orders } = usePage<{ orders: PaginatedOrders }>().props;

    function goToPage(url: string | null) {
        if (url) router.get(url, {}, { preserveScroll: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Orders" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                {/* Header */}
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">My Orders</h1>
                    <p className="text-sm text-muted-foreground">{orders.total} total orders</p>
                </div>

                {/* Empty state */}
                {orders.data.length === 0 && (
                    <div className="flex flex-1 flex-col items-center justify-center gap-2 text-muted-foreground">
                        <p className="text-lg">No orders yet.</p>
                        <p className="text-sm">Orders you place will appear here.</p>
                    </div>
                )}

                {/* Table */}
                {orders.data.length > 0 && (
                    <div className="rounded-lg border">
                        <table className="w-full text-sm">
                            <thead className="border-b bg-muted/50">
                                <tr>
                                    <th className="px-4 py-3 text-left font-medium">Order #</th>
                                    <th className="px-4 py-3 text-left font-medium">Date</th>
                                    <th className="px-4 py-3 text-left font-medium">Items</th>
                                    <th className="px-4 py-3 text-left font-medium">Status</th>
                                    <th className="px-4 py-3 text-right font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {orders.data.map((order) => (
                                    <tr key={order.id} className="hover:bg-muted/30 transition-colors">
                                        <td className="px-4 py-4 font-mono font-medium">
                                            #{order.id}
                                        </td>
                                        <td className="px-4 py-4 text-muted-foreground">
                                            {new Date(order.created_at).toLocaleDateString('en-US', {
                                                year: 'numeric', month: 'short', day: 'numeric',
                                            })}
                                        </td>
                                        <td className="px-4 py-4">
                                            <OrderItemsDrawer items={order.items} />
                                        </td>
                                        <td className="px-4 py-4">
                                            <OrderStatusBadge status={order.status} />
                                        </td>
                                        <td className="px-4 py-4 text-right font-semibold">
                                            ${parseFloat(order.total_amount).toFixed(2)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination */}
                {orders.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm">
                        <p className="text-muted-foreground">
                            Showing {orders.from}–{orders.to} of {orders.total}
                        </p>
                        <div className="flex gap-1">
                            {orders.links.map((link, i) => (
                                <button
                                    key={i}
                                    onClick={() => goToPage(link.url)}
                                    disabled={!link.url}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                    className={`rounded px-3 py-1 ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground font-medium'
                                            : 'hover:bg-muted disabled:opacity-40'
                                    }`}
                                />
                            ))}
                        </div>
                    </div>
                )}

            </div>
        </AppLayout>
    );
}