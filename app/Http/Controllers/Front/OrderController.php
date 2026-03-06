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
        $page    = request()->input('page', 1);
        $perPage = 10;

        $orders = Order::query()
            ->forUser($request->user()->id)
            ->with([
                'transaction',
                'items.variant.product.images',
            ])
            ->latest()
            ->paginate($perPage)
            ->through(function (Order $order) use (&$page, $perPage) {
                static $index = 0;
                $index++;
                $serial = (($page - 1) * $perPage) + $index;

                return [
                    'serial'           => $serial,
                    'transaction_id'   => $order->transaction?->transaction_id,
                    'total_amount'     => $order->total_amount,
                    'status'           => $order->status,
                    'delivery_status'  => $order->delivery_status,
                    'tracking_number'  => $order->tracking_number,
                    'created_at'       => $order->created_at->toDateTimeString(),
                    'shipping_address' => $order->shipping_address_json,
                    'items'            => $order->items->map(fn ($item) => [
                        'id'                => $item->id,
                        'item_type'         => $item->item_type,
                        'quantity'          => $item->quantity,
                        'price_at_purchase' => $item->price_at_purchase,
                        'line_total'        => $item->quantity * $item->price_at_purchase,

                        // ── Name ────────────────────────────────────────────
                        // For products: use the product name from the relationship.
                        // For plans / garden: use the denormalized display_name
                        // saved at order-creation time (no join needed).
                        'product_name' => $item->item_type === 'product'
                            ? ($item->variant?->product?->name ?? $item->display_name ?? 'Unknown Product')
                            : ($item->display_name ?? ucfirst($item->item_type) . ' Item'),

                        // ── Image ────────────────────────────────────────────
                        // For products: resolve via the product images relationship.
                        // For plans / garden: use the denormalized display_image.
                        'image_url' => $item->item_type === 'product'
                            ? $this->resolveProductImage($item)
                            : ($item->display_image ?? null),

                        'variant_label' => $item->variant?->size_label ?? '',
                        'variant_sku'   => $item->variant?->sku        ?? '',
                    ]),
                ];
            });

        return Inertia::render('front/orders', [
            'orders' => $orders,
        ]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function resolveProductImage($item): ?string
    {
        $images = $item->variant?->product?->images;

        if (!$images || $images->isEmpty()) {
            return null;
        }

        $url = $images->firstWhere('is_primary', true)?->image_url
            ?? $images->first()?->image_url;

        if (!$url) {
            return null;
        }

        return str_starts_with($url, 'http') ? $url : '/storage/' . ltrim($url, '/');
    }
}