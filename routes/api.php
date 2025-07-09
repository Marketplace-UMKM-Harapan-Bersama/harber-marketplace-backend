<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductSyncController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;

use App\Http\Controllers\Api\PaymentCallbackController;


Route::post('/register-seller', [SellerController::class, 'register']);
Route::post('/login', [SellerController::class, 'login']);

Route::apiResource('products', ProductController::class)->only('index','show');
Route::apiResource('product-categories', ProductCategoryController::class)->only('index','show');
Route::post('/product/sync', [ProductController::class, 'syncSingleProduct']);

//router user yang login
Route::group(['middleware'=>'auth:api'], function(){
    Route::get('/user', function(Request $request){
        return $request->user();
    });

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
