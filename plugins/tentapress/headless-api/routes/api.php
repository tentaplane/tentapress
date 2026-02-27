<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\HeadlessApi\Http\Api\V1\MediaShowController;
use TentaPress\HeadlessApi\Http\Api\V1\MenuShowController;
use TentaPress\HeadlessApi\Http\Api\V1\PagesIndexController;
use TentaPress\HeadlessApi\Http\Api\V1\PageShowController;
use TentaPress\HeadlessApi\Http\Api\V1\PostsIndexController;
use TentaPress\HeadlessApi\Http\Api\V1\PostShowController;
use TentaPress\HeadlessApi\Http\Api\V1\SiteShowController;

Route::middleware(['api', 'throttle:60,1'])->prefix('api/v1')->group(function (): void {
    Route::get('/site', SiteShowController::class)->name('tp.api.v1.site.show');

    Route::get('/pages', PagesIndexController::class)->name('tp.api.v1.pages.index');
    Route::get('/pages/{slug}', PageShowController::class)
        ->where('slug', '^[A-Za-z0-9\-]+$')
        ->name('tp.api.v1.pages.show');

    Route::get('/posts', PostsIndexController::class)->name('tp.api.v1.posts.index');
    Route::get('/posts/{slug}', PostShowController::class)
        ->where('slug', '^[a-z0-9]+(?:-[a-z0-9]+)*$')
        ->name('tp.api.v1.posts.show');

    Route::get('/menus/{location}', MenuShowController::class)
        ->where('location', '^[a-z0-9][a-z0-9\\-_]*$')
        ->name('tp.api.v1.menus.show');

    Route::get('/media/{id}', MediaShowController::class)
        ->whereNumber('id')
        ->name('tp.api.v1.media.show');
});
