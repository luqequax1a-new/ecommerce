<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminProductAttributeController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminBrandController;
use App\Http\Controllers\Admin\AdminMailController;
use App\Http\Controllers\Admin\AdminTaxController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\CronJobController;
use App\Http\Controllers\Admin\AdminUrlRewriteController;
use App\Http\Controllers\Admin\AdminSEOController;
use App\Http\Controllers\Admin\ShippingSettingsController;

// Add login route to fix the "Route [login] not defined" error
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Add frontend routes to fix the "Route [frontend.products.index] not defined" error
Route::prefix('/')->name('frontend.')->group(function () {
    Route::get('/', function () {
        return view('frontend.home');
    })->name('home');
    
    Route::get('/products', function () {
        return view('frontend.products.index');
    })->name('products.index');
});

// Admin rotaları - Angular SPA kullanıldığı için kapatıldı
/*
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard.index');
    
    // System Management
    Route::get('/system/info', [App\Http\Controllers\Admin\AdminController::class, 'systemInfo'])->name('system.info');
    Route::post('/cache/clear', [App\Http\Controllers\Admin\AdminController::class, 'clearCache'])->name('cache.clear');
    Route::post('/optimize', [App\Http\Controllers\Admin\AdminController::class, 'optimize'])->name('optimize');
    
    // Products
    Route::resource('products', AdminProductController::class);
    Route::post('products/generate-slug', [App\Http\Controllers\Admin\AdminProductController::class, 'generateSlug'])
        ->name('products.generate-slug');
    
    // Quick Edit Routes (PrestaShop-style)
    Route::patch('products/{product}/quick-update', [App\Http\Controllers\Admin\AdminProductController::class, 'quickUpdate'])
        ->name('products.quick-update');
    Route::patch('products/bulk-update', [App\Http\Controllers\Admin\AdminProductController::class, 'bulkUpdate'])
        ->name('products.bulk-update');
    Route::get('products/quick-edit-options', [App\Http\Controllers\Admin\AdminProductController::class, 'getQuickEditOptions'])
        ->name('products.quick-edit-options');
    
    // Product Clone Routes (No-Images Policy)
    Route::post('products/{product}/clone', [App\Http\Controllers\Admin\AdminProductController::class, 'clone'])
        ->name('products.clone');
    Route::get('products/{product}/clone-info', [App\Http\Controllers\Admin\AdminProductController::class, 'getCloneInfo'])
        ->name('products.clone-info');
    
    // Product Variants
    Route::post('products/{product}/variants/generate', [App\Http\Controllers\Admin\AdminProductController::class, 'generateVariantCombinations'])
        ->name('products.variants.generate');
    Route::post('products/{product}/variants/create', [App\Http\Controllers\Admin\AdminProductController::class, 'createVariants'])
        ->name('products.variants.create');
    Route::put('products/{product}/variants/update', [App\Http\Controllers\Admin\AdminProductController::class, 'updateVariants'])
        ->name('products.variants.update');
    Route::delete('products/{product}/variants/delete', [App\Http\Controllers\Admin\AdminProductController::class, 'deleteVariants'])
        ->name('products.variants.delete');
    Route::get('products/attributes/available', [App\Http\Controllers\Admin\AdminProductController::class, 'getAvailableAttributes'])
        ->name('products.attributes.available');
    Route::post('products/variants/preview-combinations', [App\Http\Controllers\Admin\AdminProductController::class, 'previewVariantCombinations'])
        ->name('products.variants.preview-combinations');
    Route::get('products/{product}/variants/statistics', [App\Http\Controllers\Admin\AdminProductController::class, 'getVariantStatistics'])
        ->name('products.variants.statistics');
    
    // Product Image Management
    Route::post('products/{product}/images/bulk-upload', [App\Http\Controllers\Admin\AdminProductController::class, 'bulkUploadImages'])
        ->name('products.images.bulk-upload');
    Route::get('products/{product}/images/gallery', [App\Http\Controllers\Admin\AdminProductController::class, 'getGalleryImages'])
        ->name('products.images.gallery');
    Route::put('products/{product}/images/{image}/metadata', [App\Http\Controllers\Admin\AdminProductController::class, 'updateImageMetadata'])
        ->name('products.images.metadata');
    Route::post('products/{product}/images/associate-variants', [App\Http\Controllers\Admin\AdminProductController::class, 'associateImagesWithVariants'])
        ->name('products.images.associate-variants');
    Route::delete('products/{product}/images/bulk-delete', [App\Http\Controllers\Admin\AdminProductController::class, 'bulkDeleteImages'])
        ->name('products.images.bulk-delete');
    Route::post('products/{product}/images/optimize', [App\Http\Controllers\Admin\AdminProductController::class, 'optimizeImages'])
        ->name('products.images.optimize');
    Route::post('products/images/regenerate-all', [App\Http\Controllers\Admin\AdminProductController::class, 'regenerateAllImages'])
        ->name('products.images.regenerate-all');
    
    // Product Attributes
    Route::resource('attributes', App\Http\Controllers\Admin\AdminProductAttributeController::class);
    Route::post('attributes/{attribute}/values', [App\Http\Controllers\Admin\AdminProductAttributeController::class, 'storeValue'])
        ->name('attributes.values.store');
    Route::put('attributes/{attribute}/values/{value}', [App\Http\Controllers\Admin\AdminProductAttributeController::class, 'updateValue'])
        ->name('attributes.values.update');
    Route::delete('attributes/{attribute}/values/{value}', [App\Http\Controllers\Admin\AdminProductAttributeController::class, 'destroyValue'])
        ->name('attributes.values.destroy');
    Route::get('attributes/{attribute}/values', [App\Http\Controllers\Admin\AdminProductAttributeController::class, 'getValues'])
        ->name('attributes.values.get');
    Route::post('attributes/update-order', [App\Http\Controllers\Admin\AdminProductAttributeController::class, 'updateOrder'])
        ->name('attributes.update-order');
    Route::post('attributes/{attribute}/values/update-order', [App\Http\Controllers\Admin\AdminProductAttributeController::class, 'updateValueOrder'])
        ->name('attributes.values.update-order');
    
    // Categories
    Route::resource('categories', App\Http\Controllers\Admin\AdminCategoryController::class);
    Route::post('categories/{category}/toggle-status', [App\Http\Controllers\Admin\AdminCategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');
    Route::post('categories/bulk-action', [App\Http\Controllers\Admin\AdminCategoryController::class, 'bulkAction'])
        ->name('categories.bulk-action');
    Route::post('categories/update-order', [App\Http\Controllers\Admin\AdminCategoryController::class, 'updateOrder'])
        ->name('categories.update-order');
    Route::post('categories/generate-slug', [App\Http\Controllers\Admin\AdminCategoryController::class, 'generateSlug'])
        ->name('categories.generate-slug');
    Route::get('categories/tree/json', [App\Http\Controllers\Admin\AdminCategoryController::class, 'getTree'])
        ->name('categories.tree');
    
    // Brands
    Route::resource('brands', App\Http\Controllers\Admin\AdminBrandController::class);
    Route::post('brands/{brand}/toggle-status', [App\Http\Controllers\Admin\AdminBrandController::class, 'toggleStatus'])
        ->name('brands.toggle-status');
    Route::post('brands/bulk-action', [App\Http\Controllers\Admin\AdminBrandController::class, 'bulkAction'])
        ->name('brands.bulk-action');
    Route::post('brands/generate-slug', [App\Http\Controllers\Admin\AdminBrandController::class, 'generateSlug'])
        ->name('brands.generate-slug');
    
    // Product Image Management
    Route::delete('products/images/{image}', [App\Http\Controllers\Admin\AdminProductController::class, 'deleteImage'])
        ->name('products.images.delete');
    Route::post('products/images/{image}/cover', [App\Http\Controllers\Admin\AdminProductController::class, 'setCoverImage'])
        ->name('products.images.cover');
    Route::post('products/images/order', [App\Http\Controllers\Admin\AdminProductController::class, 'updateImageOrder'])
        ->name('products.images.order');
    Route::post('products/{product}/regenerate-images', [App\Http\Controllers\Admin\AdminProductController::class, 'regenerateImages'])
        ->name('products.regenerate-images');
    
    // Mail Management Routes
    Route::resource('mail/configurations', App\Http\Controllers\Admin\AdminMailController::class, ['as' => 'mail']);
    Route::resource('mail/templates', App\Http\Controllers\Admin\AdminMailController::class, ['as' => 'mail.templates']);
    Route::get('mail/logs', [App\Http\Controllers\Admin\AdminMailController::class, 'logs'])->name('mail.logs');
    Route::post('mail/test', [App\Http\Controllers\Admin\AdminMailController::class, 'testConfiguration'])->name('mail.test');
    Route::post('mail/send', [App\Http\Controllers\Admin\AdminMailController::class, 'sendSingleEmail'])->name('mail.send');
    Route::post('mail/bulk', [App\Http\Controllers\Admin\AdminMailController::class, 'sendBulkEmails'])->name('mail.bulk');
    
    // Tax Management Routes
    Route::prefix('tax')->name('tax.')->group(function () {
        // Tax Management Dashboard
        Route::get('/', [App\Http\Controllers\Admin\AdminTaxController::class, 'index'])->name('index');
        
        // Tax Classes Management
        Route::get('/classes', [App\Http\Controllers\Admin\AdminTaxController::class, 'taxClasses'])->name('classes.index');
        Route::get('/classes/create', [App\Http\Controllers\Admin\AdminTaxController::class, 'createTaxClass'])->name('classes.create');
        Route::post('/classes', [App\Http\Controllers\Admin\AdminTaxController::class, 'storeTaxClass'])->name('classes.store');
        Route::get('/classes/{taxClass}/edit', [App\Http\Controllers\Admin\AdminTaxController::class, 'editTaxClass'])->name('classes.edit');
        Route::put('/classes/{taxClass}', [App\Http\Controllers\Admin\AdminTaxController::class, 'updateTaxClass'])->name('classes.update');
        Route::delete('/classes/{taxClass}', [App\Http\Controllers\Admin\AdminTaxController::class, 'destroyTaxClass'])->name('classes.destroy');
        
        // Tax Rates Management
        Route::get('/rates', [App\Http\Controllers\Admin\AdminTaxController::class, 'taxRates'])->name('rates.index');
        Route::post('/rates', [App\Http\Controllers\Admin\AdminTaxController::class, 'storeTaxRate'])->name('rates.store');
        Route::put('/rates/{taxRate}', [App\Http\Controllers\Admin\AdminTaxController::class, 'updateTaxRate'])->name('rates.update');
        Route::delete('/rates/{taxRate}', [App\Http\Controllers\Admin\AdminTaxController::class, 'destroyTaxRate'])->name('rates.destroy');
        
        // Tax Rules Management
        Route::get('/rules', [App\Http\Controllers\Admin\AdminTaxController::class, 'taxRules'])->name('rules.index');
        Route::post('/rules', [App\Http\Controllers\Admin\AdminTaxController::class, 'storeTaxRule'])->name('rules.store');
        Route::put('/rules/{taxRule}', [App\Http\Controllers\Admin\AdminTaxController::class, 'updateTaxRule'])->name('rules.update');
        Route::delete('/rules/{taxRule}', [App\Http\Controllers\Admin\AdminTaxController::class, 'destroyTaxRule'])->name('rules.destroy');
        
        // Tax Utilities
        Route::post('/test-calculation', [App\Http\Controllers\Admin\AdminTaxController::class, 'testCalculation'])->name('test-calculation');
        Route::post('/validate-tax-number', [App\Http\Controllers\Admin\AdminTaxController::class, 'validateTaxNumber'])->name('validate-tax-number');
        Route::get('/turkish-vat-rates', [App\Http\Controllers\Admin\AdminTaxController::class, 'getTurkishVATRates'])->name('turkish-vat-rates');
    });

    // Coupon Management Routes
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminCouponController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\AdminCouponController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\AdminCouponController::class, 'store'])->name('store');
        Route::get('/{coupon}', [App\Http\Controllers\Admin\AdminCouponController::class, 'show'])->name('show');
        Route::get('/{coupon}/edit', [App\Http\Controllers\Admin\AdminCouponController::class, 'edit'])->name('edit');
        Route::put('/{coupon}', [App\Http\Controllers\Admin\AdminCouponController::class, 'update'])->name('update');
        Route::delete('/{coupon}', [App\Http\Controllers\Admin\AdminCouponController::class, 'destroy'])->name('destroy');
        Route::post('/{coupon}/toggle-status', [App\Http\Controllers\Admin\AdminCouponController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/generate-code', [App\Http\Controllers\Admin\AdminCouponController::class, 'generateCode'])->name('generate-code');
        Route::get('/statistics', [App\Http\Controllers\Admin\AdminCouponController::class, 'getStatistics'])->name('statistics');
        Route::get('/report', [App\Http\Controllers\Admin\AdminCouponController::class, 'report'])->name('report');
    });
    
    // Cron Job Management Routes
    Route::prefix('cron')->name('cron.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CronJobController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\CronJobController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\CronJobController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\CronJobController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\CronJobController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\CronJobController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [App\Http\Controllers\Admin\CronJobController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/execute', [App\Http\Controllers\Admin\CronJobController::class, 'execute'])->name('execute');
        Route::get('/{id}/logs', [App\Http\Controllers\Admin\CronJobController::class, 'logs'])->name('logs');
        Route::post('/add-predefined-task', [App\Http\Controllers\Admin\CronJobController::class, 'addPredefinedTask'])->name('add-predefined-task');
    });
    
    // URL Rewrite Management Routes
    Route::prefix('url-rewrites')->name('url-rewrites.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'store'])->name('store');
        Route::get('/{urlRewrite}/edit', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'edit'])->name('edit');
        Route::put('/{urlRewrite}', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'update'])->name('update');
        Route::delete('/{urlRewrite}', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'destroy'])->name('destroy');
        Route::post('/{urlRewrite}/toggle-status', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-action', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'export'])->name('export');
        Route::get('/analytics', [App\Http\Controllers\Admin\AdminUrlRewriteController::class, 'analytics'])->name('analytics');
    });
    
    // SEO Management Routes
    Route::prefix('seo')->name('seo.')->group(function () {
        Route::post('/preview', [App\Http\Controllers\Admin\AdminSEOController::class, 'preview'])->name('seo.preview');
        Route::post('/analyze', [App\Http\Controllers\Admin\AdminSEOController::class, 'analyze'])->name('seo.analyze');
        Route::post('/canonical-url', [App\Http\Controllers\Admin\AdminSEOController::class, 'canonicalUrl'])->name('seo.canonical-url');
        Route::post('/schema-markup', [App\Http\Controllers\Admin\AdminSEOController::class, 'schemaMarkup'])->name('seo.schema-markup');
        Route::get('/sitemap-data', [App\Http\Controllers\Admin\AdminSEOController::class, 'sitemapData'])->name('seo.sitemap-data');
        Route::get('/sitemap.xml', [App\Http\Controllers\Admin\AdminSEOController::class, 'generateSitemap'])->name('seo.sitemap.xml');
        Route::post('/optimize-content', [App\Http\Controllers\Admin\AdminSEOController::class, 'optimizeContent'])->name('seo.optimize-content');
        Route::post('/recommendations', [App\Http\Controllers\Admin\AdminSEOController::class, 'recommendations'])->name('seo.recommendations');
    });
    
    // Shipping Settings Management Routes
    Route::prefix('shipping/settings')->name('shipping.settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ShippingSettingsController::class, 'index'])->name('index');
        Route::get('/edit', [App\Http\Controllers\Admin\ShippingSettingsController::class, 'edit'])->name('edit');
        Route::put('/update', [App\Http\Controllers\Admin\ShippingSettingsController::class, 'update'])->name('update');
        Route::post('/test-calculation', [App\Http\Controllers\Admin\ShippingSettingsController::class, 'testCalculation'])->name('test-calculation');
        Route::get('/configuration', [App\Http\Controllers\Admin\ShippingSettingsController::class, 'getConfiguration'])->name('configuration');
        Route::post('/reset-defaults', [App\Http\Controllers\Admin\ShippingSettingsController::class, 'resetToDefaults'])->name('reset-defaults');
        Route::post('/toggle-setting', [App\Http\Controllers\Admin\ShippingSettingsController::class, 'toggleSetting'])->name('toggle-setting');
        Route::get('/statistics', [App\Http\Controllers\Admin\ShippingSettingsController::class, 'getStatistics'])->name('statistics');
    });
});
*/

// Angular SPA route - catch all /admin requests
Route::get('/admin/{any?}', function () {
    return response()->file(public_path('admin/browser/index.csr.html'));
})->where('any', '.*')->name('admin.spa');