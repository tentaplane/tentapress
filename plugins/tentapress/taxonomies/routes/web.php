<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\Taxonomies\Http\Public\TermArchiveController;

$blogBase = 'blog';
if (class_exists(SettingsStore::class) && app()->bound(SettingsStore::class)) {
    $rawBase = (string) resolve(SettingsStore::class)->get('site.blog_base', '');
    $rawBase = trim($rawBase, '/');

    if ($rawBase !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $rawBase) === 1) {
        $blogBase = $rawBase;
    }
}

Route::middleware('web')->group(function () use ($blogBase): void {
    Route::get('/'.$blogBase.'/taxonomy/{taxonomy}/{term}', TermArchiveController::class)
        ->where('taxonomy', '^[a-z0-9]+(?:-[a-z0-9]+)*$')
        ->where('term', '^[a-z0-9]+(?:-[a-z0-9]+)*$')
        ->name('tp.public.taxonomies.term');
});
