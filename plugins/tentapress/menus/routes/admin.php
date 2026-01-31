<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Menus\Http\Admin\CreateController;
use TentaPress\Menus\Http\Admin\DestroyController;
use TentaPress\Menus\Http\Admin\EditController;
use TentaPress\Menus\Http\Admin\IndexController;
use TentaPress\Menus\Http\Admin\StoreController;
use TentaPress\Menus\Http\Admin\UpdateController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_menus')->group(function (): void {
        Route::get('/menus', IndexController::class)->name('menus.index');
        Route::get('/menus/new', CreateController::class)->name('menus.create');
        Route::post('/menus', StoreController::class)->name('menus.store');

        Route::get('/menus/{menu}/edit', EditController::class)->name('menus.edit');
        Route::put('/menus/{menu}', UpdateController::class)->name('menus.update');

        Route::delete('/menus/{menu}', DestroyController::class)->name('menus.destroy');
    });
});
