<?php

namespace App\Http\Controllers;

use App\Services\ProductSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct(
        protected ProductSearchService $searchService
    ) {}

    public function products(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => [], 'total' => 0]);
        }

        $results = $this->searchService->search($query);

        return response()->json([
            'results' => $results,
            'total'   => count($results),
        ]);
    }
}