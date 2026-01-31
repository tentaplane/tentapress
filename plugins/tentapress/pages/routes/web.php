<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Pages\Http\Public\HomeController;
use TentaPress\Pages\Http\Public\PageController;
use TentaPress\Settings\Services\SettingsStore;

$blogBase = 'blog';
if (class_exists(SettingsStore::class) && app()->bound(SettingsStore::class)) {
    $rawBase = (string) resolve(SettingsStore::class)->get('site.blog_base', '');
    $rawBase = trim($rawBase, '/');

    if ($rawBase !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rawBase) === 1) {
        $blogBase = $rawBase;
    }
}

$blogPattern = preg_quote($blogBase, '/');

Route::middleware('web')->group(function () use ($blogPattern): void {
    Route::get('/', HomeController::class)->name('tp.public.home');

    // Catch-all page route (single-segment for v1).
    // Excludes admin + common reserved prefixes.
    Route::get('{slug?}', PageController::class)
        ->where('slug', "^(?!admin$)(?!admin/)(?!api$)(?!api/)(?!{$blogPattern}$)(?!{$blogPattern}/)(?!storage$)(?!storage/)(?!vendor$)(?!vendor/)[A-Za-z0-9\\-]*$")
        ->name('tp.public.page');
});
