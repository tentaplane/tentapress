<?php

declare(strict_types=1);

namespace TentaPress\Seo;

use Illuminate\Support\ServiceProvider;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Seo\Services\SeoManager;
use TentaPress\Seo\Services\SeoEntitySaver;
use TentaPress\Seo\Services\SeoPageSaver;
use TentaPress\Seo\Services\SeoPostSaver;
use TentaPress\Seo\Services\SeoSettings;

final class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SeoSettings::class);
        $this->app->singleton(SeoManager::class);
        $this->app->singleton(SeoEntitySaver::class);
        $this->app->singleton(SeoPageSaver::class);
        $this->app->singleton(SeoPostSaver::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tentapress-seo');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        // Integrate SEO fields into the Pages editor save flow without modifying Pages controllers.
        if (class_exists(TpPage::class)) {
            TpPage::saved(function ($page): void {
                if (app()->runningInConsole()) {
                    return;
                }

                $request = request();

                // Only act for the Pages create/update routes.
                if (!$request->routeIs('tp.pages.store') && !$request->routeIs('tp.pages.update')) {
                    return;
                }

                $pageId = (int) ($page->id ?? 0);
                if ($pageId <= 0) {
                    return;
                }

                resolve(SeoPageSaver::class)->syncFromRequest($pageId, $request);
            });
        }

        if (class_exists(TpPost::class)) {
            TpPost::saved(function ($post): void {
                if (app()->runningInConsole()) {
                    return;
                }

                $request = request();

                if (!$request->routeIs('tp.posts.store') && !$request->routeIs('tp.posts.update')) {
                    return;
                }

                $postId = (int) ($post->id ?? 0);
                if ($postId <= 0) {
                    return;
                }

                resolve(SeoPostSaver::class)->syncFromRequest($postId, $request);
            });
        }
    }
}
