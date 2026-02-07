<?php

declare(strict_types=1);

namespace TentaPress\System;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use TentaPress\System\Console\PluginsCommand;
use TentaPress\System\Console\SeedDemoHomeCommand;
use TentaPress\System\Console\ThemesCommand;
use TentaPress\System\Http\AdminAuthMiddleware;
use TentaPress\System\Http\AdminErrorPagesMiddleware;
use TentaPress\System\Http\AdminMiddleware;
use TentaPress\System\Http\CanMiddleware;
use TentaPress\System\Plugin\PluginAssetPublisher;
use TentaPress\System\Plugin\PluginAssetRegistry;
use TentaPress\System\Plugin\PluginManager;
use TentaPress\System\Plugin\PluginRegistry;
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
        $this->app->singleton(AdminMiddleware::class);

        $this->app->singleton(ThemeRegistry::class);
        $this->app->singleton(ThemeManager::class);

        $this->app->afterResolving(PluginManager::class, function (PluginManager $manager): void {
            $manager->registerEnabledPluginProviders();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());

        RateLimiter::for('tp-login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email', ''));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        $this->app->make(Router::class)->aliasMiddleware('tp.auth', AdminAuthMiddleware::class);
        $this->app->make(Router::class)->aliasMiddleware('tp.can', CanMiddleware::class);
        $this->app->make(Router::class)->aliasMiddleware('tp.admin.errors', AdminErrorPagesMiddleware::class);

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
}
