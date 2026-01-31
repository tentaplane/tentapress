<?php

declare(strict_types=1);

namespace TentaPress\Settings;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use TentaPress\Settings\Services\SettingsStore;

final class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsStore::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-settings');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        // Laravel-native: make settings available to ALL views.
        // SettingsStore is request-cached, so this stays cheap.
        $settings = $this->app->make(SettingsStore::class);

        View::share('tpSiteTitle', (string) $settings->get('site.title', ''));
        View::share('tpTagline', (string) $settings->get('site.tagline', ''));
        View::share('tpBlogBase', (string) $settings->get('site.blog_base', ''));
    }
}
