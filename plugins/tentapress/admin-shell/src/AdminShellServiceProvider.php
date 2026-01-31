<?php

declare(strict_types=1);

namespace TentaPress\AdminShell;

use Illuminate\Support\ServiceProvider;
use TentaPress\AdminShell\Admin\Menu\MenuBuilder;
use TentaPress\AdminShell\Admin\Menu\MenuBuilderContract;

final class AdminShellServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MenuBuilderContract::class, MenuBuilder::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-admin');

        // Routes are grouped inside routes/admin.php using AdminRoutes::group(...)
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        // Inject menu + common admin view data into the shell layout.
        view()->composer('tentapress-admin::layouts.shell', function ($view): void {
            $menus = $this->app->make(MenuBuilderContract::class)->build(auth()->user());

            $view->with('tpMenu', $menus);
        });
    }
}
