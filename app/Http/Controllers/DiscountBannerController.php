<?php

namespace App\Http\Controllers;

use App\Models\DiscountBanner;
use Illuminate\Http\Request;

class DiscountBannerController extends Controller
{
    public function index()
    {
        return response()->json(DiscountBanner::getActive());
    }

    public function store(Request $request)
    {
        $request->validate([
            'text'       => 'required|string|max:255',
            'promo_code' => 'nullable|string|max:50',
            'is_active'  => 'boolean',
        ]);

        $banner = DiscountBanner::create($request->only('text', 'promo_code', 'is_active'));

        return response()->json($banner, 201);
    }

    public function update(Request $request, DiscountBanner $discountBanner)
    {
        $request->validate([
            'text'       => 'sometimes|string|max:255',
            'promo_code' => 'nullable|string|max:50',
            'is_active'  => 'boolean',
        ]);

        $discountBanner->update($request->only('text', 'promo_code', 'is_active'));

        return response()->json($discountBanner);
    }

    public function destroy(DiscountBanner $discountBanner)
    {
        $discountBanner->delete();

        return response()->json(['message' => 'Banner deleted.']);
    }
}