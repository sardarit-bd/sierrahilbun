<?php

use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Front\DashboardController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Lawn\CategoryController;
use App\Http\Controllers\Lawn\GardenQuizController;
use App\Http\Controllers\Lawn\LawnSizeController;
use App\Http\Controllers\Lawn\LocationController;
use App\Http\Controllers\Lawn\PlanController;
use App\Http\Controllers\Lawn\QuestionnaireController;
use App\Http\Controllers\Lawn\SoilProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShippingAddressController;
use App\Services\ProductService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function (ProductService $service) {
    return Inertia::render('home', [
        'canRegister'      => Features::enabled(Features::registration()),
        'featuredProducts' => $service->getFeaturedProducts(),
    ]);
})->name('home');

// search
Route::get('/search/products', [SearchController::class, 'products'])
    ->name('search.products')
    ->middleware('throttle:30,1');


// frontend
Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders',    [OrderController::class, 'index'])->name('orders.index');
});


// OAuth
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('{provider}/redirect', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('{provider}/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
});

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Product Reviews
Route::get('/products/{slug}/review', [ReviewController::class, 'create'])->name('product.review');
Route::post('/products/{slug}/review', [ReviewController::class, 'store'])->name('product.review.store');
Route::post('/reviews/{review}/helpful', [ReviewController::class, 'toggleHelpful'])
    ->name('reviews.helpful')
    ->middleware('auth');

// -------------------------------------------------------
// Lawn Assessment Flow 
// -------------------------------------------------------

// Zip Code Entry
Route::get('/custom-lawn', [LocationController::class, 'show'])->name('yard.start');
Route::post('/custom-lawn', [LocationController::class, 'store'])->name('yard.start.store');

// Service Selection
Route::get('/yard-issue', [CategoryController::class, 'show'])->name('yard.category');
Route::post('/yard-issue', [CategoryController::class, 'store'])->name('yard.category.store');

// Lawn Size
Route::get('/lawn-size', [LawnSizeController::class, 'show'])->name('yard.size');
Route::post('/lawn-size', [LawnSizeController::class, 'store'])->name('yard.size.store');
Route::post('/lawn-size/confirm', [LawnSizeController::class, 'confirm'])->name('yard.size.confirm');

// Soil Profile Visualization
Route::get('/lawns/post', [SoilProfileController::class, 'show'])->name('yard.soil');

// Quiz
Route::get('/lawns/questions', [QuestionnaireController::class, 'show'])->name('yard.quiz');
Route::post('/lawns/questions', [QuestionnaireController::class, 'store'])->name('yard.quiz.store');

// Generated Plan
Route::get('/yard/plan', [PlanController::class, 'show'])->name('yard.plan');
Route::post('/yard/garden-quiz', [GardenQuizController::class, 'store'])->name('yard.garden-quiz');


// Blog
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Cart
Route::get('/cart', function () {
    return Inertia::render('cart');
})->name('cart');
Route::post('/promo/validate', [PromoCodeController::class, 'validate'])->name('promo.validate');

// Static Pages
Route::get('/privacy', function () {
    return Inertia::render('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return Inertia::render('terms');
})->name('terms');


Route::middleware(['auth'])->group(function () {

    // Payment & Checkout
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::post('/payment/charge', [PaymentController::class, 'charge'])->name('payment.charge');
    Route::get('/payment/pending', [PaymentController::class, 'pending'])->name('payment.pending');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');
    Route::get('/payment/status/{transactionId}', [PaymentController::class, 'status'])->name('payment.status');

    Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/checkout/{sessionId}', [CheckoutController::class, 'show'])->name('checkout.show');


    // shipping address
    Route::get('/api/shipping-addresses',           [ShippingAddressController::class, 'index']);
    Route::post('/api/shipping-addresses',          [ShippingAddressController::class, 'store']);
    Route::put('/api/shipping-addresses/{address}', [ShippingAddressController::class, 'update']);
});

require __DIR__.'/settings.php';