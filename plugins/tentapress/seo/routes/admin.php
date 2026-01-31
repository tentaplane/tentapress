<?php

declare(strict_types=1);

use TentaPress\Seo\Http\Admin\IndexController;
use TentaPress\Seo\Http\Admin\SettingsController;
use TentaPress\Seo\Http\Admin\PageEditController;
use TentaPress\Seo\Http\Admin\PageUpdateController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_seo')->group(function (): void {
        Route::get('/seo', IndexController::class)->name('seo.index');

        Route::get('/seo/settings', SettingsController::class)->name('seo.settings');
        Route::post('/seo/settings', SettingsController::class)->name('seo.settings.update');

        Route::get('/seo/pages/{page}/edit', PageEditController::class)->name('seo.pages.edit');
        Route::put('/seo/pages/{page}', PageUpdateController::class)->name('seo.pages.update');
    });
});
