<?php

namespace App\Http\Controllers;

use App\Services\BlogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    protected BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    public function index(Request $request)
    {
        $filters = [
            'category' => $request->query('category'),
            'search' => $request->query('search'),
        ];

        $data = $this->blogService->getBlogIndexData($filters);

        return Inertia::render('blogs', [
            'posts' => $data['posts'],
            'categories' => $data['categories'],
            'filters' => $filters,
        ]);
    }

    public function show(string $slug)
    {
        $post = $this->blogService->getBlogDetails($slug);

        return Inertia::render('blogs/show', [
            'post' => $post,
        ]);
    }
}
