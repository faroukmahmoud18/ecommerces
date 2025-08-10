<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VendorAuthController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorProductsController;
use App\Http\Controllers\Vendor\VendorOrdersController;
use App\Http\Controllers\Vendor\VendorReportsController;
use App\Http\Controllers\Vendor\VendorPayoutsController;

// Vendor Authentication Routes
Route::get('login', [VendorAuthController::class, 'showLoginForm'])->name('vendor.login');
Route::post('login', [VendorAuthController::class, 'login'])->name('vendor.login.submit');
Route::post('logout', [VendorAuthController::class, 'logout'])->name('vendor.logout');
Route::get('register', [VendorAuthController::class, 'showRegistrationForm'])->name('vendor.register');
Route::post('register', [VendorAuthController::class, 'register'])->name('vendor.register.submit');

// Vendor Routes (with auth middleware)
Route::middleware(['auth:web', 'vendor'])->group(function () {
    // Dashboard
    Route::get('dashboard', [VendorDashboardController::class, 'index'])->name('vendor.dashboard');

    // Profile
    Route::get('profile', [VendorDashboardController::class, 'profile'])->name('vendor.profile');
    Route::put('profile', [VendorDashboardController::class, 'updateProfile'])->name('vendor.profile.update');

    // Products
    Route::get('products', [VendorProductsController::class, 'index'])->name('vendor.products.index');
    Route::get('products/create', [VendorProductsController::class, 'create'])->name('vendor.products.create');
    Route::post('products', [VendorProductsController::class, 'store'])->name('vendor.products.store');
    Route::get('products/{product}', [VendorProductsController::class, 'show'])->name('vendor.products.show');
    Route::get('products/{product}/edit', [VendorProductsController::class, 'edit'])->name('vendor.products.edit');
    Route::put('products/{product}', [VendorProductsController::class, 'update'])->name('vendor.products.update');
    Route::delete('products/{product}', [VendorProductsController::class, 'destroy'])->name('vendor.products.destroy');
    Route::post('products/upload-image', [VendorProductsController::class, 'uploadImage'])->name('vendor.products.upload-image');
    Route::delete('products/delete-image/{imageId}', [VendorProductsController::class, 'deleteImage'])->name('vendor.products.delete-image');

    // Orders
    Route::get('orders', [VendorOrdersController::class, 'index'])->name('vendor.orders.index');
    Route::get('orders/{order}', [VendorOrdersController::class, 'show'])->name('vendor.orders.show');
    Route::put('orders/{order}/status', [VendorOrdersController::class, 'updateStatus'])->name('vendor.orders.update-status');
    Route::get('orders/{order}/create-shipment', [VendorOrdersController::class, 'createShipment'])->name('vendor.orders.create-shipment');
    Route::post('orders/{order}/shipments', [VendorOrdersController::class, 'storeShipment'])->name('vendor.orders.store-shipment');
    Route::put('shipments/{shipment}/status', [VendorOrdersController::class, 'updateShipmentStatus'])->name('vendor.orders.update-shipment-status');
    Route::get('orders/{order}/invoice', [VendorOrdersController::class, 'downloadInvoice'])->name('vendor.orders.invoice');

    // Reports
    Route::get('reports/sales', [VendorReportsController::class, 'sales'])->name('vendor.reports.sales');
    Route::get('reports/products', [VendorReportsController::class, 'products'])->name('vendor.reports.products');
    Route::get('reports/customers', [VendorReportsController::class, 'customers'])->name('vendor.reports.customers');
    Route::get('reviews', [VendorReportsController::class, 'reviews'])->name('vendor.reviews.index');
    Route::post('reports/sales/export', [VendorReportsController::class, 'exportSales'])->name('vendor.reports.sales.export');

    // Payouts
    Route::get('payouts', [VendorPayoutsController::class, 'index'])->name('vendor.payouts.index');
    Route::get('payouts/create', [VendorPayoutsController::class, 'create'])->name('vendor.payouts.create');
    Route::post('payouts', [VendorPayoutsController::class, 'store'])->name('vendor.payouts.store');
    Route::get('payouts/{payout}', [VendorPayoutsController::class, 'show'])->name('vendor.payouts.show');
    Route::get('payouts/{payout}/receipt', [VendorPayoutsController::class, 'downloadReceipt'])->name('vendor.payouts.receipt');
});
