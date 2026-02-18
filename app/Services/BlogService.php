<?php

namespace App\Services;

use App\Repositories\Contracts\BlogRepositoryInterface;
use App\Services\CacheService;

class BlogService
{
    public function __construct(
        protected BlogRepositoryInterface $blogRepository,
        protected CacheService $cacheService
    ) {}

    public function getBlogIndexData(array $filters = [])
    {
        $cacheKey = 'blogs:index:' . md5(json_encode($filters));

        return $this->cacheService->remember(
            $cacheKey,
            now()->addMinutes(60),
            function () use ($filters) {

               $posts = $this->blogRepository->getPublishedPosts($filters)
                    ->through(function ($post) {
                        return [
                            'id' => $post->id,
                            'title' => $post->title,
                            'slug' => $post->slug,
                            'excerpt' => $post->excerpt,
                            'image' => $post->featured_image_url 
                                ? asset('storage/' . $post->featured_image_url) 
                                : null,
                            'category' => $post->category?->name,
                            'category_slug' => $post->category?->slug,
                            'author' => $post->author?->name,
                            'date' => optional($post->published_at)->format('M d, Y'),
                            'featured' => (bool) $post->is_featured,
                        ];
                    })
                    ->toArray();

                $categories = $this->blogRepository->getCategories();

                return [
                    'posts' => $posts,
                    'categories' => collect([['name' => 'All', 'slug' => 'all']])->merge($categories)->values(),
                ];
            },
            ['blogs']
        );
    }


    public function getBlogDetails(string $slug)
    {
        $cacheKey = "blogs:show:{$slug}";

        return $this->cacheService->remember(
            $cacheKey,
            now()->addMinutes(60),
            function () use ($slug) {

                $post = $this->blogRepository->findBySlug($slug);

                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'content' => $post->content,
                    'image' => $post->featured_image_url 
                                ? asset('storage/' . $post->featured_image_url) 
                                : null,
                    'author' => $post->author?->name,
                    'category' => $post->category?->name,
                    'category_slug' => $post->category?->slug,
                    'date' => optional($post->published_at)->format('M d, Y'),
                    'tags' => $post->tags->pluck('name'),
                ];
            },
            ['blogs']
        );
    }
}
