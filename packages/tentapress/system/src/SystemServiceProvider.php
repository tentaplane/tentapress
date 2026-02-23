<?php

declare(strict_types=1);

namespace TentaPress\System;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use TentaPress\System\Console\PluginsCommand;
use TentaPress\System\Console\SeedDemoHomeCommand;
use TentaPress\System\Console\ThemesCommand;
use TentaPress\System\Editor\EditorDriverDefinition;
use TentaPress\System\Editor\EditorDriverRegistry;
use TentaPress\System\Http\AdminAuthMiddleware;
use TentaPress\System\Http\AdminErrorPagesMiddleware;
use TentaPress\System\Http\AdminMiddleware;
use TentaPress\System\Http\CanMiddleware;
use TentaPress\System\Http\SecurityHeadersMiddleware;
use TentaPress\System\Plugin\PluginAssetPublisher;
use TentaPress\System\Plugin\PluginAssetRegistry;
use TentaPress\System\Plugin\PluginManager;
use TentaPress\System\Plugin\PluginRegistry;
use TentaPress\System\Support\RuntimeCacheRefresher;
use TentaPress\System\Theme\ThemeManager;
use TentaPress\System\Theme\ThemeRegistry;

final class SystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PluginRegistry::class);
        $this->app->singleton(PluginManager::class);
        $this->app->singleton(PluginAssetRegistry::class);
        $this->app->singleton(PluginAssetPublisher::class);
        $this->app->singleton(RuntimeCacheRefresher::class);
        $this->app->singleton(AdminMiddleware::class);
        $this->app->singleton(EditorDriverRegistry::class);

        $this->app->singleton(ThemeRegistry::class);
        $this->app->singleton(ThemeManager::class);
        $this->registerDefaultEditorDrivers();
        $this->app->make(PluginManager::class)->registerEnabledPluginProviders();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->make(Router::class)->aliasMiddleware('tp.auth', AdminAuthMiddleware::class);
        $this->app->make(Router::class)->aliasMiddleware('tp.can', CanMiddleware::class);
        $this->app->make(Router::class)->aliasMiddleware('tp.admin.errors', AdminErrorPagesMiddleware::class);
        $this->app->make(Router::class)->aliasMiddleware('tp.security.headers', SecurityHeadersMiddleware::class);

        $this->app->make(AdminMiddleware::class)->ensureGroup();

        // Register active theme views early.
        $this->app->make(ThemeManager::class)->registerActiveThemeViews();
        $this->app->make(ThemeManager::class)->registerActiveThemeProvider();

        if ($this->app->runningInConsole()) {
            $this->commands([
                PluginsCommand::class,
                ThemesCommand::class,
                SeedDemoHomeCommand::class,
            ]);
        }
    }

    private function registerDefaultEditorDrivers(): void
    {
        $registry = $this->app->make(EditorDriverRegistry::class);

        $registry->register(new EditorDriverDefinition(
            id: 'blocks',
            label: 'Blocks Builder',
            description: 'Structured sections and fields.',
            storage: 'blocks',
            usesBlocksEditor: true,
            sortOrder: 10,
        ));
    }
}
