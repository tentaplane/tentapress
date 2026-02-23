<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Builder\Http\Admin\BuilderPreviewController;
use TentaPress\Builder\Http\Admin\BuilderSnapshotController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::post('/builder/snapshots', BuilderSnapshotController::class)->name('builder.snapshots.store');
    Route::get('/builder/preview/{token}', BuilderPreviewController::class)->name('builder.preview.show');
});
