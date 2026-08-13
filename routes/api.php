<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\CartApiController;
use App\Http\Controllers\Api\V1\CategoryApiController;
use App\Http\Controllers\Api\V1\OrderApiController;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\SearchApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    // Public API Endpoints
    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::get('/products/{slug}', [ProductApiController::class, 'show']);
    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/search/autocomplete', [SearchApiController::class, 'autocomplete']);

    // Protected API Endpoints (Sanctum Token Auth)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/user', [AuthApiController::class, 'user']);
        Route::post('/auth/logout', [AuthApiController::class, 'logout']);

        // Cart API
        Route::get('/cart', [CartApiController::class, 'index']);
        Route::post('/cart/add', [CartApiController::class, 'store']);
        Route::delete('/cart/item/{id}', [CartApiController::class, 'destroy']);

        // Orders API
        Route::get('/orders', [OrderApiController::class, 'index']);
        Route::get('/orders/{id}', [OrderApiController::class, 'show']);
    });
});
