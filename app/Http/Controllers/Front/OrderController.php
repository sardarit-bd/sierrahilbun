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
                'items.variant.product', 
            ])
            ->latest()
            ->paginate(10)
            ->through(fn (Order $order) => [
                'id'               => $order->id,
                'transaction_id'   => $order->transaction_id,
                'total_amount'     => $order->total_amount,
                'status'           => $order->status,
                'created_at'       => $order->created_at->toDateTimeString(),
                'shipping_address' => $order->shipping_address_json,
                'items'            => $order->items->map(fn ($item) => [
                    'id'                 => $item->id,
                    'quantity'           => $item->quantity,
                    'price_at_purchase'  => $item->price_at_purchase,
                    'product_name'       => $item->variant?->product?->name ?? 'Unknown Product',
                    'variant_label'      => $item->variant?->label ?? '', // e.g. "Red / XL"
                    'variant_sku'        => $item->variant?->sku ?? '',
                ]),
            ]);

        return Inertia::render('front/orders', [
            'orders' => $orders,
        ]);
    }
}