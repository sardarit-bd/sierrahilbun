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

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
