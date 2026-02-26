
import { useState } from 'react';
import { ChevronDown, ChevronUp } from 'lucide-react';
import type { OrderItem } from '@/types/order';

export function OrderItemsDrawer({ items }: { items: OrderItem[] }) {
    const [open, setOpen] = useState(false);

    return (
        <div>
            <button
                onClick={() => setOpen(!open)}
                className="flex items-center gap-1 text-sm text-blue-600 hover:underline"
            >
                {items.length} item{items.length !== 1 ? 's' : ''}
                {open ? <ChevronUp className="h-3 w-3" /> : <ChevronDown className="h-3 w-3" />}
            </button>

            {open && (
                <ul className="mt-2 space-y-1 rounded-md border bg-muted/40 p-3">
                    {items.map((item) => (
                        <li key={item.id} className="flex justify-between text-sm">
                            <span>
                                <span className="font-medium">{item.product_name}</span>
                                {item.variant_label && (
                                    <span className="ml-1 text-muted-foreground">
                                        ({item.variant_label})
                                    </span>
                                )}
                                <span className="ml-2 text-muted-foreground">× {item.quantity}</span>
                            </span>
                            <span className="font-medium">
                                ${(parseFloat(item.price_at_purchase) * item.quantity).toFixed(2)}
                            </span>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}