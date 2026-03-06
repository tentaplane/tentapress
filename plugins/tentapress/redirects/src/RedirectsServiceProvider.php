<?php

declare(strict_types=1);

namespace TentaPress\Redirects;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use TentaPress\Redirects\Console\ImportRedirectMappingsCommand;
use TentaPress\Redirects\Http\Middleware\HandleRedirectsMiddleware;
use TentaPress\Redirects\Services\RedirectAuditLogger;
use TentaPress\Redirects\Services\RedirectChainValidator;
use TentaPress\Redirects\Services\RedirectManager;
use TentaPress\Redirects\Services\RedirectPathNormalizer;
use TentaPress\Redirects\Services\RedirectRouteConflictChecker;
use TentaPress\Redirects\Services\SlugRedirector;

final class RedirectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RedirectPathNormalizer::class);
        $this->app->singleton(RedirectRouteConflictChecker::class);
        $this->app->singleton(RedirectChainValidator::class);
        $this->app->singleton(RedirectAuditLogger::class);
        $this->app->singleton(RedirectManager::class);
        $this->app->singleton(SlugRedirector::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-redirects');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', HandleRedirectsMiddleware::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportRedirectMappingsCommand::class,
            ]);
        }
    }
}
