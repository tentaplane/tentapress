<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Taxonomies\Support\BuiltinTaxonomies;
use TentaPress\Taxonomies\Support\TermAssignmentManager;
use TentaPress\Taxonomies\Support\TaxonomyRegistry;
use TentaPress\Taxonomies\Support\TaxonomySynchronizer;

final class TaxonomiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TaxonomyRegistry::class);
        $this->app->singleton(TaxonomySynchronizer::class);
        $this->app->singleton(TermAssignmentManager::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-taxonomies');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        $this->app->booted(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        BuiltinTaxonomies::register($this->app->make(TaxonomyRegistry::class));
        $this->registerContentAssignmentHooks();

        $this->app->booted(function (): void {
            if (! Schema::hasTable('tp_taxonomies')) {
                return;
            }

            $this->app->make(TaxonomySynchronizer::class)->syncRegistered();
        });
    }

    private function registerContentAssignmentHooks(): void
    {
        if (class_exists(TpPost::class)) {
            TpPost::saving(function (): void {
                $request = request();

                if (! $request instanceof Request) {
                    return;
                }

                if (! $request->routeIs('tp.posts.store') && ! $request->routeIs('tp.posts.update')) {
                    return;
                }

                $this->app->make(TermAssignmentManager::class)->validateAndRememberAssignments($request);
            });

            TpPost::saved(function ($post): void {
                $request = request();

                if (! $request instanceof Request) {
                    return;
                }

                if (! $request->routeIs('tp.posts.store') && ! $request->routeIs('tp.posts.update')) {
                    return;
                }

                $postId = (int) ($post->id ?? 0);
                if ($postId <= 0) {
                    return;
                }

                $this->app->make(TermAssignmentManager::class)->syncRememberedAssignments($request, TpPost::class, $postId);
            });
        }

        if (class_exists(TpPage::class)) {
            TpPage::saving(function (): void {
                $request = request();

                if (! $request instanceof Request) {
                    return;
                }

                if (! $request->routeIs('tp.pages.store') && ! $request->routeIs('tp.pages.update')) {
                    return;
                }

                $this->app->make(TermAssignmentManager::class)->validateAndRememberAssignments($request);
            });

            TpPage::saved(function ($page): void {
                $request = request();

                if (! $request instanceof Request) {
                    return;
                }

                if (! $request->routeIs('tp.pages.store') && ! $request->routeIs('tp.pages.update')) {
                    return;
                }

                $pageId = (int) ($page->id ?? 0);
                if ($pageId <= 0) {
                    return;
                }

                $this->app->make(TermAssignmentManager::class)->syncRememberedAssignments($request, TpPage::class, $pageId);
            });
        }
    }
}
