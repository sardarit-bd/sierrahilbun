<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->forUser($request->user()->id)
            ->with([
                'transaction',
                'items.variant.product.images',
            ])
            ->latest()
            ->paginate(10)
            ->through(fn (Order $order) => [
                'id'              => $order->id,
                'transaction_id'  => $order->transaction?->transaction_id,
                'total_amount'    => $order->total_amount,
                'status'          => $order->status,
                'delivery_status' => $order->delivery_status,
                'tracking_number' => $order->tracking_number,
                'created_at'      => $order->created_at->toDateTimeString(),
                'shipping_address'=> $order->shipping_address_json,
                'items'           => $order->items->map(fn ($item) => [
                    'id'                => $item->id,
                    'item_type'         => $item->item_type,
                    'quantity'          => $item->quantity,
                    'price_at_purchase' => $item->price_at_purchase,
                    'line_total'        => $item->quantity * $item->price_at_purchase,
                    'product_name'      => $item->variant?->product?->name ?? 'Unknown Product',
                    'variant_label'     => $item->variant?->size_label ?? '',
                    'variant_sku'       => $item->variant?->sku ?? '',
                    'image_url'         => $item->variant?->product?->images
                                            ->firstWhere('is_primary', true)?->image_url
                                            ?? $item->variant?->product?->images->first()?->image_url,
                ]),
            ]);

        return Inertia::render('front/orders', [
            'orders' => $orders,
        ]);
    }
}