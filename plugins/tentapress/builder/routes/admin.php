<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Builder\Http\Admin\BuilderPreviewDocumentController;
use TentaPress\Builder\Http\Admin\BuilderSnapshotController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::post('/builder/snapshots', BuilderSnapshotController::class)->name('builder.snapshots.store');
    Route::get('/builder/snapshots/{token}/document', BuilderPreviewDocumentController::class)->name('builder.snapshots.document');
});
