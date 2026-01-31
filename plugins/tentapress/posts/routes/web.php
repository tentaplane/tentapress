<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Posts\Http\Public\PostController;
use TentaPress\Posts\Http\Public\PostsIndexController;
use TentaPress\Settings\Services\SettingsStore;

$blogBase = 'blog';
if (class_exists(SettingsStore::class) && app()->bound(SettingsStore::class)) {
    $rawBase = (string) resolve(SettingsStore::class)->get('site.blog_base', '');
    $rawBase = trim($rawBase, '/');

    if ($rawBase !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rawBase) === 1) {
        $blogBase = $rawBase;
    }
}

Route::middleware('web')->group(function () use ($blogBase): void {
    Route::get('/'.$blogBase, PostsIndexController::class)->name('tp.public.posts.index');

    Route::get('/'.$blogBase.'/{slug}', PostController::class)
        ->where('slug', '^[a-z0-9]+(?:-[a-z0-9]+)*$')
        ->name('tp.public.posts.show');
});
