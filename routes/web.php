<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AtikMerkeziController;

Route::get('/', [AtikMerkeziController::class, 'index'])->name('atik-merkezleri.index');
