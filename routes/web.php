<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AtikMerkeziController;

// Web sayfaları
Route::get('/', [AtikMerkeziController::class, 'index'])->name('atik-merkezleri.index');
Route::get('/konuma-gore', [AtikMerkeziController::class, 'konumaGore'])->name('atik-merkezleri.konuma-gore');
