<?php

namespace App\Http\Controllers;

use App\Services\Checkout\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
    ) {}

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'integer', 'min:1', 'max:100'],
            'promo_code'          => ['nullable', 'string', 'max:20'],
            'currency'            => ['nullable', 'string', 'size:3'],
        ]);

        try {
            $session = $this->checkoutService->createSession(
                userId:    $request->user()->id,
                cartItems: $validated['items'],
                promoCode: $validated['promo_code'] ?? null,
                currency:  $validated['currency']   ?? 'USD',
            );

            return response()->json([
                'session_id'      => $session->session_id,
                'subtotal'        => $session->subtotal,
                'discount_amount' => $session->discount_amount,
                'shipping_cost'   => $session->shipping_cost,
                'tax_amount'      => $session->tax_amount,
                'total'           => $session->total,
                'currency'        => $session->currency,
                'expires_at'      => $session->expires_at,
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, string $sessionId): Response
    {
        try {
            $session = $this->checkoutService->getValidSession(
                $sessionId,
                $request->user()->id,
            );
        } catch (\RuntimeException $e) {
            return Inertia::render('payment/session-expired');
        }

        return Inertia::render('payment/index', [
            'session_id'      => $session->session_id,
            'subtotal'        => $session->subtotal,
            'discount_amount' => $session->discount_amount,
            'shipping_cost'   => $session->shipping_cost,
            'tax_amount'      => $session->tax_amount,
            'total'           => $session->total,
            'currency'        => $session->currency,
            'expires_at'      => $session->expires_at,
        ]);
    }
}