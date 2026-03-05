<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingAddressController extends Controller
{
    // Shared validation rules
    private function rules(): array
    {
        return [
            'label'         => 'nullable|string|max:50',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'state'         => 'required|string|size:2',
            'zip_code'      => 'required|string|max:10',
            'is_default'    => 'boolean',
        ];
    }

    public function index(): JsonResponse
    {
        return response()->json(
            auth()->user()->shippingAddresses()->orderByDesc('is_default')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        $user = auth()->user();

        // First address is always default — check BEFORE any clearing
        $isFirst = $user->shippingAddresses()->count() === 0;

        if ($isFirst || !empty($data['is_default'])) {
            $data['is_default'] = true;
            $user->shippingAddresses()->update(['is_default' => false]);
        }

        $address = $user->shippingAddresses()->create($data);

        return response()->json($address, 201);
    }

    public function update(Request $request, ShippingAddress $address): JsonResponse
    {
        abort_if($address->user_id !== auth()->id(), 403);

        Log::info('Shipping update payload', $request->all()); 

        $data = $request->validate($this->rules());

        if (!empty($data['is_default'])) {
            auth()->user()->shippingAddresses()->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json($address->fresh());
    }
}