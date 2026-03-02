import React, { useState } from 'react';
import { MapPin, Pencil, Package, ChevronRight, CheckCircle2, Clock, Truck, X } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

// --- Canvas Preview Mocks & Utilities ---
// These replace the missing Inertia and internal project imports to allow rendering in this environment.

const Head = ({ title }: { title: string }) => {
    React.useEffect(() => {
        document.title = title;
    }, [title]);
    return null;
};


const OrderStatusBadge = ({ status }: { status: string }) => {
    let bg = 'bg-slate-100 text-slate-700';
    let icon = null;
    
    switch(status.toLowerCase()) {
        case 'delivered':
            bg = 'bg-green-100 text-green-700';
            icon = <CheckCircle2 className="w-3.5 h-3.5 mr-1 inline" />;
            break;
        case 'shipped':
            bg = 'bg-blue-100 text-blue-700';
            icon = <Truck className="w-3.5 h-3.5 mr-1 inline" />;
            break;
        case 'processing':
            bg = 'bg-orange-100 text-orange-700';
            icon = <Clock className="w-3.5 h-3.5 mr-1 inline" />;
            break;
    }
    
    return (
        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${bg}`}>
            {icon}
            {status}
        </span>
    );
};

const OrderItemsDrawer = ({ items }: { items: any[] }) => (
    <button className="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
        <Package className="w-4 h-4" />
        {items.length} {items.length === 1 ? 'Item' : 'Items'}
    </button>
);

// Mock Data
const mockOrders = {
    total: 3,
    from: 1,
    to: 3,
    last_page: 1,
    links: [{ url: '#', label: '&laquo; Previous', active: false }, { url: '#', label: '1', active: true }, { url: null, label: 'Next &raquo;', active: false }],
    data: [
        { id: "ORD-9021", created_at: "2026-02-25T10:00:00Z", status: "Delivered", total_amount: "245.50", items: [{}, {}, {}] },
        { id: "ORD-8734", created_at: "2026-02-18T14:30:00Z", status: "Shipped", total_amount: "89.99", items: [{}] },
        { id: "ORD-7642", created_at: "2026-01-30T09:15:00Z", status: "Processing", total_amount: "1,299.00", items: [{}, {}] },
    ]
};

const breadcrumbs = [
    { title: 'Orders', href: '/orders' },
];

const initialAddress = {
    name: "John Doe",
    street: "123 Premium Avenue, Suite 400",
    city: "San Francisco",
    state: "CA",
    zip: "94107",
    country: "United States",
    phone: "+1 (555) 123-4567"
};

export default function App() {
    const orders = mockOrders;
    
    // Address State
    const [currentAddress, setCurrentAddress] = useState(initialAddress);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [formData, setFormData] = useState(initialAddress);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSaveAddress = () => {
        setCurrentAddress(formData);
        setIsEditModalOpen(false);
        // Here you would typically make an API call using router.post() or router.put()
    };

    const handleOpenModal = () => {
        setFormData(currentAddress); // Reset form data to current address when opening
        setIsEditModalOpen(true);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Orders | My Account" />
            
            <div className="flex h-full flex-1 flex-col gap-6 p-4">

                {/* Header Section */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">My Orders</h1>
                        <p className="text-sm text-slate-500 mt-1">View and track your recent purchases</p>
                    </div>
                    <div className="hidden sm:inline-flex items-center justify-center text-sm font-medium bg-white border border-slate-200 text-slate-600 px-3.5 py-1.5 rounded-full shadow-sm">
                        {orders.total} total orders
                    </div>
                </div>

                {/* Shipping Address Section - Premium UI */}
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 rounded-2xl border border-slate-200 bg-white p-5 md:p-6 shadow-sm transition-all hover:shadow-md">
                    <div className="flex items-start gap-4 md:gap-5 w-full">
                        <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            <MapPin className="h-6 w-6" />
                        </div>
                        <div className="flex flex-col flex-1">
                            <div className="flex items-center justify-between w-full mb-1">
                                <h2 className="text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    Default Shipping Address
                                </h2>
                                {/* Mobile edit button integrated into header */}
                                <button 
                                    onClick={handleOpenModal}
                                    className="sm:hidden text-slate-400 hover:text-blue-600 transition-colors p-1 rounded-full hover:bg-slate-50"
                                >
                                    <Pencil className="h-4 w-4" />
                                </button>
                            </div>
                            <p className="text-base md:text-lg font-semibold text-slate-900">
                                {currentAddress.name}
                            </p>
                            <p className="text-sm text-slate-600 mt-0.5">
                                {currentAddress.street}
                            </p>
                            <p className="text-sm text-slate-600">
                                {currentAddress.city}, {currentAddress.state} {currentAddress.zip}
                            </p>
                            <p className="text-sm text-slate-500 mt-2 font-medium">
                                {currentAddress.phone}
                            </p>
                        </div>
                    </div>
                    
                    {/* Desktop edit button */}
                    <button 
                        onClick={handleOpenModal}
                        className="hidden sm:inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <Pencil className="h-4 w-4 text-slate-400" />
                        Edit Address
                    </button>
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
                        <button className="mt-2 rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-medium text-white shadow transition-colors hover:bg-slate-800 focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                            Start Shopping
                        </button>
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
                                        <th className="whitespace-nowrap px-6 py-4 font-semibold text-slate-600">Status</th>
                                        <th className="whitespace-nowrap px-6 py-4 font-semibold text-slate-600 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {orders.data.map((order) => (
                                        <tr key={order.id} className="hover:bg-slate-50/80 transition-colors group cursor-pointer">
                                            <td className="whitespace-nowrap px-6 py-4 font-mono font-medium text-slate-900">
                                                {order.id}
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-slate-500">
                                                {new Date(order.created_at).toLocaleDateString('en-US', {
                                                    year: 'numeric', month: 'short', day: 'numeric',
                                                })}
                                            </td>
                                            <td className="px-6 py-4">
                                                <OrderItemsDrawer items={order.items} />
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4">
                                                <OrderStatusBadge status={order.status} />
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-right font-semibold text-slate-900">
                                                ${parseFloat(order.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
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
                            Showing <span className="font-semibold text-slate-900">{orders.from}</span> to <span className="font-semibold text-slate-900">{orders.to}</span> of <span className="font-semibold text-slate-900">{orders.total}</span> results
                        </p>
                        <div className="flex flex-wrap justify-center gap-1.5">
                            {orders.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                    className={`rounded-lg px-3.5 py-2 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 ${
                                        link.active
                                            ? 'bg-blue-600 text-white font-medium shadow-sm'
                                            : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 disabled:opacity-40 disabled:hover:bg-white disabled:cursor-not-allowed'
                                    }`}
                                />
                            ))}
                        </div>
                    </div>
                )}

            </div>

            {/* Address Edit Modal */}
            {isEditModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity">
                    <div className="bg-white rounded-2xl shadow-xl w-full max-w-lg flex flex-col max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                        {/* Modal Header */}
                        <div className="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">Edit Shipping Address</h3>
                            <button 
                                onClick={() => setIsEditModalOpen(false)} 
                                className="text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-1.5 rounded-full transition-colors"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        
                        {/* Modal Body / Form */}
                        <div className="p-6 overflow-y-auto flex-1 custom-scrollbar">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="sm:col-span-2">
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                                    <input 
                                        type="text" 
                                        name="name"
                                        value={formData.name}
                                        onChange={handleInputChange}
                                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-shadow" 
                                    />
                                </div>
                                <div className="sm:col-span-2">
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Street Address</label>
                                    <input 
                                        type="text" 
                                        name="street"
                                        value={formData.street}
                                        onChange={handleInputChange}
                                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-shadow" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">City</label>
                                    <input 
                                        type="text" 
                                        name="city"
                                        value={formData.city}
                                        onChange={handleInputChange}
                                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-shadow" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">State / Province</label>
                                    <input 
                                        type="text" 
                                        name="state"
                                        value={formData.state}
                                        onChange={handleInputChange}
                                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-shadow" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">ZIP / Postal Code</label>
                                    <input 
                                        type="text" 
                                        name="zip"
                                        value={formData.zip}
                                        onChange={handleInputChange}
                                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-shadow" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Country</label>
                                    <input 
                                        type="text" 
                                        name="country"
                                        value={formData.country}
                                        onChange={handleInputChange}
                                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-shadow" 
                                    />
                                </div>
                                <div className="sm:col-span-2">
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                                    <input 
                                        type="tel" 
                                        name="phone"
                                        value={formData.phone}
                                        onChange={handleInputChange}
                                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-shadow" 
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Modal Footer */}
                        <div className="border-t border-slate-100 px-6 py-4 bg-slate-50/80 flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-2">
                            <button 
                                onClick={() => setIsEditModalOpen(false)} 
                                className="w-full sm:w-auto px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-200"
                            >
                                Cancel
                            </button>
                            <button 
                                onClick={handleSaveAddress} 
                                className="w-full sm:w-auto px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}