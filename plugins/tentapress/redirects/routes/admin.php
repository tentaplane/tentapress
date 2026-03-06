<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\Redirects\Http\Admin\CheckController;
use TentaPress\Redirects\Http\Admin\CreateController;
use TentaPress\Redirects\Http\Admin\EditController;
use TentaPress\Redirects\Http\Admin\IndexController;
use TentaPress\Redirects\Http\Admin\SettingsController;
use TentaPress\Redirects\Http\Admin\SuggestionApproveController;
use TentaPress\Redirects\Http\Admin\SuggestionRejectController;
use TentaPress\Redirects\Http\Admin\SuggestionsIndexController;
use TentaPress\Redirects\Http\Admin\StoreController;
use TentaPress\Redirects\Http\Admin\UpdateController;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_seo')->group(function (): void {
        Route::get('/redirects', IndexController::class)->name('redirects.index');
        Route::get('/redirects/create', CreateController::class)->name('redirects.create');
        Route::post('/redirects', StoreController::class)->name('redirects.store');
        Route::post('/redirects/check', CheckController::class)->name('redirects.check');
        Route::get('/redirects/{redirect}/edit', EditController::class)->name('redirects.edit');
        Route::put('/redirects/{redirect}', UpdateController::class)->name('redirects.update');
        Route::match(['get', 'post'], '/redirects/settings', SettingsController::class)->name('redirects.settings');
        Route::get('/redirects/suggestions', SuggestionsIndexController::class)->name('redirects.suggestions.index');
        Route::post('/redirects/suggestions/{suggestion}/approve', SuggestionApproveController::class)->name('redirects.suggestions.approve');
        Route::post('/redirects/suggestions/{suggestion}/reject', SuggestionRejectController::class)->name('redirects.suggestions.reject');
    });
});
