<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| Search Routes
|--------------------------------------------------------------------------
|
| Here is where you can register search-related routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Main search route
Route::get('/search', [SearchController::class, 'search'])->name('search');

// API routes for search functionality
Route::prefix('api/search')->group(function () {
    Route::get('/', [SearchController::class, 'search'])->name('api.search');
    Route::get('/suggestions', [SearchController::class, 'suggestions'])->name('api.search.suggestions');
    Route::get('/popular', [SearchController::class, 'popular'])->name('api.search.popular');
    Route::post('/track', [SearchController::class, 'track'])->name('api.search.track');
    Route::get('/indexes', [SearchController::class, 'indexes'])->name('api.search.indexes');
    Route::get('/indexes/{index}/settings', [SearchController::class, 'indexSettings'])->name('api.search.indexes.settings');
});

// Admin search routes
Route::prefix('admin/search')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\SearchController::class, 'index'])->name('admin.search');
    Route::get('/analytics', [\App\Http\Controllers\Admin\SearchController::class, 'analytics'])->name('admin.search.analytics');
    Route::get('/indexes', [\App\Http\Controllers\Admin\SearchController::class, 'indexes'])->name('admin.search.indexes');
    Route::get('/indexes/{index}/settings', [\App\Http\Controllers\Admin\SearchController::class, 'indexSettings'])->name('admin.search.indexes.settings');
    Route::post('/indexes/{index}/settings', [\App\Http\Controllers\Admin\SearchController::class, 'updateIndexSettings'])->name('admin.search.indexes.update-settings');
    Route::post('/indexes/{index}/reindex', [\App\Http\Controllers\Admin\SearchController::class, 'reindex'])->name('admin.search.indexes.reindex');
    Route::delete('/indexes/{index}', [\App\Http\Controllers\Admin\SearchController::class, 'deleteIndex'])->name('admin.search.indexes.delete');
});