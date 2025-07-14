<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AtikMerkeziController;
use App\Http\Controllers\RatingController;

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

// Atık Merkezi API Routes
Route::prefix('atik-merkezleri')->group(function () {
    // Tek merkez getir
    Route::get('/{id}', [AtikMerkeziController::class, 'show'])
        ->where('id', '[0-9]+')
        ->name('api.atik-merkezleri.show');
    
    // Birden fazla merkez getir
    Route::post('/multiple', [AtikMerkeziController::class, 'getMultiple'])
        ->name('api.atik-merkezleri.multiple');
    
    // Infinite scroll
    Route::get('/load-more', [AtikMerkeziController::class, 'loadMore'])
        ->name('api.atik-merkezleri.load-more');
    
    // Konum bazlı arama
    Route::post('/nearest', [AtikMerkeziController::class, 'nearest'])
        ->name('api.atik-merkezleri.nearest');
    
    // Genel arama
    Route::get('/search', [AtikMerkeziController::class, 'search'])
        ->name('api.atik-merkezleri.search');
    
    // Arama önerileri
    Route::get('/search/suggestions', [AtikMerkeziController::class, 'suggestions'])
        ->name('api.atik-merkezleri.suggestions');
    
    // Popüler aramalar
    Route::get('/search/popular', [AtikMerkeziController::class, 'popularSearches'])
        ->name('api.atik-merkezleri.popular-searches');
});

// Rating API Routes (moved to web.php for session-based authentication)

// Rating API Routes (legacy - with merkez in URL)
Route::prefix('atik-merkezleri/{atikMerkezi}')->middleware('auth')->group(function () {
    Route::post('/rate', [RatingController::class, 'rate']);
    Route::post('/favorite', [RatingController::class, 'addToFavorites']);
    Route::delete('/favorite', [RatingController::class, 'removeFromFavorites']);
});

// Eski uyumluluk için - Deprecated
Route::prefix('v1')->group(function () {
    Route::get('/merkez/{id}', [AtikMerkeziController::class, 'show']);
    Route::post('/merkezler', [AtikMerkeziController::class, 'getMultiple']);
    Route::get('/load-more', [AtikMerkeziController::class, 'loadMore']);
}); 