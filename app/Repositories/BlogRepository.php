<?php

namespace App\Repositories;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Repositories\Contracts\BlogRepositoryInterface;

class BlogRepository implements BlogRepositoryInterface
{
    public function getPublishedPosts(array $filters = [])
    {
        $query = BlogPost::with(['category', 'author'])
            ->where('is_published', true)
            ->orderBy('published_at', 'desc');

        if (!empty($filters['category']) && $filters['category'] !== 'All') {
            $query->whereHas('category', fn($q) => $q->where('name', $filters['category']));
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate(12); // pagination example
    }

    public function getCategories()
    {
        return BlogCategory::orderBy('name')->get(['name', 'slug']);
    }


    public function findBySlug(string $slug): BlogPost
    {
        return BlogPost::with(['category', 'author', 'tags'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();
    }
}
