<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Media\Http\Admin\CreateController;
use TentaPress\Media\Http\Admin\DestroyController;
use TentaPress\Media\Http\Admin\EditController;
use TentaPress\Media\Http\Admin\IndexController;
use TentaPress\Media\Http\Admin\Stock\ImportController as StockImportController;
use TentaPress\Media\Http\Admin\Stock\IndexController as StockIndexController;
use TentaPress\Media\Http\Admin\Stock\SettingsController as StockSettingsController;
use TentaPress\Media\Http\Admin\StoreController;
use TentaPress\Media\Http\Admin\UpdateController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_media')->group(function (): void {
        Route::get('/media', IndexController::class)->name('media.index');
        Route::get('/media/stock', StockIndexController::class)->name('media.stock');
        Route::get('/media/stock/settings', (new StockSettingsController())->edit(...))->name('media.stock.settings');
        Route::post('/media/stock/settings', (new StockSettingsController())->update(...))->name('media.stock.settings.update');
        Route::post('/media/stock/import', StockImportController::class)->name('media.stock.import');
        Route::get('/media/new', CreateController::class)->name('media.create');
        Route::post('/media', StoreController::class)->name('media.store');

        Route::get('/media/{media}/edit', EditController::class)->name('media.edit');
        Route::put('/media/{media}', UpdateController::class)->name('media.update');

        Route::delete('/media/{media}', DestroyController::class)->name('media.destroy');
    });
});
