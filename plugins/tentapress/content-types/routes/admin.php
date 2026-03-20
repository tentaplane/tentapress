<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\ContentTypes\Http\Admin\CreateController;
use TentaPress\ContentTypes\Http\Admin\DestroyController;
use TentaPress\ContentTypes\Http\Admin\EditController;
use TentaPress\ContentTypes\Http\Admin\Entries\CreateController as EntryCreateController;
use TentaPress\ContentTypes\Http\Admin\Entries\DestroyController as EntryDestroyController;
use TentaPress\ContentTypes\Http\Admin\Entries\EditController as EntryEditController;
use TentaPress\ContentTypes\Http\Admin\Entries\IndexController as EntryIndexController;
use TentaPress\ContentTypes\Http\Admin\Entries\PublishController as EntryPublishController;
use TentaPress\ContentTypes\Http\Admin\Entries\StoreController as EntryStoreController;
use TentaPress\ContentTypes\Http\Admin\Entries\UnpublishController as EntryUnpublishController;
use TentaPress\ContentTypes\Http\Admin\Entries\UpdateController as EntryUpdateController;
use TentaPress\ContentTypes\Http\Admin\IndexController;
use TentaPress\ContentTypes\Http\Admin\StoreController;
use TentaPress\ContentTypes\Http\Admin\UpdateController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_content_types')->group(function (): void {
        Route::get('/content-types', IndexController::class)->name('content-types.index');
        Route::get('/content-types/new', CreateController::class)->name('content-types.create');
        Route::post('/content-types', StoreController::class)->name('content-types.store');
        Route::get('/content-types/{contentType}/edit', EditController::class)->name('content-types.edit');
        Route::put('/content-types/{contentType}', UpdateController::class)->name('content-types.update');
        Route::delete('/content-types/{contentType}', DestroyController::class)->name('content-types.destroy');
    });

    Route::middleware('tp.can:manage_content_entries')->group(function (): void {
        Route::get('/content-types/{contentType}/entries', EntryIndexController::class)->name('content-types.entries.index');
        Route::get('/content-types/{contentType}/entries/new', EntryCreateController::class)->name('content-types.entries.create');
        Route::post('/content-types/{contentType}/entries', EntryStoreController::class)->name('content-types.entries.store');
        Route::get('/content-types/{contentType}/entries/{entry}/edit', EntryEditController::class)->name('content-types.entries.edit');
        Route::put('/content-types/{contentType}/entries/{entry}', EntryUpdateController::class)->name('content-types.entries.update');
        Route::delete('/content-types/{contentType}/entries/{entry}', EntryDestroyController::class)->name('content-types.entries.destroy');
    });

    Route::middleware('tp.can:publish_content_entries')->group(function (): void {
        Route::post('/content-types/{contentType}/entries/{entry}/publish', EntryPublishController::class)->name('content-types.entries.publish');
        Route::post('/content-types/{contentType}/entries/{entry}/unpublish', EntryUnpublishController::class)->name('content-types.entries.unpublish');
    });
});
