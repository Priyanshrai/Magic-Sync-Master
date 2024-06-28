<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::post('/connect', [DashboardController::class, 'connect'])->name('connect');
    Route::post('/disconnect', [DashboardController::class, 'disconnect'])->name('disconnect');
});