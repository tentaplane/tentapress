<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo;

use Illuminate\Support\ServiceProvider;
use TentaPress\SystemInfo\Services\SystemInfoService;

final class SystemInfoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SystemInfoService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-system-info');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
    }
}
