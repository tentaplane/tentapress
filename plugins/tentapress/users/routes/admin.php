<?php

declare(strict_types=1);

use TentaPress\Users\Http\Admin\Users\IndexController;
use TentaPress\Users\Http\Admin\Users\CreateController;
use TentaPress\Users\Http\Admin\Users\StoreController;
use TentaPress\Users\Http\Admin\Users\EditController;
use TentaPress\Users\Http\Admin\Users\UpdateController;
use TentaPress\Users\Http\Admin\Users\DestroyController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    // Users
    Route::middleware('tp.can:manage_users')->group(function (): void {
        Route::get('/users', IndexController::class)->name('users.index');
        Route::get('/users/new', CreateController::class)->name('users.create');
        Route::post('/users', StoreController::class)->name('users.store');
        Route::get('/users/{user}/edit', EditController::class)->name('users.edit');
        Route::put('/users/{user}', UpdateController::class)->name('users.update');
        Route::delete('/users/{user}', DestroyController::class)->name('users.destroy');
    });

    // Roles
    Route::middleware('tp.can:manage_roles')->group(function (): void {
        Route::get('/roles', \TentaPress\Users\Http\Admin\Roles\IndexController::class)->name('roles.index');
        Route::get('/roles/new', \TentaPress\Users\Http\Admin\Roles\CreateController::class)->name('roles.create');
        Route::post('/roles', \TentaPress\Users\Http\Admin\Roles\StoreController::class)->name('roles.store');
        Route::get('/roles/{role}/edit', \TentaPress\Users\Http\Admin\Roles\EditController::class)->name('roles.edit');
        Route::put('/roles/{role}', \TentaPress\Users\Http\Admin\Roles\UpdateController::class)->name('roles.update');
        Route::delete('/roles/{role}', \TentaPress\Users\Http\Admin\Roles\DestroyController::class)->name('roles.destroy');
    });
});
