<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\ContentTypes\Http\Api\IndexController;
use TentaPress\ContentTypes\Http\Api\ArchiveController;
use TentaPress\ContentTypes\Http\Api\ShowController;

Route::middleware('api')
    ->prefix('api/v1')
    ->group(function (): void {
        Route::get('/content-types', IndexController::class)->name('api.content-types.index');
        Route::get('/content-types/{contentTypeKey}', ArchiveController::class)->name('api.content-types.archive');
        Route::get('/content-types/{contentTypeKey}/{slug}', ShowController::class)
            ->where('slug', '^[a-z0-9]+(?:-[a-z0-9]+)*$')
            ->name('api.content-types.show');
    });
