<?php

declare(strict_types=1);

use TentaPress\Posts\Http\Admin\IndexController;
use TentaPress\Posts\Http\Admin\CreateController;
use TentaPress\Posts\Http\Admin\StoreController;
use TentaPress\Posts\Http\Admin\EditController;
use TentaPress\Posts\Http\Admin\EditorController;
use TentaPress\Posts\Http\Admin\UpdateController;
use TentaPress\Posts\Http\Admin\PublishController;
use TentaPress\Posts\Http\Admin\UnpublishController;
use TentaPress\Posts\Http\Admin\DestroyController;
use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_posts')->group(function (): void {
        Route::get('/posts', IndexController::class)->name('posts.index');
        Route::get('/posts/new', CreateController::class)->name('posts.create');
        Route::post('/posts', StoreController::class)->name('posts.store');

        Route::get('/posts/{post}/edit', EditController::class)->name('posts.edit');
        Route::get('/posts/{post}/editor', EditorController::class)->name('posts.editor');
        Route::put('/posts/{post}', UpdateController::class)->name('posts.update');

        Route::post('/posts/{post}/publish', PublishController::class)->name('posts.publish');
        Route::post('/posts/{post}/unpublish', UnpublishController::class)->name('posts.unpublish');

        Route::delete('/posts/{post}', DestroyController::class)->name('posts.destroy');
    });
});
