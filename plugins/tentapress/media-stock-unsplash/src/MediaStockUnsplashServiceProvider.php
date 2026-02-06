<?php

declare(strict_types=1);

namespace TentaPress\MediaStockUnsplash;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Stock\StockSettings;
use TentaPress\Media\Stock\StockSourceRegistry;
use TentaPress\MediaStockUnsplash\Http\Admin\SettingsController;
use TentaPress\MediaStockUnsplash\Stock\UnsplashSource;
use TentaPress\Settings\Services\SettingsStore;

final class MediaStockUnsplashServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! class_exists(SettingsStore::class)) {
            return;
        }

        if (! $this->app->bound(StockSettings::class)) {
            $this->app->singleton(StockSettings::class, fn ($app) => new StockSettings($app->make(SettingsStore::class)));
        }
        $this->app->singleton(SettingsController::class);

        $this->app->afterResolving(StockSourceRegistry::class, function (StockSourceRegistry $registry): void {
            $registry->register($this->app->make(UnsplashSource::class));
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-media-stock-unsplash');
    }
}
