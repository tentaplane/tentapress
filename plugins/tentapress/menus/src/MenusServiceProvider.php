<?php

declare(strict_types=1);

namespace TentaPress\Menus;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use TentaPress\Menus\Services\MenuEditorSaver;
use TentaPress\Menus\Services\MenuRenderer;
use TentaPress\Menus\Services\MenuSlugger;
use TentaPress\Menus\Services\ThemeMenuLocations;

final class MenusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeMenuLocations::class);
        $this->app->singleton(MenuRenderer::class);
        $this->app->singleton(MenuSlugger::class);
        $this->app->singleton(MenuEditorSaver::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-menus');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        View::share('tpMenus', $this->app->make(MenuRenderer::class));
        View::share('tpMenuLocations', $this->app->make(ThemeMenuLocations::class)->all());
    }
}
