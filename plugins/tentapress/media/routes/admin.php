<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Media\Http\Admin\CreateController;
use TentaPress\Media\Http\Admin\DestroyController;
use TentaPress\Media\Http\Admin\EditController;
use TentaPress\Media\Http\Admin\IndexController;
use TentaPress\Media\Http\Admin\StoreController;
use TentaPress\Media\Http\Admin\UpdateController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_media')->group(function (): void {
        Route::get('/media', IndexController::class)->name('media.index');
        Route::get('/media/new', CreateController::class)->name('media.create');
        Route::post('/media', StoreController::class)->name('media.store');

        Route::get('/media/{media}/edit', EditController::class)->name('media.edit');
        Route::put('/media/{media}', UpdateController::class)->name('media.update');

        Route::delete('/media/{media}', DestroyController::class)->name('media.destroy');
    });
});
