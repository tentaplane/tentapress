<?php

declare(strict_types=1);

namespace TentaPress\MediaStockPexels;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Stock\StockSettings;
use TentaPress\Media\Stock\StockSourceRegistry;
use TentaPress\MediaStockPexels\Http\Admin\SettingsController;
use TentaPress\MediaStockPexels\Stock\PexelsSource;
use TentaPress\Settings\Services\SettingsStore;

final class MediaStockPexelsServiceProvider extends ServiceProvider
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
            $registry->register($this->app->make(PexelsSource::class));
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-media-stock-pexels');
    }
}
