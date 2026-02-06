<?php

declare(strict_types=1);

namespace TentaPress\MediaOptimizationCloudflare;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Optimization\OptimizationProviderRegistry;
use TentaPress\MediaOptimizationCloudflare\Optimization\CloudflareOptimizationProvider;
use TentaPress\Settings\Services\SettingsStore;

final class MediaOptimizationCloudflareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! class_exists(SettingsStore::class)) {
            return;
        }

        if (! $this->app->bound(CloudflareOptimizationProvider::class)) {
            $this->app->singleton(CloudflareOptimizationProvider::class, fn ($app) => new CloudflareOptimizationProvider(
                $app->make(SettingsStore::class),
            ));
        }

        if ($this->app->bound(OptimizationProviderRegistry::class)) {
            $this->app->afterResolving(OptimizationProviderRegistry::class, function (OptimizationProviderRegistry $registry): void {
                $registry->register($this->app->make(CloudflareOptimizationProvider::class));
            });
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-media-optimization-cloudflare');
    }
}
