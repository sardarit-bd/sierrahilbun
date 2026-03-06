<?php

namespace App\Http\Controllers;

use App\Services\Checkout\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'items'                               => ['required', 'array', 'min:1'],
            'items.*.type'                        => ['required', 'string', 'in:product,plan,garden'],
            'items.*.product_id'                  => ['required_if:items.*.type,product', 'nullable', 'integer', 'exists:products,id'],
            'items.*.plan_id'                     => ['required_if:items.*.type,plan',    'nullable', 'integer', 'exists:plans,id'],
            'items.*.assessment_id'               => ['nullable', 'string', 'exists:yard_assessments,id'],
            'items.*.garden_products'             => ['required_if:items.*.type,garden',  'nullable', 'array'],
            'items.*.garden_products.garden_size' => ['required_if:items.*.type,garden',  'nullable', 'string', 'in:xs,sm,l'],
            'items.*.quantity'                    => ['required', 'integer', 'min:1', 'max:100'],
            'promo_code'                          => ['nullable', 'string', 'max:20'],
            'currency'                            => ['nullable', 'string', 'size:3'],
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