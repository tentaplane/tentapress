<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TentaPress\System\Support\AdminRoutes;

AdminRoutes::group(function (): void {
    Route::middleware('tp.can:manage_global_content')->group(function (): void {
        Route::get('/global-content', 'TentaPress\\GlobalContent\\Http\\Admin\\IndexController@__invoke')->name('global-content.index');
        Route::get('/global-content/new', 'TentaPress\\GlobalContent\\Http\\Admin\\CreateController@__invoke')->name('global-content.create');
        Route::post('/global-content', 'TentaPress\\GlobalContent\\Http\\Admin\\StoreController@__invoke')->name('global-content.store');
        Route::get('/global-content/library', 'TentaPress\\GlobalContent\\Http\\Admin\\LibraryController@__invoke')->name('global-content.library');
        Route::post('/global-content/detach', 'TentaPress\\GlobalContent\\Http\\Admin\\DetachController@__invoke')->name('global-content.detach');
        Route::get('/global-content/{globalContent}/edit', 'TentaPress\\GlobalContent\\Http\\Admin\\EditController@__invoke')->name('global-content.edit');
        Route::get('/global-content/{globalContent}/editor', 'TentaPress\\GlobalContent\\Http\\Admin\\EditorController@__invoke')->name('global-content.editor');
        Route::put('/global-content/{globalContent}', 'TentaPress\\GlobalContent\\Http\\Admin\\UpdateController@__invoke')->name('global-content.update');
        Route::delete('/global-content/{globalContent}', 'TentaPress\\GlobalContent\\Http\\Admin\\DestroyController@__invoke')->name('global-content.destroy');
    });
});
