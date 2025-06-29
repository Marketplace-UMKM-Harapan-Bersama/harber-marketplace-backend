<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductSyncController;
use App\Http\Controllers\SellerController;

Route::post('/register', [SellerController::class, 'register']);
Route::post('/login', [SellerController::class, 'login']);

Route::apiResource('/product-categories', ProductCategoryController::class)->only('index');
Route::apiResource('/products', ProductController::class)->only('index');


