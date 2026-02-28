<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            auth()->user()->shippingAddresses()->orderByDesc('is_default')->get()
        );
    }

    // POST /api/shipping-addresses
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
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
        ]);

        $user = auth()->user();

        // If setting as default, clear existing default first
        if (!empty($data['is_default'])) {
            $user->shippingAddresses()->update(['is_default' => false]);
        }

        // First address is always default
        if ($user->shippingAddresses()->count() === 0) {
            $data['is_default'] = true;
        }

        $address = $user->shippingAddresses()->create($data);

        return response()->json($address, 201);
    }

    // PUT /api/shipping-addresses/{address}
    public function update(Request $request, ShippingAddress $address): JsonResponse
    {
        abort_if($address->user_id !== auth()->id(), 403);

        $data = $request->validate([/* same rules */]);

        if (!empty($data['is_default'])) {
            auth()->user()->shippingAddresses()->update(['is_default' => false]);
        }

        $address->update($data);
        return response()->json($address);
    }
}
