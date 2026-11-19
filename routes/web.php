<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\OrderSlipController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'home'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{category:slug}', [ShopController::class, 'index'])->name('category');
Route::get('/product/{product:slug}', [ShopController::class, 'show'])->name('product');
Route::get('/contact', [ShopController::class, 'contact'])->name('contact');

Route::get('/lang/{locale}', LocaleController::class)->whereIn('locale', ['bn', 'en'])->name('locale.switch');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/feeds/meta.xml', [FeedController::class, 'meta'])->middleware('throttle:30,1')->name('feeds.meta');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/cart/{product:slug}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{product:slug}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product:slug}', [CartController::class, 'remove'])->name('cart.remove');
});

// Checkout
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::get('/checkout/quote', [CheckoutController::class, 'quote'])->middleware('throttle:60,1')->name('checkout.quote');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/order/{order:number}', [CheckoutController::class, 'confirmation'])->name('orders.show');

// Customer auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
    Route::post('/wishlist/{product:slug}', [AccountController::class, 'toggleWishlist'])->middleware('throttle:60,1')->name('wishlist.toggle');
    Route::post('/product/{product:slug}/reviews', [AccountController::class, 'review'])->middleware('throttle:5,1')->name('reviews.store');

    Route::get('/manage/orders/{order:number}/slip', OrderSlipController::class)->name('admin.orders.slip');
});
