<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationBunny;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Optimization\OptimizationProviderRegistry;
use TentaPress\MediaOptimizationBunny\Optimization\BunnyOptimizationProvider;
use TentaPress\Settings\Services\SettingsStore;

final class MediaOptimizationBunnyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! class_exists(SettingsStore::class)) {
            return;
        }

        if (! $this->app->bound(BunnyOptimizationProvider::class)) {
            $this->app->singleton(BunnyOptimizationProvider::class, fn ($app) => new BunnyOptimizationProvider(
                $app->make(SettingsStore::class),
            ));
        }

        if ($this->app->bound(OptimizationProviderRegistry::class)) {
            $this->app->afterResolving(OptimizationProviderRegistry::class, function (OptimizationProviderRegistry $registry): void {
                $registry->register($this->app->make(BunnyOptimizationProvider::class));
            });
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-media-optimization-bunny');
    }
}
