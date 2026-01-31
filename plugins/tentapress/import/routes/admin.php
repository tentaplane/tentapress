<?php

declare(strict_types=1);

use TentaPress\Import\Http\Admin\IndexController;
use TentaPress\Import\Http\Admin\AnalyzeController;
use TentaPress\Import\Http\Admin\RunController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:import_site')->group(function (): void {
        Route::get('/import', IndexController::class)->name('import.index');
        Route::post('/import/analyze', AnalyzeController::class)->name('import.analyze');
        Route::post('/import/run', RunController::class)->name('import.run');
    });
});
