<?php

declare(strict_types=1);

namespace TentaPress\PluginBoilerplate;

use Illuminate\Support\ServiceProvider;
use TentaPress\PluginBoilerplate\Console\PluginBoilerplateCheckCommand;
use TentaPress\PluginBoilerplate\Services\PluginBoilerplateCapabilitySeeder;
use TentaPress\PluginBoilerplate\Services\PluginBoilerplateSettings;

final class PluginBoilerplateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PluginBoilerplateSettings::class);
        $this->app->singleton(PluginBoilerplateCapabilitySeeder::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-plugin-boilerplate');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        $this->app->booted(function (): void {
            $this->app->make(PluginBoilerplateCapabilitySeeder::class)->run();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                PluginBoilerplateCheckCommand::class,
            ]);
        }
    }
}
