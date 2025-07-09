<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductCategoryController;

Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/sellers/{id}', [SellerController::class, 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/users', [UserController::class, 'oauthUser']);
    Route::get('/sellers', [SellerController::class, 'oauthSeller']);
    Route::post('/product/sync',[ProductSyncController::class,'sync']);
    Route::get('/order', [ProductSyncController::class, 'index']);
    Route::get('/order/{id}', [ProductSyncController::class, 'show']);

    Route::post('/products/{id}/add-to-cart', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'index']);               // Lihat isi keranjang
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);     // Hapus produk dari keranjang
    Route::post('/cart/checkout', [CartController::class, 'checkout']);  // Checkout
});
Route::apiResource('products', ProductController::class)->only('index','show');
Route::apiResource('product-categories', ProductCategoryController::class)->only('index','show');
Route::post('/product/sync', [ProductController::class, 'syncSingleProduct']);
