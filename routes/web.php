<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('home', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/products', function () {
    return Inertia::render('products');
})->name('products');

Route::get('/custom-lawn', function () {
    return Inertia::render('CustomLawnPlan');
})->name('custom-lawn');

Route::get('/blogs', function () {
    return Inertia::render('blogs');
})->name('blogs');

Route::get('/blogs/{slug}', function ($slug) {
    // $post = BlogPost::where('slug', $slug)->firstOrFail();

    return Inertia::render('blogs/post', [
        'slug' => $slug,
        // 'post' => $post 
    ]);
})->name('blogs.post');

Route::get('/cart', function () {
    return Inertia::render('cart');
})->name('cart');

Route::get('/test-cart', function () {
    return Inertia::render('TestCart');
})->name('test.cart');

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
