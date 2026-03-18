<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Marketing\Http\Admin\IndexController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_marketing')->group(function (): void {
        Route::get('/marketing', IndexController::class)->name('marketing.index');
        Route::post('/marketing', IndexController::class)->name('marketing.update');
    });
});
