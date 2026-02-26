// resources/js/types/order.d.ts

export interface OrderItem {
    id: number;
    quantity: number;
    price_at_purchase: string;       // decimal comes as string from Laravel
    product_name: string;
    variant_label: string;
    variant_sku: string;
}

export interface ShippingAddress {
    name?: string;
    line1?: string;
    line2?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
}

export interface Order {
    id: number;
    transaction_id: number | null;
    total_amount: string;
    status: 'paid' | 'processing' | 'shipped' | string;
    created_at: string;
    shipping_address: ShippingAddress | null;
    items: OrderItem[];
}

export interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
}