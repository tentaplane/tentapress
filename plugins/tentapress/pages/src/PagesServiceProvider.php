<?php

declare(strict_types=1);

namespace TentaPress\Pages;

use Illuminate\Support\ServiceProvider;
use TentaPress\Pages\Services\PageRenderer;
use TentaPress\Pages\Services\PageSlugger;
use TentaPress\Pages\Support\BlocksNormalizer;

final class PagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PageSlugger::class);
        $this->app->singleton(PageRenderer::class);
        $this->app->singleton(BlocksNormalizer::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-pages');

        // Admin routes load normally.
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        // IMPORTANT: Frontend catch-all route must register after the app has booted
        // to reduce the chance it captures routes defined by other plugins.
        $this->app->booted(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}
