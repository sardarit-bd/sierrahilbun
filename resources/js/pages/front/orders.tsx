import { useState } from 'react';
import { MapPin, Pencil, Package, CheckCircle2, Clock, Truck, X, ChevronDown, ChevronUp } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, usePage } from '@inertiajs/react';

const DeliveryStatusBadge = ({ status }: { status: string }) => {
    const map: Record<string, { bg: string; icon: React.ReactNode }> = {
        delivered:  { bg: 'bg-green-100 text-green-700',  icon: <CheckCircle2 className="w-3.5 h-3.5 mr-1 inline" /> },
        shipped:    { bg: 'bg-blue-100 text-blue-700',    icon: <Truck className="w-3.5 h-3.5 mr-1 inline" /> },
        processing: { bg: 'bg-orange-100 text-orange-700', icon: <Clock className="w-3.5 h-3.5 mr-1 inline" /> },
        pending:    { bg: 'bg-slate-100 text-slate-700',  icon: <Clock className="w-3.5 h-3.5 mr-1 inline" /> },
    };
    const s = map[status?.toLowerCase()] ?? map.pending;
    return (
        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${s.bg}`}>
            {s.icon}{status}
        </span>
    );
};

const OrderItemsPanel = ({ items }: { items: any[] }) => {
    const [open, setOpen] = useState(false);
    return (
        <div>
            <button
                onClick={() => setOpen(!open)}
                className="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors"
            >
                <Package className="w-4 h-4" />
                {items.length} {items.length === 1 ? 'Item' : 'Items'}
                {open ? <ChevronUp className="w-3.5 h-3.5" /> : <ChevronDown className="w-3.5 h-3.5" />}
            </button>

            {open && (
                <div className="mt-3 space-y-2">
                    {items.map((item) => (
                        <div key={item.id} className="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            {item.image_url ? (
                                <img
                                    src={`/storage${item.image_url}`}
                                    alt={item.product_name}
                                    className="w-12 h-12 rounded-lg object-cover flex-shrink-0"
                                />
                            ) : (
                                <div className="w-12 h-12 rounded-lg bg-slate-200 flex items-center justify-center flex-shrink-0">
                                    <Package className="w-5 h-5 text-slate-400" />
                                </div>
                            )}
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-semibold text-slate-900 truncate">{item.product_name}</p>
                                {item.variant_label && (
                                    <p className="text-xs text-slate-500">{item.variant_label}</p>
                                )}
                                <p className="text-xs text-slate-500 mt-0.5">
                                    Qty: {item.quantity} × ${parseFloat(item.price_at_purchase).toFixed(2)}
                                </p>
                            </div>
                            <p className="text-sm font-semibold text-slate-900 flex-shrink-0">
                                ${parseFloat(item.line_total).toFixed(2)}
                            </p>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

const breadcrumbs = [{ title: 'Orders', href: '/orders' }];

export default function Orders() {
    const { orders } = usePage<{ orders: any }>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Orders | My Account" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">

                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">My Orders</h1>
                        <p className="text-sm text-slate-500 mt-1">View and track your recent purchases</p>
                    </div>
                    <div className="hidden sm:inline-flex items-center justify-center text-sm font-medium bg-white border border-slate-200 text-slate-600 px-3.5 py-1.5 rounded-full shadow-sm">
                        {orders.total} total orders
                    </div>
                </div>

                {/* Empty state */}
                {orders.data.length === 0 && (
                    <div className="flex flex-1 flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-slate-300 py-16 text-slate-500 bg-slate-50/50">
                        <div className="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                            <Package className="h-8 w-8 text-slate-400" />
                        </div>
                        <div className="text-center">
                            <p className="text-lg font-semibold text-slate-900">No orders yet</p>
                            <p className="text-sm mt-1 max-w-sm">When you place orders, they will appear here so you can track their status.</p>
                        </div>
                    </div>
                )}

                {/* Table */}
                {orders.data.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm text-left">
                                <thead className="border-b border-slate-100 bg-slate-50/80">
                                    <tr>
                                        <th className="whitespace-nowrap px-6 py-4 font-semibold text-slate-600">Order #</th>
                                        <th className="whitespace-nowrap px-6 py-4 font-semibold text-slate-600">Date</th>
                                        <th className="whitespace-nowrap px-6 py-4 font-semibold text-slate-600">Items</th>
                                        <th className="whitespace-nowrap px-6 py-4 font-semibold text-slate-600">Delivery</th>
                                        <th className="whitespace-nowrap px-6 py-4 font-semibold text-slate-600">Tracking</th>
                                        <th className="whitespace-nowrap px-6 py-4 font-semibold text-slate-600 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {orders.data.map((order: any) => (
                                        <tr key={order.serial} className="hover:bg-slate-50/80 transition-colors">
                                            <td className="whitespace-nowrap px-6 py-4 font-mono font-medium text-slate-900">
                                                #{order.serial}
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-slate-500">
                                                {new Date(order.created_at).toLocaleDateString('en-US', {
                                                    year: 'numeric', month: 'short', day: 'numeric',
                                                })}
                                            </td>
                                            <td className="px-6 py-4 min-w-[200px]">
                                                <OrderItemsPanel items={order.items} />
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4">
                                                <DeliveryStatusBadge status={order.delivery_status} />
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-slate-500 font-mono text-xs">
                                                {order.tracking_number ?? '—'}
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-right font-semibold text-slate-900">
                                                ${parseFloat(order.total_amount).toLocaleString(undefined, {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                })}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Pagination */}
                {orders.last_page > 1 && (
                    <div className="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 text-sm">
                        <p className="text-slate-500 text-center sm:text-left">
                            Showing <span className="font-semibold text-slate-900">{orders.from}</span> to{' '}
                            <span className="font-semibold text-slate-900">{orders.to}</span> of{' '}
                            <span className="font-semibold text-slate-900">{orders.total}</span> results
                        </p>
                        <div className="flex flex-wrap justify-center gap-1.5">
                            {orders.links.map((link: any, i: number) => (
                                <Link
                                    key={i}
                                    href={link.url ?? '#'}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                    className={`rounded-lg px-3.5 py-2 text-sm transition-all ${
                                        link.active
                                            ? 'bg-blue-600 text-white font-medium shadow-sm pointer-events-none'
                                            : !link.url
                                            ? 'bg-white border border-slate-200 text-slate-400 pointer-events-none opacity-40'
                                            : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900'
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