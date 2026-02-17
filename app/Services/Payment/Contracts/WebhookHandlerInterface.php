<?php

namespace App\Services\Payment\Contracts;

use Illuminate\Http\Request;

interface WebhookHandlerInterface
{
    public function handle(Request $request): void;
    public function verify(Request $request): bool;
}