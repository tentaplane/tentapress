<?php

declare(strict_types=1);

namespace TentaPress\Boilerplate;

use Illuminate\Support\ServiceProvider;
use TentaPress\Boilerplate\Console\BoilerplateCheckCommand;
use TentaPress\Boilerplate\Services\BoilerplateCapabilitySeeder;
use TentaPress\Boilerplate\Services\BoilerplateSettings;

final class BoilerplateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BoilerplateSettings::class);
        $this->app->singleton(BoilerplateCapabilitySeeder::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-boilerplate');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        $this->app->booted(function (): void {
            $this->app->make(BoilerplateCapabilitySeeder::class)->run();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                BoilerplateCheckCommand::class,
            ]);
        }
    }
}
