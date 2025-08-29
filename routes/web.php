<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\MailController as AdminMailController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\ProductAttributeController as AdminProductAttributeController;
use App\Http\Controllers\Admin\TaxController as AdminTaxController;
use App\Http\Controllers\Admin\UrlRewriteController as AdminUrlRewriteController;
use App\Http\Controllers\Admin\SEOController as AdminSEOController;
use App\Http\Controllers\Frontend\ShopController;

// Frontend routes - SEO optimized
Route::get('/', [ShopController::class, 'home'])->name('home');

// Category routes with hierarchical path support
Route::get('/kategori/{path}', [ShopController::class, 'categoryShow'])
    ->where('path', '.+')
    ->name('category.show');

// Brand routes
Route::get('/marka/{slug}', [ShopController::class, 'brandShow'])
    ->where('slug', '[a-z0-9-]+')
    ->name('brand.show');

// Product routes
Route::get('/urun/{slug}', [ShopController::class, 'productShow'])
    ->where('slug', '[a-z0-9-]+')
    ->name('product.show');

// Legacy routes for backward compatibility - will redirect to new URLs
Route::get('/p/{slug}', function($slug) {
    return redirect()->route('product.show', ['slug' => $slug], 301);
});

Route::get('/c/{slug}', function($slug) {
    $category = \App\Models\Category::where('slug', $slug)->first();
    if ($category) {
        $path = '';
        $current = $category;
        $segments = [$current->slug];
        
        while ($current->parent) {
            $current = $current->parent;
            array_unshift($segments, $current->slug);
        }
        
        $path = implode('/', $segments);
        return redirect()->route('category.show', ['path' => $path], 301);
    }
    abort(404);
});

Route::get('/b/{slug}', function($slug) {
    return redirect()->route('brand.show', ['slug' => $slug], 301);
});

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
    Route::post('products/generate-slug', [AdminProductController::class, 'generateSlug'])
        ->name('products.generate-slug');
    Route::post('products/{product}/variants/generate', [AdminProductController::class, 'generateVariantCombinations'])
        ->name('products.variants.generate');
    Route::post('products/{product}/variants/create', [AdminProductController::class, 'createVariants'])
        ->name('products.variants.create');
    Route::put('products/{product}/variants/update', [AdminProductController::class, 'updateVariants'])
        ->name('products.variants.update');
    Route::delete('products/{product}/variants/delete', [AdminProductController::class, 'deleteVariants'])
        ->name('products.variants.delete');
    Route::get('products/attributes/available', [AdminProductController::class, 'getAvailableAttributes'])
        ->name('products.attributes.available');
    Route::get('products/{product}/variants/statistics', [AdminProductController::class, 'getVariantStatistics'])
        ->name('products.variants.statistics');
    
    // Product Image Management
    Route::post('products/{product}/images/bulk-upload', [AdminProductController::class, 'bulkUploadImages'])
        ->name('products.images.bulk-upload');
    Route::get('products/{product}/images/gallery', [AdminProductController::class, 'getGalleryImages'])
        ->name('products.images.gallery');
    Route::put('products/{product}/images/{image}/metadata', [AdminProductController::class, 'updateImageMetadata'])
        ->name('products.images.metadata');
    Route::post('products/{product}/images/associate-variants', [AdminProductController::class, 'associateImagesWithVariants'])
        ->name('products.images.associate-variants');
    Route::delete('products/{product}/images/bulk-delete', [AdminProductController::class, 'bulkDeleteImages'])
        ->name('products.images.bulk-delete');
    Route::post('products/{product}/images/optimize', [AdminProductController::class, 'optimizeImages'])
        ->name('products.images.optimize');
    
    // Product Attributes
    Route::resource('attributes', AdminProductAttributeController::class);
    Route::post('attributes/{attribute}/values', [AdminProductAttributeController::class, 'storeValue'])
        ->name('attributes.values.store');
    Route::put('attributes/{attribute}/values/{value}', [AdminProductAttributeController::class, 'updateValue'])
        ->name('attributes.values.update');
    Route::delete('attributes/{attribute}/values/{value}', [AdminProductAttributeController::class, 'destroyValue'])
        ->name('attributes.values.destroy');
    Route::get('attributes/{attribute}/values', [AdminProductAttributeController::class, 'getValues'])
        ->name('attributes.values.get');
    Route::post('attributes/update-order', [AdminProductAttributeController::class, 'updateOrder'])
        ->name('attributes.update-order');
    Route::post('attributes/{attribute}/values/update-order', [AdminProductAttributeController::class, 'updateValueOrder'])
        ->name('attributes.values.update-order');
    
    // Categories
    Route::resource('categories', AdminCategoryController::class);
    Route::post('categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');
    Route::post('categories/bulk-action', [AdminCategoryController::class, 'bulkAction'])
        ->name('categories.bulk-action');
    Route::post('categories/update-order', [AdminCategoryController::class, 'updateOrder'])
        ->name('categories.update-order');
    Route::post('categories/generate-slug', [AdminCategoryController::class, 'generateSlug'])
        ->name('categories.generate-slug');
    Route::get('categories/tree/json', [AdminCategoryController::class, 'getTree'])
        ->name('categories.tree');
    
    // Brands
    Route::resource('brands', AdminBrandController::class);
    Route::post('brands/{brand}/toggle-status', [AdminBrandController::class, 'toggleStatus'])
        ->name('brands.toggle-status');
    Route::post('brands/bulk-action', [AdminBrandController::class, 'bulkAction'])
        ->name('brands.bulk-action');
    Route::post('brands/generate-slug', [AdminBrandController::class, 'generateSlug'])
        ->name('brands.generate-slug');
    
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
    
    // Tax Management Routes
    Route::prefix('tax')->name('tax.')->group(function () {
        // Tax Management Dashboard
        Route::get('/', [AdminTaxController::class, 'index'])->name('index');
        
        // Tax Classes Management
        Route::get('/classes', [AdminTaxController::class, 'taxClasses'])->name('classes.index');
        Route::get('/classes/create', [AdminTaxController::class, 'createTaxClass'])->name('classes.create');
        Route::post('/classes', [AdminTaxController::class, 'storeTaxClass'])->name('classes.store');
        Route::get('/classes/{taxClass}/edit', [AdminTaxController::class, 'editTaxClass'])->name('classes.edit');
        Route::put('/classes/{taxClass}', [AdminTaxController::class, 'updateTaxClass'])->name('classes.update');
        Route::delete('/classes/{taxClass}', [AdminTaxController::class, 'destroyTaxClass'])->name('classes.destroy');
        
        // Tax Rates Management
        Route::get('/rates', [AdminTaxController::class, 'taxRates'])->name('rates.index');
        Route::post('/rates', [AdminTaxController::class, 'storeTaxRate'])->name('rates.store');
        Route::put('/rates/{taxRate}', [AdminTaxController::class, 'updateTaxRate'])->name('rates.update');
        Route::delete('/rates/{taxRate}', [AdminTaxController::class, 'destroyTaxRate'])->name('rates.destroy');
        
        // Tax Rules Management
        Route::get('/rules', [AdminTaxController::class, 'taxRules'])->name('rules.index');
        Route::post('/rules', [AdminTaxController::class, 'storeTaxRule'])->name('rules.store');
        Route::put('/rules/{taxRule}', [AdminTaxController::class, 'updateTaxRule'])->name('rules.update');
        Route::delete('/rules/{taxRule}', [AdminTaxController::class, 'destroyTaxRule'])->name('rules.destroy');
        
        // Tax Utilities
        Route::post('/test-calculation', [AdminTaxController::class, 'testCalculation'])->name('test-calculation');
        Route::post('/validate-tax-number', [AdminTaxController::class, 'validateTaxNumber'])->name('validate-tax-number');
        Route::get('/turkish-vat-rates', [AdminTaxController::class, 'getTurkishVATRates'])->name('turkish-vat-rates');
    });
    
    // URL Rewrite Management Routes
    Route::prefix('url-rewrites')->name('url-rewrites.')->group(function () {
        Route::get('/', [AdminUrlRewriteController::class, 'index'])->name('index');
        Route::get('/create', [AdminUrlRewriteController::class, 'create'])->name('create');
        Route::post('/', [AdminUrlRewriteController::class, 'store'])->name('store');
        Route::get('/{urlRewrite}/edit', [AdminUrlRewriteController::class, 'edit'])->name('edit');
        Route::put('/{urlRewrite}', [AdminUrlRewriteController::class, 'update'])->name('update');
        Route::delete('/{urlRewrite}', [AdminUrlRewriteController::class, 'destroy'])->name('destroy');
        Route::post('/{urlRewrite}/toggle-status', [AdminUrlRewriteController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-action', [AdminUrlRewriteController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [AdminUrlRewriteController::class, 'export'])->name('export');
        Route::get('/analytics', [AdminUrlRewriteController::class, 'analytics'])->name('analytics');
    });
    
    // SEO Management Routes
    Route::prefix('seo')->name('seo.')->group(function () {
        Route::post('/preview', [AdminSEOController::class, 'preview'])->name('preview');
        Route::post('/analyze', [AdminSEOController::class, 'analyze'])->name('analyze');
        Route::post('/canonical-url', [AdminSEOController::class, 'canonicalUrl'])->name('canonical-url');
        Route::post('/schema-markup', [AdminSEOController::class, 'schemaMarkup'])->name('schema-markup');
        Route::get('/sitemap-data', [AdminSEOController::class, 'sitemapData'])->name('sitemap-data');
        Route::get('/sitemap.xml', [AdminSEOController::class, 'generateSitemap'])->name('sitemap.xml');
        Route::post('/optimize-content', [AdminSEOController::class, 'optimizeContent'])->name('optimize-content');
        Route::post('/recommendations', [AdminSEOController::class, 'recommendations'])->name('recommendations');
    });
});
