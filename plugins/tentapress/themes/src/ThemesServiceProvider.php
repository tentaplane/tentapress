<?php

declare(strict_types=1);

namespace TentaPress\Themes;

use Illuminate\Support\ServiceProvider;

final class ThemesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-themes');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
    }
}
