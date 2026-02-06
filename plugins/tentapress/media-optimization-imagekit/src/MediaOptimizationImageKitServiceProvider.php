<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationImageKit;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Optimization\OptimizationProviderRegistry;
use TentaPress\MediaOptimizationImageKit\Optimization\ImageKitOptimizationProvider;
use TentaPress\Settings\Services\SettingsStore;

final class MediaOptimizationImageKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! class_exists(SettingsStore::class)) {
            return;
        }

        if (! $this->app->bound(ImageKitOptimizationProvider::class)) {
            $this->app->singleton(ImageKitOptimizationProvider::class, fn ($app) => new ImageKitOptimizationProvider(
                $app->make(SettingsStore::class),
            ));
        }

        if ($this->app->bound(OptimizationProviderRegistry::class)) {
            $this->app->afterResolving(OptimizationProviderRegistry::class, function (OptimizationProviderRegistry $registry): void {
                $registry->register($this->app->make(ImageKitOptimizationProvider::class));
            });
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-media-optimization-imagekit');
    }
}
