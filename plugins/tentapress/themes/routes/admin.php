<?php

declare(strict_types=1);

use TentaPress\Themes\Http\Admin\IndexController;
use TentaPress\Themes\Http\Admin\ScreenshotController;
use TentaPress\Themes\Http\Admin\ShowController;
use TentaPress\Themes\Http\Admin\ActivateController;
use TentaPress\Themes\Http\Admin\SyncController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_themes')->group(function (): void {
        Route::get('/themes', IndexController::class)->name('themes.index');

        // Secure screenshot streaming (specific route must be above the catch-all show route).
        Route::get('/themes/{themePath}/screenshot', ScreenshotController::class)
            ->where('themePath', '.*')
            ->name('themes.screenshot');

        // Theme details page (IDs like "vendor/name")
        Route::get('/themes/{themePath}', ShowController::class)
            ->where('themePath', '.*')
            ->name('themes.show');

        Route::post('/themes/sync', SyncController::class)->name('themes.sync');
        Route::post('/themes/activate', ActivateController::class)->name('themes.activate');
    });
});
