<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Services\ProductService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function (ProductService $service) {
    return Inertia::render('home', [
        'canRegister'     => Features::enabled(Features::registration()),
        'featuredProducts' => $service->getFeaturedProducts(),
    ]);
})->name('home');


// product
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');


// product review
Route::get('/products/{slug}/review', [ReviewController::class, 'create'])->name('product.review');
Route::post('/products/{slug}/review', [ReviewController::class, 'store'])->name('product.review.store');


// helpful review
Route::post('/reviews/{review}/helpful', [ReviewController::class, 'toggleHelpful'])
    ->name('reviews.helpful')
    ->middleware('auth');




// get your plan
Route::get('/yard-issue', function () {
    return Inertia::render('yard-issue');
});
Route::get('/yard/plan', [PlanController::class, 'index']);
Route::get('/lawn-size', function () {
    return Inertia::render('lawn-size');
});
Route::get('/lawns/post', function () {
    return Inertia::render('lawns/post');
});
Route::get('/lawns/questions', function () {
    return Inertia::render('lawns/questions/post');
});



Route::get('/custom-lawn', function () {
    return Inertia::render('CustomLawnPlan');
})->name('custom-lawn');

// blog
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Route::get('/blogs/{slug}', function ($slug) {
//     // $post = BlogPost::where('slug', $slug)->firstOrFail();

//     return Inertia::render('blogs/post', [
//         'slug' => $slug,
//         // 'post' => $post 
//     ]);
// })->name('blogs.post');


// cart
Route::get('/cart', function () {
    return Inertia::render('cart');
})->name('cart');

Route::get('/privacy', function () {
    return Inertia::render('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return Inertia::render('terms');
})->name('terms');

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// payment gateway & checkout
Route::middleware(['auth'])->group(function () {
    Route::get('/payment',                          [PaymentController::class, 'index'])->name('payment.index');
    Route::post('/payment/charge',                  [PaymentController::class, 'charge'])->name('payment.charge');
    Route::get('/payment/pending',                  [PaymentController::class, 'pending'])->name('payment.pending');
    Route::get('/payment/success',                  [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed',                   [PaymentController::class, 'failed'])->name('payment.failed');
    Route::get('/payment/status/{transactionId}',   [PaymentController::class, 'status'])->name('payment.status');

    // checkout
    Route::post('/checkout',              [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/checkout/{sessionId}',   [CheckoutController::class, 'show'])->name('checkout.show');

});

require __DIR__.'/settings.php';
