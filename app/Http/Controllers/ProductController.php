<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $service
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('products', [
            'products' => $this->service->getShopList($request->all()),
            'categories' => ProductCategory::select('name', 'slug')->get(),
            'filters' => $request->only(['sort', 'category']),
        ]);
    }

    public function show(string $slug): Response
    {
        // 1. Get the formatted product data
        $productData = $this->service->getProductDetails($slug);

        // 2. Return the React page
        return Inertia::render('Product/Show', [
            'product' => $productData,
        ]);
    }
}