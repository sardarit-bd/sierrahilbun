<?php

namespace App\Http\Controllers;

use App\Services\Payment\Contracts\WebhookHandlerInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookHandlerInterface $webhookHandler,
    ) {}

    public function stripe(Request $request): Response
    {
        if (!$this->webhookHandler->verify($request)) {
            return response('Unauthorized', 401);
        }

        try {
            $this->webhookHandler->handle($request);
        } catch (\Throwable $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
            ]);

            return response('Webhook Error', 200);
        }

        return response('OK', 200);
    }
}