<?php

namespace App\Repositories\Contracts;

interface BlogRepositoryInterface
{
    public function getPublishedPosts(array $filters = []);
    public function getCategories();
    public function findBySlug(string $slug);
}
