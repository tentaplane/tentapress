<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy;

use Illuminate\Support\ServiceProvider;
use TentaPress\StaticDeploy\Services\StaticExporter;
use TentaPress\StaticDeploy\Support\StaticReplacementRules;

final class StaticDeployServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StaticExporter::class);
        $this->app->singleton(StaticReplacementRules::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tentapress-static-deploy');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
    }
}
