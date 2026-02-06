<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationImgix;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Optimization\OptimizationProviderRegistry;
use TentaPress\MediaOptimizationImgix\Optimization\ImgixOptimizationProvider;
use TentaPress\Settings\Services\SettingsStore;

final class MediaOptimizationImgixServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! class_exists(SettingsStore::class)) {
            return;
        }

        if (! $this->app->bound(ImgixOptimizationProvider::class)) {
            $this->app->singleton(ImgixOptimizationProvider::class, fn ($app) => new ImgixOptimizationProvider(
                $app->make(SettingsStore::class),
            ));
        }

        if ($this->app->bound(OptimizationProviderRegistry::class)) {
            $this->app->afterResolving(OptimizationProviderRegistry::class, function (OptimizationProviderRegistry $registry): void {
                $registry->register($this->app->make(ImgixOptimizationProvider::class));
            });
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-media-optimization-imgix');
    }
}
