<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Payment\DTO\PaymentRequestDTO;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('payment/index');
    }

    public function charge(Request $request)
    {
        $validated = $request->validate([
            'gateway'           => ['required', 'string', 'in:stripe'],
            'amount'            => ['required', 'numeric', 'min:0.5'],
            'currency'          => ['required', 'string', 'size:3'],
            'payment_method_id' => ['required', 'string'],
            'description'       => ['nullable', 'string', 'max:255'],
        ]);

        $dto = new PaymentRequestDTO(
            amount:          $validated['amount'],
            currency:        strtolower($validated['currency']),
            paymentMethodId: $validated['payment_method_id'],
            description:     $validated['description'] ?? '',
        );

        $response = $this->paymentService->charge(
            $validated['gateway'],
            $dto,
            $request->user(),
        );

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