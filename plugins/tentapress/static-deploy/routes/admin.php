<?php

declare(strict_types=1);

use TentaPress\StaticDeploy\Http\Admin\IndexController;
use TentaPress\StaticDeploy\Http\Admin\GenerateController;
use TentaPress\StaticDeploy\Http\Admin\DownloadController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:deploy_static')->group(function (): void {
        Route::get('/static-deploy', IndexController::class)->name('static.index');
        Route::post('/static-deploy/generate', GenerateController::class)->name('static.generate');
        Route::get('/static-deploy/download', DownloadController::class)->name('static.download');
    });
});
