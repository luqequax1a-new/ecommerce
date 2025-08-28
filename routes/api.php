<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\AddressController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Location API Routes
|--------------------------------------------------------------------------
|
| Routes for Turkish address system with caching and rate limiting
| Supports province and district dependent dropdown functionality
|
*/

Route::prefix('locations')->middleware(['throttle:60,1'])->group(function () {
    // Get all active provinces
    Route::get('provinces', [LocationController::class, 'provinces'])
        ->name('api.locations.provinces');
    
    // Get districts for a specific province
    Route::get('districts', [LocationController::class, 'districts'])
        ->name('api.locations.districts');
    
    // Get province details with districts
    Route::get('province/{id}', [LocationController::class, 'province'])
        ->where('id', '[1-9]|[1-7][0-9]|8[0-1]') // Only accept 1-81
        ->name('api.locations.province');
});

/*
|--------------------------------------------------------------------------
| Address Book API Routes
|--------------------------------------------------------------------------
|
| Routes for customer address management with Turkish validation
| Supports CRUD operations, default address selection, and validation
|
*/

Route::prefix('addresses')->middleware(['throttle:120,1'])->group(function () {
    // Address book CRUD operations (requires authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Get user's address book
        Route::get('/', [AddressController::class, 'index'])
            ->name('api.addresses.index');
        
        // Create new address
        Route::post('/', [AddressController::class, 'store'])
            ->name('api.addresses.store');
        
        // Get specific address
        Route::get('{id}', [AddressController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('api.addresses.show');
        
        // Update address
        Route::put('{id}', [AddressController::class, 'update'])
            ->where('id', '[0-9]+')
            ->name('api.addresses.update');
        
        // Delete address
        Route::delete('{id}', [AddressController::class, 'destroy'])
            ->where('id', '[0-9]+')
            ->name('api.addresses.destroy');
        
        // Set default billing address
        Route::post('{id}/set-default-billing', [AddressController::class, 'setDefaultBilling'])
            ->where('id', '[0-9]+')
            ->name('api.addresses.set-default-billing');
        
        // Set default shipping address
        Route::post('{id}/set-default-shipping', [AddressController::class, 'setDefaultShipping'])
            ->where('id', '[0-9]+')
            ->name('api.addresses.set-default-shipping');
        
        // Get default addresses
        Route::get('defaults/all', [AddressController::class, 'defaults'])
            ->name('api.addresses.defaults');
    });
});