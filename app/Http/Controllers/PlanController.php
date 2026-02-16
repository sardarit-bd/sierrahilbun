<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index()
    {
        $services = Service::with(['plans' => function ($query) {
            $query->orderBy('base_price_yearly', 'asc');
        }, 'plans.features' => function ($query) {
            $query->orderBy('sort_order', 'asc');
        }])->whereIn('name', ['Lawn', 'Pest', 'Garden'])->get()->keyBy('name');

        return Inertia::render('yard/post', [
            'lawnService' => $services['Lawn'] ?? null,
            'pestService' => $services['Pest'] ?? null,
            'gardenService' => $services['Garden'] ?? null,
        ]);
    }
}
