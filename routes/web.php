<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;

// Frontend rotaları
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/p/{slug}', [ProductController::class, 'show'])->name('product.show');

// Admin rotaları
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', AdminProductController::class);
    
    // Görsel yönetimi için özel rotalar
    Route::delete('products/images/{image}', [AdminProductController::class, 'deleteImage'])
        ->name('products.images.delete');
    Route::post('products/images/{image}/cover', [AdminProductController::class, 'setCoverImage'])
        ->name('products.images.cover');
    Route::post('products/images/order', [AdminProductController::class, 'updateImageOrder'])
        ->name('products.images.order');
    Route::post('products/{product}/regenerate-images', [AdminProductController::class, 'regenerateImages'])
        ->name('products.regenerate-images');
});
