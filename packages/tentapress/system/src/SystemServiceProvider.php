<?php

declare(strict_types=1);

namespace TentaPress\System;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Livewire\Blaze\Blaze;
use TentaPress\System\Console\PluginsCommand;
use TentaPress\System\Console\SeedDemoHomeCommand;
use TentaPress\System\Console\ThemesCommand;
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

        $this->app->singleton(ThemeRegistry::class);
        $this->app->singleton(ThemeManager::class);
        $this->app->make(PluginManager::class)->registerEnabledPluginProviders();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->configureBlaze();

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

    private function configureBlaze(): void
    {
        if (! class_exists(Blaze::class)) {
            return;
        }

        $enabled = (bool) config('tentapress.blaze.enabled', false);
        if (! $enabled) {
            Blaze::disable();

            return;
        }

        Blaze::enable();

        $optimizer = Blaze::optimize();
        $configuredPaths = $this->resolvedBlazePaths();

        foreach ($configuredPaths as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $path = $entry['path'] ?? null;
            if (! is_string($path) || $path === '' || ! is_dir($path)) {
                continue;
            }

            $optimizer->in(
                path: $path,
                compile: (bool) ($entry['compile'] ?? true),
                memo: (bool) ($entry['memo'] ?? false),
                fold: (bool) ($entry['fold'] ?? false),
            );
        }
    }

    /**
     * @return array<int,array{path:string,compile:bool,memo:bool,fold:bool}>
     */
    private function resolvedBlazePaths(): array
    {
        $resolved = [];

        $activeTheme = $this->app->make(ThemeManager::class)->activeTheme();
        $activeThemePath = (string) ($activeTheme['path'] ?? '');
        if ($activeThemePath !== '') {
            $resolved[] = [
                'path' => base_path('themes/'.$activeThemePath.'/views/components'),
                'compile' => (bool) config('tentapress.blaze.active_theme_components.compile', true),
                'memo' => (bool) config('tentapress.blaze.active_theme_components.memo', false),
                'fold' => (bool) config('tentapress.blaze.active_theme_components.fold', false),
            ];
        }

        $explicitPaths = config('tentapress.blaze.paths', []);
        if (is_array($explicitPaths)) {
            foreach ($explicitPaths as $path) {
                if (is_array($path)) {
                    $resolved[] = $path;
                }
            }
        }

        return $resolved;
    }
}
