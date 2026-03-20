<?php

declare(strict_types=1);

namespace TentaPress\Posts;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use TentaPress\Posts\ContentReference\PostContentReferenceSource;
use TentaPress\Posts\Console\PublishScheduledPostsCommand;
use TentaPress\Posts\Services\PostRenderer;
use TentaPress\Posts\Services\PostSlugger;
use TentaPress\Posts\Support\BlocksNormalizer;
use TentaPress\System\ContentReference\ContentReferenceRegistry;

final class PostsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostSlugger::class);
        $this->app->singleton(PostRenderer::class);
        $this->app->singleton(BlocksNormalizer::class);
        $this->app->singleton(PostContentReferenceSource::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tentapress-posts');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        $this->app->booted(function (): void {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });

        $this->app->booted(function (): void {
            if (! $this->app->bound(ContentReferenceRegistry::class)) {
                return;
            }

            $this->app->make(ContentReferenceRegistry::class)->register(
                $this->app->make(PostContentReferenceSource::class)
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishScheduledPostsCommand::class,
            ]);

            $this->app->booted(function (): void {
                $this->app->make(Schedule::class)
                    ->command('tp:posts:publish-scheduled')
                    ->everyMinute()
                    ->withoutOverlapping();
            });
        }
    }
}
