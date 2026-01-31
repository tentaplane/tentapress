<?php

declare(strict_types=1);

use TentaPress\Users\Http\Auth\Login\CreateController;
use TentaPress\Users\Http\Auth\Login\StoreController;
use Illuminate\Support\Facades\Route;

// Login routes should NOT be behind tp.admin
Route::middleware('web')->prefix('admin')->as('tp.')->group(function (): void {
    Route::get('/login', CreateController::class)->name('login');
    Route::post('/login', StoreController::class)->name('login.submit');
    Route::post('/logout', \TentaPress\Users\Http\Auth\Logout\StoreController::class)->name('logout');
});
