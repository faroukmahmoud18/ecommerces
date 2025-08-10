<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\Admin\AdminShippingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Shipping Routes
Route::get('/tracking', [ShippingController::class, 'trackShipment'])->name('tracking');
Route::get('/shipping', [ShippingController::class, 'getProviders'])->name('shipping.providers');
Route::post('/shipping/options', [ShippingController::class, 'getShippingOptions'])->name('shipping.options');
Route::post('/shipping/order-options/{order}', [ShippingController::class, 'getShippingOptionsForOrder'])->name('shipping.order-options');

// Customer shipping routes
Route::middleware(['auth'])->group(function () {
    // Customer tracking page
    Route::get('/tracking/customer', [ShippingController::class, 'trackShipment'])->name('customer.tracking');
});

// Vendor shipping routes
Route::middleware(['auth', 'vendor'])->group(function () {
    Route::post('/orders/{order}/shipments', [ShippingController::class, 'createShipment'])->name('vendor.orders.shipments');
    Route::get('/shipments/{shipment}', [ShippingController::class, 'getShipment'])->name('vendor.shipments.show');
    Route::post('/shipments/{shipment}/status', [ShippingController::class, 'updateStatus'])->name('vendor.shipments.update-status');
    Route::get('/shipments/{shipment}/label', [ShippingController::class, 'printLabel'])->name('vendor.shipments.label');
});

// Admin shipping routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/shipping', [AdminShippingController::class, 'index'])->name('admin.shipping');
    Route::get('/admin/api/shipments', [AdminShippingController::class, 'getShipments'])->name('admin.api.shipments');
    Route::get('/admin/api/shipments/{shipment}', [AdminShippingController::class, 'getShipment'])->name('admin.api.shipments.show');
    Route::get('/admin/api/shipping-providers', [AdminShippingController::class, 'getProviders'])->name('admin.api.shipping-providers');
    Route::get('/admin/api/shipping-providers/{provider}', [AdminShippingController::class, 'getProvider'])->name('admin.api.shipping-providers.show');
    Route::get('/admin/api/shipping-zones', [AdminShippingController::class, 'getZones'])->name('admin.api.shipping-zones');
    Route::get('/admin/api/shipping-zones/{zone}', [AdminShippingController::class, 'getZone'])->name('admin.api.shipping-zones.show');
    Route::get('/admin/api/shipping-rates', [AdminShippingController::class, 'getRates'])->name('admin.api.shipping-rates');
    Route::get('/admin/api/shipping-rates/{rate}', [AdminShippingController::class, 'getRate'])->name('admin.api.shipping-rates.show');

    // CRUD operations for shipping providers
    Route::post('/admin/shipping-providers', [AdminShippingController::class, 'createProvider'])->name('admin.shipping-providers.create');
    Route::put('/admin/shipping-providers/{provider}', [AdminShippingController::class, 'updateProvider'])->name('admin.shipping-providers.update');
    Route::delete('/admin/shipping-providers/{provider}', [AdminShippingController::class, 'deleteProvider'])->name('admin.shipping-providers.delete');

    // CRUD operations for shipping zones
    Route::post('/admin/shipping-zones', [AdminShippingController::class, 'createZone'])->name('admin.shipping-zones.create');
    Route::put('/admin/shipping-zones/{zone}', [AdminShippingController::class, 'updateZone'])->name('admin.shipping-zones.update');
    Route::delete('/admin/shipping-zones/{zone}', [AdminShippingController::class, 'deleteZone'])->name('admin.shipping-zones.delete');

    // CRUD operations for shipping rates
    Route::post('/admin/shipping-rates', [AdminShippingController::class, 'createRate'])->name('admin.shipping-rates.create');
    Route::put('/admin/shipping-rates/{rate}', [AdminShippingController::class, 'updateRate'])->name('admin.shipping-rates.update');
    Route::delete('/admin/shipping-rates/{rate}', [AdminShippingController::class, 'deleteRate'])->name('admin.shipping-rates.delete');
});