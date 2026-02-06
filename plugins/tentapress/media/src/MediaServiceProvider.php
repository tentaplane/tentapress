<?php

declare(strict_types=1);

namespace TentaPress\Media;

use TentaPress\Settings\Services\SettingsStore;
use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Stock\StockManager;
use TentaPress\Media\Stock\StockSourceRegistry;
use TentaPress\Media\Support\LocalMediaUrlGenerator;
use TentaPress\Media\Support\NullMediaUrlGenerator;

final class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->bound(MediaUrlGenerator::class)) {
            return;
        }

        $this->app->singleton(MediaUrlGenerator::class, function () {
            $driver = (string) config('tentapress.media.url_driver', 'local');

            return match ($driver) {
                'local' => new LocalMediaUrlGenerator(),
                'null' => new NullMediaUrlGenerator(),
                default => new LocalMediaUrlGenerator(),
            };
        });

        if (class_exists(SettingsStore::class)) {
            if (! $this->app->bound(StockSourceRegistry::class)) {
                $this->app->singleton(StockSourceRegistry::class, fn () => new StockSourceRegistry());
            }

            if (! $this->app->bound(StockManager::class)) {
                $this->app->singleton(StockManager::class, fn ($app) => new StockManager($app->make(StockSourceRegistry::class)));
            }
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-media');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
    }
}
