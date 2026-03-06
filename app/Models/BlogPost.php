<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class BlogPost extends Model
{
    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image_url',
        'status',       
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        // Auto-set published_at when status becomes published
        static::saving(function (BlogPost $post) {
            if ($post->status === 'published' && is_null($post->published_at)) {
                $post->published_at = now();
            }

            // Clear published_at if reverted to draft
            if (in_array($post->status, ['draft', 'review'])) {
                $post->published_at = null;
            }
        });

        static::saved(function () {
            app(CacheService::class)->flush(['blogs']);
        });

        static::deleted(function () {
            app(CacheService::class)->flush(['blogs']);
        });
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tags', 'post_id', 'tag_id');
    }
}