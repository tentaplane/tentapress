<?php

declare(strict_types=1);

namespace TentaPress\Marketing;

use Illuminate\Support\ServiceProvider;
use TentaPress\Marketing\Providers\Ga4Provider;
use TentaPress\Marketing\Providers\PlausibleProvider;
use TentaPress\Marketing\Providers\RybbitProvider;
use TentaPress\Marketing\Providers\UmamiProvider;
use TentaPress\Marketing\Services\ConsentState;
use TentaPress\Marketing\Services\MarketingCapabilitySeeder;
use TentaPress\Marketing\Services\MarketingManager;
use TentaPress\Marketing\Services\MarketingProviderRegistry;
use TentaPress\Marketing\Services\MarketingSettings;
use TentaPress\Settings\Services\SettingsStore;

final class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! class_exists(SettingsStore::class)) {
            return;
        }

        $this->app->singleton(MarketingProviderRegistry::class, function (): MarketingProviderRegistry {
            $registry = new MarketingProviderRegistry();
            $registry->register(new Ga4Provider());
            $registry->register(new PlausibleProvider());
            $registry->register(new UmamiProvider());
            $registry->register(new RybbitProvider());

            return $registry;
        });
        $this->app->singleton(MarketingSettings::class, fn ($app) => new MarketingSettings($app->make(SettingsStore::class)));
        $this->app->singleton(ConsentState::class);
        $this->app->singleton(MarketingManager::class);
        $this->app->singleton(MarketingCapabilitySeeder::class);
    }

    public function boot(): void
    {
        if (! class_exists(SettingsStore::class)) {
            return;
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-marketing');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        $this->app->booted(function (): void {
            $this->app->make(MarketingCapabilitySeeder::class)->run();
        });
    }
}
