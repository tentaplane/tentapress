<?php

declare(strict_types=1);

use TentaPress\Settings\Http\Admin\IndexController;
use TentaPress\Settings\Http\Admin\UpdateController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_settings')->group(function (): void {
        Route::get('/settings', IndexController::class)->name('settings.index');
        Route::post('/settings', UpdateController::class)->name('settings.update');
    });
});
