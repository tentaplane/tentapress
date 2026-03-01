<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Revisions\Http\Admin\PageAutosaveController;
use TentaPress\Revisions\Http\Admin\PageCompareController;
use TentaPress\Revisions\Http\Admin\PageRestoreController;
use TentaPress\Revisions\Http\Admin\PostAutosaveController;
use TentaPress\Revisions\Http\Admin\PostCompareController;
use TentaPress\Revisions\Http\Admin\PostRestoreController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_pages')->group(function (): void {
        Route::post('/pages/{page}/revisions/autosave', PageAutosaveController::class)->name('pages.revisions.autosave');
        Route::get('/pages/{page}/revisions/compare', PageCompareController::class)->name('pages.revisions.compare');
        Route::post('/pages/{page}/revisions/{revision}/restore', PageRestoreController::class)->name('pages.revisions.restore');
    });

    Route::middleware('tp.can:manage_posts')->group(function (): void {
        Route::post('/posts/{post}/revisions/autosave', PostAutosaveController::class)->name('posts.revisions.autosave');
        Route::get('/posts/{post}/revisions/compare', PostCompareController::class)->name('posts.revisions.compare');
        Route::post('/posts/{post}/revisions/{revision}/restore', PostRestoreController::class)->name('posts.revisions.restore');
    });
});
