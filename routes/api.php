<?php

use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);




// API Public 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// API Private 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'getCart']);

    Route::put('/cart/update/{itemId}', [CartController::class, 'updateItem']); 
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'removeItem']); 

    Route::post('/checkout', [OrderController::class, 'checkout']); 
    Route::get('/orders', [OrderController::class, 'index']);       
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Quản lý đơn hàng
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

    // --- QUẢN LÝ SẢN PHẨM ---
     Route::get('/products/create-info', [AdminProductController::class, 'getFormData']); 
    Route::apiResource('products', AdminProductController::class);

});
