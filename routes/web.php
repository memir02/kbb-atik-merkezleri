<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AtikMerkeziController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AtikMerkeziController::class, 'index'])->name('atik-merkezleri.index');
Route::get('/konuma-gore', [AtikMerkeziController::class, 'konumaGore'])->name('atik-merkezleri.konuma-gore');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
