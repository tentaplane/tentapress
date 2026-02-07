<?php

declare(strict_types=1);

use TentaPress\SystemInfo\Http\Admin\SystemInfoController;
use TentaPress\SystemInfo\Http\Admin\DiagnosticsDownloadController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;
use TentaPress\SystemInfo\Http\Admin\Plugins\DisableController;
use TentaPress\SystemInfo\Http\Admin\Plugins\EnableController;
use TentaPress\SystemInfo\Http\Admin\Plugins\IndexController;
use TentaPress\SystemInfo\Http\Admin\Plugins\InstallController;
use TentaPress\SystemInfo\Http\Admin\Plugins\InstallStatusController;
use TentaPress\SystemInfo\Http\Admin\Plugins\SyncController;

AdminRoutes::group(function (): void {
    Route::get('/plugins', IndexController::class)
        ->name('plugins.index')
        ->middleware('tp.can:manage_plugins');

    Route::post('/plugins/sync', SyncController::class)
        ->name('plugins.sync')
        ->middleware('tp.can:manage_plugins');

    Route::post('/plugins/enable', EnableController::class)
        ->name('plugins.enable')
        ->middleware('tp.can:manage_plugins');

    Route::post('/plugins/disable', DisableController::class)
        ->name('plugins.disable')
        ->middleware('tp.can:manage_plugins');

    Route::post('/plugins/install', InstallController::class)
        ->name('plugins.install')
        ->middleware('tp.can:manage_plugins');

    Route::get('/plugins/install-attempts/{installId}', InstallStatusController::class)
        ->whereNumber('installId')
        ->name('plugins.install-attempts.show')
        ->middleware('tp.can:manage_plugins');

    Route::get('/system-info', SystemInfoController::class)
        ->name('system-info')
        ->middleware('tp.can:manage_plugins');

    Route::get('/system-info/diagnostics', DiagnosticsDownloadController::class)
        ->name('system-info.diagnostics')
        ->middleware('tp.can:manage_plugins');
});
