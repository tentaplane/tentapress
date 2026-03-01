<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use TentaPress\Taxonomies\Support\BuiltinTaxonomies;
use TentaPress\Taxonomies\Support\TaxonomyRegistry;
use TentaPress\Taxonomies\Support\TaxonomySynchronizer;

final class TaxonomiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TaxonomyRegistry::class);
        $this->app->singleton(TaxonomySynchronizer::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        BuiltinTaxonomies::register($this->app->make(TaxonomyRegistry::class));

        $this->app->booted(function (): void {
            if (! Schema::hasTable('tp_taxonomies')) {
                return;
            }

            $this->app->make(TaxonomySynchronizer::class)->syncRegistered();
        });
    }
}
