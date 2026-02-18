<?php

namespace App\Http\Controllers;

use App\Services\Checkout\CheckoutService;
use App\Services\Payment\DTO\PaymentRequestDTO;
use App\Services\Payment\PaymentService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService  $paymentService,
        private readonly CheckoutService $checkoutService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('payment/index');
    }

    public function charge(Request $request)
    {
        $validated = $request->validate([
            'session_id'        => ['required', 'string'],
            'gateway'           => ['required', 'string', 'in:stripe'],
            'currency'          => ['required', 'string', 'size:3'],
            'payment_method_id' => ['required', 'string'],
            'description'       => ['nullable', 'string', 'max:255'],
        ]);

        // Validate & fetch locked session — amount comes from here only
        try {
            $session = $this->checkoutService->getValidSession(
                $validated['session_id'],
                $request->user()->id,
            );
        } catch (\RuntimeException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }

        // Build DTO with server-side total — never from request
        $dto = new PaymentRequestDTO(
            amount:          (float) $session->total,     
            currency:        strtolower($session->currency),
            paymentMethodId: $validated['payment_method_id'],
            description:     $validated['description'] ?? '',
            metadata:        [
                'session_id' => $session->session_id,
                'user_id'    => $request->user()->id,
            ],
        );

        $response = $this->paymentService->charge(
            $validated['gateway'],
            $dto,
            $request->user(),
        );

        // Link transaction to checkout session
        if ($response->transactionId) {
            Transaction::where('transaction_id', $response->transactionId)
                ->update(['checkout_session_id' => $session->session_id]);
        }

        return to_route('payment.pending', [
            'transaction_id' => $response->transactionId,
        ]);
    }

    public function pending(Request $request): Response
    {
        return Inertia::render('payment/pending', [
            'transaction_id' => $request->query('transaction_id'),
        ]);
    }

    public function status(string $transactionId): \Illuminate\Http\JsonResponse
    {
        $transaction = Transaction::where('transaction_id', $transactionId)
            ->select('transaction_id', 'status', 'amount', 'currency')
            ->firstOrFail();

        return response()->json([
            'status'         => $transaction->status,
            'transaction_id' => $transaction->transaction_id,
            'amount'         => $transaction->amount,
            'currency'       => $transaction->currency,
        ]);
    }

    public function success(): Response
    {
        return Inertia::render('payment/success', [
            'transaction_id' => session('transaction_id'),
            'amount'         => session('amount'),
            'currency'       => session('currency'),
        ]);
    }

    public function failed(): Response
    {
        return Inertia::render('payment/failed', [
            'error' => session('error'),
        ]);
    }
}