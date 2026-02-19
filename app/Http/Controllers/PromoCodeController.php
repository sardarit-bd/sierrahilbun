<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function validate(Request $request)
    {
        $request->validate([
            'code'     => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $promo = PromoCode::whereRaw('UPPER(code) = ?', [strtoupper($request->code)])->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promo code.',
            ], 422);
        }

        $validity = $promo->isValid((float) $request->subtotal);

        if (!$validity['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validity['message'],
            ], 422);
        }

        $discount = $promo->calculateDiscount((float) $request->subtotal);

        return response()->json([
            'success'  => true,
            'message'  => 'Promo code applied successfully!',
            'promo'    => [
                'code'     => $promo->code,
                'type'     => $promo->type,
                'value'    => $promo->value,
                'discount' => $discount,
            ],
        ]);
    }
}