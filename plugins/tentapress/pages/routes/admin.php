<?php

declare(strict_types=1);

use TentaPress\Pages\Http\Admin\IndexController;
use TentaPress\Pages\Http\Admin\CreateController;
use TentaPress\Pages\Http\Admin\StoreController;
use TentaPress\Pages\Http\Admin\EditController;
use TentaPress\Pages\Http\Admin\UpdateController;
use TentaPress\Pages\Http\Admin\PublishController;
use TentaPress\Pages\Http\Admin\UnpublishController;
use TentaPress\Pages\Http\Admin\DestroyController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_pages')->group(function (): void {
        Route::get('/pages', IndexController::class)->name('pages.index');
        Route::get('/pages/new', CreateController::class)->name('pages.create');
        Route::post('/pages', StoreController::class)->name('pages.store');

        Route::get('/pages/{page}/edit', EditController::class)->name('pages.edit');
        Route::put('/pages/{page}', UpdateController::class)->name('pages.update');

        Route::post('/pages/{page}/publish', PublishController::class)->name('pages.publish');
        Route::post('/pages/{page}/unpublish', UnpublishController::class)->name('pages.unpublish');

        Route::delete('/pages/{page}', DestroyController::class)->name('pages.destroy');
    });
});
