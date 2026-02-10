<?php

declare(strict_types=1);

namespace TentaPress\Media;

use Illuminate\Support\ServiceProvider;
use TentaPress\Media\Console\BackfillMediaVariantsCommand;
use TentaPress\Media\Console\OptimizeMediaCommand;
use TentaPress\Media\Console\VerifyMediaVariantsCommand;
use TentaPress\Media\Contracts\MediaUrlGenerator;
use TentaPress\Media\Optimization\OptimizationManager;
use TentaPress\Media\Optimization\OptimizationProviderRegistry;
use TentaPress\Media\Optimization\OptimizationSettings;
use TentaPress\Media\Stock\StockManager;
use TentaPress\Media\Stock\StockSourceRegistry;
use TentaPress\Media\Support\LocalMediaUrlGenerator;
use TentaPress\Media\Support\MediaReferenceResolver;
use TentaPress\Media\Support\NullMediaUrlGenerator;
use TentaPress\Media\Support\OptimizedMediaUrlGenerator;
use TentaPress\Settings\Services\SettingsStore;

final class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->bound(MediaUrlGenerator::class)) {
            return;
        }

        if (class_exists(SettingsStore::class)) {
            if (! $this->app->bound(StockSourceRegistry::class)) {
                $this->app->singleton(StockSourceRegistry::class, fn () => new StockSourceRegistry());
            }

            if (! $this->app->bound(StockManager::class)) {
                $this->app->singleton(StockManager::class, fn ($app) => new StockManager($app->make(StockSourceRegistry::class)));
            }

            if (! $this->app->bound(OptimizationProviderRegistry::class)) {
                $this->app->singleton(OptimizationProviderRegistry::class, fn () => new OptimizationProviderRegistry());
            }

            if (! $this->app->bound(OptimizationSettings::class)) {
                $this->app->singleton(OptimizationSettings::class, fn ($app) => new OptimizationSettings($app->make(SettingsStore::class)));
            }

            if (! $this->app->bound(OptimizationManager::class)) {
                $this->app->singleton(OptimizationManager::class, fn ($app) => new OptimizationManager(
                    $app->make(OptimizationProviderRegistry::class),
                    $app->make(OptimizationSettings::class),
                ));
            }
        }

        $this->app->singleton(MediaUrlGenerator::class, function () {
            $driver = (string) config('tentapress.media.url_driver', 'local');
            $generator = match ($driver) {
                'local' => new LocalMediaUrlGenerator(),
                'null' => new NullMediaUrlGenerator(),
                default => new LocalMediaUrlGenerator(),
            };

            if ($this->app->bound(OptimizationManager::class)) {
                return new OptimizedMediaUrlGenerator($generator, $this->app->make(OptimizationManager::class));
            }

            return $generator;
        });

        if (! $this->app->bound(MediaReferenceResolver::class)) {
            $this->app->singleton(MediaReferenceResolver::class, fn ($app) => new MediaReferenceResolver(
                $app->make(MediaUrlGenerator::class),
            ));
        }

        if (! $this->app->bound('tp.media.reference_resolver')) {
            $this->app->alias(MediaReferenceResolver::class, 'tp.media.reference_resolver');
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-media');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                OptimizeMediaCommand::class,
                BackfillMediaVariantsCommand::class,
                VerifyMediaVariantsCommand::class,
            ]);
        }
    }
}
