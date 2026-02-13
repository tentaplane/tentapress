<?php

declare(strict_types=1);

use TentaPress\Import\Http\Admin\IndexController;
use TentaPress\Import\Http\Admin\AnalyzeController;
use TentaPress\Import\Http\Admin\ProgressController;
use TentaPress\Import\Http\Admin\RunController;
use TentaPress\Import\Http\Admin\RunStreamController;
use TentaPress\Import\Http\Admin\StartController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:import_site')->group(function (): void {
        Route::get('/import', IndexController::class)->name('import.index');
        Route::post('/import/analyze', AnalyzeController::class)->name('import.analyze');
        Route::post('/import/start', StartController::class)->name('import.start');
        Route::get('/import/progress/{run}', ProgressController::class)->name('import.progress');
        Route::post('/import/run', RunController::class)->name('import.run');
        Route::post('/import/run/stream', RunStreamController::class)->name('import.run.stream');
    });
});
