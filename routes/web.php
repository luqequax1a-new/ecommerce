<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\MailController as AdminMailController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;

// Frontend rotaları
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/p/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/kategori/{slug}', [ProductController::class, 'category'])->name('category.show');

// Admin rotaları
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard.index');
    
    // System Management
    Route::get('/system/info', [AdminController::class, 'systemInfo'])->name('system.info');
    Route::post('/cache/clear', [AdminController::class, 'clearCache'])->name('cache.clear');
    Route::post('/optimize', [AdminController::class, 'optimize'])->name('optimize');
    
    // Products
    Route::resource('products', AdminProductController::class);
    
    // Categories
    Route::resource('categories', AdminCategoryController::class);
    Route::post('categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');
    Route::post('categories/bulk-action', [AdminCategoryController::class, 'bulkAction'])
        ->name('categories.bulk-action');
    
    // Brands
    Route::resource('brands', AdminBrandController::class);
    Route::post('brands/{brand}/toggle-status', [AdminBrandController::class, 'toggleStatus'])
        ->name('brands.toggle-status');
    Route::post('brands/bulk-action', [AdminBrandController::class, 'bulkAction'])
        ->name('brands.bulk-action');
    
    // Product Image Management
    Route::delete('products/images/{image}', [AdminProductController::class, 'deleteImage'])
        ->name('products.images.delete');
    Route::post('products/images/{image}/cover', [AdminProductController::class, 'setCoverImage'])
        ->name('products.images.cover');
    Route::post('products/images/order', [AdminProductController::class, 'updateImageOrder'])
        ->name('products.images.order');
    Route::post('products/{product}/regenerate-images', [AdminProductController::class, 'regenerateImages'])
        ->name('products.regenerate-images');
    
    // Mail Management Routes
    Route::resource('mail/configurations', AdminMailController::class, ['as' => 'mail']);
    Route::resource('mail/templates', AdminMailController::class, ['as' => 'mail.templates']);
    Route::get('mail/logs', [AdminMailController::class, 'logs'])->name('mail.logs');
    Route::post('mail/test', [AdminMailController::class, 'testConfiguration'])->name('mail.test');
    Route::post('mail/send', [AdminMailController::class, 'sendSingleEmail'])->name('mail.send');
    Route::post('mail/bulk', [AdminMailController::class, 'sendBulkEmails'])->name('mail.bulk');
});
