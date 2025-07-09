<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;

use App\Http\Controllers\Api\PaymentCallbackController;

Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/sellers/{id}', [SellerController::class, 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/users', [UserController::class, 'oauthUser']);
    Route::get('/sellers', [SellerController::class, 'oauthSeller']);
    Route::post('/product/sync',[ProductSyncController::class,'sync']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/order/{id}', [OrderController::class, 'show']);

    Route::post('/products/{id}/add-to-cart', [CartController::class, 'addToCart']);

    Route::get('/cart', [CartController::class, 'index']);               // Lihat isi keranjang
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);     // Hapus produk dari keranjang
    Route::post('/cart/checkout', [CartController::class, 'checkout']);  // Checkout
    Route::patch('/cart/product/{product_id}/increase', [CartController::class, 'increaseByProduct']);
    Route::patch('/cart/product/{product_id}/decrease', [CartController::class, 'decreaseByProduct']);
    Route::delete('/clear-cart', [CartController::class, 'clear']);
});

Route::post('/midtrans/callback', [PaymentCallbackController::class, 'handle']);
Route::post('/product/sync', [ProductSyncController::class, 'syncSingleProduct']);
Route::apiResource('products', ProductController::class)->only('index','show');
Route::apiResource('product-categories', ProductCategoryController::class)->only('index','show');
