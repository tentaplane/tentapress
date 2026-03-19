<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\PluginBoilerplate\Http\Admin\IndexController;
use TentaPress\PluginBoilerplate\Http\Admin\UpdateController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_plugin_boilerplate')->group(function (): void {
        Route::get('/plugin-boilerplate', IndexController::class)->name('plugin-boilerplate.index');
        Route::post('/plugin-boilerplate', UpdateController::class)->name('plugin-boilerplate.update');
    });
});
