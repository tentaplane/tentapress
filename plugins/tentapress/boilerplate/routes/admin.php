<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Boilerplate\Http\Admin\IndexController;
use TentaPress\Boilerplate\Http\Admin\UpdateController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_boilerplate')->group(function (): void {
        Route::get('/boilerplate', IndexController::class)->name('boilerplate.index');
        Route::post('/boilerplate', UpdateController::class)->name('boilerplate.update');
    });
});
