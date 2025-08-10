<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\Admin\OfferController as AdminOfferController;
use App\Http\Controllers\Vendor\OfferController as VendorOfferController;

// Public routes for offers
Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
Route::get('/offers/{id}', [OfferController::class, 'show'])->name('offers.show');

// Admin routes for offers
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::prefix('offers')->group(function () {
        Route::get('/', [AdminOfferController::class, 'index'])->name('admin.offers.index');
        Route::get('/create', [AdminOfferController::class, 'create'])->name('admin.offers.create');
        Route::post('/', [AdminOfferController::class, 'store'])->name('admin.offers.store');
        Route::get('/{id}', [AdminOfferController::class, 'show'])->name('admin.offers.show');
        Route::get('/{id}/edit', [AdminOfferController::class, 'edit'])->name('admin.offers.edit');
        Route::put('/{id}', [AdminOfferController::class, 'update'])->name('admin.offers.update');
        Route::delete('/{id}', [AdminOfferController::class, 'destroy'])->name('admin.offers.destroy');
        Route::post('/{id}/toggle-status', [AdminOfferController::class, 'toggleStatus'])->name('admin.offers.toggle-status');
    });
});

// Vendor routes for offers
Route::prefix('vendor')->middleware(['auth', 'vendor'])->group(function () {
    Route::prefix('offers')->group(function () {
        Route::get('/', [VendorOfferController::class, 'index'])->name('vendor.offers.index');
        Route::get('/create', [VendorOfferController::class, 'create'])->name('vendor.offers.create');
        Route::post('/', [VendorOfferController::class, 'store'])->name('vendor.offers.store');
        Route::get('/{id}', [VendorOfferController::class, 'show'])->name('vendor.offers.show');
        Route::get('/{id}/edit', [VendorOfferController::class, 'edit'])->name('vendor.offers.edit');
        Route::put('/{id}', [VendorOfferController::class, 'update'])->name('vendor.offers.update');
        Route::delete('/{id}', [VendorOfferController::class, 'destroy'])->name('vendor.offers.destroy');
        Route::post('/{id}/toggle-status', [VendorOfferController::class, 'toggleStatus'])->name('vendor.offers.toggle-status');
    });
});
