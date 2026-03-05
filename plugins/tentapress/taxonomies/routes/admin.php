<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;
use TentaPress\Taxonomies\Http\Admin\IndexController;
use TentaPress\Taxonomies\Http\Admin\Terms\CreateController;
use TentaPress\Taxonomies\Http\Admin\Terms\DestroyController;
use TentaPress\Taxonomies\Http\Admin\Terms\EditController;
use TentaPress\Taxonomies\Http\Admin\Terms\StoreController;
use TentaPress\Taxonomies\Http\Admin\Terms\TermsIndexController;
use TentaPress\Taxonomies\Http\Admin\Terms\UpdateController;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_posts')->group(function (): void {
        Route::get('/taxonomies', IndexController::class)->name('taxonomies.index');

        Route::get('/taxonomies/{taxonomy}/terms', TermsIndexController::class)->name('taxonomies.terms.index');
        Route::get('/taxonomies/{taxonomy}/terms/new', CreateController::class)->name('taxonomies.terms.create');
        Route::post('/taxonomies/{taxonomy}/terms', StoreController::class)->name('taxonomies.terms.store');

        Route::get('/taxonomies/{taxonomy}/terms/{term}/edit', EditController::class)->name('taxonomies.terms.edit');
        Route::put('/taxonomies/{taxonomy}/terms/{term}', UpdateController::class)->name('taxonomies.terms.update');
        Route::delete('/taxonomies/{taxonomy}/terms/{term}', DestroyController::class)->name('taxonomies.terms.destroy');
    });
});
