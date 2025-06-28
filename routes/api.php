<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductSyncController;
use App\Http\Controllers\SellerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [SellerController::class, 'register']);
Route::post('/login', [SellerController::class, 'login']);



