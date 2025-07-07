<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AtikMerkeziController;

Route::get('/', [AtikMerkeziController::class, 'index'])->name('atik-merkezleri.index');
Route::get('/konuma-gore', [AtikMerkeziController::class, 'konumaGore'])->name('atik-merkezleri.konuma-gore');

// API Routes for map integration
Route::prefix('api')->group(function () {
    Route::get('/merkez/{id}', [AtikMerkeziController::class, 'getMerkez']);
    Route::post('/merkezler', [AtikMerkeziController::class, 'getMerkezler']);
    Route::get('/load-more', [AtikMerkeziController::class, 'loadMore']);
});
