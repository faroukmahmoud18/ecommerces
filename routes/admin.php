
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrdersController;
use App\Http\Controllers\VendorController;

// Admin Routes (with auth and admin middleware)
Route::middleware(['auth:web', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('profile', [AdminDashboardController::class, 'profile'])->name('profile');
    Route::put('profile', [AdminDashboardController::class, 'updateProfile'])->name('profile.update');

    // Settings
    Route::get('settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::put('settings', [AdminDashboardController::class, 'updateSettings'])->name('settings.update');

    // Vendors
    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('vendors/create', [VendorController::class, 'create'])->name('vendors.create');
    Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
    Route::get('vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
    Route::get('vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
    Route::put('vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
    Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');
    Route::post('vendors/{vendor}/status', [VendorController::class, 'changeStatus'])->name('vendors.change-status');

    // Orders
    Route::get('orders', [AdminOrdersController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrdersController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/status', [AdminOrdersController::class, 'updateStatus'])->name('orders.update-status');
    Route::put('orders/{order}/payment-status', [AdminOrdersController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
    Route::get('orders/{order}/create-shipment', [AdminOrdersController::class, 'createShipment'])->name('orders.create-shipment');
    Route::post('orders/{order}/shipments', [AdminOrdersController::class, 'storeShipment'])->name('orders.store-shipment');
    Route::put('shipments/{shipment}/status', [AdminOrdersController::class, 'updateShipmentStatus'])->name('orders.update-shipment-status');
    Route::get('orders/{order}/invoice', [AdminOrdersController::class, 'downloadInvoice'])->name('orders.invoice');
});
