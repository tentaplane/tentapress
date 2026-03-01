<?php

declare(strict_types=1);

namespace TentaPress\Revisions;

use Illuminate\Support\ServiceProvider;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Services\RevisionHistory;
use TentaPress\Revisions\Services\RevisionRecorder;

final class RevisionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RevisionRecorder::class);
        $this->app->singleton(RevisionHistory::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-revisions');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        if (class_exists(TpPage::class)) {
            TpPage::saved(function (TpPage $page): void {
                if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                    return;
                }

                $request = request();

                if (! $request->routeIs('tp.pages.store') && ! $request->routeIs('tp.pages.update')) {
                    return;
                }

                resolve(RevisionRecorder::class)->capturePage($page);
            });
        }

        if (class_exists(TpPost::class)) {
            TpPost::saved(function (TpPost $post): void {
                if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                    return;
                }

                $request = request();

                if (! $request->routeIs('tp.posts.store') && ! $request->routeIs('tp.posts.update')) {
                    return;
                }

                resolve(RevisionRecorder::class)->capturePost($post);
            });
        }
    }
}
