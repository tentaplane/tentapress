<?php

declare(strict_types=1);

namespace TentaPress\Media;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Stock\StockManager;
use TentaPress\Media\Stock\StockSettings;
use TentaPress\Media\Support\LocalMediaUrlGenerator;
use TentaPress\Media\Support\NullMediaUrlGenerator;
use TentaPress\Settings\Services\SettingsStore;

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
            if (! $this->app->bound(StockSettings::class)) {
                $this->app->singleton(StockSettings::class, fn ($app) => new StockSettings($app->make(SettingsStore::class)));
            }

            if (! $this->app->bound(StockManager::class)) {
                $this->app->singleton(StockManager::class, fn ($app) => new StockManager($app->make(StockSettings::class)));
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
